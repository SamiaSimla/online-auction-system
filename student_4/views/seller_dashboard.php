<?php
// ============================================
// views/seller_dashboard.php
// Seller's personal dashboard - see their listings & winners
// ============================================
session_start();

// Auth guard — must be logged in as verified seller
if (!isset($_SESSION['user']) || !$_SESSION['user']['seller_verified']) {
    header('Location: /auction_project/views/login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/AuctionModel.php';

$sellerId = (int)$_SESSION['user']['id'];
$model    = new AuctionModel();

// Get all listings by this seller
$listings = $model->getListingsBySeller($sellerId);

// Count by status
$statusCounts = ['active' => 0, 'ended' => 0, 'cancelled' => 0];
foreach ($listings as $l) {
    if (isset($statusCounts[$l['status']])) {
        $statusCounts[$l['status']]++;
    }
}

// Calculate total revenue (sum of winning bids)
$totalRevenue = 0;
$soldCount    = 0;
foreach ($listings as $l) {
    if ($l['status'] === 'ended' && $l['winner_bid_id']) {
        $totalRevenue += (float)$l['current_bid'];
        $soldCount++;
    }
}

// Active filter
$filter = $_GET['filter'] ?? 'all';
$filtered = ($filter === 'all') ? $listings : array_filter($listings, fn($l) => $l['status'] === $filter);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Listings — AuctionHub Seller</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../public/css/style.css">
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-logo">
        <span class="logo-icon">⬡</span>
        <span class="logo-text">Auction<strong>Hub</strong></span>
    </div>
    <nav class="sidebar-nav">
        <a href="/auction_project/views/browse.php" class="nav-item"><span class="nav-icon">▣</span> Browse Auctions</a>
        <a href="seller_dashboard.php" class="nav-item active"><span class="nav-icon">◈</span> My Listings</a>
        <a href="my_bids.php" class="nav-item"><span class="nav-icon">◇</span> My Bids</a>
        <a href="/auction_project/views/profile.php" class="nav-item"><span class="nav-icon">◉</span> Profile</a>
    </nav>
    <div class="sidebar-footer">
        <div class="admin-badge">
            <div class="admin-avatar"><?= strtoupper(substr($_SESSION['user']['name'], 0, 1)) ?></div>
            <div>
                <div class="admin-name"><?= htmlspecialchars($_SESSION['user']['name']) ?></div>
                <div class="admin-role">Verified Seller</div>
            </div>
        </div>
        <a href="/auction_project/views/logout.php" class="logout-btn">Log out →</a>
    </div>
</aside>

<main class="main-content">

    <header class="topbar">
        <div class="topbar-left">
            <h1 class="page-title">My Listings</h1>
            <p class="page-sub"><?= count($listings) ?> total listing(s)</p>
        </div>
        <div class="topbar-right">
            <a href="/auction_project/views/create_listing.php" class="refresh-btn" style="background:var(--accent-1);color:#fff">+ Create New Listing</a>
        </div>
    </header>

    <!-- Summary stats -->
    <section class="stat-grid" style="margin-bottom:32px">
        <div class="stat-card accent-lime">
            <div class="stat-icon">▣</div>
            <div class="stat-value"><?= $statusCounts['active'] ?></div>
            <div class="stat-label">Active Auctions</div>
        </div>
        <div class="stat-card accent-blue">
            <div class="stat-icon">✓</div>
            <div class="stat-value"><?= $soldCount ?></div>
            <div class="stat-label">Items Sold</div>
        </div>
        <div class="stat-card accent-purple">
            <div class="stat-icon">$</div>
            <div class="stat-value">$<?= number_format($totalRevenue, 2) ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
        <div class="stat-card accent-teal">
            <div class="stat-icon">◈</div>
            <div class="stat-value"><?= count($listings) ?></div>
            <div class="stat-label">Total Listings</div>
        </div>
    </section>

    <?php if (empty($listings)): ?>
    <div class="empty-state">
        <div class="empty-icon">◈</div>
        <p>You haven't created any listings yet.</p>
        <a href="/auction_project/views/create_listing.php" class="refresh-btn" style="display:inline-block;margin-top:16px;background:var(--accent-1);color:#fff">Create Your First Listing →</a>
    </div>
    <?php else: ?>

    <!-- Filter tabs -->
    <div class="filter-bar" style="margin-bottom:24px">
        <div class="status-tabs">
            <a href="?filter=all" class="tab <?= $filter==='all' ? 'tab-active' : '' ?>">
                All <span class="tab-count"><?= count($listings) ?></span>
            </a>
            <a href="?filter=active" class="tab <?= $filter==='active' ? 'tab-active' : '' ?>">
                Live <span class="tab-count"><?= $statusCounts['active'] ?></span>
            </a>
            <a href="?filter=ended" class="tab <?= $filter==='ended' ? 'tab-active' : '' ?>">
                Ended <span class="tab-count"><?= $statusCounts['ended'] ?></span>
            </a>
            <a href="?filter=cancelled" class="tab <?= $filter==='cancelled' ? 'tab-active' : '' ?>">
                Cancelled <span class="tab-count"><?= $statusCounts['cancelled'] ?></span>
            </a>
        </div>
    </div>

    <?php if (empty($filtered)): ?>
    <div class="empty-state">
        <div class="empty-icon">◈</div>
        <p>No listings in this category.</p>
    </div>
    <?php else: ?>

    <!-- Listings table -->
    <div class="table-card">
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Start Price</th>
                        <th>Current Bid</th>
                        <th>Bids</th>
                        <th>Ends</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($filtered as $l): ?>
                <tr>
                    <td class="row-num"><?= $l['id'] ?></td>
                    <td class="title-cell"><?= htmlspecialchars($l['title']) ?></td>
                    <td><?= htmlspecialchars($l['category'] ?? '—') ?></td>
                    <td class="amount-cell">$<?= number_format($l['starting_price'], 2) ?></td>
                    <td class="amount-cell">
                        <?= $l['current_bid'] > 0 ? '$' . number_format($l['current_bid'], 2) : '—' ?>
                    </td>
                    <td><?= $l['bid_count'] ?></td>
                    <td class="date-cell"><?= date('d M Y H:i', strtotime($l['end_datetime'])) ?></td>
                    <td>
                        <span class="badge badge-<?= $l['status'] ?>">
                            <?= ucfirst($l['status']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-btns">
                            <a href="auction_detail.php?id=<?= $l['id'] ?>" class="btn-sm btn-view">View</a>
                            
                            <?php if ($l['status'] === 'ended' && $l['winner_bid_id']): ?>
                                <!-- Seller can see winner contact -->
                                <a href="contact_info.php?listing=<?= $l['id'] ?>" class="btn-sm btn-approve" title="View winner contact">
                                    Winner Info
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($l['status'] === 'active' && $l['bid_count'] == 0): ?>
                                <a href="/auction_project/views/edit_listing.php?id=<?= $l['id'] ?>" class="btn-sm btn-view">Edit</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php endif; ?>
    <?php endif; ?>

</main>

</body>
</html>