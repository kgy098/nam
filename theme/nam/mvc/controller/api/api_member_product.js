(function (global, $) {
  'use strict';

  var ENDPOINT = 'ctrl_member_product.php';
  var T = {
    LIST: 'MP_LIST',
    GET:  'MP_GET',
    ADD:  'MP_ADD',
    UPD:  'MP_UPD',
    DEL:  'MP_DEL'
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
      // params: { mb_id, product_id, checkin_datetime, status?, room_no?, memo? }
      var p = {
        type: T.ADD,
        mb_id: params.mb_id,
        product_id: parseInt(params.product_id, 10),
        checkin_datetime: params.checkin_datetime
      };
      if (params.status)  p.status  = params.status;      // 기본 '입실'
      if (params.room_no != null) p.room_no = params.room_no;
      if (params.memo != null)    p.memo    = params.memo;
      return call(p);
    },
    update: function (id, fields) {
      // fields: { checkout_datetime?, status?, room_no?, memo? }
      var p = { type: T.UPD, id: id };
      if (fields.checkout_datetime) p.checkout_datetime = fields.checkout_datetime;
      if (fields.status)            p.status            = fields.status;
      if (fields.room_no != null)   p.room_no           = fields.room_no;
      if (fields.memo != null)      p.memo              = fields.memo;
      return call(p);
    },
    remove: function (id) {
      return call({ type: T.DEL, id: id });
    },
    _endpoint: ENDPOINT,
    _T: T
  };

  global.MemberProductAPI = API;
})(window, jQuery);
