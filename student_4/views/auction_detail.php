<?php
// ============================================
// views/auction_detail.php
// Admin: full detail of one listing + bid history
// Also accessible by buyers to view their auction
// ============================================
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: /auction_project/views/login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/AuctionController.php';

// Auto-close expired auctions on page load
$controller = new AuctionController();

$pdo = getDBConnection();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: auctions.php');
    exit;
}

// Fetch listing
$stmt = $pdo->prepare("
    SELECT l.*, c.name AS category, u.name AS seller_name, u.email AS seller_email, u.phone AS seller_phone, u.id AS seller_user_id
    FROM listings l
    LEFT JOIN categories c ON c.id = l.category_id
    LEFT JOIN users      u ON u.id = l.seller_id
    WHERE l.id = ?
");
$stmt->execute([$id]);
$listing = $stmt->fetch();
if (!$listing) {
    echo '<p style="color:#fff;padding:40px">Listing not found.</p>';
    exit;
}

// Fetch bids (highest first)
$bidsStmt = $pdo->prepare("
    SELECT b.*, u.name AS buyer_name, u.email AS buyer_email
    FROM bids b
    JOIN users u ON u.id = b.buyer_id
    WHERE b.listing_id = ?
    ORDER BY b.amount DESC
");
$bidsStmt->execute([$id]);
$bids = $bidsStmt->fetchAll();

// Winner info
$winner = null;
if ($listing['winner_bid_id']) {
    $ws = $pdo->prepare("
        SELECT b.amount, u.name, u.email, u.phone
        FROM bids b JOIN users u ON u.id = b.buyer_id
        WHERE b.id = ?
    ");
    $ws->execute([$listing['winner_bid_id']]);
    $winner = $ws->fetch();
}

$isAdmin = $_SESSION['user']['role'] === 'admin';
$isSeller = (int)$_SESSION['user']['id'] === (int)$listing['seller_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($listing['title']) ?> — AuctionHub</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../public/css/style.css">
</head>
<body>

<?php if ($isAdmin): ?>
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
<?php else: ?>
<!-- Buyer sidebar -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <span class="logo-icon">⬡</span>
        <span class="logo-text">Auction<strong>Hub</strong></span>
    </div>
    <nav class="sidebar-nav">
        <a href="/auction_project/views/browse.php" class="nav-item"><span class="nav-icon">▣</span> Browse Auctions</a>
        <a href="my_bids.php" class="nav-item"><span class="nav-icon">◇</span> My Bids</a>
    </nav>
    <div class="sidebar-footer">
        <div class="admin-badge">
            <div class="admin-avatar"><?= strtoupper(substr($_SESSION['user']['name'], 0, 1)) ?></div>
            <div>
                <div class="admin-name"><?= htmlspecialchars($_SESSION['user']['name']) ?></div>
                <div class="admin-role"><?= ucfirst($_SESSION['user']['role']) ?></div>
            </div>
        </div>
        <a href="/auction_project/views/logout.php" class="logout-btn">Log out →</a>
    </div>
</aside>
<?php endif; ?>

<main class="main-content">

    <header class="topbar">
        <div class="topbar-left">
            <a href="<?= $isAdmin ? 'auctions.php' : 'my_bids.php' ?>" class="back-link">
                ← <?= $isAdmin ? 'Back to Auctions' : 'Back to My Bids' ?>
            </a>
            <h1 class="page-title"><?= htmlspecialchars($listing['title']) ?></h1>
            <p class="page-sub">Listing #<?= $listing['id'] ?> · <?= htmlspecialchars($listing['category'] ?? '—') ?></p>
        </div>
        <div class="topbar-right">
            <span class="badge badge-<?= $listing['status'] ?>" style="font-size:13px;padding:6px 14px">
                <?= ucfirst($listing['status']) ?>
            </span>
        </div>
    </header>

    <div class="detail-grid">

        <!-- Left: listing info + bid history -->
        <div class="detail-main">
            <div class="table-card">
                <h2 class="section-title">Listing Details</h2>
                <div class="detail-rows">
                    <div class="detail-row">
                        <span class="dr-label">Description</span>
                        <span><?= nl2br(htmlspecialchars($listing['description'])) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="dr-label">Starting Price</span>
                        <span class="amount-cell">$<?= number_format($listing['starting_price'], 2) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="dr-label">Reserve Price</span>
                        <span class="amount-cell">
                            <?= $listing['reserve_price'] ? '$' . number_format($listing['reserve_price'], 2) : '<span class="text-muted">None</span>' ?>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="dr-label">Current Bid</span>
                        <span class="amount-cell">
                            <?= $listing['current_bid'] ? '$' . number_format($listing['current_bid'], 2) : '<span class="text-muted">No bids</span>' ?>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="dr-label">End Date</span>
                        <span><?= date('d M Y H:i', strtotime($listing['end_datetime'])) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="dr-label">Created</span>
                        <span><?= date('d M Y H:i', strtotime($listing['created_at'])) ?></span>
                    </div>
                </div>
            </div>

            <!-- Bid history -->
            <div class="table-card" style="margin-top:16px">
                <h2 class="section-title">Bid History (<?= count($bids) ?>)</h2>
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Buyer</th>
                                <?php if ($isAdmin): ?>
                                <th>Email</th>
                                <?php endif; ?>
                                <th>Amount</th>
                                <th>Placed At</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($bids)): ?>
                            <tr><td colspan="<?= $isAdmin ? 5 : 4 ?>" class="empty-row">No bids yet.</td></tr>
                        <?php else: ?>
                        <?php foreach ($bids as $i => $b): ?>
                        <tr class="<?= $i === 0 ? 'row-highlight' : '' ?>">
                            <td><span class="rank rank-<?= $i + 1 ?>"><?= $i + 1 ?></span></td>
                            <td><?= htmlspecialchars($b['buyer_name']) ?></td>
                            <?php if ($isAdmin): ?>
                            <td class="text-muted"><?= htmlspecialchars($b['buyer_email']) ?></td>
                            <?php endif; ?>
                            <td class="amount-cell">$<?= number_format($b['amount'], 2) ?></td>
                            <td class="date-cell"><?= date('d M Y H:i', strtotime($b['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right: seller + winner -->
        <div class="detail-side">

            <!-- Seller info — visible to admin always; visible to winner too -->
            <?php if ($isAdmin || ($listing['status'] === 'ended' && $winner && (int)$_SESSION['user']['id'] === $winner['id'] ?? 0)): ?>
            <div class="table-card">
                <h2 class="section-title">Seller Info</h2>
                <div class="contact-card">
                    <div class="contact-avatar"><?= strtoupper(substr($listing['seller_name'], 0, 1)) ?></div>
                    <div class="contact-name"><?= htmlspecialchars($listing['seller_name']) ?></div>
                    <div class="contact-detail">✉ <?= htmlspecialchars($listing['seller_email']) ?></div>
                    <div class="contact-detail">☎ <?= htmlspecialchars($listing['seller_phone'] ?? '—') ?></div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Auction result -->
            <?php if ($listing['status'] === 'ended'): ?>
            <div class="table-card" style="margin-top:16px">
                <h2 class="section-title">Auction Result</h2>
                <?php if ($winner): ?>
                <div class="winner-box">
                    <div class="winner-crown">🏆</div>
                    <?php if ($isAdmin || $isSeller): ?>
                    <div class="contact-name"><?= htmlspecialchars($winner['name']) ?></div>
                    <div class="contact-detail">✉ <?= htmlspecialchars($winner['email']) ?></div>
                    <div class="contact-detail">☎ <?= htmlspecialchars($winner['phone'] ?? '—') ?></div>
                    <?php else: ?>
                    <div class="contact-name">Auction Closed</div>
                    <div class="contact-detail">A winner has been declared.</div>
                    <?php endif; ?>
                    <div class="winner-amount">$<?= number_format($winner['amount'], 2) ?></div>
                </div>
                <?php else: ?>
                <div class="reserve-notice">
                    <span style="font-size:28px">⊘</span>
                    <p>Reserve price not met.<br>No winner declared.</p>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Live auction info -->
            <?php if ($listing['status'] === 'active'): ?>
            <div class="table-card" style="margin-top:16px">
                <h2 class="section-title">Auction Status</h2>
                <div style="text-align:center;padding:16px">
                    <div style="color:var(--accent-3);font-size:13px;margin-bottom:8px">● Live Auction</div>
                    <div style="font-size:12px;color:var(--text-muted)">Ends:</div>
                    <div style="font-family:'Syne',sans-serif;font-weight:700;font-size:16px;margin-top:4px">
                        <?= date('d M Y H:i', strtotime($listing['end_datetime'])) ?>
                    </div>
                    <div id="countdown" style="color:var(--accent-1);margin-top:10px;font-size:13px"></div>
                </div>
            </div>

            <script>
            (function() {
                const end = new Date('<?= $listing['end_datetime'] ?>').getTime();
                const el  = document.getElementById('countdown');
                function tick() {
                    const diff = end - Date.now();
                    if (diff <= 0) { el.textContent = 'Auction has ended'; return; }
                    const h = Math.floor(diff / 3600000);
                    const m = Math.floor((diff % 3600000) / 60000);
                    const s = Math.floor((diff % 60000) / 1000);
                    el.textContent = `⏱ ${h}h ${m}m ${s}s remaining`;
                }
                tick();
                setInterval(tick, 1000);
            })();
            </script>
            <?php endif; ?>

        </div>
    </div>

</main>
</body>
</html>
