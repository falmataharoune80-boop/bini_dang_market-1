function startFlashTimer(targetDate) {
    function update() {
        const now = new Date().getTime();
        const distance = targetDate - now;
        
        if (distance < 0) {
            document.getElementById('flash-timer').innerHTML = '<span>Offre terminée</span>';
            return;
        }
        
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (86400000)) / 3600000);
        const minutes = Math.floor((distance % 3600000) / 60000);
        const seconds = Math.floor((distance % 60000) / 1000);
        
        document.getElementById('flash-timer').innerHTML = `
            <div class="timer-box">${String(days).padStart(2, '0')}<span>j</span></div>
            <div class="timer-box">${String(hours).padStart(2, '0')}<span>h</span></div>
            <div class="timer-box">${String(minutes).padStart(2, '0')}<span>m</span></div>
            <div class="timer-box">${String(seconds).padStart(2, '0')}<span>s</span></div>
        `;
    }
    
    update();
    setInterval(update, 1000);
}