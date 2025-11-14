(function (global, $) {
  'use strict';

  var ENDPOINT = 'ctrl_auth_code.php';
  var T = {
    LIST: 'AUTH_LIST',
    GET:  'AUTH_GET',
    ADD:  'AUTH_ADD',
    USE:  'AUTH_USE',
    DEL:  'AUTH_DEL'
  };

  function call(params) {
    return $.ajax({
      url: ENDPOINT,
      method: 'POST',
      data: params,
      dataType: 'json'
    }).then(function (res) {
      if (res && res.result === 'SUCCESS') return res;
      return $.Deferred().reject(res || { result:'FAIL' }).promise();
    });
  }

  function pageParams(page, num) {
    var p = parseInt(page || 1, 10);
    var n = parseInt(num || 20, 10);
    return { start: (p - 1) * n, num: n };
  }

  var API = {
    list: function (page, num) {
      var pg = pageParams(page, num);
      return call({ type: T.LIST, start: pg.start, num: pg.num });
    },
    get: function (id) {
      return call({ type: T.GET, id: id });
    },
    add: function (mb_id, phone, code, expires_dt) {
      return call({ type: T.ADD, mb_id: mb_id, phone: phone, code: code, expires_dt: expires_dt });
    },
    markUsed: function (id, used) {
      return call({ type: T.USE, id: id, used: (used != null ? (used ? 1 : 0) : 1) });
    },
    remove: function (id) {
      return call({ type: T.DEL, id: id });
    },
    _endpoint: ENDPOINT,
    _T: T
  };

  global.AuthCodeAPI = API;
})(window, jQuery);
