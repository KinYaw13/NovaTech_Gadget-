document.addEventListener("DOMContentLoaded", () => {
  const widget = document.querySelector("[data-ai-chatbot]");
  if (!widget) return;

  const button = widget.querySelector("[data-chatbot-button]");
  const panel = widget.querySelector("[data-chatbot-panel]");
  const close = widget.querySelector("[data-chatbot-close]");
  const form = widget.querySelector("[data-chatbot-form]");
  const input = widget.querySelector("[data-chatbot-input]");
  const messages = widget.querySelector("[data-chatbot-messages]");
  const emailInput = widget.querySelector("[data-chatbot-email]");
  const base = widget.dataset.base || "";

  let pendingRequest = null;
  let dragging = false;
  let moved = false;
  let startX = 0;
  let startY = 0;
  let startLeft = 0;
  let startTop = 0;
  const SAFE_MARGIN = 16;

  const saved = JSON.parse(localStorage.getItem("novatechChatbotPosition") || "null");
  if (saved && Number.isFinite(saved.left) && Number.isFinite(saved.top)) {
    widget.style.left = `${saved.left}px`;
    widget.style.top = `${saved.top}px`;
    widget.style.right = "auto";
    widget.style.bottom = "auto";
  }

  const clamp = (value, min, max) => Math.max(min, Math.min(max, value));

  const clampButtonToViewport = () => {
    const rect = widget.getBoundingClientRect();
    const width = rect.width || 68;
    const height = rect.height || 68;
    const left = clamp(rect.left, SAFE_MARGIN, Math.max(SAFE_MARGIN, window.innerWidth - width - SAFE_MARGIN));
    const top = clamp(rect.top, SAFE_MARGIN, Math.max(SAFE_MARGIN, window.innerHeight - height - SAFE_MARGIN));

    widget.style.left = `${left}px`;
    widget.style.top = `${top}px`;
    widget.style.right = "auto";
    widget.style.bottom = "auto";
    return { left, top, width, height, right: left + width, bottom: top + height };
  };

  const positionPanel = () => {
    if (panel.hidden) return;

    const buttonRect = clampButtonToViewport();
    panel.style.position = "fixed";
    panel.style.right = "auto";
    panel.style.bottom = "auto";
    panel.style.left = "0px";
    panel.style.top = "0px";

    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;
    const panelWidth = Math.min(panel.offsetWidth || 390, viewportWidth - SAFE_MARGIN * 2);
    const panelHeight = Math.min(panel.offsetHeight || 560, viewportHeight - SAFE_MARGIN * 2);
    const gap = 12;

    panel.style.width = `${panelWidth}px`;
    panel.style.maxWidth = `calc(100vw - ${SAFE_MARGIN * 2}px)`;
    panel.style.maxHeight = `calc(100vh - ${SAFE_MARGIN * 2}px)`;

    let left;
    let top;

    if (viewportWidth < 768) {
      left = (viewportWidth - panelWidth) / 2;
      top = viewportHeight - panelHeight - SAFE_MARGIN;
    } else {
      const canOpenLeft = buttonRect.right - panelWidth >= SAFE_MARGIN;
      const canOpenRight = buttonRect.left + panelWidth <= viewportWidth - SAFE_MARGIN;
      left = canOpenLeft ? buttonRect.right - panelWidth : (canOpenRight ? buttonRect.left : buttonRect.left + buttonRect.width / 2 - panelWidth / 2);

      const canOpenAbove = buttonRect.top - gap - panelHeight >= SAFE_MARGIN;
      const canOpenBelow = buttonRect.bottom + gap + panelHeight <= viewportHeight - SAFE_MARGIN;
      top = canOpenAbove ? buttonRect.top - panelHeight - gap : (canOpenBelow ? buttonRect.bottom + gap : buttonRect.top + buttonRect.height / 2 - panelHeight / 2);
    }

    left = clamp(left, SAFE_MARGIN, Math.max(SAFE_MARGIN, viewportWidth - panelWidth - SAFE_MARGIN));
    top = clamp(top, SAFE_MARGIN, Math.max(SAFE_MARGIN, viewportHeight - panelHeight - SAFE_MARGIN));

    panel.style.left = `${Math.round(left)}px`;
    panel.style.top = `${Math.round(top)}px`;
  };

  const openPanel = () => {
    panel.hidden = false;
    positionPanel();
    input.focus();
  };

  const togglePanel = () => {
    if (panel.hidden) {
      openPanel();
    } else {
      panel.hidden = true;
    }
  };

  const pointFromEvent = (event) => {
    const touch = event.touches?.[0] || event.changedTouches?.[0];
    return touch ? { x: touch.clientX, y: touch.clientY } : { x: event.clientX, y: event.clientY };
  };

  const startDrag = (event) => {
    const point = pointFromEvent(event);
    const rect = widget.getBoundingClientRect();
    dragging = true;
    moved = false;
    startX = point.x;
    startY = point.y;
    startLeft = rect.left;
    startTop = rect.top;
  };

  const moveDrag = (event) => {
    if (!dragging) return;
    const point = pointFromEvent(event);
    const dx = point.x - startX;
    const dy = point.y - startY;
    if (Math.abs(dx) > 5 || Math.abs(dy) > 5) moved = true;
    if (!moved) return;
    event.preventDefault();
    const left = Math.max(8, Math.min(window.innerWidth - 68, startLeft + dx));
    const top = Math.max(8, Math.min(window.innerHeight - 68, startTop + dy));
    widget.style.left = `${left}px`;
    widget.style.top = `${top}px`;
    widget.style.right = "auto";
    widget.style.bottom = "auto";
    positionPanel();
  };

  const endDrag = () => {
    if (!dragging) return;
    dragging = false;
    const rect = widget.getBoundingClientRect();
    localStorage.setItem("novatechChatbotPosition", JSON.stringify({ left: rect.left, top: rect.top }));
    if (!moved) {
      togglePanel();
    } else {
      const safeRect = clampButtonToViewport();
      localStorage.setItem("novatechChatbotPosition", JSON.stringify({ left: safeRect.left, top: safeRect.top }));
      positionPanel();
    }
  };

  const escapeHtml = (value) => String(value || "").replace(/[&<>"']/g, (char) => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;",
  }[char]));

  const assetUrl = (url) => {
    if (!url) return "";
    if (/^(https?:)?\/\//i.test(url) || url.startsWith("data:")) return url;
    return `${base}${url.replace(/^\/+/, "")}`;
  };

  const scrollToBottom = () => {
    messages.scrollTop = messages.scrollHeight;
  };

  const addMessage = (text, type = "bot") => {
    const bubble = document.createElement("div");
    bubble.className = `ai-message ${type}`;
    bubble.textContent = text;
    messages.appendChild(bubble);
    scrollToBottom();
    return bubble;
  };

  const addTyping = () => {
    const bubble = document.createElement("div");
    bubble.className = "ai-message bot ai-typing";
    bubble.textContent = "NovaTech AI is checking...";
    messages.appendChild(bubble);
    scrollToBottom();
    return bubble;
  };

  const addProductCard = (product) => {
    const card = document.createElement("div");
    card.className = "ai-product-card";
    const image = assetUrl(product.image);
    const img = image ? `<img src="${escapeHtml(image)}" alt="${escapeHtml(product.name)}">` : "";
    card.innerHTML = `
      ${img}
      <h4>${escapeHtml(product.name)}</h4>
      <p>${escapeHtml(product.brand || "")}${product.brand ? " · " : ""}${escapeHtml(product.category || "")}</p>
      <p><strong>${escapeHtml(product.price || "")}</strong>${product.rating ? ` · ${escapeHtml(product.rating)}` : ""}</p>
      <p>${escapeHtml(product.stock || "")}</p>
      <p>${escapeHtml(product.warranty || "")}</p>
      <div class="ai-product-actions">
        <a href="${base}${escapeHtml(product.view_url)}">View Product</a>
        <form method="post" action="${base}cart.php">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="product_id" value="${escapeHtml(product.id)}">
          <input type="hidden" name="quantity" value="1">
          <button type="submit" class="secondary">Add to Cart</button>
        </form>
      </div>
    `;
    messages.appendChild(card);
    scrollToBottom();
  };

  const addRequestCard = (request) => {
    const card = document.createElement("div");
    card.className = "ai-product-card ai-request-card";
    card.innerHTML = `
      <h4>${escapeHtml(request.product_name)}</h4>
      <p>${escapeHtml(request.brand || "")}${request.brand ? " · " : ""}${escapeHtml(request.category || "")}</p>
      <p><strong>${escapeHtml(request.estimated_price || "")}</strong></p>
      <p>${escapeHtml(request.status || "Pending Admin Approval")}</p>
    `;
    messages.appendChild(card);
    scrollToBottom();
  };

  const addRequestForm = (pending) => {
    pendingRequest = pending;
    const card = document.createElement("form");
    card.className = "ai-request-form";
    card.innerHTML = `
      <label>Product name
        <input name="product_name" value="${escapeHtml(pending.product_name || "")}" required>
      </label>
      <label>Expected price
        <input name="expected_price" placeholder="RM899" inputmode="decimal" required>
      </label>
      <label>Category
        <select name="category">
          ${["Smartphones", "Laptops", "Tablets", "Gaming Console", "Smartwatches", "Earbuds", "Headphones", "Chargers", "Keyboards", "Mice", "Accessories"].map((category) => `<option value="${category}"${category === pending.category ? " selected" : ""}>${category}</option>`).join("")}
        </select>
      </label>
      <label>Email optional
        <input name="email" type="email" value="${escapeHtml(emailInput?.value || "")}" placeholder="you@example.com">
      </label>
      <label>Notes optional
        <textarea name="notes" rows="2" placeholder="Any model, storage, color, or seller details?"></textarea>
      </label>
      <button type="submit">Send request to admin</button>
    `;

    card.addEventListener("submit", async (event) => {
      event.preventDefault();
      const data = new FormData(card);
      await submitRequest({
        message: pending.customer_message || pending.product_name || "",
        normalized_query: pending.normalized_query || "",
        product_name: data.get("product_name"),
        category: data.get("category"),
        brand: pending.brand || "",
        expected_price: data.get("expected_price"),
        email: data.get("email"),
        notes: data.get("notes"),
      });
    });

    messages.appendChild(card);
    scrollToBottom();
  };

  const submitRequest = async (payload) => {
    const loading = addTyping();
    const body = new FormData();
    Object.entries(payload).forEach(([key, value]) => body.append(key, value || ""));

    try {
      const response = await fetch(`${base}chatbot_request.php`, { method: "POST", body });
      const data = await response.json();
      loading.remove();
      addMessage(data.message || "Done.");
      if (data.ok && data.request) {
        pendingRequest = null;
        addRequestCard(data.request);
      }
    } catch (error) {
      loading.remove();
      addMessage("Sorry, the product request could not be saved right now.");
    }
  };

  const submitMessage = async (message, manualPrice = "") => {
    addMessage(message, "user");
    input.value = "";
    const loading = addTyping();
    const body = new FormData();
    body.append("message", pendingRequest?.customer_message || message);
    if (manualPrice) body.append("manual_price", manualPrice);
    body.append("email", emailInput?.value || "");

    try {
      const response = await fetch(`${base}chatbot_search.php`, { method: "POST", body });
      const data = await response.json();
      loading.remove();
      addMessage(data.message || "Done.");
      if (data.products) data.products.forEach(addProductCard);
      if (data.request) {
        pendingRequest = null;
        addRequestCard(data.request);
      }
      if (data.type === "need_price" || data.type === "invalid_price") {
        addRequestForm(data.pending_request || {});
      }
      if (data.type === "price_rejected") {
        pendingRequest = null;
      }
    } catch (error) {
      loading.remove();
      addMessage("Sorry, the chatbot service is unavailable right now.");
    }
  };

  button.addEventListener("mousedown", startDrag);
  document.addEventListener("mousemove", moveDrag);
  document.addEventListener("mouseup", endDrag);
  button.addEventListener("touchstart", startDrag, { passive: true });
  document.addEventListener("touchmove", moveDrag, { passive: false });
  document.addEventListener("touchend", endDrag);
  close.addEventListener("click", () => { panel.hidden = true; });
  window.addEventListener("resize", () => {
    const safeRect = clampButtonToViewport();
    localStorage.setItem("novatechChatbotPosition", JSON.stringify({ left: safeRect.left, top: safeRect.top }));
    positionPanel();
  });
  window.addEventListener("scroll", positionPanel, { passive: true });

  messages.querySelectorAll("[data-chatbot-chip]").forEach((chip) => {
    chip.addEventListener("click", () => {
      const value = chip.dataset.chatbotChip || chip.textContent.trim();
      if (value === "Request a gadget") {
        addMessage(value, "user");
        addMessage("Tell me the gadget name first, for example Steam Deck or Nintendo Switch.");
        input.focus();
        return;
      }
      if (value === "Check request status") {
        addMessage(value, "user");
        addMessage("Admin request status can be checked by admin in Requests. Customers will see updates when admin contacts them.");
        return;
      }
      submitMessage(value);
    });
  });

  form.addEventListener("submit", (event) => {
    event.preventDefault();
    const message = input.value.trim();
    if (!message) return;

    if (pendingRequest && /^(?:rm|myr)?\s*[0-9][0-9,]*(?:\.[0-9]{1,2})?$/i.test(message)) {
      submitMessage(pendingRequest.customer_message || pendingRequest.product_name || message, message);
      return;
    }

    pendingRequest = null;
    submitMessage(message);
  });
});
