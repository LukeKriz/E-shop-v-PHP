const days = document.getElementById('days');
const hours = document.getElementById('hours');
const minutes = document.getElementById('minutes');
const seconds = document.getElementById('seconds');


const currentYear = new Date().getFullYear();

const openning = new Date(`April 1 2023 21:00:00`);



function updateCountDown(){
    const currentTime=new Date();
    const diff = openning - currentTime

    const d = Math.floor(diff / 1000 / 60 / 60 / 24);
    const h = Math.floor(diff / 1000 / 60 / 60 ) % 24;
    const m = Math.floor(diff / 1000 / 60) % 60;
    const s = Math .floor(diff / 1000) % 60;

    days.innerHTML = d;
    hours.innerHTML = h;
    minutes.innerHTML = m;
    seconds.innerHTML = s;
}


setInterval(updateCountDown,1000);