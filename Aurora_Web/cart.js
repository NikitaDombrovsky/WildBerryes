(function () {
  "use strict";

  var STORAGE_KEY = "avrora_cart_v1";

  /**
   * Подставьте сюда URL вашей платёжной страницы (ЮKassa, банк, Stripe и т.д.).
   */
  var PAYMENT_URL = "https://example.com/oplata";

  function parsePriceRub(text) {
    var num = String(text).replace(/[^\d]/g, "");
    return parseInt(num, 10) || 0;
  }

  function formatRub(amount) {
    var n = Math.round(amount);
    return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ") + " ₽";
  }

  function loadCart() {
    try {
      var raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) return [];
      var data = JSON.parse(raw);
      return Array.isArray(data) ? data : [];
    } catch (e) {
      return [];
    }
  }

  function saveCart(items) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
  }

  function getCart() {
    return loadCart();
  }

  function setCart(items) {
    saveCart(items);
    updateBadge();
    renderCart();
  }

  function addItem(product) {
    var items = loadCart();
    var i;
    for (i = 0; i < items.length; i++) {
      if (items[i].id === product.id) {
        items[i].qty += 1;
        setCart(items);
        return;
      }
    }
    items.push({
      id: product.id,
      name: product.name,
      priceLabel: product.priceLabel,
      priceRub: product.priceRub,
      qty: 1,
    });
    setCart(items);
  }

  function removeOne(id) {
    var items = loadCart();
    var next = [];
    for (var i = 0; i < items.length; i++) {
      if (items[i].id !== id) {
        next.push(items[i]);
        continue;
      }
      if (items[i].qty > 1) {
        next.push({
          id: items[i].id,
          name: items[i].name,
          priceLabel: items[i].priceLabel,
          priceRub: items[i].priceRub,
          qty: items[i].qty - 1,
        });
      }
    }
    setCart(next);
  }

  function removeLine(id) {
    var items = loadCart().filter(function (it) {
      return it.id !== id;
    });
    setCart(items);
  }

  function cartTotalRub() {
    var items = loadCart();
    var sum = 0;
    for (var i = 0; i < items.length; i++) {
      sum += items[i].priceRub * items[i].qty;
    }
    return sum;
  }

  function updateBadge() {
    var el = document.getElementById("cart-count");
    if (!el) return;
    var n = 0;
    loadCart().forEach(function (it) {
      n += it.qty;
    });
    el.textContent = String(n);
    el.hidden = n === 0;
  }

  function renderCart() {
    var container = document.getElementById("cart-items");
    var totalEl = document.getElementById("cart-total");
    var payBtn = document.getElementById("cart-pay");
    if (!container) return;

    var items = loadCart();
    container.innerHTML = "";

    if (items.length === 0) {
      var empty = document.createElement("p");
      empty.className = "cart-empty-msg";
      empty.textContent = "Корзина пуста";
      container.appendChild(empty);
      if (totalEl) totalEl.textContent = "0 ₽";
      if (payBtn) {
        payBtn.disabled = true;
        payBtn.setAttribute("aria-disabled", "true");
      }
      return;
    }

    if (payBtn) {
      payBtn.disabled = false;
      payBtn.removeAttribute("aria-disabled");
    }

    items.forEach(function (it) {
      var line = document.createElement("div");
      line.className = "cart-line";
      line.setAttribute("data-id", it.id);

      var info = document.createElement("div");
      info.className = "cart-line-info";

      var name = document.createElement("p");
      name.className = "cart-line-name";
      name.textContent = it.name;

      var meta = document.createElement("p");
      meta.className = "cart-line-meta";
      meta.textContent =
        it.qty > 1
          ? it.qty + " × " + it.priceLabel + " · " + formatRub(it.priceRub * it.qty)
          : it.priceLabel;

      info.appendChild(name);
      info.appendChild(meta);

      var actions = document.createElement("div");
      actions.className = "cart-line-actions";

      var btnMinus = document.createElement("button");
      btnMinus.type = "button";
      btnMinus.className = "cart-qty-btn";
      btnMinus.setAttribute("data-action", "minus");
      btnMinus.setAttribute("data-id", it.id);
      btnMinus.setAttribute("aria-label", "Уменьшить количество");
      btnMinus.textContent = "−";

      var btnRemove = document.createElement("button");
      btnRemove.type = "button";
      btnRemove.className = "cart-remove-btn";
      btnRemove.setAttribute("data-action", "remove");
      btnRemove.setAttribute("data-id", it.id);
      btnRemove.textContent = "Удалить";

      actions.appendChild(btnMinus);
      actions.appendChild(btnRemove);

      line.appendChild(info);
      line.appendChild(actions);
      container.appendChild(line);
    });

    if (totalEl) totalEl.textContent = formatRub(cartTotalRub());
  }

  function openModal() {
    var modal = document.getElementById("cart-modal");
    if (!modal) return;
    modal.hidden = false;
    modal.setAttribute("aria-hidden", "false");
    document.body.classList.add("modal-open");
    renderCart();
    var closeBtn = document.getElementById("cart-close");
    if (closeBtn) closeBtn.focus();
  }

  function closeModal() {
    var modal = document.getElementById("cart-modal");
    if (!modal) return;
    modal.hidden = true;
    modal.setAttribute("aria-hidden", "true");
    document.body.classList.remove("modal-open");
  }

  function init() {
    updateBadge();

    var modal = document.getElementById("cart-modal");
    if (modal) {
      modal.addEventListener("click", function (e) {
        if (e.target === modal) closeModal();
      });
    }

    document.addEventListener("click", function (e) {
      var btn = e.target.closest(".add-btn");
      if (btn) {
        var card = btn.closest(".product-card");
        if (!card) return;
        var id = card.getAttribute("data-product-id");
        var nameEl = card.querySelector(".product-name");
        var priceEl = card.querySelector(".product-price");
        if (!id || !nameEl || !priceEl) return;
        var name = nameEl.textContent.trim();
        var priceLabel = priceEl.textContent.trim();
        var priceRub = parsePriceRub(priceLabel);
        addItem({
          id: id,
          name: name,
          priceLabel: priceLabel,
          priceRub: priceRub,
        });
        return;
      }

      if (e.target.closest("#cart-open")) {
        e.preventDefault();
        openModal();
        return;
      }

      if (e.target.closest("#cart-close")) {
        closeModal();
        return;
      }

      var actionBtn = e.target.closest("[data-action]");
      if (actionBtn && actionBtn.closest("#cart-items")) {
        var lineId = actionBtn.getAttribute("data-id");
        var action = actionBtn.getAttribute("data-action");
        if (!lineId) return;
        if (action === "minus") removeOne(lineId);
        if (action === "remove") removeLine(lineId);
      }
    });

    var payBtn = document.getElementById("cart-pay");
    if (payBtn) {
      payBtn.addEventListener("click", function () {
        if (loadCart().length === 0) return;
        window.location.href = PAYMENT_URL;
      });
    }

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape") {
        var modal = document.getElementById("cart-modal");
        if (modal && !modal.hidden) closeModal();
      }
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
