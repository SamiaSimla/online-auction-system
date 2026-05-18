<?php
// ============================================
// cron/close_auctions.php
// Run this every minute via cron:
//   * * * * * php /path/to/auction_project/cron/close_auctions.php >> /tmp/auction_cron.log 2>&1
// ============================================

require_once __DIR__ . '/../controllers/AuctionController.php';

$controller = new AuctionController();
$controller->closeExpiredAuctions();

echo "[" . date('Y-m-d H:i:s') . "] Auction closing job ran.\n";
