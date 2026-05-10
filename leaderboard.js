// Leaderboard Module
class LeaderboardManager {
    constructor() {
        this.currentPeriod = 'weekly';
        this.data = {
            weekly: [
                { rank: 1, name: 'Sarah Johnson', avatar: '👩‍🍳', dishes: 45, score: 4500, medal: '🥇' },
                { rank: 2, name: 'Ahmed Khan', avatar: '🧑‍🍳', dishes: 38, score: 3800, medal: '🥈' },
                { rank: 3, name: 'Priya Sharma', avatar: '👩‍🍳', dishes: 32, score: 3200, medal: '🥉' },
                { rank: 4, name: 'Carlos Mendez', avatar: '🧑‍🍳', dishes: 28, score: 2800, medal: '🏅' },
                { rank: 5, name: 'Emma Wilson', avatar: '👩‍🍳', dishes: 25, score: 2500, medal: '🏅' }
            ],
            monthly: [
                { rank: 1, name: 'Priya Sharma', avatar: '👩‍🍳', dishes: 120, score: 12000, medal: '🥇' },
                { rank: 2, name: 'Sarah Johnson', avatar: '👩‍🍳', dishes: 110, score: 11000, medal: '🥈' },
                { rank: 3, name: 'Ahmed Khan', avatar: '🧑‍🍳', dishes: 95, score: 9500, medal: '🥉' }
            ],
            alltime: [
                { rank: 1, name: 'Sarah Johnson', avatar: '👩‍🍳', dishes: 450, score: 45000, medal: '🥇' },
                { rank: 2, name: 'Priya Sharma', avatar: '👩‍🍳', dishes: 420, score: 42000, medal: '🥈' },
                { rank: 3, name: 'Ahmed Khan', avatar: '🧑‍🍳', dishes: 380, score: 38000, medal: '🥉' }
            ]
        };
        this.init();
    }
    
    init() {
        this.render();
        this.attachEventListeners();
    }
    
    render() {
        const data = this.data[this.currentPeriod];
        const container = document.getElementById('leaderboardList');
        
        if (!container) return;
        
        container.innerHTML = data.map(leader => `
            <div class="leaderboard-item">
                <div class="rank rank-${leader.rank}">
                    ${leader.rank <= 3 ? `<span class="medal-animation">${leader.medal}</span>` : `#${leader.rank}`}
                </div>
                <div class="avatar">${leader.avatar}</div>
                <div class="info">
                    <strong>${leader.name}</strong>
                    <div><small>${leader.dishes} dishes cooked</small></div>
                </div>
                <div class="score">${leader.score.toLocaleString()} pts</div>
            </div>
        `).join('');
    }
    
    switchPeriod(period) {
        this.currentPeriod = period;
        this.render();
        showNotification(`Showing ${period} rankings`, 'info');
    }
    
    attachEventListeners() {
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                this.switchPeriod(btn.dataset.period);
            });
        });
    }
}

// Initialize when DOM ready
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('leaderboardList')) {
        window.leaderboardManager = new LeaderboardManager();
    }
});