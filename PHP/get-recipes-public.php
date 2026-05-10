<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/config/database.php';

$response = ['success' => false, 'data' => []];
$ingredientIds = isset($_GET['ingredients']) ? array_map('intval', explode(',', $_GET['ingredients'])) : [];

try {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        $response['success'] = true;
        $response['source']  = 'fallback';
        $response['data']    = filterFallbackRecipes($ingredientIds);
        echo json_encode($response);
        exit();
    }

    // Modified query to include ingredients
    if (!empty($ingredientIds)) {
        $placeholders = implode(',', array_fill(0, count($ingredientIds), '?'));
        $stmt = $db->prepare("
            SELECT DISTINCT r.recipe_id as id, r.recipe_name as name,
                   r.image_path, r.cooking_time as time, r.difficulty,
                   r.total_likes as likes, r.instructions as steps, r.description
            FROM recipes r
            JOIN recipe_ingredients ri ON r.recipe_id = ri.recipe_id
            WHERE ri.ingredient_id IN ($placeholders)
            ORDER BY r.total_likes DESC
        ");
        $stmt->execute($ingredientIds);
    } else {
        $stmt = $db->query("
            SELECT recipe_id as id, recipe_name as name,
                   image_path, cooking_time as time, difficulty,
                   total_likes as likes, instructions as steps, description
            FROM recipes
            ORDER BY total_likes DESC
        ");
    }

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 🔧 FIX: Fetch ingredients for each recipe
    foreach ($rows as &$row) {
        $ingStmt = $db->prepare("
            SELECT ingredient_id 
            FROM recipe_ingredients 
            WHERE recipe_id = ?
        ");
        $ingStmt->execute([$row['id']]);
        $row['ingredients'] = $ingStmt->fetchAll(PDO::FETCH_COLUMN);
    }
    unset($row);

    if (empty($rows)) {
        $response['success'] = true;
        $response['source']  = 'fallback';
        $response['data']    = filterFallbackRecipes($ingredientIds);
    } else {
        $response['success'] = true;
        $response['source']  = 'database';
        $response['data']    = $rows;
    }

} catch (Exception $e) {
    $response['success'] = true;
    $response['source']  = 'fallback';
    $response['data']    = filterFallbackRecipes($ingredientIds);
}

echo json_encode($response);

function filterFallbackRecipes($ingredientIds) {
    $all = [
        ['id'=>1,'name'=>'Spaghetti Carbonara','image_path'=>null,'time'=>25,'difficulty'=>'Medium','likes'=>1200,'ingredients'=>[2,3,5,9,10],'description'=>'Classic Italian pasta dish with eggs and cheese.','steps'=>"1. Boil pasta in salted water until al dente\n2. Fry pancetta or bacon until crispy\n3. Whisk eggs with grated Parmesan cheese\n4. Drain pasta, mix with egg mixture off heat\n5. Add pancetta and black pepper, serve hot"],
        ['id'=>2,'name'=>'Margherita Pizza',   'image_path'=>null,'time'=>40,'difficulty'=>'Hard',  'likes'=>980, 'ingredients'=>[1,7,10,14,17],'description'=>'Simple pizza with tomato, mozzarella, and basil.','steps'=>"1. Prepare or buy pizza dough\n2. Spread tomato sauce evenly\n3. Add fresh mozzarella slices\n4. Top with fresh basil leaves\n5. Bake at 450°F (230°C) for 12-15 minutes"],
        ['id'=>3,'name'=>'Greek Salad',         'image_path'=>null,'time'=>15,'difficulty'=>'Easy',  'likes'=>2100,'ingredients'=>[1,2,10,14,25],'description'=>'Fresh Mediterranean salad with feta and olives.','steps'=>"1. Chop tomatoes, cucumber, and red onion\n2. Crumble feta cheese over vegetables\n3. Add kalamata olives\n4. Drizzle generously with olive oil\n5. Season with oregano, salt, and pepper"],
        ['id'=>4,'name'=>'Chicken Stir Fry',    'image_path'=>null,'time'=>20,'difficulty'=>'Medium','likes'=>1500,'ingredients'=>[2,3,4,13,14],'description'=>'Quick and healthy chicken stir fry.','steps'=>"1. Cut chicken into thin strips\n2. Heat olive oil in a wok or large pan\n3. Stir fry chicken until golden brown\n4. Add bell peppers, onion, and garlic\n5. Season with soy sauce, ginger, and serve over rice"],
        ['id'=>5,'name'=>'Classic Omelette',    'image_path'=>null,'time'=>10,'difficulty'=>'Easy',  'likes'=>800, 'ingredients'=>[5,10,18],'description'=>'Fluffy French-style omelette.','steps'=>"1. Beat eggs with a pinch of salt and pepper\n2. Melt butter in a non-stick pan over medium heat\n3. Pour in eggs and swirl gently\n4. Add cheese or fillings of your choice\n5. Fold gently and serve immediately"],
        ['id'=>6,'name'=>'Garlic Butter Rice',  'image_path'=>null,'time'=>20,'difficulty'=>'Easy',  'likes'=>650, 'ingredients'=>[3,8,18],'description'=>'Fluffy buttery garlic rice side dish.','steps'=>"1. Sauté minced garlic in butter until fragrant\n2. Add rice and stir to coat\n3. Pour in water or broth (2:1 ratio)\n4. Cover and cook on low for 15 minutes\n5. Fluff with fork, garnish with parsley"],
        ['id'=>7,'name'=>'Chocolate Cake',      'image_path'=>null,'time'=>60,'difficulty'=>'Medium','likes'=>3200,'ingredients'=>[5,6,7,18,19],'description'=>'Rich and moist chocolate cake.','steps'=>"1. Preheat oven to 350°F (175°C)\n2. Mix flour, sugar, and cocoa powder\n3. Cream butter and add eggs one at a time\n4. Combine wet and dry ingredients with milk\n5. Bake for 30-35 minutes, frost when cooled"],
    ];
    
    if (empty($ingredientIds)) return $all;
    
    return array_values(array_filter($all, function($r) use ($ingredientIds) {
        return count(array_intersect($r['ingredients'], $ingredientIds)) > 0;
    }));
}
?>