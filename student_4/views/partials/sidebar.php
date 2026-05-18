<?php
// views/partials/sidebar.php
// Include this in every admin page: include __DIR__ . '/../partials/sidebar.php';

$currentPage = basename($_SERVER['PHP_SELF']);

// Pending seller requests count (badge)
require_once __DIR__ . '/../../config/database.php';
$pdo = getDBConnection();
$pendingCount = (int)$pdo->query("
    SELECT COUNT(*) 
    FROM users 
    WHERE seller_verified = 0 AND role = 'buyer'
")->fetchColumn();
?>

<aside class="sidebar">
    <div class="sidebar-logo">
        <span class="logo-icon">⬡</span>
        <span class="logo-text">Auction<strong>Hub</strong></span>
    </div>

    <nav class="sidebar-nav">
        <a href="/student_4/views/admin_dashboard.php"
           class="nav-item <?= $currentPage === 'admin_dashboard.php' ? 'active' : '' ?>">
            <span class="nav-icon">▣</span> Dashboard
        </a>

        <a href="/student_4/views/auctions.php"
           class="nav-item <?= $currentPage === 'auctions.php' ? 'active' : '' ?>">
            <span class="nav-icon">◈</span> Auctions
        </a>

        <a href="/student_4/views/users.php"
           class="nav-item <?= $currentPage === 'users.php' ? 'active' : '' ?>">
            <span class="nav-icon">◉</span> Users
        </a>

        <a href="/student_4/views/categories.php"
           class="nav-item <?= $currentPage === 'categories.php' ? 'active' : '' ?>">
            <span class="nav-icon">◎</span> Categories
        </a>

        <a href="/student_4/views/seller_requests.php"
           class="nav-item <?= $currentPage === 'seller_requests.php' ? 'active' : '' ?>">
            <span class="nav-icon">◇</span> Seller Requests

            <?php if ($pendingCount > 0): ?>
                <span class="nav-badge"><?= $pendingCount ?></span>
            <?php endif; ?>
        </a>

        <a href="/student_4/views/reports.php"
           class="nav-item <?= $currentPage === 'reports.php' ? 'active' : '' ?>">
            <span class="nav-icon">◈</span> Reports
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="admin-badge">
            <div class="admin-avatar">A</div>
            <div>
                <div class="admin-name">Admin User</div>
                <div class="admin-role">Super Admin</div>
            </div>
        </div>

        <a href="/student_4/views/logout.php" class="logout-btn">
            Log out →
        </a>
    </div>
</aside>