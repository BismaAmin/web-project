// Ingredients Selection Module
class IngredientsManager {
    constructor() {
        this.selectedIngredients = new Set();
        this.ingredients = [];
        this.currentCategory = 'all';
        this.searchTerm = '';
        this.init();
    }
    
    async init() {
        await this.loadIngredients();
        this.loadSelections();
        this.render();
        this.attachEventListeners();
    }
    
    async loadIngredients() {
        // Default ingredients data
        this.ingredients = [
            { id: 1, name: 'Tomato', emoji: '🍅', category: 'Vegetables' },
            { id: 2, name: 'Onion', emoji: '🧅', category: 'Vegetables' },
            { id: 3, name: 'Garlic', emoji: '🧄', category: 'Herbs & Spices' },
            { id: 4, name: 'Chicken', emoji: '🍗', category: 'Meat' },
            { id: 5, name: 'Eggs', emoji: '🥚', category: 'Dairy' },
            { id: 6, name: 'Milk', emoji: '🥛', category: 'Dairy' },
            { id: 7, name: 'Flour', emoji: '🌾', category: 'Grains' },
            { id: 8, name: 'Rice', emoji: '🍚', category: 'Grains' },
            { id: 9, name: 'Pasta', emoji: '🍝', category: 'Grains' },
            { id: 10, name: 'Cheese', emoji: '🧀', category: 'Dairy' }
        ];
    }
    
    loadSelections() {
        const saved = localStorage.getItem('selectedIngredients');
        if (saved) {
            this.selectedIngredients = new Set(JSON.parse(saved));
        }
    }
    
    saveSelections() {
        localStorage.setItem('selectedIngredients', JSON.stringify([...this.selectedIngredients]));
    }
    
    toggleIngredient(id) {
        if (this.selectedIngredients.has(id)) {
            this.selectedIngredients.delete(id);
            showNotification('Ingredient removed', 'info');
        } else {
            this.selectedIngredients.add(id);
            showNotification('Ingredient added', 'success');
        }
        this.saveSelections();
        this.render();
        this.updateCount();
    }
    
    clearAll() {
        this.selectedIngredients.clear();
        this.saveSelections();
        this.render();
        this.updateCount();
        showNotification('All ingredients cleared', 'info');
    }
    
    findRecipes() {
        if (this.selectedIngredients.size === 0) {
            showNotification('Please select at least one ingredient', 'error');
            return;
        }
        sessionStorage.setItem('fromRecipes', 'true');
        window.location.href = 'recipes.html';
    }
    
    updateCount() {
        const countEl = document.getElementById('selectedCount');
        if (countEl) {
            countEl.textContent = `Selected: ${this.selectedIngredients.size} ingredient(s)`;
        }
    }
    
    render() {
        let filtered = this.ingredients;
        
        if (this.currentCategory !== 'all') {
            filtered = filtered.filter(i => i.category === this.currentCategory);
        }
        
        if (this.searchTerm) {
            filtered = filtered.filter(i => 
                i.name.toLowerCase().includes(this.searchTerm.toLowerCase())
            );
        }
        
        const grid = document.getElementById('ingredientsGrid');
        if (!grid) return;
        
        grid.innerHTML = filtered.map(ing => `
            <div class="ingredient-card ${this.selectedIngredients.has(ing.id) ? 'selected' : ''}" data-id="${ing.id}">
                <span class="ingredient-emoji">${ing.emoji}</span>
                <div class="ingredient-name">${ing.name}</div>
                <div class="ingredient-category">${ing.category}</div>
            </div>
        `).join('');
        
        // Add click handlers
        document.querySelectorAll('.ingredient-card').forEach(card => {
            card.addEventListener('click', () => {
                const id = parseInt(card.dataset.id);
                this.toggleIngredient(id);
            });
        });
    }
    
    attachEventListeners() {
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.searchTerm = e.target.value;
                this.render();
            });
        }
        
        const clearBtn = document.getElementById('clearAllBtn');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => this.clearAll());
        }
        
        const findBtn = document.getElementById('findRecipesBtn');
        if (findBtn) {
            findBtn.addEventListener('click', () => this.findRecipes());
        }
        
        // Category filters
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                this.currentCategory = btn.dataset.category;
                this.render();
            });
        });
    }
}

// Initialize when DOM ready
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('ingredientsGrid')) {
        window.ingredientsManager = new IngredientsManager();
    }
});