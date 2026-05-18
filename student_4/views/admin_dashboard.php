<?php
// ============================================
// views/admin_dashboard.php
// Admin analytics dashboard — Student 4
// ============================================
session_start();

// Auth guard
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /student_4/views/login.php');
    exit;
}

require_once __DIR__ . '/../controllers/AuctionController.php';

$controller = new AuctionController();
$data       = $controller->adminDashboard();
$stats      = $data['stats'];

// Prepare chart data as JSON for JS
$catLabels  = json_encode(array_column($data['listings_by_cat'], 'category'));
$catData    = json_encode(array_column($data['listings_by_cat'], 'total'));
$bidDays    = json_encode(array_column($data['bids_last_7'],     'day'));
$bidCounts  = json_encode(array_column($data['bids_last_7'],     'total_bids'));
$revLabels  = json_encode(array_column($data['revenue_by_cat'],  'category'));
$revData    = json_encode(array_column($data['revenue_by_cat'],  'revenue'));
$userDays   = json_encode(array_column($data['new_users_last_7'],'day'));
$userCounts = json_encode(array_column($data['new_users_last_7'],'new_users'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard — AuctionHub</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<link rel="stylesheet" href="../public/css/style.css">
</head>
<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<main class="main-content">

    <!-- Top bar -->
    <header class="topbar">
        <div class="topbar-left">
            <h1 class="page-title">Analytics Dashboard</h1>
            <p class="page-sub">Last updated: <?= date('d M Y, H:i') ?></p>
        </div>
        <div class="topbar-right">
            <button class="refresh-btn" onclick="refreshDashboard()">↻ Refresh</button>
        </div>
    </header>

    <!-- ===== STAT CARDS ===== -->
    <section class="stat-grid">

        <div class="stat-card accent-orange">
            <div class="stat-icon">◈</div>
            <div class="stat-value"><?= number_format($stats['total_buyers']) ?></div>
            <div class="stat-label">Total Buyers</div>
        </div>

        <div class="stat-card accent-teal">
            <div class="stat-icon">◉</div>
            <div class="stat-value"><?= number_format($stats['total_sellers']) ?></div>
            <div class="stat-label">Verified Sellers</div>
        </div>

        <div class="stat-card accent-lime">
            <div class="stat-icon">▣</div>
            <div class="stat-value"><?= number_format($stats['listings_active']) ?></div>
            <div class="stat-label">Live Auctions</div>
        </div>

        <div class="stat-card accent-blue">
            <div class="stat-icon">◇</div>
            <div class="stat-value"><?= number_format($stats['total_bids']) ?></div>
            <div class="stat-label">Total Bids</div>
        </div>

        <div class="stat-card accent-purple">
            <div class="stat-icon">$</div>
            <div class="stat-value">$<?= number_format($stats['total_revenue'], 2) ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>

        <div class="stat-card accent-red">
            <div class="stat-icon">✕</div>
            <div class="stat-value"><?= number_format($stats['reserve_not_met']) ?></div>
            <div class="stat-label">Reserve Not Met</div>
        </div>

    </section>

    <!-- ===== CHARTS ROW 1 ===== -->
    <section class="charts-row">

        <div class="chart-card wide">
            <div class="chart-header">
                <h2 class="chart-title">Bids — Last 7 Days</h2>
                <span class="chart-badge">Daily Activity</span>
            </div>
            <div class="chart-wrap">
                <canvas id="bidsChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h2 class="chart-title">Listings by Category</h2>
                <span class="chart-badge">Distribution</span>
            </div>
            <div class="chart-wrap">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

    </section>

    <!-- ===== CHARTS ROW 2 ===== -->
    <section class="charts-row">

        <div class="chart-card">
            <div class="chart-header">
                <h2 class="chart-title">Revenue by Category</h2>
                <span class="chart-badge">$USD</span>
            </div>
            <div class="chart-wrap">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <div class="chart-card wide">
            <div class="chart-header">
                <h2 class="chart-title">New Registrations — Last 7 Days</h2>
                <span class="chart-badge">Users</span>
            </div>
            <div class="chart-wrap">
                <canvas id="usersChart"></canvas>
            </div>
        </div>

    </section>

    <!-- ===== TABLES ROW ===== -->
    <section class="tables-row">

        <!-- Recent Ended Auctions -->
        <div class="table-card wide">
            <div class="table-header">
                <h2 class="chart-title">Recent Ended Auctions</h2>
            </div>
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Seller</th>
                            <th>Winner</th>
                            <th>Winning Bid</th>
                            <th>Ended</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['recent_auctions'])): ?>
                        <tr><td colspan="8" class="empty-row">No ended auctions yet.</td></tr>
                        <?php else: ?>
                        <?php foreach ($data['recent_auctions'] as $i => $a): ?>
                        <tr>
                            <td class="row-num"><?= $i + 1 ?></td>
                            <td class="title-cell">
                                <a href="auction_detail.php?id=<?= $a['id'] ?>" style="color:inherit;text-decoration:underline;text-underline-offset:3px">
                                    <?= htmlspecialchars($a['title']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($a['category'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($a['seller_name'] ?? '—') ?></td>
                            <td><?= $a['winner_name'] ? htmlspecialchars($a['winner_name']) : '<span class="no-winner">—</span>' ?></td>
                            <td class="amount-cell"><?= $a['winning_amount'] ? '$' . number_format($a['winning_amount'], 2) : '—' ?></td>
                            <td class="date-cell"><?= date('d M Y', strtotime($a['end_datetime'])) ?></td>
                            <td>
                                <?php if ($a['winner_name']): ?>
                                    <span class="badge badge-won">Sold</span>
                                <?php else: ?>
                                    <span class="badge badge-reserve">No Sale</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Sellers -->
        <div class="table-card">
            <div class="table-header">
                <h2 class="chart-title">Top Sellers</h2>
            </div>
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Seller</th>
                            <th>Auctions</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['top_sellers'])): ?>
                        <tr><td colspan="4" class="empty-row">No data yet.</td></tr>
                        <?php else: ?>
                        <?php foreach ($data['top_sellers'] as $i => $s): ?>
                        <tr>
                            <td>
                                <span class="rank rank-<?= $i + 1 ?>"><?= $i + 1 ?></span>
                            </td>
                            <td><?= htmlspecialchars($s['name']) ?></td>
                            <td><?= $s['total_auctions'] ?></td>
                            <td class="amount-cell">$<?= number_format($s['total_revenue'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </section>

</main>

<!-- ========== CHARTS JS ========== -->
<script>
Chart.defaults.font.family = "'DM Mono', monospace";
Chart.defaults.color = "#94a3b8";

const PALETTE = ['#f97316','#14b8a6','#a3e635','#3b82f6','#a855f7','#f43f5e','#eab308','#06b6d4'];

// ---- Bids last 7 days (Line) ----
new Chart(document.getElementById('bidsChart'), {
    type: 'line',
    data: {
        labels: <?= $bidDays ?>,
        datasets: [{
            label: 'Bids',
            data: <?= $bidCounts ?>,
            borderColor: '#f97316',
            backgroundColor: 'rgba(249,115,22,0.12)',
            borderWidth: 2.5,
            pointBackgroundColor: '#f97316',
            pointRadius: 5,
            tension: 0.4,
            fill: true,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,0.04)' } },
            y: { grid: { color: 'rgba(255,255,255,0.04)' }, beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});

// ---- Listings by category (Doughnut) ----
new Chart(document.getElementById('categoryChart'), {
    type: 'doughnut',
    data: {
        labels: <?= $catLabels ?>,
        datasets: [{
            data: <?= $catData ?>,
            backgroundColor: PALETTE,
            borderColor: '#0f172a',
            borderWidth: 3,
            hoverOffset: 8,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { padding: 16, boxWidth: 12 } }
        },
        cutout: '65%',
    }
});

// ---- Revenue by category (Bar) ----
new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
        labels: <?= $revLabels ?>,
        datasets: [{
            label: 'Revenue ($)',
            data: <?= $revData ?>,
            backgroundColor: PALETTE.map(c => c + 'cc'),
            borderColor: PALETTE,
            borderWidth: 1.5,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,0.04)' } },
            y: { grid: { color: 'rgba(255,255,255,0.04)' }, beginAtZero: true }
        }
    }
});

// ---- New users last 7 days (Bar) ----
new Chart(document.getElementById('usersChart'), {
    type: 'bar',
    data: {
        labels: <?= $userDays ?>,
        datasets: [{
            label: 'New Users',
            data: <?= $userCounts ?>,
            backgroundColor: 'rgba(168,85,247,0.7)',
            borderColor: '#a855f7',
            borderWidth: 1.5,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,0.04)' } },
            y: { grid: { color: 'rgba(255,255,255,0.04)' }, beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});

// ---- Refresh via AJAX ----
function refreshDashboard() {
    fetch('../api/admin_stats.php')
        .then(r => r.json())
        .then(d => {
            if (d.error) { alert('Session expired. Please log in again.'); return; }
            location.reload();
        })
        .catch(() => location.reload());
}
</script>

</body>
</html>
