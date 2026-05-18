function placeBid(listingId){
    var amount = document.getElementById('bidAmount').value;
    var msg = document.getElementById('bidMessage');
    msg.innerHTML = '';

    var xhr = new XMLHttpRequest();
    xhr.open('POST','../controllers/bids.php',true);
    xhr.setRequestHeader('Content-type','application/x-www-form-urlencoded');
    xhr.onload = function(){
        var res = JSON.parse(xhr.responseText);
        if(res.ok){
            document.getElementById('currentBid').innerHTML = res.new_bid;
            document.getElementById('bidCount').innerHTML = res.bid_count;
            var row = '<tr><td>'+escapeHtml(res.bidder)+'</td><td>Tk. '+res.new_bid+'</td><td>'+res.time+'</td></tr>';
            document.getElementById('bidHistory').innerHTML = row + document.getElementById('bidHistory').innerHTML;
            document.getElementById('bidAmount').value = '';
            msg.className = 'success';
            msg.innerHTML = 'Bid placed successfully';
        }else{
            msg.className = 'error';
            msg.innerHTML = res.error;
        }
    };
    xhr.send('listing_id='+listingId+'&amount='+encodeURIComponent(amount));
}

function escapeHtml(text){
    var div = document.createElement('div');
    div.innerText = text;
    return div.innerHTML;
}
