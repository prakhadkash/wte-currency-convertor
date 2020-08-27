"use strict";

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

var wpteccCookie = {
  get: function get(cookieName) {
    var cookies = document.cookie.split('; ').map(function (c) {
      var keyVal = c.split('=');
      return _defineProperty({}, keyVal[0], keyVal[1]);
    });
    var cookie = cookies.filter(function (c) {
      return typeof c[cookieName] !== 'undefined';
    });
    return cookie && cookie[0][cookieName];
  },
  create: function create(name, value, days) {
    var expires;

    if (days) {
      var date = new Date();
      date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
      expires = "; expires=" + date.toGMTString();
    } else {
      expires = "";
    }

    document.cookie = "".concat(name, "=").concat(value + expires, "; path=/");
  }
};

(function () {
  var selectors = document.querySelectorAll('[data-wpte-currency]');

  var handleCurrencySelection = function handleCurrencySelection(select) {
    return function (e) {
      if (select) {
        wpteccCookie.create('wptecc-user-currency', e.target.value);
        window.location.reload();
      }
    };
  };

  selectors && selectors.forEach(function (el) {
    if (el.tagName.toLowerCase() === 'select') {
      el.addEventListener('change', handleCurrencySelection(true));
    } else {
      el.addEventListener('click', handleCurrencySelection()); // Useful other elements except select.
    }
  });
})();