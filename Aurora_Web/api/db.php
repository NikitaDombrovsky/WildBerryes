<?php

require_once __DIR__ . '/SupabaseClient.php';
require_once __DIR__ . '/dao/UserDao.php';
require_once __DIR__ . '/dao/ProductDao.php';
require_once __DIR__ . '/dao/OrderDao.php';
require_once __DIR__ . '/dao/FavoriteDao.php';

/**
 * Фасад — аналог SupabaseDb.java.
 * Использование:
 *   $db = SupabaseDb::getInstance();
 *   $user = $db->users()->findByEmail('test@example.com');
 */
class SupabaseDb {

    private static $instance = null;

    private $users;
    private $products;
    private $orders;
    private $favorites;

    private function __construct() {
        $this->users     = new UserDao();
        $this->products  = new ProductDao();
        $this->orders    = new OrderDao();
        $this->favorites = new FavoriteDao();
    }

    public static function getInstance() {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }

    public function users()     { return $this->users; }
    public function products()  { return $this->products; }
    public function orders()    { return $this->orders; }
    public function favorites() { return $this->favorites; }
}
