<?php
// views/users.php — Admin: view all users, search, toggle seller status
session_start();
require_once __DIR__ . '/../config/database.php';
$pdo = getDBConnection();

// ---- Handle toggle seller verified ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
    $uid     = (int)$_POST['toggle_id'];
    $current = (int)$_POST['current_verified'];
    $new     = $current ? 0 : 1;
    $pdo->prepare("UPDATE users SET seller_verified = ? WHERE id = ?")->execute([$new, $uid]);
    header('Location: users.php?msg=updated');
    exit;
}

// ---- Handle delete ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $uid = (int)$_POST['delete_id'];
    // Don't delete admin
    $role = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $role->execute([$uid]);
    if ($role->fetchColumn() !== 'admin') {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$uid]);
    }
    header('Location: users.php?msg=deleted');
    exit;
}

$search     = trim($_GET['search'] ?? '');
$roleFilter = $_GET['role'] ?? 'all';

$where  = [];
$params = [];
if ($search !== '') {
    $where[]  = '(u.name LIKE ? OR u.email LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($roleFilter === 'seller') {
    $where[] = 'u.seller_verified = 1';
} elseif ($roleFilter === 'buyer') {
    $where[] = 'u.seller_verified = 0 AND u.role = "buyer"';
} elseif ($roleFilter === 'admin') {
    $where[] = 'u.role = "admin"';
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $pdo->prepare("
    SELECT u.*,
        (SELECT COUNT(*) FROM listings l WHERE l.seller_id = u.id) AS listing_count,
        (SELECT COUNT(*) FROM bids    b WHERE b.buyer_id  = u.id) AS bid_count
    FROM users u
    $whereSQL
    ORDER BY u.created_at DESC
");
$stmt->execute($params);
$users = $stmt->fetchAll();

$msg = match($_GET['msg'] ?? '') { 'updated' => 'User updated.', 'deleted' => 'User deleted.', default => '' };
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Users — AuctionHub Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
<?php include __DIR__ . '/partials/sidebar.php'; ?>
<main class="main-content">

    <header class="topbar">
        <div class="topbar-left">
            <h1 class="page-title">Users</h1>
            <p class="page-sub"><?= count($users) ?> users found</p>
        </div>
    </header>

    <?php if ($msg): ?>
    <div class="alert alert-success"><?= $msg ?></div>
    <?php endif; ?>

    <!-- Filter bar -->
    <div class="filter-bar">
        <div class="status-tabs">
            <?php foreach (['all'=>'All','seller'=>'Sellers','buyer'=>'Buyers','admin'=>'Admins'] as $k=>$v): ?>
            <a href="?role=<?= $k ?>&search=<?= urlencode($search) ?>"
               class="tab <?= $roleFilter===$k ? 'tab-active' : '' ?>"><?= $v ?></a>
            <?php endforeach; ?>
        </div>
        <form method="GET" class="search-form">
            <input type="hidden" name="role" value="<?= htmlspecialchars($roleFilter) ?>">
            <input type="text" name="search" placeholder="Search name or email…" value="<?= htmlspecialchars($search) ?>" class="search-input">
            <button type="submit" class="search-btn">Search</button>
        </form>
    </div>

    <div class="table-card" style="margin-top:20px">
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th><th>Name</th><th>Email</th><th>Phone</th>
                        <th>Role</th><th>Seller?</th><th>Listings</th><th>Bids</th>
                        <th>Joined</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="10" class="empty-row">No users found.</td></tr>
                <?php else: ?>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td class="row-num"><?= $u['id'] ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px">
                            <div class="admin-avatar" style="width:28px;height:28px;font-size:12px">
                                <?= strtoupper(substr($u['name'],0,1)) ?>
                            </div>
                            <?= htmlspecialchars($u['name']) ?>
                        </div>
                    </td>
                    <td class="text-muted"><?= htmlspecialchars($u['email']) ?></td>
                    <td class="text-muted"><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
                    <td><span class="badge badge-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                    <td>
                        <?php if ($u['seller_verified']): ?>
                            <span class="badge badge-active">✓ Verified</span>
                        <?php else: ?>
                            <span class="badge badge-cancelled">✗ No</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $u['listing_count'] ?></td>
                    <td><?= $u['bid_count'] ?></td>
                    <td class="date-cell"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <div class="action-btns">
                            <?php if ($u['role'] !== 'admin'): ?>
                            <!-- Toggle seller -->
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="toggle_id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="current_verified" value="<?= $u['seller_verified'] ?>">
                                <button type="submit" class="btn-sm btn-view" title="Toggle seller status">
                                    <?= $u['seller_verified'] ? 'Revoke' : 'Verify' ?>
                                </button>
                            </form>
                            <!-- Delete -->
                            <form method="POST" style="display:inline"
                                  onsubmit="return confirm('Delete this user?')">
                                <input type="hidden" name="delete_id" value="<?= $u['id'] ?>">
                                <button type="submit" class="btn-sm btn-danger">Delete</button>
                            </form>
                            <?php else: ?>
                            <span class="text-muted" style="font-size:11px">Admin</span>
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