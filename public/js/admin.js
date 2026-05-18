function approveSeller(userId){
    sellerAction('../controllers/approveCheck.php', userId, true);
}

function rejectSeller(userId){
    sellerAction('../controllers/rejectCheck.php', userId, false);
}

function sellerAction(url, userId, approve){
    let xhttp = new XMLHttpRequest();
    xhttp.open('POST',url,true);
    xhttp.setRequestHeader('Content-type','application/x-www-form-urlencoded');
    xhttp.onload = function(){
        let res = JSON.parse(xhttp.responseText);
        if(res.ok){
            if(approve){
                document.getElementById('sellerMsg'+userId).innerHTML = 'Approved ✓';
                let row = document.getElementById('sellerRow'+userId);
                let btns = row.getElementsByTagName('button');
                while(btns.length > 0){ btns[0].parentNode.removeChild(btns[0]); }
            }else{
                let row2 = document.getElementById('sellerRow'+userId);
                row2.parentNode.removeChild(row2);
            }
        }else{
            alert(res.error);
        }
    };
    xhttp.send('user_id='+userId);
}