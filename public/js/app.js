/* FarmRex – small progressive enhancements (no build step needed). */
(function () {
  var root = document.documentElement;

  // ----- Light / dark mode -----
  function setTheme(t) {
    root.setAttribute('data-theme', t);
    try { localStorage.setItem('farmrex-theme', t); } catch (e) {}
  }
  document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      setTheme(root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
    });
  });

  // ----- Mobile sidebar -----
  document.querySelectorAll('[data-nav-toggle]').forEach(function (el) {
    el.addEventListener('click', function () { document.body.classList.toggle('nav-open'); });
  });

  // ----- User menu -----
  var um = document.querySelector('.user-menu');
  if (um) {
    um.querySelector('.avatar').addEventListener('click', function (e) { e.stopPropagation(); um.classList.toggle('open'); });
    document.addEventListener('click', function (e) { if (!um.contains(e.target)) um.classList.remove('open'); });
  }

  // ----- Confirm before destructive actions -----
  document.querySelectorAll('form[data-confirm]').forEach(function (f) {
    f.addEventListener('submit', function (e) {
      if (!window.confirm(f.getAttribute('data-confirm'))) e.preventDefault();
    });
  });

  // ----- Auto-submit period selectors -----
  document.querySelectorAll('select[data-autosubmit]').forEach(function (s) {
    s.addEventListener('change', function () { s.form.submit(); });
  });

  // ----- Income form: quantity x price = total -----
  var qty = document.getElementById('quantity'), price = document.getElementById('unit_price'), total = document.getElementById('amount');
  if (qty && price && total && document.querySelector('form[action*="/incomes"]')) {
    var touched = total.value !== '';
    total.addEventListener('input', function () { touched = total.value !== ''; });
    var calc = function () {
      var q = parseFloat(qty.value), p = parseFloat(price.value);
      if (!touched && q > 0 && p >= 0) total.value = Math.round(q * p * 100) / 100;
    };
    qty.addEventListener('input', calc); price.addEventListener('input', calc);
  }

  // ----- Hide flash messages after a while -----
  document.querySelectorAll('.flash').forEach(function (f) {
    setTimeout(function () { f.style.transition = 'opacity .4s'; f.style.opacity = '0'; setTimeout(function () { f.remove(); }, 450); }, 6000);
  });
})();
