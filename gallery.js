// Gallery Module with Like System
class GalleryManager {
    constructor() {
        this.galleryItems = [];
        this.likedItems = [];
        this.visibleItems = 6;
        this.init();
    }
    
    async init() {
        this.loadGalleryData();
        this.loadLikedItems();
        this.render();
        this.attachEventListeners();
    }
    
    loadGalleryData() {
        this.galleryItems = [
            { id: 1, image: '🍲', username: 'sarah', dish: 'Creamy Pasta', likes: 234, caption: 'Amazing pasta!', time: '2 days ago' },
            { id: 2, image: '🥘', username: 'ahmed', dish: 'Chicken Curry', likes: 189, caption: 'Best curry ever!', time: '3 days ago' },
            { id: 3, image: '🍛', username: 'priya', dish: 'Biryani', likes: 156, caption: 'My special biryani', time: '5 days ago' },
            { id: 4, image: '🍣', username: 'ken', dish: 'Sushi Roll', likes: 98, caption: 'Fresh sushi!', time: '1 week ago' },
            { id: 5, image: '🥩', username: 'carlos', dish: 'Grilled Steak', likes: 267, caption: 'Perfect medium rare', time: '2 days ago' },
            { id: 6, image: '🥗', username: 'emma', dish: 'Quinoa Salad', likes: 145, caption: 'Healthy and tasty', time: '4 days ago' },
            { id: 7, image: '🍜', username: 'ming', dish: 'Ramen', likes: 312, caption: 'Authentic Japanese', time: '1 day ago' },
            { id: 8, image: '🍰', username: 'lisa', dish: 'Chocolate Cake', likes: 423, caption: 'So delicious!', time: '3 days ago' }
        ];
    }
    
    loadLikedItems() {
        const saved = localStorage.getItem('likedItems');
        this.likedItems = saved ? JSON.parse(saved) : [];
    }
    
    saveLikedItems() {
        localStorage.setItem('likedItems', JSON.stringify(this.likedItems));
    }
    
    toggleLike(id) {
        if (this.likedItems.includes(id)) {
            this.likedItems = this.likedItems.filter(i => i !== id);
            showNotification('Like removed', 'info');
        } else {
            this.likedItems.push(id);
            showNotification('Liked!', 'success');
        }
        this.saveLikedItems();
        this.render();
    }
    
    shareDish(id) {
        const dish = this.galleryItems.find(i => i.id === id);
        navigator.clipboard.writeText(`Check out ${dish.dish} on Sizzle & Share!`);
        showNotification('Share link copied!', 'success');
    }
    
    loadMore() {
        this.visibleItems += 3;
        this.render();
    }
    
    render() {
        const grid = document.getElementById('galleryGrid');
        if (!grid) return;
        
        const itemsToShow = this.galleryItems.slice(0, this.visibleItems);
        
        grid.innerHTML = itemsToShow.map(item => `
            <div class="gallery-item">
                <div class="gallery-image">${item.image}</div>
                <div class="gallery-info">
                    <h3>${item.dish}</h3>
                    <p>${item.caption}</p>
                    <small>by @${item.username} • ${item.time}</small>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem;">
                        <button class="like-btn ${this.likedItems.includes(item.id) ? 'liked' : ''}" onclick="galleryManager.toggleLike(${item.id})">
                            ❤️ <span id="like-count-${item.id}">${item.likes + (this.likedItems.includes(item.id) ? 1 : 0)}</span>
                        </button>
                        <button class="btn btn-outline btn-sm" onclick="galleryManager.shareDish(${item.id})">📤 Share</button>
                    </div>
                </div>
            </div>
        `).join('');
        
        const loadMoreBtn = document.getElementById('loadMoreBtn');
        if (loadMoreBtn) {
            loadMoreBtn.style.display = this.visibleItems >= this.galleryItems.length ? 'none' : 'block';
        }
    }
    
    attachEventListeners() {
        const loadMoreBtn = document.getElementById('loadMoreBtn');
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', () => this.loadMore());
        }
    }
}

// Initialize when DOM ready
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('galleryGrid')) {
        window.galleryManager = new GalleryManager();
    }
});