<?php
/**
 * Рендерит одну карточку товара.
 * @param array $p  — массив с ключами id, name, description, price, image_url, category
 */
function renderProductCard($p) {
    $id       = (int)$p['id'];
    $name     = htmlspecialchars($p['name'],        ENT_QUOTES);
    $note     = htmlspecialchars($p['description'], ENT_QUOTES);
    $price    = number_format((float)$p['price'], 0, '.', ' ') . ' ₽';
    $imageUrl = htmlspecialchars($p['image_url'],   ENT_QUOTES);
    $seller   = htmlspecialchars($p['seller_name'], ENT_QUOTES);

    $mediaStyle = $imageUrl
        ? "background-image:url('{$imageUrl}');background-size:cover;background-position:center;"
        : '';
    ?>
    <article class="product-card" data-product-id="<?= $id ?>">
        <div class="product-media" style="<?= $mediaStyle ?>"></div>
        <div class="product-row">
            <div>
                <p class="product-name"><?= $name ?></p>
                <?php if ($note): ?>
                    <p class="product-note"><?= $note ?></p>
                <?php endif; ?>
                <?php if ($seller): ?>
                    <p class="product-note" style="opacity:.6"><?= $seller ?></p>
                <?php endif; ?>
                <p class="product-price"><?= $price ?></p>
            </div>
            <button type="button" class="add-btn" aria-label="В корзину">+</button>
        </div>
    </article>
    <?php
}
