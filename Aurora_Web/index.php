<?php
require_once __DIR__ . '/api/db.php';
require_once __DIR__ . '/includes/product-card.php';

$db       = SupabaseDb::getInstance();
$products = $db->products()->getAll();
// На главной показываем первые 4 товара
$featured = array_slice($products, 0, 4);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AvroraShop@</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <div class="page">
    <main class="shell">
      <header class="top-bar">
        <a href="index.php" class="logo">
          <span class="logo-mark">AS</span>
          <span>AvroraShop@</span>
        </a>
        <div class="top-bar-actions">
          <nav class="nav">
            <a href="index.php"  class="nav-link is-active">Главная</a>
            <a href="women.php"  class="nav-link">Женщинам</a>
            <a href="man.php"    class="nav-link">Мужчинам</a>
          </nav>
          <button type="button" class="cart-btn" id="cart-open" aria-label="Открыть корзину">
            <span class="cart-btn__icon" aria-hidden="true">🛒</span>
            <span class="cart-btn__badge" id="cart-count" hidden>0</span>
          </button>
        </div>
      </header>

      <section class="left">
        <p class="kicker">Онлайн-каталог</p>
        <h1 class="hero-title">Одежда без лишнего</h1>
        <p class="hero-lead">
          Для неё и для него. Базовые силуэты и спокойная палитра — чёрный, графит и золотой акцент.
        </p>
        <div class="hero-meta">
          <span>Доставка по России</span>
          <span>Возврат 14 дней</span>
        </div>
        <div class="hero-actions">
          <a href="women.php" class="btn btn-primary">Женщинам</a>
          <a href="man.php"   class="btn btn-ghost">Мужчинам</a>
        </div>
      </section>

      <section class="right">
        <div class="right-head">
          <h2>Каталог</h2>
          <p>Несколько позиций из текущей коллекции — полный ассортимент в разделах выше.</p>
        </div>
        <div class="search-bar">
          <label class="sr-only" for="product-search">Поиск по названию товара</label>
          <input type="search" id="product-search" name="q" class="search-input"
                 placeholder="Поиск по названию…" autocomplete="off" spellcheck="false" />
        </div>
        <p class="search-empty" id="search-empty" hidden role="status">Ничего не найдено</p>
        <div class="products-grid" id="products-grid">
          <?php if (empty($featured)): ?>
            <p class="product-note" style="grid-column:1/-1;padding:16px 0">Товары не найдены</p>
          <?php else: ?>
            <?php foreach ($featured as $p): renderProductCard($p); endforeach; ?>
          <?php endif; ?>
        </div>
      </section>
    </main>
  </div>

  <div class="modal-overlay" id="cart-modal" hidden aria-hidden="true">
    <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="cart-modal-title">
      <div class="modal-header">
        <h2 id="cart-modal-title">Корзина</h2>
        <button type="button" class="modal-close" id="cart-close" aria-label="Закрыть">×</button>
      </div>
      <div class="cart-body" id="cart-items"></div>
      <div class="cart-footer">
        <p class="cart-total">Итого: <span id="cart-total">0 ₽</span></p>
        <button type="button" class="btn btn-primary cart-pay" id="cart-pay" disabled>Оплатить</button>
      </div>
    </div>
  </div>

  <script src="product-search.js"></script>
  <script src="cart.js"></script>
</body>
</html>
