<?php 
    require_once(__DIR__.'/../config/functions.php'); 
    require_once(__DIR__.'/../models/catagoryModel.php'); 
    include('header.php');
    $cats=getAllCategories();  
?>
<h2>Active Auctions</h2>
<div class="filters">
    <select id="categoryFilter">
        <option value="0">All Categories</option>
        <?php while($cat=mysqli_fetch_assoc($cats)){ ?>
            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
        <?php } ?>
    </select>
    <input type="text" id="searchBox" placeholder="Search auction title">
</div>
<div id="listingCards" class="cards"></div>
<script src="../public/js/browse.js"></script>
<?php include('footer.php'); ?>
