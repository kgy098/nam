(function (global, $) {
  'use strict';

  var ENDPOINT = g5_ctrl_url + '/ctrl_lounge_reservation.php';
  var T = {
    LIST:       'LRES_LIST',
    GET:        'LRES_GET',
    BY_STUDENT: 'LRES_BY_STUDENT',
    BY_DATE:    'LRES_BY_DATE',
    ADD:        'LRES_ADD',
    UPD:        'LRES_UPD',
    DEL:        'LRES_DEL'
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

    byStudent: function (mb_id, page, num) {
      var pg = pageParams(page, num);
      return call({ type: T.BY_STUDENT, mb_id: mb_id, start: pg.start, num: pg.num });
    },

    byDate: function (reserved_date, opts) {
      opts = opts || {};
      var pg = pageParams(opts.page, opts.num);
      var p = {
        type: T.BY_DATE,
        reserved_date: reserved_date,
        start: pg.start,
        num: pg.num
      };
      if (opts.lounge_id != null) p.lounge_id = parseInt(opts.lounge_id, 10);
      if (opts.seat_id   != null) p.seat_id   = parseInt(opts.seat_id, 10);
      return call(p);
    },

    add: function (params) {
      // params: { mb_id, lounge_id, seat_id, reserved_date, start_time, end_time, status? }
      var p = {
        type: T.ADD,
        mb_id: params.mb_id,
        lounge_id: parseInt(params.lounge_id, 10),
        seat_id: parseInt(params.seat_id, 10),
        reserved_date: params.reserved_date,
        start_time: params.start_time,
        end_time: params.end_time
      };
      if (params.status) p.status = params.status; // 기본 '예약'
      return call(p);
    },

    update: function (id, fields) {
      // fields: { lounge_id?, seat_id?, reserved_date?, start_time?, end_time?, status? }
      var p = { type: T.UPD, id: id };
      if (fields.lounge_id != null)     p.lounge_id     = parseInt(fields.lounge_id, 10);
      if (fields.seat_id   != null)     p.seat_id       = parseInt(fields.seat_id, 10);
      if (fields.reserved_date)         p.reserved_date = fields.reserved_date;
      if (fields.start_time)            p.start_time    = fields.start_time;
      if (fields.end_time)              p.end_time      = fields.end_time;
      if (fields.status)                p.status        = fields.status;
      return call(p);
    },

    remove: function (id) {
      return call({ type: T.DEL, id: id });
    },

    _endpoint: ENDPOINT,
    _T: T
  };

  global.LoungeReservationAPI = API;
})(window, jQuery);
