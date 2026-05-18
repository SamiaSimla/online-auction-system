<?php
// ============================================
// api/admin_stats.php
// AJAX endpoint — returns JSON for charts
// ============================================

header('Content-Type: application/json');
session_start();

// Admin auth check
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

require_once __DIR__ . '/../controllers/AuctionController.php';

$controller = new AuctionController();
$data       = $controller->adminDashboard();

echo json_encode($data, JSON_PRETTY_PRINT);
