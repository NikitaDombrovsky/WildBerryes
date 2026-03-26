/**
 * Поиск по товарам.
 * - Клиентская фильтрация по уже отрендеренным карточкам (мгновенно).
 * - При паузе 400 мс — живой поиск через /api/products.php?search=...
 *   с перерисовкой грида (только на .php-страницах).
 */
(function () {
  "use strict";

  var DEBOUNCE_MS = 400;

  function normalize(str) {
    return String(str).toLowerCase().trim();
  }

  function formatPrice(price) {
    return Math.round(price).toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ") + " \u20BD";
  }

  /** Строит HTML одной карточки из объекта продукта */
  function buildCard(p) {
    var mediaStyle = p.image_url
      ? "background-image:url('" + p.image_url + "');background-size:cover;background-position:center;"
      : "";
    var note = p.description
      ? '<p class="product-note">' + escHtml(p.description) + "</p>"
      : "";
    return (
      '<article class="product-card" data-product-id="' + p.id + '">' +
        '<div class="product-media" style="' + mediaStyle + '"></div>' +
        '<div class="product-row">' +
          "<div>" +
            '<p class="product-name">' + escHtml(p.name) + "</p>" +
            note +
            '<p class="product-price">' + formatPrice(p.price) + "</p>" +
          "</div>" +
          '<button type="button" class="add-btn" aria-label="\u0412 \u043a\u043e\u0440\u0437\u0438\u043d\u0443">+</button>' +
        "</div>" +
      "</article>"
    );
  }

  function escHtml(str) {
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  /** Клиентская фильтрация уже существующих карточек */
  function filterLocal(grid, empty, query) {
    var cards = grid.querySelectorAll(".product-card");
    var count = 0;
    var q = normalize(query);
    var parts = q ? q.split(/\s+/).filter(Boolean) : [];
    cards.forEach(function (card) {
      var name = normalize(card.querySelector(".product-name")
        ? card.querySelector(".product-name").textContent : "");
      var ok = parts.every(function (p) { return name.indexOf(p) !== -1; });
      card.hidden = !ok;
      if (ok) count++;
    });
    if (empty) empty.hidden = !query.trim() || count > 0;
  }

  /** Живой поиск через API — перерисовывает грид */
  function fetchAndRender(grid, empty, query) {
    var url = "api/products.php?search=" + encodeURIComponent(query);
    fetch(url)
      .then(function (r) { return r.json(); })
      .then(function (products) {
        if (!Array.isArray(products)) return;
        grid.innerHTML = products.length
          ? products.map(buildCard).join("")
          : "";
        if (empty) empty.hidden = products.length > 0;
      })
      .catch(function () {});
  }

  function init() {
    var grid  = document.querySelector(".products-grid");
    var input = document.getElementById("product-search");
    var empty = document.getElementById("search-empty");
    if (!grid || !input) return;

    var isPhp = window.location.pathname.indexOf(".php") !== -1 ||
                window.location.pathname.slice(-1) === "/";
    var timer = null;

    input.addEventListener("input", function () {
      var query = input.value;

      // Мгновенная клиентская фильтрация
      filterLocal(grid, empty, query);

      // Живой поиск через API с дебаунсом (только на PHP-страницах)
      if (!isPhp) return;
      clearTimeout(timer);
      if (!query.trim()) return; // пустой запрос — оставляем серверный рендер
      timer = setTimeout(function () {
        fetchAndRender(grid, empty, query);
      }, DEBOUNCE_MS);
    });

    input.addEventListener("search", function () {
      if (!input.value.trim()) {
        // Сброс поиска — показываем все карточки
        grid.querySelectorAll(".product-card").forEach(function (c) { c.hidden = false; });
        if (empty) empty.hidden = true;
      }
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
