// Profile Module with Edit Functionality
class ProfileManager {
    constructor() {
        this.profile = {
            name: 'Sarah Johnson',
            username: 'sarah_j',
            email: 'sarah@example.com',
            bio: 'Food enthusiast | Home Chef | Challenge Winner 🏆',
            avatar: 'https://randomuser.me/api/portraits/women/1.jpg'
        };
        this.stats = {
            uploads: 45,
            likes: 2340,
            challengesJoined: 12,
            challengesWon: 3
        };
        this.init();
    }
    
    init() {
        this.loadSavedProfile();
        this.loadStats();
        this.render();
        this.attachEventListeners();
    }
    
    loadSavedProfile() {
        const saved = localStorage.getItem('userProfile');
        if (saved) {
            this.profile = JSON.parse(saved);
        }
    }
    
    loadStats() {
        const saved = localStorage.getItem('userStats');
        if (saved) {
            this.stats = JSON.parse(saved);
        }
    }
    
    saveProfile() {
        localStorage.setItem('userProfile', JSON.stringify(this.profile));
        localStorage.setItem('userStats', JSON.stringify(this.stats));
    }
    
    render() {
        // Update profile header
        const nameEl = document.getElementById('profileName');
        const avatarEl = document.getElementById('profileAvatar');
        const usernameSpan = document.querySelector('.profile-header p.mb-2');
        const bioPara = document.querySelector('.profile-header p:last-of-type');
        
        if (nameEl) nameEl.textContent = this.profile.name;
        if (avatarEl) avatarEl.src = this.profile.avatar;
        if (usernameSpan) usernameSpan.textContent = `@${this.profile.username}`;
        if (bioPara && bioPara !== usernameSpan) bioPara.textContent = this.profile.bio;
        
        // Update stats
        const uploadCount = document.getElementById('uploadCount');
        const likesCount = document.getElementById('likesCount');
        const challengesCount = document.getElementById('challengesCount');
        const winsCount = document.getElementById('winsCount');
        
        if (uploadCount) uploadCount.textContent = this.stats.uploads;
        if (likesCount) likesCount.textContent = this.stats.likes.toLocaleString();
        if (challengesCount) challengesCount.textContent = this.stats.challengesJoined;
        if (winsCount) winsCount.textContent = this.stats.challengesWon;
        
        // Update form fields
        const editName = document.getElementById('editName');
        const editUsername = document.getElementById('editUsername');
        const editEmail = document.getElementById('editEmail');
        const editBio = document.getElementById('editBio');
        const editAvatar = document.getElementById('editAvatar');
        
        if (editName) editName.value = this.profile.name;
        if (editUsername) editUsername.value = this.profile.username;
        if (editEmail) editEmail.value = this.profile.email;
        if (editBio) editBio.value = this.profile.bio;
        if (editAvatar) editAvatar.value = this.profile.avatar;
    }
    
    updateProfile(formData) {
        this.profile.name = formData.get('name') || this.profile.name;
        this.profile.username = formData.get('username') || this.profile.username;
        this.profile.email = formData.get('email') || this.profile.email;
        this.profile.bio = formData.get('bio') || this.profile.bio;
        this.profile.avatar = formData.get('avatar') || this.profile.avatar;
        
        this.saveProfile();
        this.render();
        showNotification('Profile updated successfully!', 'success');
    }
    
    openModal() {
        const modal = document.getElementById('editProfileModal');
        if (modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }
    
    closeModal() {
        const modal = document.getElementById('editProfileModal');
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
        }
    }
    
    attachEventListeners() {
        const editBtn = document.getElementById('editProfileBtn');
        if (editBtn) {
            editBtn.addEventListener('click', () => this.openModal());
        }
        
        const closeBtns = document.querySelectorAll('#closeModalBtn, #cancelModalBtn');
        closeBtns.forEach(btn => {
            btn.addEventListener('click', () => this.closeModal());
        });
        
        const saveBtn = document.getElementById('saveProfileBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => {
                const formData = new FormData();
                formData.append('name', document.getElementById('editName')?.value);
                formData.append('username', document.getElementById('editUsername')?.value);
                formData.append('email', document.getElementById('editEmail')?.value);
                formData.append('bio', document.getElementById('editBio')?.value);
                formData.append('avatar', document.getElementById('editAvatar')?.value);
                this.updateProfile(formData);
                this.closeModal();
            });
        }
        
        // Close modal on outside click
        const modal = document.getElementById('editProfileModal');
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) this.closeModal();
            });
        }
    }
}

// Initialize when DOM ready
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('profileName')) {
        window.profileManager = new ProfileManager();
    }
});