<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/config/database.php';

$response = ['success' => false, 'data' => []];

try {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        $response['success'] = true;
        $response['source']  = 'fallback';
        $response['data']    = getDefaultIngredients();
        echo json_encode($response);
        exit();
    }

    $stmt = $db->query("SELECT ingredient_id as id, ingredient_name as name, image_path, category FROM ingredients ORDER BY category, ingredient_name");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Normalise image_path: empty → null
    foreach ($rows as &$row) {
        if (empty($row['image_path'])) {
            $row['image_path'] = null;
        }
    }
    unset($row);

    if (empty($rows)) {
        $response['success'] = true;
        $response['source']  = 'fallback';
        $response['data']    = getDefaultIngredients();
    } else {
        $response['success'] = true;
        $response['source']  = 'database';
        $response['data']    = $rows;
    }

} catch (Exception $e) {
    $response['success'] = true;
    $response['source']  = 'fallback';
    $response['data']    = getDefaultIngredients();
}

echo json_encode($response);

function getDefaultIngredients() {
    return [
        ['id'=>1, 'name'=>'Tomato',       'image_path'=>null, 'category'=>'Vegetables'],
        ['id'=>2, 'name'=>'Onion',         'image_path'=>null, 'category'=>'Vegetables'],
        ['id'=>3, 'name'=>'Garlic',        'image_path'=>null, 'category'=>'Herbs & Spices'],
        ['id'=>4, 'name'=>'Chicken',       'image_path'=>null, 'category'=>'Meat'],
        ['id'=>5, 'name'=>'Eggs',          'image_path'=>null, 'category'=>'Dairy'],
        ['id'=>6, 'name'=>'Milk',          'image_path'=>null, 'category'=>'Dairy'],
        ['id'=>7, 'name'=>'Flour',         'image_path'=>null, 'category'=>'Grains'],
        ['id'=>8, 'name'=>'Rice',          'image_path'=>null, 'category'=>'Grains'],
        ['id'=>9, 'name'=>'Pasta',         'image_path'=>null, 'category'=>'Grains'],
        ['id'=>10,'name'=>'Cheese',        'image_path'=>null, 'category'=>'Dairy'],
        ['id'=>11,'name'=>'Potato',        'image_path'=>null, 'category'=>'Vegetables'],
        ['id'=>12,'name'=>'Carrot',        'image_path'=>null, 'category'=>'Vegetables'],
        ['id'=>13,'name'=>'Bell Pepper',   'image_path'=>null, 'category'=>'Vegetables'],
        ['id'=>14,'name'=>'Olive Oil',     'image_path'=>null, 'category'=>'Oils'],
        ['id'=>15,'name'=>'Salt',          'image_path'=>null, 'category'=>'Seasonings'],
        ['id'=>16,'name'=>'Black Pepper',  'image_path'=>null, 'category'=>'Seasonings'],
        ['id'=>17,'name'=>'Basil',         'image_path'=>null, 'category'=>'Herbs & Spices'],
        ['id'=>18,'name'=>'Butter',        'image_path'=>null, 'category'=>'Dairy'],
        ['id'=>19,'name'=>'Sugar',         'image_path'=>null, 'category'=>'Sweeteners'],
        ['id'=>20,'name'=>'Beef',          'image_path'=>null, 'category'=>'Meat'],
    ];
}
?>
