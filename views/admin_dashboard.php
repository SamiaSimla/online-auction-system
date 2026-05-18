<?php 
    require_once(__DIR__.'/../config/functions.php'); 
    require_once(__DIR__.'/../models/userModel.php'); 
    requireAdmin(); 
    include('header.php'); 
    $requests=getPendingSellerRequests(); 
?>
<h2>Admin Dashboard</h2>
<h3>Seller Requests</h3>
<table>
    <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Motivation</th>
        <th>Action</th>
    </tr>
    <?php while($r=mysqli_fetch_assoc($requests)){ ?>
    <tr id="sellerRow<?php echo $r['user_id']; ?>">
        <td><?php echo htmlspecialchars($r['name']); ?></td>
        <td><?php echo htmlspecialchars($r['email']); ?></td>
        <td><?php echo htmlspecialchars($r['phone']); ?></td>
        <td><?php echo htmlspecialchars($r['motivation']); ?></td>
        <td>
            <button onclick="approveSeller(<?php echo $r['user_id']; ?>)">Approve</button>
            <button onclick="rejectSeller(<?php echo $r['user_id']; ?>)">Reject</button>
            <span id="sellerMsg<?php echo $r['user_id']; ?>"></span>
        </td>
    </tr>
    <?php } ?>
</table>
<script src="../public/js/admin.js"></script>
<?php include('footer.php'); ?>