<?php
    require_once(__DIR__.'/../config/functions.php');
    require_once(__DIR__.'/../models/listingModel.php');
    require_once(__DIR__.'/../models/bidModel.php');
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $listing = getListingById($id);
    include('header.php');
    if(!$listing){ 
        echo '<p class="error">Listing not found</p>'; 
        include('footer.php'); 
        exit(); 
    }
    $bids = getLastBids($id, 10);
    $winner = $listing['winner_bid_id'] ? getWinningBid($id) : null;
?>
<h2><?php echo htmlspecialchars($listing['title']); ?></h2>
<div class="detail">
    <img class="detail-img" src="../<?php echo htmlspecialchars($listing['image_path']); ?>">
    <p><b>Category:</b> <?php echo htmlspecialchars($listing['category_name']); ?></p>
    <p><b>Description:</b> <?php echo htmlspecialchars($listing['description']); ?></p>
    <p><b>Seller:</b> <?php echo htmlspecialchars($listing['seller_name']); ?></p>
    <p><b>Current Highest Bid:</b> Tk. <span id="currentBid"><?php echo htmlspecialchars($listing['current_bid']); ?></span></p>
    <p><b>Bid Count:</b> <span id="bidCount"><?php echo htmlspecialchars($listing['bid_count']); ?></span></p>
    <p><b>Time:</b> <span class="countdown" data-end="<?php echo htmlspecialchars($listing['end_datetime']); ?>"></span></p>
    <?php if($listing['status']=='ended'){ ?>
        <?php if($listing['current_bid'] < $listing['reserve_price']){ ?>
            <p class="badge cancelled">Reserve not met</p>
        <?php }elseif($winner){ ?>
            <p class="badge ended">Winner: <?php echo htmlspecialchars($winner['name']); ?></p>
        <?php } ?>
    <?php } ?>
</div>

<?php if(isset($_SESSION['user_id']) && $listing['status']=='active' && strtotime($listing['end_datetime'])>time()){ ?>
<div class="box">
    <h3>Place Bid</h3>
    <input type="number" step="0.01" id="bidAmount" placeholder="Enter amount">
    <button onclick="placeBid(<?php echo htmlspecialchars($listing['id']); ?>)">Place Bid</button>
    <p id="bidMessage" class="error"></p>
</div>
<?php } ?>

<h3>Last 10 Bids</h3>
<table>
    <thead>
        <tr>
            <th>Bidder</th>
            <th>Amount</th>
            <th>Time</th>
        </tr>
    </thead>
    <tbody id="bidHistory">
    <?php while($bid=mysqli_fetch_assoc($bids)){ ?>
        <tr>
            <td><?php echo htmlspecialchars($bid['bidder_name']); ?></td>
            <td>Tk. <?php echo htmlspecialchars($bid['amount']); ?></td>
            <td><?php echo htmlspecialchars($bid['created_at']); ?></td>
        </tr>
    <?php } ?>
    </tbody>
</table>
<script src="../public/js/bid.js"></script>
<?php include('footer.php'); ?>
