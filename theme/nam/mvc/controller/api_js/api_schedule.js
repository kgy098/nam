(function (global, $) {
  'use strict';

  var ENDPOINT = 'ctrl_schedule.php';
  var T = {
    LIST: 'SCHEDULE_LIST',
    GET:  'SCHEDULE_GET',
    ADD:  'SCHEDULE_ADD',
    UPD:  'SCHEDULE_UPD',
    DEL:  'SCHEDULE_DEL'
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
    add: function (params) {
      // params: { mb_id?, title, description?, start_date, end_date? }
      var p = {
        type: T.ADD,
        title: params.title,
        start_date: params.start_date
      };
      if (params.mb_id)       p.mb_id       = params.mb_id;
      if (params.description) p.description = params.description;
      if (params.end_date)    p.end_date    = params.end_date;
      return call(p);
    },
    update: function (id, fields) {
      // fields: { title?, description?, start_date?, end_date? }
      var p = { type: T.UPD, id: id };
      if (fields.title)       p.title       = fields.title;
      if (fields.description) p.description = fields.description;
      if (fields.start_date)  p.start_date  = fields.start_date;
      if (fields.end_date)    p.end_date    = fields.end_date;
      return call(p);
    },
    remove: function (id) {
      return call({ type: T.DEL, id: id });
    },
    _endpoint: ENDPOINT,
    _T: T
  };

  global.ScheduleAPI = API;
})(window, jQuery);
