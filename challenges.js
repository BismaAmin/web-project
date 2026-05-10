// Challenge Module with Countdown Timer
class ChallengeManager {
    constructor() {
        this.joined = localStorage.getItem('challengeJoined') === 'true';
        this.participantCount = 234;
        this.timeLeft = { days: 3, hours: 12, minutes: 30, seconds: 0 };
        this.init();
    }
    
    init() {
        this.startCountdown();
        this.updateUI();
        this.attachEventListeners();
    }
    
    startCountdown() {
        const countdownEl = document.getElementById('countdown');
        if (!countdownEl) return;
        
        setInterval(() => {
            if (this.timeLeft.days > 0 || this.timeLeft.hours > 0 || 
                this.timeLeft.minutes > 0 || this.timeLeft.seconds > 0) {
                
                countdownEl.innerHTML = `${this.timeLeft.days}d ${this.timeLeft.hours}h ${this.timeLeft.minutes}m ${this.timeLeft.seconds}s`;
                
                if (this.timeLeft.seconds > 0) {
                    this.timeLeft.seconds--;
                } else {
                    this.timeLeft.seconds = 59;
                    if (this.timeLeft.minutes > 0) {
                        this.timeLeft.minutes--;
                    } else {
                        this.timeLeft.minutes = 59;
                        if (this.timeLeft.hours > 0) {
                            this.timeLeft.hours--;
                        } else {
                            this.timeLeft.hours = 23;
                            if (this.timeLeft.days > 0) {
                                this.timeLeft.days--;
                            }
                        }
                    }
                }
            } else {
                countdownEl.innerHTML = "Challenge Ended!";
            }
        }, 1000);
    }
    
    updateUI() {
        const joinBtn = document.getElementById('joinChallengeBtn');
        const submissionForm = document.getElementById('submissionForm');
        const participantSpan = document.getElementById('participantCount');
        
        if (participantSpan) {
            participantSpan.textContent = this.participantCount;
        }
        
        if (this.joined && submissionForm) {
            submissionForm.style.display = 'block';
            if (joinBtn) {
                joinBtn.textContent = '✓ Already Joined';
                joinBtn.disabled = true;
                joinBtn.style.opacity = '0.6';
            }
        }
    }
    
    joinChallenge() {
        if (this.joined) return;
        
        this.joined = true;
        localStorage.setItem('challengeJoined', 'true');
        
        const submissionForm = document.getElementById('submissionForm');
        const joinBtn = document.getElementById('joinChallengeBtn');
        
        if (submissionForm) submissionForm.style.display = 'block';
        if (joinBtn) {
            joinBtn.textContent = '✓ Joined Successfully!';
            joinBtn.disabled = true;
            joinBtn.style.opacity = '0.6';
        }
        
        this.participantCount++;
        const participantSpan = document.getElementById('participantCount');
        if (participantSpan) participantSpan.textContent = this.participantCount;
        
        showNotification('You joined the challenge! Good luck! 🎉', 'success');
        this.triggerConfetti();
    }
    
    submitEntry(dishName, description, imageFile) {
        if (!dishName || !description) {
            showNotification('Please fill all fields!', 'error');
            return false;
        }
        
        if (!imageFile) {
            showNotification('Please upload a photo of your dish!', 'error');
            return false;
        }
        
        showNotification('Your entry has been submitted! Results will be announced soon. 🎉', 'success');
        
        // Save submission
        const submissions = JSON.parse(localStorage.getItem('challengeSubmissions') || '[]');
        submissions.push({
            dishName: dishName,
            description: description,
            date: new Date().toISOString()
        });
        localStorage.setItem('challengeSubmissions', JSON.stringify(submissions));
        
        return true;
    }
    
    triggerConfetti() {
        const colors = ['#9B59B6', '#6C3483', '#FAD7A1', '#2ECC71', '#E74C3C'];
        for (let i = 0; i < 100; i++) {
            const confetti = document.createElement('div');
            confetti.style.cssText = `
                position: fixed;
                width: 10px;
                height: 10px;
                background: ${colors[Math.floor(Math.random() * colors.length)]};
                left: ${Math.random() * 100}%;
                top: -10px;
                border-radius: 50%;
                pointer-events: none;
                z-index: 10000;
                animation: fall ${Math.random() * 2 + 2}s linear forwards;
            `;
            document.body.appendChild(confetti);
            setTimeout(() => confetti.remove(), 3000);
        }
    }
    
    attachEventListeners() {
        const joinBtn = document.getElementById('joinChallengeBtn');
        if (joinBtn) {
            joinBtn.addEventListener('click', () => this.joinChallenge());
        }
        
        const submitForm = document.getElementById('challengeSubmitForm');
        if (submitForm) {
            submitForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const dishName = submitForm.querySelector('input[name="dish_name"]')?.value;
                const description = submitForm.querySelector('textarea[name="description"]')?.value;
                const imageFile = document.getElementById('challengeImage')?.files[0];
                
                if (this.submitEntry(dishName, description, imageFile)) {
                    submitForm.reset();
                }
            });
        }
    }
}

// Add confetti animation style
const confettiStyle = document.createElement('style');
confettiStyle.textContent = `
    @keyframes fall {
        to {
            transform: translateY(100vh) rotate(360deg);
            opacity: 0;
        }
    }
`;
document.head.appendChild(confettiStyle);

// Initialize when DOM ready
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('challengeSubmitForm') || document.getElementById('joinChallengeBtn')) {
        window.challengeManager = new ChallengeManager();
    }
});