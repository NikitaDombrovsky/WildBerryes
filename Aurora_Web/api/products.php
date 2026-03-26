<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/db.php';

$db       = SupabaseDb::getInstance();
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$search   = isset($_GET['search'])   ? trim($_GET['search'])   : '';

if ($search !== '') {
    $products = $db->products()->search($search);
} elseif ($category !== '') {
    $products = $db->products()->getByCategory($category);
} else {
    $products = $db->products()->getAll();
}

echo json_encode($products, JSON_UNESCAPED_UNICODE);
