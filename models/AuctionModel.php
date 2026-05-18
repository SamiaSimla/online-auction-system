<?php
// ============================================
// models/AuctionModel.php
// All database queries for Student 4 tasks
// ============================================

require_once __DIR__ . '/../config/db1.php';

class AuctionModel {

    private PDO $pdo;

    public function __construct() {
        $this->pdo = getDBConnection();
    }

    // ------------------------------------------
    // AUCTION CLOSING: close all expired auctions
    // ------------------------------------------
    public function closeExpiredAuctions(): int {
        // Find all active auctions whose end_datetime has passed
        $stmt = $this->pdo->prepare("
            SELECT id, reserve_price
            FROM listings
            WHERE status = 'active'
              AND end_datetime <= NOW()
        ");
        $stmt->execute();
        $expired = $stmt->fetchAll();

        $closedCount = 0;

        foreach ($expired as $listing) {
            $listingId    = $listing['id'];
            $reservePrice = $listing['reserve_price'];

            // Find the highest bid for this listing
            $bidStmt = $this->pdo->prepare("
                SELECT id, amount
                FROM bids
                WHERE listing_id = :listing_id
                ORDER BY amount DESC, created_at ASC
                LIMIT 1
            ");
            $bidStmt->execute([':listing_id' => $listingId]);
            $highestBid = $bidStmt->fetch();

            if ($highestBid && (float)$highestBid['amount'] >= (float)$reservePrice) {
                // Winner found — set winner_bid_id, update current_bid, mark ended
                $updateStmt = $this->pdo->prepare("
                    UPDATE listings
                    SET status = 'ended',
                        winner_bid_id = :winner_bid_id,
                        current_bid   = :current_bid
                    WHERE id = :id
                ");
                $updateStmt->execute([
                    ':winner_bid_id' => $highestBid['id'],
                    ':current_bid'   => $highestBid['amount'],
                    ':id'            => $listingId,
                ]);
            } else {
                // No bids or reserve not met — end with no winner
                $updateStmt = $this->pdo->prepare("
                    UPDATE listings
                    SET status = 'ended', winner_bid_id = NULL
                    WHERE id = :id
                ");
                $updateStmt->execute([':id' => $listingId]);
            }

            $closedCount++;
        }

        return $closedCount;
    }

    // ------------------------------------------
    // WINNER INFO: get winner + seller contact for a listing
    // ------------------------------------------
    public function getWinnerInfo(int $listingId): array|false {
        $stmt = $this->pdo->prepare("
            SELECT
                l.id            AS listing_id,
                l.title,
                l.winner_bid_id,
                l.status,
                l.reserve_price,
                b.amount        AS winning_amount,
                u.id            AS winner_id,
                u.name          AS winner_name,
                u.email         AS winner_email,
                u.phone         AS winner_phone,
                s.id            AS seller_id,
                s.name          AS seller_name,
                s.email         AS seller_email,
                s.phone         AS seller_phone
            FROM listings l
            LEFT JOIN bids  b ON b.id  = l.winner_bid_id
            LEFT JOIN users u ON u.id  = b.buyer_id
            LEFT JOIN users s ON s.id  = l.seller_id
            WHERE l.id = :listing_id
        ");
        $stmt->execute([':listing_id' => $listingId]);
        return $stmt->fetch();
    }

    // ------------------------------------------
    // MY BIDS: all bids placed by a buyer
    // ------------------------------------------
    public function getBidsByBuyer(int $buyerId): array {
        $stmt = $this->pdo->prepare("
            SELECT
                b.id            AS bid_id,
                b.amount,
                b.created_at    AS bid_time,
                l.id            AS listing_id,
                l.title,
                l.status        AS auction_status,
                l.end_datetime,
                l.winner_bid_id,
                l.reserve_price,
                l.current_bid,
                l.image_path,
                l.seller_id,
                (SELECT MAX(bb.amount) FROM bids bb WHERE bb.listing_id = l.id) AS highest_bid,
                CASE
                    WHEN l.winner_bid_id = b.id THEN 'won'
                    WHEN l.status = 'ended' AND l.winner_bid_id IS NULL THEN 'reserve_not_met'
                    WHEN l.status = 'ended' AND l.winner_bid_id != b.id THEN 'lost'
                    WHEN l.status = 'active'
                         AND b.amount = (SELECT MAX(bb.amount) FROM bids bb WHERE bb.listing_id = l.id) THEN 'leading'
                    WHEN l.status = 'active' THEN 'outbid'
                    ELSE 'unknown'
                END AS bid_status
            FROM bids b
            JOIN listings l ON l.id = b.listing_id
            WHERE b.buyer_id = :buyer_id
            ORDER BY b.created_at DESC
        ");
        $stmt->execute([':buyer_id' => $buyerId]);
        return $stmt->fetchAll();
    }

    // ------------------------------------------
    // ADMIN ANALYTICS: summary counts
    // ------------------------------------------
    public function getAdminStats(): array {
        $stats = [];

        $stmt = $this->pdo->query("SELECT COUNT(*) FROM users WHERE role = 'buyer'");
        $stats['total_buyers'] = (int)$stmt->fetchColumn();

        $stmt = $this->pdo->query("SELECT COUNT(*) FROM users WHERE seller_verified = 1");
        $stats['total_sellers'] = (int)$stmt->fetchColumn();

        $stmt = $this->pdo->query("SELECT status, COUNT(*) AS cnt FROM listings GROUP BY status");
        $byStatus = $stmt->fetchAll();
        $stats['listings_active']    = 0;
        $stats['listings_ended']     = 0;
        $stats['listings_cancelled'] = 0;
        foreach ($byStatus as $row) {
            $stats['listings_' . $row['status']] = (int)$row['cnt'];
        }
        $stats['listings_total'] = $stats['listings_active'] + $stats['listings_ended'] + $stats['listings_cancelled'];

        $stmt = $this->pdo->query("SELECT COUNT(*) FROM bids");
        $stats['total_bids'] = (int)$stmt->fetchColumn();

        $stmt = $this->pdo->query("
            SELECT COALESCE(SUM(b.amount), 0)
            FROM listings l
            JOIN bids b ON b.id = l.winner_bid_id
            WHERE l.status = 'ended' AND l.winner_bid_id IS NOT NULL
        ");
        $stats['total_revenue'] = (float)$stmt->fetchColumn();

        $stmt = $this->pdo->query("
            SELECT COUNT(*) FROM listings
            WHERE status = 'ended' AND winner_bid_id IS NULL
        ");
        $stats['reserve_not_met'] = (int)$stmt->fetchColumn();

        return $stats;
    }

    // ------------------------------------------
    // ADMIN: listings per category (for chart)
    // ------------------------------------------
    public function getListingsPerCategory(): array {
        $stmt = $this->pdo->query("
            SELECT c.name AS category, COUNT(l.id) AS total
            FROM categories c
            LEFT JOIN listings l ON l.category_id = c.id
            GROUP BY c.id, c.name
            ORDER BY total DESC
        ");
        return $stmt->fetchAll();
    }

    // ------------------------------------------
    // ADMIN: bids over last 7 days (for chart)
    // ------------------------------------------
    public function getBidsLast7Days(): array {
        $stmt = $this->pdo->query("
            SELECT
                DATE(created_at) AS day,
                COUNT(*)         AS total_bids
            FROM bids
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            GROUP BY DATE(created_at)
            ORDER BY day ASC
        ");
        return $stmt->fetchAll();
    }

    // ------------------------------------------
    // ADMIN: revenue per category (for chart)
    // ------------------------------------------
    public function getRevenuePerCategory(): array {
        $stmt = $this->pdo->query("
            SELECT
                c.name AS category,
                COALESCE(SUM(b.amount), 0) AS revenue
            FROM categories c
            LEFT JOIN listings l ON l.category_id = c.id
                AND l.status = 'ended'
                AND l.winner_bid_id IS NOT NULL
            LEFT JOIN bids b ON b.id = l.winner_bid_id
            GROUP BY c.id, c.name
            ORDER BY revenue DESC
        ");
        return $stmt->fetchAll();
    }

    // ------------------------------------------
    // ADMIN: recent ended auctions table
    // ------------------------------------------
    public function getRecentEndedAuctions(int $limit = 10): array {
        $stmt = $this->pdo->prepare("
            SELECT
                l.id,
                l.title,
                l.end_datetime,
                l.reserve_price,
                l.winner_bid_id,
                b.amount        AS winning_amount,
                u.name          AS winner_name,
                s.name          AS seller_name,
                c.name          AS category
            FROM listings l
            LEFT JOIN bids       b ON b.id  = l.winner_bid_id
            LEFT JOIN users      u ON u.id  = b.buyer_id
            LEFT JOIN users      s ON s.id  = l.seller_id
            LEFT JOIN categories c ON c.id  = l.category_id
            WHERE l.status = 'ended'
            ORDER BY l.end_datetime DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ------------------------------------------
    // ADMIN: top sellers by revenue
    // ------------------------------------------
    public function getTopSellers(int $limit = 5): array {
        $stmt = $this->pdo->prepare("
            SELECT
                u.name,
                COUNT(l.id)                AS total_auctions,
                COALESCE(SUM(b.amount), 0) AS total_revenue
            FROM users u
            JOIN listings l ON l.seller_id = u.id
                AND l.status = 'ended'
                AND l.winner_bid_id IS NOT NULL
            JOIN bids b ON b.id = l.winner_bid_id
            GROUP BY u.id, u.name
            ORDER BY total_revenue DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ------------------------------------------
    // ADMIN: new user registrations last 7 days
    // ------------------------------------------
    public function getNewUsersLast7Days(): array {
        $stmt = $this->pdo->query("
            SELECT
                DATE(created_at) AS day,
                COUNT(*)         AS new_users
            FROM users
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            GROUP BY DATE(created_at)
            ORDER BY day ASC
        ");
        return $stmt->fetchAll();
    }

    // ------------------------------------------
    // SELLER DASHBOARD: listings owned by seller
    // ------------------------------------------
    public function getListingsBySeller(int $sellerId): array {
        $stmt = $this->pdo->prepare("
            SELECT l.*, c.name AS category,
                   (SELECT COUNT(*) FROM bids b WHERE b.listing_id = l.id) AS bid_count,
                   (SELECT MAX(b.amount) FROM bids b WHERE b.listing_id = l.id) AS highest_bid
            FROM listings l
            LEFT JOIN categories c ON c.id = l.category_id
            WHERE l.seller_id = :seller_id
            ORDER BY l.created_at DESC
        ");
        $stmt->execute([':seller_id' => $sellerId]);
        return $stmt->fetchAll();
    }
}
?>