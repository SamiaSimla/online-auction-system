<?php
// views/auctions.php — Admin: view all listings, filter by status, cancel
session_start();
require_once __DIR__ . '/../config/database.php';
$pdo = getDBConnection();

// ---- Handle cancel action ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    $cancelId = (int)$_POST['cancel_id'];
    // Only cancel if no bids exist
    $check = $pdo->prepare("SELECT COUNT(*) FROM bids WHERE listing_id = ?");
    $check->execute([$cancelId]);
    if ($check->fetchColumn() == 0) {
        $pdo->prepare("UPDATE listings SET status = 'cancelled' WHERE id = ?")->execute([$cancelId]);
        $msg = ['type' => 'success', 'text' => 'Listing cancelled successfully.'];
    } else {
        $msg = ['type' => 'error', 'text' => 'Cannot cancel — this listing already has bids.'];
    }
}

// ---- Filter ----
$statusFilter = $_GET['status'] ?? 'all';
$search       = trim($_GET['search'] ?? '');

$where  = [];
$params = [];
if ($statusFilter !== 'all') {
    $where[]  = 'l.status = ?';
    $params[] = $statusFilter;
}
if ($search !== '') {
    $where[]  = 'l.title LIKE ?';
    $params[] = "%$search%";
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $pdo->prepare("
    SELECT l.*, c.name AS category, u.name AS seller_name,
           (SELECT COUNT(*) FROM bids b WHERE b.listing_id = l.id) AS bid_count
    FROM listings l
    LEFT JOIN categories c ON c.id = l.category_id
    LEFT JOIN users      u ON u.id = l.seller_id
    $whereSQL
    ORDER BY l.created_at DESC
");
$stmt->execute($params);
$listings = $stmt->fetchAll();

// Counts for tabs
$counts = $pdo->query("SELECT status, COUNT(*) AS n FROM listings GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$counts['all'] = array_sum($counts);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Auctions — AuctionHub Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
<?php include __DIR__ . '/partials/sidebar.php'; ?>
<main class="main-content">

    <header class="topbar">
        <div class="topbar-left">
            <h1 class="page-title">Auctions</h1>
            <p class="page-sub">All listings on the platform</p>
        </div>
    </header>

    <?php if (isset($msg)): ?>
    <div class="alert alert-<?= $msg['type'] ?>"><?= $msg['text'] ?></div>
    <?php endif; ?>

    <!-- Filter bar -->
    <div class="filter-bar">
        <div class="status-tabs">
            <?php foreach (['all'=>'All','active'=>'Active','ended'=>'Ended','cancelled'=>'Cancelled'] as $key=>$label): ?>
            <a href="?status=<?= $key ?>&search=<?= urlencode($search) ?>"
               class="tab <?= $statusFilter === $key ? 'tab-active' : '' ?>">
                <?= $label ?>
                <span class="tab-count"><?= $counts[$key] ?? 0 ?></span>
            </a>
            <?php endforeach; ?>
        </div>
        <form method="GET" class="search-form">
            <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
            <input type="text" name="search" placeholder="Search by title…" value="<?= htmlspecialchars($search) ?>" class="search-input">
            <button type="submit" class="search-btn">Search</button>
        </form>
    </div>

    <!-- Table -->
    <div class="table-card" style="margin-top:20px;">
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Seller</th>
                        <th>Start Price</th>
                        <th>Current Bid</th>
                        <th>Bids</th>
                        <th>Ends</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($listings)): ?>
                    <tr><td colspan="10" class="empty-row">No listings found.</td></tr>
                <?php else: ?>
                <?php foreach ($listings as $l): ?>
                <tr>
                    <td class="row-num"><?= $l['id'] ?></td>
                    <td class="title-cell"><?= htmlspecialchars($l['title']) ?></td>
                    <td><?= htmlspecialchars($l['category'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($l['seller_name'] ?? '—') ?></td>
                    <td class="amount-cell">$<?= number_format($l['starting_price'], 2) ?></td>
                    <td class="amount-cell"><?= $l['current_bid'] ? '$'.number_format($l['current_bid'],2) : '—' ?></td>
                    <td><?= $l['bid_count'] ?></td>
                    <td class="date-cell"><?= date('d M Y H:i', strtotime($l['end_datetime'])) ?></td>
                    <td>
                        <span class="badge badge-<?= $l['status'] ?>"><?= ucfirst($l['status']) ?></span>
                    </td>
                    <td>
                        <div class="action-btns">
                            <a href="auction_detail.php?id=<?= $l['id'] ?>" class="btn-sm btn-view">View</a>
                            <?php if ($l['status'] === 'active' && $l['bid_count'] == 0): ?>
                            <form method="POST" style="display:inline"
                                  onsubmit="return confirm('Cancel this listing?')">
                                <input type="hidden" name="cancel_id" value="<?= $l['id'] ?>">
                                <button type="submit" class="btn-sm btn-danger">Cancel</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
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