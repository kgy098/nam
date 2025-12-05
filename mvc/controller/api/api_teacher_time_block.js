(function (global, $) {
  'use strict';

  var ENDPOINT = g5_ctrl_url + '/ctrl_teacher_time_block.php';
  var T = {
    LIST: 'TTB_LIST',
    GET:  'TTB_GET',
    ADD:  'TTB_ADD',
    UPD:  'TTB_UPD',
    DEL:  'TTB_DEL',
    SLOTS: 'TTB_SLOTS'
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

    slots: function (params) {
      return call({
        type: T.SLOTS,
        mb_id: params.mb_id,
        target_date: params.target_date
      });
    },

    get: function (id) {
      return call({ type: T.GET, id: id });
    },

    add: function (params) {
      // params: { mb_id, target_date, start_time, end_time, ttb_type, memo? }
      var p = {
        type: T.ADD,
        mb_id: params.mb_id,
        target_date: params.target_date,
        start_time: params.start_time,
        end_time: params.end_time,
        ttb_type: params.ttb_type
      };
      if (params.memo != null) p.memo = params.memo;
      return call(p);
    },

    update: function (id, fields) {
      // fields: { target_date?, start_time?, end_time?, ttb_type?, memo? }
      var p = { type: T.UPD, id: id };
      if (fields.target_date) p.target_date = fields.target_date;
      if (fields.start_time)  p.start_time  = fields.start_time;
      if (fields.end_time)    p.end_time    = fields.end_time;
      if (fields.ttb_type)    p.ttb_type    = fields.ttb_type; // 'AVAILABLE'|'BREAK'
      if (fields.memo != null)p.memo        = fields.memo;
      return call(p);
    },

    remove: function (id) {
      return call({ type: T.DEL, id: id });
    },

    _endpoint: ENDPOINT,
    _T: T
  };

  global.TeacherTimeBlockAPI = API;
})(window, jQuery);
