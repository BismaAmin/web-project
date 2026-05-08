// Recipes Module with 3D Flip Cards
class RecipesManager {
    constructor() {
        this.recipes = [];
        this.selectedIngredients = [];
        this.filters = {
            sort: 'default',
            difficulty: 'all',
            time: 'all'
        };
        this.init();
    }
    
    async init() {
        await this.loadRecipes();
        this.loadSelectedIngredients();
        this.render();
        this.attachEventListeners();
    }
    
    async loadRecipes() {
        this.recipes = [
            { id: 1, name: 'Spaghetti Carbonara', image: '🍝', time: 25, difficulty: 'Medium', likes: 1200, ingredients: [1,2,3,4,5,9], steps: '1. Boil pasta\n2. Fry bacon\n3. Mix eggs and cheese\n4. Combine everything' },
            { id: 2, name: 'Margherita Pizza', image: '🍕', time: 40, difficulty: 'Hard', likes: 980, ingredients: [1,3,9,10], steps: '1. Prepare dough\n2. Add tomato sauce\n3. Top with cheese and basil\n4. Bake at 450°F' },
            { id: 3, name: 'Greek Salad', image: '🥗', time: 15, difficulty: 'Easy', likes: 2100, ingredients: [1,2,10,11,12], steps: '1. Chop vegetables\n2. Add feta cheese\n3. Add olives\n4. Dress with olive oil' },
            { id: 4, name: 'Chicken Stir Fry', image: '🥘', time: 20, difficulty: 'Medium', likes: 1500, ingredients: [2,3,4,13], steps: '1. Cut chicken\n2. Stir fry vegetables\n3. Add chicken\n4. Add sauce' },
            { id: 5, name: 'Omelette', image: '🍳', time: 10, difficulty: 'Easy', likes: 800, ingredients: [2,3,5], steps: '1. Beat eggs\n2. Add fillings\n3. Cook in pan\n4. Fold and serve' },
            { id: 6, name: 'Garlic Butter Rice', image: '🍚', time: 20, difficulty: 'Easy', likes: 650, ingredients: [3,8,14], steps: '1. Sauté garlic in butter\n2. Add rice\n3. Add water\n4. Cook until done' }
        ];
    }
    
    loadSelectedIngredients() {
        const saved = localStorage.getItem('selectedIngredients');
        if (saved) {
            this.selectedIngredients = JSON.parse(saved);
        }
    }
    
    filterRecipes() {
        let filtered = [...this.recipes];
        
        // Filter by ingredients
        if (this.selectedIngredients.length > 0) {
            filtered = filtered.filter(recipe =>
                recipe.ingredients.some(ing => this.selectedIngredients.includes(ing))
            );
        }
        
        // Filter by difficulty
        if (this.filters.difficulty !== 'all') {
            filtered = filtered.filter(r => r.difficulty === this.filters.difficulty);
        }
        
        // Filter by time
        if (this.filters.time !== 'all') {
            const [min, max] = this.filters.time.split('-');
            if (max) {
                filtered = filtered.filter(r => r.time >= parseInt(min) && r.time <= parseInt(max));
            } else {
                filtered = filtered.filter(r => r.time >= 60);
            }
        }
        
        // Sort
        if (this.filters.sort === 'time-asc') {
            filtered.sort((a, b) => a.time - b.time);
        } else if (this.filters.sort === 'time-desc') {
            filtered.sort((a, b) => b.time - a.time);
        } else if (this.filters.sort === 'popular') {
            filtered.sort((a, b) => b.likes - a.likes);
        }
        
        return filtered;
    }
    
    render() {
        const filtered = this.filterRecipes();
        const grid = document.getElementById('recipesGrid');
        
        if (!grid) return;
        
        if (filtered.length === 0) {
            grid.innerHTML = '<div class="no-results">😢 No recipes found. Try different ingredients!</div>';
            return;
        }
        
        grid.innerHTML = filtered.map(recipe => `
            <div class="flip-card">
                <div class="flip-card-inner">
                    <div class="flip-card-front">
                        <div class="recipe-image">${recipe.image}</div>
                        <div class="recipe-info">
                            <h3>${recipe.name}</h3>
                            <div>⏱️ ${recipe.time} min</div>
                            <div class="difficulty-${recipe.difficulty.toLowerCase()}">${recipe.difficulty}</div>
                            <div>❤️ ${recipe.likes} likes</div>
                        </div>
                    </div>
                    <div class="flip-card-back">
                        <h3>📖 Instructions</h3>
                        <p style="white-space: pre-line;">${recipe.steps}</p>
                        <div class="recipe-actions">
                            <button class="btn btn-outline" onclick="recipesManager.saveRecipe(${recipe.id})">❤️ Save</button>
                            <button class="btn btn-primary" onclick="recipesManager.cookRecipe(${recipe.id})">👨‍🍳 Cook</button>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }
    
    saveRecipe(id) {
        showNotification('Recipe saved to favorites!', 'success');
    }
    
    cookRecipe(id) {
        showNotification('Get ready to cook! Instructions sent.', 'success');
    }
    
    attachEventListeners() {
        const sortBy = document.getElementById('sortBy');
        if (sortBy) {
            sortBy.addEventListener('change', (e) => {
                this.filters.sort = e.target.value;
                this.render();
            });
        }
        
        const difficultyFilter = document.getElementById('difficultyFilter');
        if (difficultyFilter) {
            difficultyFilter.addEventListener('change', (e) => {
                this.filters.difficulty = e.target.value;
                this.render();
            });
        }
        
        const timeFilter = document.getElementById('timeFilter');
        if (timeFilter) {
            timeFilter.addEventListener('change', (e) => {
                this.filters.time = e.target.value;
                this.render();
            });
        }
    }
}

// Initialize when DOM ready
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('recipesGrid')) {
        window.recipesManager = new RecipesManager();
    }
});