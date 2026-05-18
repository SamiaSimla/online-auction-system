<?php
// views/seller_requests.php — Admin: approve/reject seller requests via AJAX
session_start();
require_once __DIR__ . '/../config/database.php';
$pdo = getDBConnection();

// ---- AJAX handler ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    $uid    = (int)($_POST['user_id'] ?? 0);
    $action = $_POST['decision'] ?? '';

    if (!$uid || !in_array($action, ['approve','reject'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid request.']);
        exit;
    }

    if ($action === 'approve') {
        $pdo->prepare("UPDATE users SET seller_verified = 1 WHERE id = ?")->execute([$uid]);
        echo json_encode(['success' => true, 'message' => 'Seller approved.', 'action' => 'approve']);
    } else {
        // Reject: keep as buyer, mark verified=0 (already 0), optionally add a rejected flag
        // For now just ensure they stay unverified
        $pdo->prepare("UPDATE users SET seller_verified = 0 WHERE id = ?")->execute([$uid]);
        echo json_encode(['success' => true, 'message' => 'Request rejected.', 'action' => 'reject']);
    }
    exit;
}

// ---- Load pending requests ----
$pending = $pdo->query("
    SELECT u.*, 
        (SELECT COUNT(*) FROM bids b WHERE b.buyer_id = u.id) AS bid_count
    FROM users u
    WHERE u.seller_verified = 0 AND u.role = 'buyer'
    ORDER BY u.created_at DESC
")->fetchAll();

$approved = $pdo->query("
    SELECT u.*,
        (SELECT COUNT(*) FROM listings l WHERE l.seller_id = u.id) AS listing_count
    FROM users u
    WHERE u.seller_verified = 1
    ORDER BY u.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Seller Requests — AuctionHub Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
<?php include __DIR__ . '/partials/sidebar.php'; ?>
<main class="main-content">

    <header class="topbar">
        <div class="topbar-left">
            <h1 class="page-title">Seller Requests</h1>
            <p class="page-sub">
                <span id="pendingCount"><?= count($pending) ?></span> pending request(s)
            </p>
        </div>
    </header>

    <div id="ajaxMsg" class="alert" style="display:none"></div>

    <!-- PENDING requests -->
    <div class="table-card" style="margin-bottom:20px">
        <div class="table-header" style="margin-bottom:16px">
            <h2 class="chart-title">⏳ Pending Requests</h2>
        </div>
        <div class="table-scroll">
            <table class="data-table" id="pendingTable">
                <thead>
                    <tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Bio</th><th>Joined</th><th>Bids Placed</th><th>Decision</th></tr>
                </thead>
                <tbody>
                <?php if (empty($pending)): ?>
                    <tr id="emptyRow"><td colspan="8" class="empty-row">No pending requests.</td></tr>
                <?php else: ?>
                <?php foreach ($pending as $u): ?>
                <tr id="row-<?= $u['id'] ?>">
                    <td class="row-num"><?= $u['id'] ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px">
                            <div class="admin-avatar" style="width:28px;height:28px;font-size:12px;background:var(--accent-4)">
                                <?= strtoupper(substr($u['name'],0,1)) ?>
                            </div>
                            <strong><?= htmlspecialchars($u['name']) ?></strong>
                        </div>
                    </td>
                    <td class="text-muted"><?= htmlspecialchars($u['email']) ?></td>
                    <td class="text-muted"><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
                    <td class="text-muted" style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                        <?= htmlspecialchars($u['bio'] ?? '—') ?>
                    </td>
                    <td class="date-cell"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                    <td><?= $u['bid_count'] ?></td>
                    <td>
                        <div class="action-btns">
                            <button class="btn-sm btn-approve"
                                    onclick="decide(<?= $u['id'] ?>, 'approve')">✓ Approve</button>
                            <button class="btn-sm btn-danger"
                                    onclick="decide(<?= $u['id'] ?>, 'reject')">✕ Reject</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- APPROVED sellers -->
    <div class="table-card">
        <div class="table-header" style="margin-bottom:16px">
            <h2 class="chart-title">✓ Verified Sellers (<?= count($approved) ?>)</h2>
        </div>
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Listings</th><th>Joined</th><th>Action</th></tr>
                </thead>
                <tbody>
                <?php if (empty($approved)): ?>
                    <tr><td colspan="7" class="empty-row">No verified sellers yet.</td></tr>
                <?php else: ?>
                <?php foreach ($approved as $u): ?>
                <tr>
                    <td class="row-num"><?= $u['id'] ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px">
                            <div class="admin-avatar" style="width:28px;height:28px;font-size:12px;background:var(--accent-2)">
                                <?= strtoupper(substr($u['name'],0,1)) ?>
                            </div>
                            <?= htmlspecialchars($u['name']) ?>
                        </div>
                    </td>
                    <td class="text-muted"><?= htmlspecialchars($u['email']) ?></td>
                    <td class="text-muted"><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
                    <td><?= $u['listing_count'] ?></td>
                    <td class="date-cell"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <button class="btn-sm btn-danger"
                                onclick="decide(<?= $u['id'] ?>, 'reject')" title="Revoke seller status">
                            Revoke
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<script>
function decide(userId, decision) {
    const label = decision === 'approve' ? 'approve' : 'reject';
    if (!confirm(`Are you sure you want to ${label} this request?`)) return;

    fetch('seller_requests.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `ajax=1&user_id=${userId}&decision=${decision}`
    })
    .then(r => r.json())
    .then(data => {
        const msgBox = document.getElementById('ajaxMsg');
        msgBox.style.display = 'block';
        msgBox.className = 'alert alert-' + (data.success ? 'success' : 'error');
        msgBox.textContent = data.message;

        if (data.success) {
            // Remove row from pending table
            const row = document.getElementById('row-' + userId);
            if (row) row.remove();

            // Update pending count
            const tbody = document.querySelector('#pendingTable tbody');
            const countEl = document.getElementById('pendingCount');
            const remaining = tbody.querySelectorAll('tr[id^="row-"]').length;
            countEl.textContent = remaining;

            if (remaining === 0) {
                tbody.innerHTML = '<tr id="emptyRow"><td colspan="8" class="empty-row">No pending requests.</td></tr>';
            }

            // Reload page after 1.5s to refresh approved table
            setTimeout(() => location.reload(), 1500);
        }

        setTimeout(() => { msgBox.style.display = 'none'; }, 3000);
    });
}
</script>

</body>
</html>