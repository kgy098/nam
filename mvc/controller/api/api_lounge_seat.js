/* api_lounge_seat.js */
(function (global, $) {
  'use strict';

  var ENDPOINT = 'ctrl_lounge_seat.php';
  var T = {
    LIST:      'LSEAT_LIST',
    GET:       'LSEAT_GET',
    BY_LOUNGE: 'LSEAT_BY_LOUNGE',
    ADD:       'LSEAT_ADD',
    UPD:       'LSEAT_UPD',
    DEL:       'LSEAT_DEL'
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
    byLounge: function (lounge_id, opts) {
      opts = opts || {};
      var pg = pageParams(opts.page, opts.num);
      var p = { type: T.BY_LOUNGE, lounge_id: parseInt(lounge_id,10), start: pg.start, num: pg.num };
      if (opts.only_active != null) p.is_active = opts.only_active ? 1 : 0;
      return call(p);
    },
    add: function (params) {
      // params: { lounge_id, seat_no, is_active? }
      var p = {
        type: T.ADD,
        lounge_id: parseInt(params.lounge_id,10),
        seat_no: params.seat_no
      };
      if (params.is_active != null) p.is_active = params.is_active ? 1 : 0;
      return call(p);
    },
    update: function (id, fields) {
      // fields: { lounge_id?, seat_no?, is_active? }
      var p = { type: T.UPD, id: id };
      if (fields.lounge_id != null) p.lounge_id = parseInt(fields.lounge_id,10);
      if (fields.seat_no != null)   p.seat_no   = fields.seat_no;
      if (fields.is_active != null) p.is_active = fields.is_active ? 1 : 0;
      return call(p);
    },
    remove: function (id) {
      return call({ type: T.DEL, id: id });
    },
    _endpoint: ENDPOINT,
    _T: T
  };

  global.LoungeSeatAPI = API;
})(window, jQuery);
