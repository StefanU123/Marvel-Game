(function () {
    const enabledKey = 'marvel_notifications_enabled';
    const intervalMs = 300000;
    const button = document.getElementById('enable-notifications');
    const statusText = document.getElementById('notification-status');
    let timerId = null;

    function notificationsSupported() {
        return 'Notification' in window;
    }

    function setStatus(message) {
        if (statusText) {
            statusText.textContent = message;
        }
    }

    function notificationsEnabled() {
        return localStorage.getItem(enabledKey) === 'true';
    }

    async function fetchTopFive() {
        const response = await fetch('/api/leaderboard.php?limit=5', {
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) {
            return [];
        }

        const data = await response.json();
        if (!data || !Array.isArray(data.leaderboard)) {
            return [];
        }

        return data.leaderboard.slice(0, 5);
    }

    function buildNotificationBody(players) {
        const lines = ['Top 5 players:'];

        players.forEach(function (player, index) {
            const username = player.username || 'Unknown';
            const score = Number(player.total_score || 0).toLocaleString();
            lines.push((index + 1) + '. ' + username + ' - ' + score + ' pts');
        });

        lines.push('Can you beat them? Play now and climb the leaderboard!');
        return lines.join('\n');
    }

    async function showLeaderboardNotification() {
        if (!notificationsSupported() || Notification.permission !== 'granted') {
            return;
        }

        const players = await fetchTopFive();
        if (players.length === 0) {
            return;
        }

        new Notification('Marvel Game Leaderboard', {
            body: buildNotificationBody(players)
        });
    }

    function startNotifications() {
        if (!notificationsSupported() || Notification.permission !== 'granted') {
            return;
        }

        if (timerId !== null) {
            clearInterval(timerId);
        }

        timerId = setInterval(showLeaderboardNotification, intervalMs);
    }

    if (button) {
        button.addEventListener('click', async function () {
            if (!notificationsSupported()) {
                setStatus('Notifications are not supported in this browser.');
                return;
            }

            const permission = await Notification.requestPermission();

            if (permission === 'granted') {
                localStorage.setItem(enabledKey, 'true');
                setStatus('Notifications enabled.');
                await showLeaderboardNotification();
                startNotifications();
            } else if (permission === 'denied') {
                setStatus('Notifications blocked.');
            }
        });
    }

    if (notificationsSupported() && notificationsEnabled() && Notification.permission === 'granted') {
        startNotifications();
    }
})();
