(function (global, $) {
  'use strict';

  var ENDPOINT = g5_ctrl_url + '/ctrl_class.php';
  var T = {
    LIST: 'CLASS_LIST',
    GET: 'CLASS_GET',
    ACTIVE: 'CLASS_ACTIVE',
    ADD: 'CLASS_ADD',
    UPD: 'CLASS_UPD',
    ACTIVE_UPD: 'CLASS_ACTIVE_UPD',
    DEL: 'CLASS_DEL'
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
    var n = parseInt(num || 20, 10);   // 필요하면 project 상수로 변경
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

      if (typeof is_active !== 'undefined' && is_active !== null) {
        payload.is_active = Number(is_active);
      }

      return call(payload);
    },

    setActive: function (id, is_active) {
      return call({
        type: T.ACTIVE_UPD,
        id: id,
        is_active: is_active ? 1 : 0   // 1=활성, 0=비활성(soft delete)
      });
    },

    add: function (name, description, is_active) {
      var payload = { type: T.ADD, name: name };

      if (typeof description !== 'undefined') {
        payload.description = (description === '' ? null : description);
      }

      if (typeof is_active !== 'undefined' && is_active !== null) {
        payload.is_active = Number(is_active);
      }

      return call(payload);
    },

    update: function (id, fields) {
      var payload = { type: T.UPD, id: id };

      if (typeof fields.name !== 'undefined') {
        payload.name = fields.name;
      }

      if (typeof fields.description !== 'undefined') {
        payload.description = (fields.description === '' ? null : fields.description);
      }

      if (typeof fields.is_active !== 'undefined' && fields.is_active !== null) {
        payload.is_active = Number(fields.is_active);
      }

      return call(payload);
    },

    remove: function (id) {
      return call({ type: T.DEL, id: id });
    },

    _endpoint: ENDPOINT,
    _T: T
  };

  global.apiClass = API;

})(window, jQuery);
