function cancelListing(id){

    let formData = new FormData();

    formData.append("listing_id", id);

    fetch("../../api/listings/cancel.php", {

        method: "POST",
        body: formData

    })
    .then(res => res.json())
    .then(data => {

        if(data.success){

            document.getElementById("status"+id).innerHTML = "cancelled";

        }else{

            alert(data.message);
        }
    });
}