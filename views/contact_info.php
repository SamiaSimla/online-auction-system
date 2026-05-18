<?php
require_once __DIR__ . '/../controllers/AuctionController.php';
require_once __DIR__ . '/../config/db1.php';
// ============================================
// views/contact_info.php
// Winner sees seller's contact info (and vice versa)
// Requirement: Student 4, tasks 3 & 4
// ============================================
session_start();
require_once __DIR__ . '/../controllers/AuctionController.php';
require_once __DIR__ . '/../config/db1.php';

if (!isset($_SESSION['user'])) {
    header('Location: /views/login.php');
    exit;
}


$listingId  = (int)($_GET['listing'] ?? 0);
$userId     = (int)$_SESSION['user']['id'];
$userRole   = $_SESSION['user']['role'];

if (!$listingId) {
    header('Location: my_bids.php');
    exit;
}

$controller = new AuctionController();
$info       = $controller->getContactInfo($listingId);

if (!$info) {
    $error = 'Listing not found.';
} elseif ($info['status'] !== 'ended') {
    $error = 'This auction has not ended yet.';
} elseif (!$info['winner_bid_id']) {
    $error = 'This auction ended without a winner (reserve price not met).';
} else {
    // Check permission:
    // - Winner can view seller contact
    // - Seller can view winner contact
    // - Admin can view both
    $isWinner = ($userId === (int)($info['winner_id'] ?? 0));
    $isSeller = ($userId === (int)($info['seller_id']  ?? 0));
    $isAdmin  = ($userRole === 'admin');

    if (!$isWinner && !$isSeller && !$isAdmin) {
        $error = 'You do not have permission to view this contact information.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contact Info — AuctionHub</title>
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
        <?php if ($userRole === 'admin'): ?>
            <a href="/student_4/views/admin_dashboard.php" class="nav-item"><span class="nav-icon">▣</span> Dashboard</a>
            <a href="/student_4/views/auctions.php" class="nav-item"><span class="nav-icon">◈</span> Auctions</a>
        <?php elseif ($info['seller_id'] == $userId): ?>
            <a href="/student_4/views/seller_dashboard.php" class="nav-item"><span class="nav-icon">▣</span> My Listings</a>
        <?php else: ?>
            <a href="/auction_project/views/browse.php" class="nav-item"><span class="nav-icon">▣</span> Browse Auctions</a>
        <?php endif; ?>
        <a href="/student_4/views/my_bids.php" class="nav-item active"><span class="nav-icon">◇</span> My Bids</a>
        <?php if ($userRole !== 'admin'): ?>
        <a href="/auction_project/views/profile.php" class="nav-item"><span class="nav-icon">◉</span> Profile</a>
        <?php endif; ?>
    </nav>
    <div class="sidebar-footer">
        <div class="admin-badge">
            <div class="admin-avatar"><?= strtoupper(substr($_SESSION['user']['name'], 0, 1)) ?></div>
            <div>
                <div class="admin-name"><?= htmlspecialchars($_SESSION['user']['name']) ?></div>
                <div class="admin-role"><?= ucfirst($userRole) ?></div>
            </div>
        </div>
        <a href="/auction_project/views/logout.php" class="logout-btn">Log out →</a>
    </div>
</aside>

<main class="main-content">

    <header class="topbar">
        <div class="topbar-left">
            <?php if ($userRole === 'admin'): ?>
                <a href="/student_4/views/auctions.php" class="back-link">← Back to Auctions</a>
            <?php elseif (isset($info) && $info['seller_id'] == $userId): ?>
                <a href="/student_4/views/seller_dashboard.php" class="back-link">← Back to My Listings</a>
            <?php else: ?>
                <a href="/student_4/views/my_bids.php" class="back-link">← Back to My Bids</a>
            <?php endif; ?>
            <h1 class="page-title">Contact Information</h1>
            <?php if (isset($info)): ?>
            <p class="page-sub"><?= htmlspecialchars($info['title']) ?></p>
            <?php endif; ?>
        </div>
    </header>

    <?php if (isset($error)): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php else: ?>

    <div style="max-width:700px">

        <!-- Winning Bid Summary -->
        <div class="table-card" style="margin-bottom:20px">
            <h2 class="section-title">Auction Summary</h2>
            <div class="detail-rows">
                <div class="detail-row">
                    <span class="dr-label">Item</span>
                    <span><?= htmlspecialchars($info['title']) ?></span>
                </div>
                <div class="detail-row row-highlight">
                    <span class="dr-label">Winning Bid</span>
                    <span class="amount-cell" style="font-family:'Syne',sans-serif;font-size:22px;font-weight:800">
                        $<?= number_format($info['winning_amount'], 2) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- WINNER sees SELLER contact -->
        <?php if ($isWinner || $isAdmin): ?>
        <div class="table-card" style="margin-bottom:20px">
            <h2 class="section-title">📦 Seller Contact — Arrange Payment & Delivery</h2>
            <div class="contact-card" style="align-items:flex-start;text-align:left;padding:8px 0">
                <div style="display:flex;align-items:center;gap:16px;margin-bottom:16px">
                    <div class="contact-avatar" style="background:var(--accent-2)">
                        <?= strtoupper(substr($info['seller_name'], 0, 1)) ?>
                    </div>
                    <div>
                        <div class="contact-name"><?= htmlspecialchars($info['seller_name']) ?></div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:2px">Seller</div>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:10px;width:100%">
                    <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:var(--surface-2);border-radius:8px;border:1px solid var(--border)">
                        <span style="font-size:16px">✉</span>
                        <div>
                            <div style="font-size:10px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.6px;margin-bottom:2px">Email</div>
                            <a href="mailto:<?= htmlspecialchars($info['seller_email']) ?>"
                               style="color:var(--accent-4);font-weight:500">
                                <?= htmlspecialchars($info['seller_email']) ?>
                            </a>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:var(--surface-2);border-radius:8px;border:1px solid var(--border)">
                        <span style="font-size:16px">☎</span>
                        <div>
                            <div style="font-size:10px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.6px;margin-bottom:2px">Phone</div>
                            <a href="tel:<?= htmlspecialchars($info['seller_phone'] ?? '') ?>"
                               style="color:var(--accent-4);font-weight:500">
                                <?= htmlspecialchars($info['seller_phone'] ?? '—') ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- SELLER sees WINNER contact -->
        <?php if ($isSeller || $isAdmin): ?>
        <div class="table-card">
            <h2 class="section-title">🏆 Winner Contact — Arrange Payment & Delivery</h2>
            <div class="contact-card" style="align-items:flex-start;text-align:left;padding:8px 0">
                <div style="display:flex;align-items:center;gap:16px;margin-bottom:16px">
                    <div class="contact-avatar" style="background:var(--accent-3);color:#000">
                        <?= strtoupper(substr($info['winner_name'], 0, 1)) ?>
                    </div>
                    <div>
                        <div class="contact-name"><?= htmlspecialchars($info['winner_name']) ?></div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:2px">Winner</div>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:10px;width:100%">
                    <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:var(--surface-2);border-radius:8px;border:1px solid var(--border)">
                        <span style="font-size:16px">✉</span>
                        <div>
                            <div style="font-size:10px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.6px;margin-bottom:2px">Email</div>
                            <a href="mailto:<?= htmlspecialchars($info['winner_email']) ?>"
                               style="color:var(--accent-4);font-weight:500">
                                <?= htmlspecialchars($info['winner_email']) ?>
                            </a>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:var(--surface-2);border-radius:8px;border:1px solid var(--border)">
                        <span style="font-size:16px">☎</span>
                        <div>
                            <div style="font-size:10px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.6px;margin-bottom:2px">Phone</div>
                            <a href="tel:<?= htmlspecialchars($info['winner_phone'] ?? '') ?>"
                               style="color:var(--accent-4);font-weight:500">
                                <?= htmlspecialchars($info['winner_phone'] ?? '—') ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
    <?php endif; ?>

</main>
</body>
</html>