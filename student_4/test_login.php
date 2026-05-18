<?php
// ============================================
// test_login.php
// Quick login helper for testing (REMOVE IN PRODUCTION!)
// Usage: test_login.php?as=buyer or ?as=seller or ?as=admin
// ============================================

session_start();

$role = $_GET['as'] ?? 'buyer';

require_once __DIR__ . '/config/database.php';
$pdo = getDBConnection();

// Get a test user based on role
if ($role === 'admin') {
    $stmt = $pdo->query("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
} elseif ($role === 'seller') {
    $stmt = $pdo->query("SELECT * FROM users WHERE seller_verified = 1 LIMIT 1");
} else {
    $stmt = $pdo->query("SELECT * FROM users WHERE role = 'buyer' AND seller_verified = 0 LIMIT 1");
}

$user = $stmt->fetch();

if ($user) {
    $_SESSION['user'] = [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
        'seller_verified' => $user['seller_verified']
    ];
    
    echo "<h2>✅ Test Login Successful</h2>";
    echo "<p>Logged in as: <strong>{$user['name']}</strong> ({$user['email']})</p>";
    echo "<p>Role: <strong>{$user['role']}</strong></p>";
    echo "<p>Seller Verified: <strong>" . ($user['seller_verified'] ? 'Yes' : 'No') . "</strong></p>";
    
    echo "<hr>";
    echo "<h3>Quick Links:</h3>";
    echo "<ul>";
    
    if ($user['role'] === 'admin') {
        echo "<li><a href='/online-auction-system/student_4/views/admin_dashboard.php'>Admin Dashboard</a></li>";
        echo "<li><a href='/online-auction-system/student_4/views/auctions.php'>View All Auctions</a></li>";
        echo "<li><a href='/online-auction-system/student_4/views/reports.php'>Reports</a></li>";
    } elseif ($user['seller_verified']) {
        echo "<li><a href='/online-auction-system/student_4/views/seller_dashboard.php'>My Seller Dashboard</a></li>";
        echo "<li><a href='/online-auction-system/student_4/views/my_bids.php'>My Bids</a></li>";
    } else {
        echo "<li><a href='/online-auction-system/student_4/views/my_bids.php'>My Bids</a></li>";
        echo "<li><a href='/online-auction-system/student_4/views/browse.php'>Browse Auctions</a></li>";
    }
    
    echo "</ul>";
    
    echo "<hr>";
    echo "<h3>Test Winner/Seller Contact Pages:</h3>";
    echo "<p>Find an ended auction with a winner, then test:</p>";
    echo "<ul>";
    echo "<li><code>/online-auction-system/student_4/views/contact_info.php?listing=1</code> (replace 1 with actual listing ID)</li>";
    echo "</ul>";
    
} else {
    echo "<h2>❌ No test user found</h2>";
    echo "<p>Please run the database.sql file to seed test data.</p>";
}