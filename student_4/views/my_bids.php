<?php
// ============================================
// views/my_bids.php
// Shows a buyer's bidding history + status
// ============================================
session_start();

// Auth guard — must be logged in as buyer or seller
if (!isset($_SESSION['user'])) {
    header('Location: /auction_project/views/login.php');
    exit;
}

require_once __DIR__ . '/../controllers/AuctionController.php';

$buyerId    = (int)$_SESSION['user']['id'];
$controller = new AuctionController();

// Auto-close any expired auctions before showing status
// (so "leading" flips to "won" / "lost" automatically)
$bids = $controller->myBids($buyerId);

// Count statuses for summary
$counts = ['won' => 0, 'leading' => 0, 'outbid' => 0, 'lost' => 0, 'reserve_not_met' => 0];
foreach ($bids as $b) {
    if (isset($counts[$b['bid_status']])) $counts[$b['bid_status']]++;
}

// Active filter from query string
$filter = $_GET['filter'] ?? 'all';
$filtered = ($filter === 'all') ? $bids : array_filter($bids, fn($b) => $b['bid_status'] === $filter);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Bids — AuctionHub</title>
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
        <?php if ($_SESSION['user']['seller_verified']): ?>
        <a href="/student_4/views/seller_dashboard.php" class="nav-item"><span class="nav-icon">◈</span> My Listings</a>
        <?php endif; ?>
        <a href="my_bids.php" class="nav-item active"><span class="nav-icon">◇</span> My Bids</a>
        <a href="/auction_project/views/profile.php" class="nav-item"><span class="nav-icon">◉</span> Profile</a>
    </nav>
    <div class="sidebar-footer">
        <div class="admin-badge">
            <div class="admin-avatar"><?= strtoupper(substr($_SESSION['user']['name'], 0, 1)) ?></div>
            <div>
                <div class="admin-name"><?= htmlspecialchars($_SESSION['user']['name']) ?></div>
                <div class="admin-role">Buyer Account</div>
            </div>
        </div>
        <a href="/auction_project/views/logout.php" class="logout-btn">Log out →</a>
    </div>
</aside>

<main class="main-content">
    <header class="topbar">
        <div class="topbar-left">
            <h1 class="page-title">My Bids</h1>
            <p class="page-sub"><?= count($bids) ?> bid(s) placed</p>
        </div>
    </header>

    <?php if (empty($bids)): ?>
    <div class="empty-state">
        <div class="empty-icon">◇</div>
        <p>You haven't placed any bids yet.</p>
        <a href="/auction_project/views/browse.php" class="refresh-btn" style="display:inline-block;margin-top:16px">Browse Auctions →</a>
    </div>
    <?php else: ?>

    <!-- Filter tabs -->
    <div class="filter-bar" style="margin-bottom:24px">
        <div class="status-tabs">
            <a href="?filter=all" class="tab <?= $filter==='all' ? 'tab-active' : '' ?>">
                All <span class="tab-count"><?= count($bids) ?></span>
            </a>
            <a href="?filter=won" class="tab <?= $filter==='won' ? 'tab-active' : '' ?>">
                🏆 Won <span class="tab-count"><?= $counts['won'] ?></span>
            </a>
            <a href="?filter=leading" class="tab <?= $filter==='leading' ? 'tab-active' : '' ?>">
                ▲ Leading <span class="tab-count"><?= $counts['leading'] ?></span>
            </a>
            <a href="?filter=outbid" class="tab <?= $filter==='outbid' ? 'tab-active' : '' ?>">
                ▼ Outbid <span class="tab-count"><?= $counts['outbid'] ?></span>
            </a>
            <a href="?filter=lost" class="tab <?= $filter==='lost' ? 'tab-active' : '' ?>">
                ✕ Lost <span class="tab-count"><?= $counts['lost'] ?></span>
            </a>
            <a href="?filter=reserve_not_met" class="tab <?= $filter==='reserve_not_met' ? 'tab-active' : '' ?>">
                ⊘ No Sale <span class="tab-count"><?= $counts['reserve_not_met'] ?></span>
            </a>
        </div>
    </div>

    <?php if (empty($filtered)): ?>
    <div class="empty-state">
        <div class="empty-icon">◇</div>
        <p>No bids in this category.</p>
    </div>
    <?php else: ?>

    <section class="bids-grid">
        <?php foreach ($filtered as $bid): ?>
        <?php
            $statusClass = match($bid['bid_status']) {
                'won'              => 'status-won',
                'leading'         => 'status-leading',
                'outbid'          => 'status-outbid',
                'lost'            => 'status-lost',
                'reserve_not_met' => 'status-reserve',
                default           => ''
            };
            $statusLabel = match($bid['bid_status']) {
                'won'              => '🏆 Won',
                'leading'         => '▲ Leading',
                'outbid'          => '▼ Outbid',
                'lost'            => '✕ Lost',
                'reserve_not_met' => '⊘ No Sale',
                default           => '— Unknown'
            };
        ?>
        <div class="bid-card <?= $statusClass ?>">
            <div class="bid-card-top">
                <span class="bid-status-badge"><?= $statusLabel ?></span>
                <span class="bid-date"><?= date('d M Y', strtotime($bid['bid_time'])) ?></span>
            </div>

            <h3 class="bid-title"><?= htmlspecialchars($bid['title']) ?></h3>

            <div class="bid-amounts">
                <div class="bid-amount-item">
                    <span class="amount-label">Your Bid</span>
                    <span class="amount-value your-bid">$<?= number_format($bid['amount'], 2) ?></span>
                </div>
                <div class="bid-amount-item">
                    <span class="amount-label">Highest Bid</span>
                    <span class="amount-value"><?= $bid['highest_bid'] ? '$' . number_format($bid['highest_bid'], 2) : '—' ?></span>
                </div>
            </div>

            <!-- Countdown for active auctions -->
            <?php if ($bid['auction_status'] === 'active'): ?>
            <div style="font-size:11px;color:var(--accent-2);margin-bottom:10px">
                ⏱ Ends: <?= date('d M Y H:i', strtotime($bid['end_datetime'])) ?>
            </div>
            <?php endif; ?>

            <div class="bid-footer">
                <span class="auction-status <?= $bid['auction_status'] === 'active' ? 'live-dot' : '' ?>">
                    <?= $bid['auction_status'] === 'active' ? '● Live' : 'Ended' ?>
                </span>
                <div style="display:flex;gap:10px">
                    <?php if ($bid['bid_status'] === 'won'): ?>
                    <a href="contact_info.php?listing=<?= $bid['listing_id'] ?>" class="contact-link">View Seller Contact →</a>
                    <?php endif; ?>
                    <a href="auction_detail.php?id=<?= $bid['listing_id'] ?>" class="contact-link">Details →</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </section>

    <?php endif; ?>
    <?php endif; ?>
</main>

</body>
</html>