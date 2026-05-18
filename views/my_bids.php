<?php 
    require_once(__DIR__.'/../config/functions.php'); 
    require_once(__DIR__.'/../models/bidModel.php'); 
    requireLogin(); 
    include('header.php'); 
    $rows=getBuyerBidRows($_SESSION['user_id']); 
?>
<h2>My Bids</h2>
<table>
    <tr>
        <th>Auction</th>
        <th>My Highest Bid</th>
        <th>Current Leading Bid</th>
        <th>Status</th>
        <th>Contact</th>
    </tr>
<?php while($row=mysqli_fetch_assoc($rows)){ ?>
    <?php
    $status = '';
    $contact = '-';
    if($row['status']=='active'){
        $status = ((float)$row['my_highest_bid'] == (float)$row['current_bid']) ? 'Leading' : 'Outbid';
    }else{
        if($row['current_bid'] < $row['reserve_price']){
            $status = 'Reserve Not Met';
        }elseif((float)$row['my_highest_bid'] == (float)$row['winner_amount']){
            $status = '🏆 You Won!';
            $contact = htmlspecialchars($row['seller_name']).'<br>'.htmlspecialchars($row['seller_email']);
        }else{
            $status = 'Lost';
        }
    }
    ?>
    <tr>
        <td><a href="listing_detail.php?id=<?php echo $row['listing_id']; ?>"><?php echo htmlspecialchars($row['title']); ?></a></td>
        <td>Tk. <?php echo htmlspecialchars($row['my_highest_bid']); ?></td>
        <td>Tk. <?php echo htmlspecialchars($row['current_bid']); ?></td>
        <td><span class="badge"><?php echo htmlspecialchars($status); ?></span></td>
        <td><?php echo $contact; ?></td>
    </tr>
<?php } ?>
</table>
<?php include('footer.php'); ?>
