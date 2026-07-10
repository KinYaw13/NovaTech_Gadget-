(() => {
  const validate = (value) => ({
    length: value.length >= 8,
    upper: /[A-Z]/.test(value),
    lower: /[a-z]/.test(value),
    digits: (value.match(/\d/g) || []).length >= 6,
    spaces: !/\s/.test(value),
  });

  document.querySelectorAll('[data-password-rules]').forEach((list) => {
    const input = document.getElementById(list.dataset.passwordRules);
    if (!input) return;

    const update = () => {
      const state = validate(input.value);
      list.querySelectorAll('[data-rule]').forEach((item) => {
        const passed = Boolean(state[item.dataset.rule]);
        item.classList.toggle('is-valid', passed);
        item.classList.toggle('is-pending', !passed);
      });
    };

    input.addEventListener('input', update);
    update();
  });
})();
