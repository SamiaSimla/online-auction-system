function renderListings(listings){
    var box = document.getElementById('listingCards');
    box.innerHTML = '';
    if(listings.length == 0){
        box.innerHTML = '<p>No active auctions found.</p>';
        return;
    }
    for(var i=0; i<listings.length; i++){
        var l = listings[i];
        var html = '';
        html += '<div class="card">';
        html += '<img class="thumb" src="../'+l.image_path+'">';
        html += '<h3><a href="listing_detail.php?id='+l.id+'">'+escapeHtml(l.title)+'</a></h3>';
        html += '<p>Current Bid: Tk. '+l.current_bid+'</p>';
        html += '<p>Bid Count: '+l.bid_count+'</p>';
        html += '<p>Time: <span class="countdown" data-end="'+l.end_datetime+'"></span></p>';
        html += '</div>';
        box.innerHTML += html;
    }
    updateCountdowns();
}

function loadListings(){
    var cid = document.getElementById('categoryFilter').value;
    var xhr = new XMLHttpRequest();
    xhr.open('GET','../controllers/listingsIndex.php?category_id='+cid,true);
    xhr.onload = function(){
        var res = JSON.parse(xhr.responseText);
        if(res.ok){ renderListings(res.listings); }
    };
    xhr.send();
}

function searchListings(){
    var q = document.getElementById('searchBox').value;
    var xhr = new XMLHttpRequest();
    xhr.open('GET','../controllers/listingsSearch.php?q='+encodeURIComponent(q),true);
    xhr.onload = function(){
        var res = JSON.parse(xhr.responseText);
        if(res.ok){ renderListings(res.listings); }
    };
    xhr.send();
}

function escapeHtml(text){
    var div = document.createElement('div');
    div.innerText = text;
    return div.innerHTML;
}

document.getElementById('categoryFilter').onchange = loadListings;
document.getElementById('searchBox').onkeyup = searchListings;
loadListings();
