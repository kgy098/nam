(function (global, $) {
  'use strict';

  var ENDPOINT = 'ctrl_lounge.php';
  var T = {
    LIST:   'LOUNGE_LIST',
    GET:    'LOUNGE_GET',
    ACTIVE: 'LOUNGE_ACTIVE',
    ADD:    'LOUNGE_ADD',
    UPD:    'LOUNGE_UPD',
    DEL:    'LOUNGE_DEL'
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
    active: function (is_active, page, num) {
      var pg = pageParams(page, num);
      var payload = { type: T.ACTIVE, start: pg.start, num: pg.num };
      if (is_active != null) payload.is_active = is_active ? 1 : 0;
      return call(payload);
    },
    add: function (params) {
      var p = { type: T.ADD, name: params.name };
      if (params.location != null)    p.location    = params.location;
      if (params.total_seats != null) p.total_seats = parseInt(params.total_seats, 10);
      if (params.is_active != null)   p.is_active   = params.is_active ? 1 : 0;
      return call(p);
    },
    update: function (id, fields) {
      var p = { type: T.UPD, id: id };
      if (fields.name != null)        p.name        = fields.name;
      if (fields.location != null)    p.location    = fields.location;
      if (fields.total_seats != null) p.total_seats = parseInt(fields.total_seats, 10);
      if (fields.is_active != null)   p.is_active   = fields.is_active ? 1 : 0;
      return call(p);
    },
    remove: function (id) {
      return call({ type: T.DEL, id: id });
    },
    _endpoint: ENDPOINT,
    _T: T
  };

  global.LoungeAPI = API;
})(window, jQuery);
