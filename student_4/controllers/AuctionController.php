<?php
// ============================================
// controllers/AuctionController.php
// Handles all Student 4 actions
// ============================================

require_once __DIR__ . '/../models/AuctionModel.php';

class AuctionController {

    private AuctionModel $model;

    public function __construct() {
        $this->model = new AuctionModel();
    }

    // ------------------------------------------
    // Called by a cron job or on page load
    // Closes all expired auctions automatically
    // ------------------------------------------
    public function closeExpiredAuctions(): void {
        $count = $this->model->closeExpiredAuctions();
        echo "Closed $count expired auction(s).\n";
    }

    // ------------------------------------------
    // Admin analytics dashboard data
    // ------------------------------------------
    public function adminDashboard(): array {
        // Auto-close expired auctions every time admin views dashboard
        $this->model->closeExpiredAuctions();

        return [
            'stats'            => $this->model->getAdminStats(),
            'listings_by_cat'  => $this->model->getListingsPerCategory(),
            'bids_last_7'      => $this->model->getBidsLast7Days(),
            'revenue_by_cat'   => $this->model->getRevenuePerCategory(),
            'recent_auctions'  => $this->model->getRecentEndedAuctions(10),
            'top_sellers'      => $this->model->getTopSellers(5),
            'new_users_last_7' => $this->model->getNewUsersLast7Days(),
        ];
    }

    // ------------------------------------------
    // My Bids page for a logged-in buyer
    // ------------------------------------------
    public function myBids(int $buyerId): array {
        return $this->model->getBidsByBuyer($buyerId);
    }

    // ------------------------------------------
    // Winner/Seller contact info for a listing
    // ------------------------------------------
    public function getContactInfo(int $listingId): array|false {
        return $this->model->getWinnerInfo($listingId);
    }
}
