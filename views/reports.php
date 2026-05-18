<?php
// views/reports.php — Admin: summary report with CSV export
session_start();
require_once __DIR__ . '/../config/db1.php';
$pdo = getDBConnection();

// ---- CSV Export ----
if (isset($_GET['export'])) {
    $type = $_GET['export'];
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $type . '_report_' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');

    if ($type === 'auctions') {
        fputcsv($out, ['ID','Title','Category','Seller','Start Price','Reserve','Current Bid','Bids','Status','End Date','Winner','Winning Amount']);
        $rows = $pdo->query("
            SELECT l.id, l.title, c.name AS cat, u.name AS seller,
                   l.starting_price, l.reserve_price, l.current_bid,
                   (SELECT COUNT(*) FROM bids b WHERE b.listing_id = l.id) AS bid_count,
                   l.status, l.end_datetime,
                   w.name AS winner, wb.amount AS win_amount
            FROM listings l
            LEFT JOIN categories c ON c.id = l.category_id
            LEFT JOIN users u ON u.id = l.seller_id
            LEFT JOIN bids  wb ON wb.id = l.winner_bid_id
            LEFT JOIN users w  ON w.id  = wb.buyer_id
            ORDER BY l.id DESC
        ")->fetchAll();
        foreach ($rows as $r) fputcsv($out, $r);

    } elseif ($type === 'users') {
        fputcsv($out, ['ID','Name','Email','Phone','Role','Seller Verified','Listings','Bids','Joined']);
        $rows = $pdo->query("
            SELECT u.id, u.name, u.email, u.phone, u.role, u.seller_verified,
                (SELECT COUNT(*) FROM listings l WHERE l.seller_id = u.id) AS listings,
                (SELECT COUNT(*) FROM bids b WHERE b.buyer_id = u.id) AS bids,
                u.created_at
            FROM users u ORDER BY u.id
        ")->fetchAll();
        foreach ($rows as $r) fputcsv($out, $r);

    } elseif ($type === 'bids') {
        fputcsv($out, ['Bid ID','Listing ID','Listing Title','Buyer','Amount','Placed At']);
        $rows = $pdo->query("
            SELECT b.id, b.listing_id, l.title, u.name AS buyer, b.amount, b.created_at
            FROM bids b
            JOIN listings l ON l.id = b.listing_id
            JOIN users    u ON u.id = b.buyer_id
            ORDER BY b.id DESC
        ")->fetchAll();
        foreach ($rows as $r) fputcsv($out, $r);
    }

    fclose($out);
    exit;
}

// ---- Summary stats ----
$totalUsers    = $pdo->query("SELECT COUNT(*) FROM users WHERE role='buyer'")->fetchColumn();
$totalSellers  = $pdo->query("SELECT COUNT(*) FROM users WHERE seller_verified=1")->fetchColumn();
$totalListings = $pdo->query("SELECT COUNT(*) FROM listings")->fetchColumn();
$totalBids     = $pdo->query("SELECT COUNT(*) FROM bids")->fetchColumn();
$totalRevenue  = $pdo->query("
    SELECT COALESCE(SUM(b.amount),0) FROM listings l
    JOIN bids b ON b.id = l.winner_bid_id WHERE l.status='ended'
")->fetchColumn();
$avgBidsPerAuction = $totalListings > 0
    ? round($pdo->query("SELECT COUNT(*) FROM bids")->fetchColumn() / $totalListings, 1)
    : 0;

// Revenue by month
$revenueByMonth = $pdo->query("
    SELECT DATE_FORMAT(l.end_datetime, '%Y-%m') AS month,
           COALESCE(SUM(b.amount),0) AS revenue,
           COUNT(*) AS auctions
    FROM listings l
    JOIN bids b ON b.id = l.winner_bid_id
    WHERE l.status = 'ended'
    GROUP BY month ORDER BY month DESC LIMIT 12
")->fetchAll();

// Top 10 highest winning bids
$topBids = $pdo->query("
    SELECT l.title, b.amount, u.name AS winner, s.name AS seller,
           c.name AS category, l.end_datetime
    FROM listings l
    JOIN bids b ON b.id = l.winner_bid_id
    JOIN users u ON u.id = b.buyer_id
    JOIN users s ON s.id = l.seller_id
    LEFT JOIN categories c ON c.id = l.category_id
    WHERE l.status = 'ended'
    ORDER BY b.amount DESC LIMIT 10
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reports — AuctionHub Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
<?php include __DIR__ . '/partials/sidebar.php'; ?>
<main class="main-content">

    <header class="topbar">
        <div class="topbar-left">
            <h1 class="page-title">Reports</h1>
            <p class="page-sub">Platform summary & CSV exports</p>
        </div>
        <div class="topbar-right" style="display:flex;gap:10px;flex-wrap:wrap">
            <a href="?export=auctions" class="refresh-btn">⬇ Export Auctions</a>
            <a href="?export=users"    class="refresh-btn">⬇ Export Users</a>
            <a href="?export=bids"     class="refresh-btn">⬇ Export Bids</a>
        </div>
    </header>

    <!-- Summary cards -->
    <section class="stat-grid">
        <div class="stat-card accent-orange">
            <div class="stat-icon">◉</div>
            <div class="stat-value"><?= number_format($totalUsers) ?></div>
            <div class="stat-label">Total Buyers</div>
        </div>
        <div class="stat-card accent-teal">
            <div class="stat-icon">◈</div>
            <div class="stat-value"><?= number_format($totalSellers) ?></div>
            <div class="stat-label">Verified Sellers</div>
        </div>
        <div class="stat-card accent-lime">
            <div class="stat-icon">▣</div>
            <div class="stat-value"><?= number_format($totalListings) ?></div>
            <div class="stat-label">Total Listings</div>
        </div>
        <div class="stat-card accent-blue">
            <div class="stat-icon">◇</div>
            <div class="stat-value"><?= number_format($totalBids) ?></div>
            <div class="stat-label">Total Bids</div>
        </div>
        <div class="stat-card accent-purple">
            <div class="stat-icon">$</div>
            <div class="stat-value">$<?= number_format($totalRevenue, 2) ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
        <div class="stat-card accent-red">
            <div class="stat-icon">≈</div>
            <div class="stat-value"><?= $avgBidsPerAuction ?></div>
            <div class="stat-label">Avg Bids / Auction</div>
        </div>
    </section>

    <!-- Revenue by Month -->
    <div class="table-card" style="margin-bottom:20px">
        <div class="table-header" style="margin-bottom:16px">
            <h2 class="chart-title">Revenue by Month</h2>
        </div>
        <div class="table-scroll">
            <table class="data-table">
                <thead><tr><th>Month</th><th>Auctions Sold</th><th>Revenue</th></tr></thead>
                <tbody>
                <?php if (empty($revenueByMonth)): ?>
                    <tr><td colspan="3" class="empty-row">No revenue data yet.</td></tr>
                <?php else: ?>
                <?php foreach ($revenueByMonth as $r): ?>
                <tr>
                    <td><?= $r['month'] ?></td>
                    <td><?= $r['auctions'] ?></td>
                    <td class="amount-cell">$<?= number_format($r['revenue'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top 10 Winning Bids -->
    <div class="table-card">
        <div class="table-header" style="margin-bottom:16px">
            <h2 class="chart-title">Top 10 Highest Winning Bids</h2>
        </div>
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr><th>Rank</th><th>Title</th><th>Category</th><th>Winner</th><th>Seller</th><th>Amount</th><th>Sold</th></tr>
                </thead>
                <tbody>
                <?php if (empty($topBids)): ?>
                    <tr><td colspan="7" class="empty-row">No ended auctions with winners yet.</td></tr>
                <?php else: ?>
                <?php foreach ($topBids as $i => $b): ?>
                <tr>
                    <td><span class="rank rank-<?= $i+1 ?>"><?= $i+1 ?></span></td>
                    <td class="title-cell"><?= htmlspecialchars($b['title']) ?></td>
                    <td><?= htmlspecialchars($b['category'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($b['winner']) ?></td>
                    <td><?= htmlspecialchars($b['seller']) ?></td>
                    <td class="amount-cell">$<?= number_format($b['amount'], 2) ?></td>
                    <td class="date-cell"><?= date('d M Y', strtotime($b['end_datetime'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>
</body>
</html>