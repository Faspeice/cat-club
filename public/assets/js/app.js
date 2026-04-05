/* global document, window */
(function () {
  'use strict';

  function bindPasswordToggles() {
    var nodes = document.querySelectorAll('[data-toggle-password]');
    for (var i = 0; i < nodes.length; i++) {
      (function (btn) {
        if (btn.getAttribute('data-ui-bound') === '1') return;
        btn.setAttribute('data-ui-bound', '1');
        btn.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopPropagation();
          var id = btn.getAttribute('data-toggle-password');
          var input = id ? document.getElementById(id) : null;
          if (!input) return;
          input.type = input.type === 'password' ? 'text' : 'password';
          var showLabel = btn.getAttribute('data-label-show') || 'Показать';
          var hideLabel = btn.getAttribute('data-label-hide') || 'Скрыть';
          btn.textContent = input.type === 'password' ? showLabel : hideLabel;
          btn.setAttribute(
            'aria-label',
            input.type === 'password' ? 'Показать пароль' : 'Скрыть пароль'
          );
        });
      })(nodes[i]);
    }
  }

  function openPanel(panel, trigger) {
    if (!panel) return;
    panel.removeAttribute('hidden');
    if (trigger) trigger.setAttribute('aria-expanded', 'true');
    try {
      panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } catch (err) {
      panel.scrollIntoView();
    }
    window.setTimeout(function () {
      var fe = panel.querySelector('input, textarea, select');
      if (fe) fe.focus();
    }, 200);
  }

  function closePanel(panel, trigger) {
    if (!panel) return;
    panel.setAttribute('hidden', '');
    if (trigger) trigger.setAttribute('aria-expanded', 'false');
  }

  function bindOpenPanels() {
    var nodes = document.querySelectorAll('[data-open-panel]');
    for (var j = 0; j < nodes.length; j++) {
      (function (btn) {
        if (btn.getAttribute('data-ui-bound') === '1') return;
        btn.setAttribute('data-ui-bound', '1');
        btn.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopPropagation();
          var id = btn.getAttribute('data-open-panel');
          var panel = id ? document.getElementById(id) : null;
          if (!panel) return;
          if (panel.hasAttribute('hidden')) {
            openPanel(panel, btn);
          } else {
            closePanel(panel, btn);
          }
        });
      })(nodes[j]);
    }
  }

  function bindSwitchAuth() {
    var nodes = document.querySelectorAll('[data-switch-auth]');
    for (var k = 0; k < nodes.length; k++) {
      (function (btn) {
        if (btn.getAttribute('data-ui-bound') === '1') return;
        btn.setAttribute('data-ui-bound', '1');
        btn.addEventListener('click', function (e) {
          e.preventDefault();
          var targetId = btn.getAttribute('data-switch-auth');
          var target = targetId ? document.getElementById(targetId) : null;
          if (!target) return;
          var sections = document.querySelectorAll('.form-section');
          for (var s = 0; s < sections.length; s++) {
            sections[s].classList.remove('active');
          }
          target.classList.add('active');
          if (window.history && window.history.replaceState) {
            window.history.replaceState(
              null,
              '',
              btn.getAttribute('href') || window.location.pathname
            );
          }
        });
      })(nodes[k]);
    }
  }

  function bindNavToggle() {
    var toggle = document.getElementById('nav-toggle');
    var header = document.querySelector('.site-header');
    var menu = document.getElementById('primary-menu');
    if (!toggle || !header) return;

    function setOpen(open) {
      if (open) {
        header.classList.add('site-header--open');
        toggle.setAttribute('aria-expanded', 'true');
        toggle.setAttribute('aria-label', 'Закрыть меню');
      } else {
        header.classList.remove('site-header--open');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Открыть меню');
      }
    }

    toggle.addEventListener('click', function () {
      setOpen(!header.classList.contains('site-header--open'));
    });

    document.addEventListener('keydown', function (e) {
      if (
        e.key === 'Escape' &&
        header.classList.contains('site-header--open')
      ) {
        setOpen(false);
        toggle.focus();
      }
    });

    if (menu) {
      menu.addEventListener('click', function (e) {
        if (!window.matchMedia('(max-width: 900px)').matches) return;
        if (e.target.closest('a')) {
          setOpen(false);
        }
      });
    }

    var mq = window.matchMedia('(min-width: 901px)');
    function onWide() {
      if (mq.matches) setOpen(false);
    }
    if (mq.addEventListener) {
      mq.addEventListener('change', onWide);
    } else if (mq.addListener) {
      mq.addListener(onWide);
    }
    window.addEventListener('resize', onWide);
  }

  function applyHashPanels() {
    var h = window.location && window.location.hash;
    if (h !== '#add-post' && h !== '#add-pet' && h !== '#add-event') return;
    var id = h.slice(1);
    var panel = document.getElementById(id);
    var trig = document.querySelector('[data-open-panel="' + id + '"]');
    if (panel && panel.hasAttribute('hidden')) {
      openPanel(panel, trig);
    }
  }

  function initUi() {
    bindPasswordToggles();
    bindOpenPanels();
    bindSwitchAuth();
    bindNavToggle();
    applyHashPanels();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initUi);
  } else {
    initUi();
  }
})();
