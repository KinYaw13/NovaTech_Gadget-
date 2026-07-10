document.addEventListener("DOMContentLoaded", () => {
  const updateViewportClass = () => {
    const width = window.innerWidth;
    document.body.classList.remove("is-small-mobile", "is-mobile", "is-tablet", "is-desktop");

    if (width < 480) {
      document.body.classList.add("is-small-mobile");
    } else if (width < 768) {
      document.body.classList.add("is-mobile");
    } else if (width < 1025) {
      document.body.classList.add("is-tablet");
    } else {
      document.body.classList.add("is-desktop");
    }
  };

  const enhanceResponsiveTables = (root = document) => {
    root.querySelectorAll("table").forEach((table) => {
      if (table.closest(".email-template, .no-responsive-table")) return;

      const headers = Array.from(table.querySelectorAll("thead th")).map((header, index) => {
        const label = header.textContent.trim();
        return label || (index === table.querySelectorAll("thead th").length - 1 ? "Actions" : "Details");
      });

      if (!headers.length) return;

      table.classList.add("responsive-table");

      table.querySelectorAll("tbody tr").forEach((row) => {
        Array.from(row.children).forEach((cell, index) => {
          const label = headers[index] || "Details";
          if (!cell.dataset.label) cell.dataset.label = label;

          if (/action|warranty|return|review|delete|update/i.test(label) || cell.querySelector("button, .btn, form")) {
            cell.classList.add("actions-cell");
          }
        });
      });

      const parent = table.parentElement;
      const alreadyWrapped = parent?.classList.contains("table-wrapper") || parent?.classList.contains("admin-table-wrapper") || parent?.classList.contains("table-scroll");
      if (parent && !alreadyWrapped) {
        const wrapper = document.createElement("div");
        wrapper.className = table.closest("[class*='admin']") ? "admin-table-wrapper table-wrapper" : "table-wrapper";
        parent.insertBefore(wrapper, table);
        wrapper.appendChild(table);
      }
    });
  };

  updateViewportClass();
  enhanceResponsiveTables();

  const selectMenus = new Map();
  let activeSelect = null;

  const closeCustomSelect = (select) => {
    const target = select || activeSelect;
    if (!target) return;

    const state = selectMenus.get(target);
    if (!state) return;

    state.wrapper.classList.remove("is-open");
    state.trigger.setAttribute("aria-expanded", "false");
    state.menu.classList.remove("is-open");
    state.menu.replaceChildren();

    if (!select || activeSelect === target) activeSelect = null;
  };

  const closeAllCustomSelects = () => {
    Array.from(selectMenus.keys()).forEach((select) => closeCustomSelect(select));
  };

  const getSelectedOption = (select) => select.options[select.selectedIndex] || select.options[0];

  const updateCustomSelectLabel = (select) => {
    const state = selectMenus.get(select);
    if (!state) return;

    const option = getSelectedOption(select);
    state.value.textContent = option ? option.textContent.trim() : "Select";
    state.trigger.classList.toggle("is-placeholder", !option || option.value === "");
  };

  const positionCustomSelectMenu = (select) => {
    const state = selectMenus.get(select);
    if (!state || !state.menu.classList.contains("is-open")) return;

    const margin = 12;
    const triggerRect = state.trigger.getBoundingClientRect();
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;
    const menuWidth = Math.min(Math.max(triggerRect.width, 180), viewportWidth - margin * 2);
    const availableBelow = viewportHeight - triggerRect.bottom - margin;
    const availableAbove = triggerRect.top - margin;
    const preferredHeight = Math.min(340, Math.max(180, Math.max(availableBelow, availableAbove)));
    const openUp = availableBelow < 220 && availableAbove > availableBelow;
    const left = Math.min(Math.max(triggerRect.left, margin), viewportWidth - menuWidth - margin);
    const top = openUp
      ? Math.max(margin, triggerRect.top - preferredHeight - 8)
      : Math.min(triggerRect.bottom + 8, viewportHeight - preferredHeight - margin);

    state.menu.style.left = `${left}px`;
    state.menu.style.top = `${top}px`;
    state.menu.style.width = `${menuWidth}px`;
    state.menu.style.maxHeight = `${preferredHeight}px`;
    state.menu.classList.toggle("opens-up", openUp);
  };

  const buildCustomSelectMenu = (select) => {
    const state = selectMenus.get(select);
    if (!state) return;

    state.menu.replaceChildren();
    Array.from(select.options).forEach((option, index) => {
      if (option.hidden) return;

      const item = document.createElement("button");
      item.type = "button";
      item.className = "nt-select-option";
      item.setAttribute("role", "option");
      item.setAttribute("aria-selected", option.selected ? "true" : "false");
      item.dataset.value = option.value;
      item.dataset.index = String(index);
      item.textContent = option.textContent;

      if (option.disabled) {
        item.disabled = true;
        item.classList.add("is-disabled");
      }

      if (option.selected) item.classList.add("is-selected");

      item.addEventListener("click", () => {
        select.selectedIndex = index;
        select.dispatchEvent(new Event("input", { bubbles: true }));
        select.dispatchEvent(new Event("change", { bubbles: true }));
        updateCustomSelectLabel(select);
        closeCustomSelect(select);
        state.trigger.focus();
      });

      state.menu.appendChild(item);
    });
  };

  const openCustomSelect = (select) => {
    const state = selectMenus.get(select);
    if (!state || select.disabled) return;

    closeAllCustomSelects();
    buildCustomSelectMenu(select);
    activeSelect = select;
    state.wrapper.classList.add("is-open");
    state.trigger.setAttribute("aria-expanded", "true");
    state.menu.classList.add("is-open");
    positionCustomSelectMenu(select);
  };

  const moveCustomSelectSelection = (select, direction) => {
    const options = Array.from(select.options);
    if (!options.length) return;

    let nextIndex = select.selectedIndex;
    do {
      nextIndex = (nextIndex + direction + options.length) % options.length;
    } while (options[nextIndex]?.disabled && nextIndex !== select.selectedIndex);

    select.selectedIndex = nextIndex;
    select.dispatchEvent(new Event("input", { bubbles: true }));
    select.dispatchEvent(new Event("change", { bubbles: true }));
    updateCustomSelectLabel(select);
    buildCustomSelectMenu(select);
    positionCustomSelectMenu(select);
  };

  const enhanceSelect = (select) => {
    if (!(select instanceof HTMLSelectElement)) return;
    if (select.dataset.ntSelect === "ready" || select.multiple || select.closest(".nt-select")) return;
    if (select.matches("[data-native-select], .native-select")) return;

    const wrapper = document.createElement("div");
    wrapper.className = "nt-select";
    select.parentNode.insertBefore(wrapper, select);
    wrapper.appendChild(select);

    const trigger = document.createElement("button");
    trigger.type = "button";
    trigger.className = "nt-select-trigger";
    trigger.setAttribute("aria-haspopup", "listbox");
    trigger.setAttribute("aria-expanded", "false");

    const value = document.createElement("span");
    value.className = "nt-select-value";
    const arrow = document.createElement("span");
    arrow.className = "nt-select-arrow";
    arrow.setAttribute("aria-hidden", "true");
    arrow.textContent = "v";
    trigger.append(value, arrow);
    wrapper.appendChild(trigger);

    const menu = document.createElement("div");
    menu.className = "nt-select-menu";
    menu.setAttribute("role", "listbox");
    document.body.appendChild(menu);

    select.classList.add("nt-select-native");
    select.dataset.ntSelect = "ready";
    selectMenus.set(select, { wrapper, trigger, value, menu });
    updateCustomSelectLabel(select);

    trigger.addEventListener("click", (event) => {
      event.stopPropagation();
      if (activeSelect === select) {
        closeCustomSelect(select);
      } else {
        openCustomSelect(select);
      }
    });

    trigger.addEventListener("keydown", (event) => {
      if (event.key === "ArrowDown" || event.key === "ArrowUp") {
        event.preventDefault();
        if (activeSelect !== select) openCustomSelect(select);
        moveCustomSelectSelection(select, event.key === "ArrowDown" ? 1 : -1);
      }

      if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        activeSelect === select ? closeCustomSelect(select) : openCustomSelect(select);
      }

      if (event.key === "Escape") {
        closeCustomSelect(select);
      }
    });

    select.addEventListener("change", () => updateCustomSelectLabel(select));
    select.addEventListener("invalid", () => {
      trigger.focus();
      openCustomSelect(select);
    });

    select.form?.addEventListener("reset", () => {
      window.setTimeout(() => updateCustomSelectLabel(select), 0);
    });
  };

  const initCustomSelects = (root = document) => {
    root.querySelectorAll("select").forEach(enhanceSelect);
  };

  initCustomSelects();

  document.querySelectorAll("[data-discount-product]").forEach((productSelect) => {
    const form = productSelect.closest("form");
    const categorySelect = form?.querySelector("[data-discount-category]");
    if (!categorySelect) return;

    const filterDiscountProducts = () => {
      const selectedCategory = categorySelect.value;
      let selectedProductStillVisible = productSelect.value === "0" || productSelect.value === "";

      Array.from(productSelect.options).forEach((option) => {
        const optionCategory = option.dataset.category || "";
        const isPlaceholder = option.value === "0" || option.value === "";
        const shouldShow = isPlaceholder || selectedCategory === "" || optionCategory === selectedCategory;
        option.hidden = !shouldShow;
        option.disabled = !shouldShow;
        if (option.selected && shouldShow) selectedProductStillVisible = true;
      });

      if (!selectedProductStillVisible) {
        productSelect.value = "0";
      }

      productSelect.dispatchEvent(new Event("change", { bubbles: true }));
    };

    categorySelect.addEventListener("change", filterDiscountProducts);
    filterDiscountProducts();
  });

  document.addEventListener("click", (event) => {
    const clickedInsideSelect = event.target.closest?.(".nt-select") || event.target.closest?.(".nt-select-menu");
    if (!clickedInsideSelect) closeAllCustomSelects();
  });

  window.addEventListener("resize", () => {
    if (activeSelect) positionCustomSelectMenu(activeSelect);
    updateViewportClass();
  });

  window.addEventListener("scroll", () => {
    if (activeSelect) positionCustomSelectMenu(activeSelect);
  }, { passive: true });

  const selectObserver = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
      mutation.addedNodes.forEach((node) => {
        if (!(node instanceof Element)) return;
        if (node.matches("select")) enhanceSelect(node);
        initCustomSelects(node);
        if (node.matches("table")) {
          enhanceResponsiveTables(node.parentElement || document);
        } else {
          enhanceResponsiveTables(node);
        }
      });
    });
  });

  selectObserver.observe(document.body, { childList: true, subtree: true });

  const mobileToggle = document.querySelector(".mobile-menu-toggle");
  const mobileMenu = document.querySelector(".mobile-nav-menu");

  if (mobileToggle && mobileMenu) {
    const closeMobileMenu = () => {
      mobileToggle.classList.remove("is-open");
      mobileToggle.setAttribute("aria-expanded", "false");
      mobileMenu.classList.remove("is-open");
    };

    mobileToggle.addEventListener("click", (event) => {
      event.stopPropagation();
      const isOpen = mobileMenu.classList.toggle("is-open");
      mobileToggle.classList.toggle("is-open", isOpen);
      mobileToggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
    });

    mobileMenu.querySelectorAll("a").forEach((link) => {
      link.addEventListener("click", closeMobileMenu);
    });

    document.addEventListener("click", (event) => {
      if (!mobileMenu.classList.contains("is-open")) return;
      if (mobileMenu.contains(event.target) || mobileToggle.contains(event.target)) return;
      closeMobileMenu();
    });

    window.addEventListener("resize", () => {
      if (window.innerWidth >= 901) closeMobileMenu();
    });
  }

  const formatPrice = (value) => {
    const number = Number.parseFloat(value || "0");
    return `RM ${number.toLocaleString("en-MY", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
  };

  document.querySelectorAll("[data-color-options]").forEach((group) => {
    group.addEventListener("click", (event) => {
      const button = event.target.closest(".color-option");
      if (!button) return;

      group.querySelectorAll(".color-option").forEach((item) => item.classList.remove("selected"));
      button.classList.add("selected");

      const selectedInput = document.querySelector("[data-selected-color]");
      if (selectedInput) {
        selectedInput.value = button.dataset.colorName || "";
      }

      const image = document.querySelector(".detail-media .real-product-img");
      if (image && button.dataset.colorImage) {
        image.src = button.dataset.colorImage;
        image.style.display = "";
      }
    });
  });

  document.querySelectorAll("[data-variant-options]").forEach((group) => {
    group.addEventListener("click", (event) => {
      const button = event.target.closest(".variant-option");
      if (!button) return;

      group.querySelectorAll(".variant-option").forEach((item) => item.classList.remove("selected"));
      button.classList.add("selected");

      const price = button.dataset.variantPrice || "0";
      const priceDisplay = document.querySelector("[data-product-price]");
      const selectedVariant = document.querySelector("[data-selected-variant]");
      const selectedPrice = document.querySelector("[data-selected-price]");

      if (priceDisplay) priceDisplay.textContent = formatPrice(price);
      if (selectedVariant) selectedVariant.value = button.dataset.variantName || "";
      if (selectedPrice) selectedPrice.value = price;
    });
  });

  document.querySelectorAll("form").forEach((form) => {
    form.addEventListener("submit", () => {
      const button = form.querySelector("button[type='submit']");
      if (button) {
        button.dataset.originalText = button.textContent;
      }
    });
  });

  document.querySelectorAll("[data-add-color-row]").forEach((button) => {
    button.addEventListener("click", () => {
      const editor = button.previousElementSibling;
      const lastRow = editor?.querySelector(".color-image-row:last-child");
      if (!lastRow) return;

      const newRow = lastRow.cloneNode(true);
      newRow.querySelectorAll("input").forEach((input) => {
        if (input.type === "color") {
          input.value = "#d8d8d8";
        } else {
          input.value = "";
        }
      });
      newRow.querySelector(".color-image-current")?.replaceChildren();
      editor.appendChild(newRow);
    });
  });
});
