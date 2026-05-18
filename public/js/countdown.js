function updateCountdown(){

    let countdowns = document.querySelectorAll(".countdown");

    countdowns.forEach(function(item){

        let end = new Date(item.dataset.end).getTime();

        let now = new Date().getTime();

        let distance = end - now;

        if(distance <= 0){

            item.innerHTML = "Expired";

            return;
        }

        let days = Math.floor(distance / (1000 * 60 * 60 * 24));

        let hours = Math.floor(
            (distance % (1000 * 60 * 60 * 24))
            /
            (1000 * 60 * 60)
        );

        let minutes = Math.floor(
            (distance % (1000 * 60 * 60))
            /
            (1000 * 60)
        );

        let seconds = Math.floor(
            (distance % (1000 * 60))
            /
            1000
        );

        item.innerHTML =
            days+"d "+
            hours+"h "+
            minutes+"m "+
            seconds+"s";
    });
}

setInterval(updateCountdown, 1000);

updateCountdown();