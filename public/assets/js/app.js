/* global document, window */
(function () {
  'use strict';

  function on(selector, eventName, handler) {
    document.addEventListener(eventName, function (e) {
      var el = e.target;
      if (!el) return;
      if (el.matches(selector)) handler(e, el);
      var parent = el.closest ? el.closest(selector) : null;
      if (parent) handler(e, parent);
    });
  }

  on('[data-toggle-password]', 'click', function (e, btn) {
    e.preventDefault();
    var id = btn.getAttribute('data-toggle-password');
    var input = document.getElementById(id);
    if (!input) return;
    input.type = input.type === 'password' ? 'text' : 'password';
    btn.textContent = input.type === 'password' ? 'Показать' : 'Скрыть';
  });

  on('[data-switch-auth]', 'click', function (e, btn) {
    e.preventDefault();
    var targetId = btn.getAttribute('data-switch-auth');
    var target = document.getElementById(targetId);
    if (!target) return;
    var sections = document.querySelectorAll('.form-section');
    for (var i = 0; i < sections.length; i++) sections[i].classList.remove('active');
    target.classList.add('active');
    if (window && window.history && window.history.replaceState) {
      window.history.replaceState(null, '', btn.getAttribute('href') || window.location.pathname);
    }
  });
})();
