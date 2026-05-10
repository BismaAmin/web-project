// Dashboard Module with Charts
class DashboardManager {
    constructor() {
        this.stats = {
            uploads: 0,
            likes: 0,
            challengesJoined: 0,
            challengesWon: 0
        };
        this.activities = [];
        this.init();
    }
    
    async init() {
        this.loadStats();
        this.loadActivities();
        this.render();
        this.initChart();
    }
    
    loadStats() {
        const saved = localStorage.getItem('userStats');
        if (saved) {
            this.stats = JSON.parse(saved);
        } else {
            // Default stats
            this.stats = {
                uploads: 5,
                likes: 127,
                challengesJoined: 3,
                challengesWon: 1
            };
        }
    }
    
    loadActivities() {
        this.activities = [
            { type: 'upload', message: 'You uploaded "Spaghetti Carbonara"', time: '2 days ago' },
            { type: 'like', message: 'Your dish got 15 new likes', time: '3 days ago' },
            { type: 'challenge', message: 'You joined "Pasta Paradise" challenge', time: '5 days ago' },
            { type: 'win', message: 'You won "Vegan Delight" challenge! 🏆', time: '1 week ago' }
        ];
    }
    
    render() {
        // Update stats
        const uploadsEl = document.getElementById('totalUploads');
        const likesEl = document.getElementById('totalLikes');
        const joinedEl = document.getElementById('challengesJoined');
        const wonEl = document.getElementById('challengesWon');
        
        if (uploadsEl) uploadsEl.textContent = this.stats.uploads;
        if (likesEl) likesEl.textContent = this.stats.likes;
        if (joinedEl) joinedEl.textContent = this.stats.challengesJoined;
        if (wonEl) wonEl.textContent = this.stats.challengesWon;
        
        // Update username
        const username = localStorage.getItem('username') || 'Food Lover';
        const usernameEl = document.getElementById('username');
        if (usernameEl) usernameEl.textContent = username;
        
        // Update activities
        const activityList = document.getElementById('activityList');
        if (activityList) {
            activityList.innerHTML = this.activities.map(activity => `
                <div class="activity-item">
                    <div class="activity-icon">${this.getActivityIcon(activity.type)}</div>
                    <div class="activity-content">
                        <div>${activity.message}</div>
                        <small>${activity.time}</small>
                    </div>
                </div>
            `).join('');
        }
    }
    
    getActivityIcon(type) {
        const icons = {
            upload: '📸',
            like: '❤️',
            challenge: '🏆',
            win: '🎉'
        };
        return icons[type] || '📌';
    }
    
    initChart() {
        const canvas = document.getElementById('statsChart');
        if (!canvas || typeof Chart === 'undefined') return;
        
        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: ['Uploads', 'Likes', 'Challenges', 'Wins'],
                datasets: [{
                    label: 'Your Stats',
                    data: [this.stats.uploads, this.stats.likes, this.stats.challengesJoined, this.stats.challengesWon],
                    backgroundColor: ['#9B59B6', '#2ECC71', '#FAD7A1', '#E74C3C'],
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
}

// Initialize when DOM ready
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('totalUploads')) {
        window.dashboardManager = new DashboardManager();
    }
});