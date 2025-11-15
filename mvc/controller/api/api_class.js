(function (global, $) {
  'use strict';

  var ENDPOINT = 'ctrl_class.php';
  var T = {
    LIST:   'CLASS_LIST',
    GET:    'CLASS_GET',
    ACTIVE: 'CLASS_ACTIVE',
    ADD:    'CLASS_ADD',
    UPD:    'CLASS_UPD',
    DEL:    'CLASS_DEL'
  };

  function call(params) {
    return $.ajax({
      url: ENDPOINT,
      method: 'POST',
      data: params,
      dataType: 'json'
    }).then(function (res) {
      if (res && res.result === 'SUCCESS') return res;
      return $.Deferred().reject(res || { result: 'FAIL' }).promise();
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
    add: function (name, description, is_active) {
      var payload = { type: T.ADD, name: name };
      if (typeof description !== 'undefined') payload.description = description;
      if (typeof is_active !== 'undefined')   payload.is_active   = is_active ? 1 : 0;
      return call(payload);
    },
    update: function (id, fields) {
      var payload = { type: T.UPD, id: id };
      if (fields.name != null)        payload.name = fields.name;
      if (fields.description != null) payload.description = fields.description;
      if (fields.is_active != null)   payload.is_active = fields.is_active ? 1 : 0;
      return call(payload);
    },
    remove: function (id) {
      return call({ type: T.DEL, id: id });
    },
    _endpoint: ENDPOINT,
    _T: T
  };

  global.ClassAPI = API;
})(window, jQuery);
