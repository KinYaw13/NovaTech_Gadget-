(function () {
  function money(value) {
    return 'RM ' + Number(value || 0).toLocaleString('en-MY', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  function setSection(section, active) {
    if (!section) return;
    section.hidden = !active;
    section.querySelectorAll('input, select, textarea').forEach(function (field) {
      field.disabled = !active;
    });
  }

  function clearFieldError(name) {
    var error = document.querySelector('[data-error-for="' + name + '"]');
    if (error) error.textContent = '';
  }

  function setFieldError(name, message) {
    var error = document.querySelector('[data-error-for="' + name + '"]');
    if (error) error.textContent = message;
  }

  function selectedDelivery() {
    return document.querySelector('input[name="delivery_method"]:checked');
  }

  function updateDeliveryCards() {
    document.querySelectorAll('.delivery-option').forEach(function (card) {
      var input = card.querySelector('input[type="radio"]');
      card.classList.toggle('selected', !!input && input.checked);
    });
  }

  function updateSummary() {
    var summary = document.querySelector('[data-checkout-summary]');
    var deliveryInput = selectedDelivery();
    if (!summary || !deliveryInput) return;

    var subtotal = Number(summary.dataset.subtotal || 0);
    var discount = Number(summary.dataset.discount || 0);
    var deliveryFee = Number(deliveryInput.dataset.deliveryFee || 0);
    var taxableSubtotal = Math.max(0, subtotal - discount);
    var tax = taxableSubtotal * 0.06;
    var total = taxableSubtotal + deliveryFee + tax;

    var deliveryNode = summary.querySelector('[data-summary-delivery]');
    var taxNode = summary.querySelector('[data-summary-tax]');
    var totalNode = summary.querySelector('[data-summary-total]');
    var labelNode = summary.querySelector('[data-delivery-summary-label]');

    if (deliveryNode) deliveryNode.textContent = money(deliveryFee);
    if (taxNode) taxNode.textContent = money(tax);
    if (totalNode) totalNode.textContent = money(total);
    if (labelNode) labelNode.textContent = deliveryInput.dataset.deliveryLabel || '';
  }

  function updatePaymentFields() {
    var payment = document.querySelector('[data-payment-method]');
    var method = payment ? payment.value : 'Credit / Debit Card';
    setSection(document.querySelector('[data-card-fields]'), method === 'Credit / Debit Card');
    setSection(document.querySelector('[data-online-banking-fields]'), method === 'Online Banking');
    setSection(document.querySelector('[data-cod-fields]'), method === 'Cash on Delivery');
  }

  function validateVisiblePayment() {
    var payment = document.querySelector('[data-payment-method]');
    var method = payment ? payment.value : 'Credit / Debit Card';
    var valid = true;

    ['bank_name', 'cardholder_name', 'card_number', 'expiry_date', 'cvv', 'online_bank_name', 'account_holder_name'].forEach(clearFieldError);

    if (method === 'Online Banking') {
      var bank = document.querySelector('[name="online_bank_name"]');
      var holder = document.querySelector('[name="account_holder_name"]');

      if (!bank || bank.value.trim() === '') {
        setFieldError('online_bank_name', 'Please select a bank before OTP is sent.');
        valid = false;
      }
      if (!holder || holder.value.trim() === '') {
        setFieldError('account_holder_name', 'Account holder name is required before OTP is sent.');
        valid = false;
      }
    }

    if (method === 'Credit / Debit Card') {
      var cardBank = document.querySelector('[name="bank_name"]');
      var cardHolder = document.querySelector('[name="cardholder_name"]');
      var cardNumber = document.querySelector('[name="card_number"]');
      var expiry = document.querySelector('[name="expiry_date"]');
      var cvv = document.querySelector('[name="cvv"]');

      if (!cardBank || cardBank.value.trim() === '') {
        setFieldError('bank_name', 'Please select a Malaysian bank.');
        valid = false;
      }
      if (!cardHolder || cardHolder.value.trim() === '') {
        setFieldError('cardholder_name', 'Cardholder name is required.');
        valid = false;
      }
      if (!cardNumber || cardNumber.value.replace(/\D/g, '').length !== 16) {
        setFieldError('card_number', 'Card number must be 16 digits.');
        valid = false;
      }
      if (!expiry || !/^(0[1-9]|1[0-2])\/\d{2}$/.test(expiry.value.trim())) {
        setFieldError('expiry_date', 'Use MM/YY format.');
        valid = false;
      }
      if (!cvv || !/^\d{3}$/.test(cvv.value.trim())) {
        setFieldError('cvv', 'CVV must be 3 digits.');
        valid = false;
      }
    }

    return valid;
  }

  function formatCardNumber(value) {
    return value.replace(/\D/g, '').slice(0, 16).replace(/(.{4})/g, '$1 ').trim();
  }

  function formatExpiry(value) {
    var digits = value.replace(/\D/g, '').slice(0, 4);
    if (digits.length >= 3) {
      return digits.slice(0, 2) + '/' + digits.slice(2);
    }
    return digits;
  }

  function bindPaymentMasks() {
    var cardNumber = document.querySelector('[data-card-number]');
    var expiry = document.querySelector('[data-expiry]');
    var cvv = document.querySelector('[data-cvv]');

    if (cardNumber) {
      cardNumber.addEventListener('input', function () {
        cardNumber.value = formatCardNumber(cardNumber.value);
      });
    }

    if (expiry) {
      expiry.addEventListener('input', function () {
        expiry.value = formatExpiry(expiry.value);
      });
    }

    if (cvv) {
      cvv.addEventListener('input', function () {
        cvv.value = cvv.value.replace(/\D/g, '').slice(0, 3);
      });
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('[data-checkout-form]');
    var payment = document.querySelector('[data-payment-method]');

    updatePaymentFields();
    bindPaymentMasks();
    updateDeliveryCards();
    updateSummary();

    if (payment) {
      payment.addEventListener('change', updatePaymentFields);
    }

    document.querySelectorAll('input[name="delivery_method"]').forEach(function (radio) {
      radio.addEventListener('change', function () {
        updateDeliveryCards();
        updateSummary();
      });
    });

    if (form) {
      form.addEventListener('submit', function (event) {
        updatePaymentFields();
        if (!validateVisiblePayment()) {
          event.preventDefault();
          var firstError = form.querySelector('.field-error:not(:empty)');
          if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
          }
        }
      });
    }
  });
})();
