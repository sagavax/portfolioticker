
setInterval(updateClock, 1000);

updateClock();

function updateClock() {
    // 1. Get the current time
    const now = new Date();
    let hours = now.getHours();
    let minutes = now.getMinutes();
    let seconds = now.getSeconds();

    // 2. Determine if it's AM or PM
    const period = hours >= 12 ? 'PM' : 'AM';

    // 3. Convert from 24-hour to 12-hour format
    // If hours is 0 (midnight), it should be 12
    if (hours === 0) {
        hours = 12;
    }
    // If hours is greater than 12, subtract 12
    if (hours > 12) {
        hours = hours - 12;
    }

    // 4. Add a leading zero to single-digit minutes and seconds
    minutes = minutes < 10 ? '0' + minutes : minutes;
    seconds = seconds < 10 ? '0' + seconds : seconds;
    
    // 5. Construct the final time string
    const timeString = `${hours}:${minutes}:${seconds}`;

    // 6. Update the HTML elements with the new time
    document.getElementById('clock').textContent = timeString;
    //document.getElementById('clock-period').textContent = period;
}