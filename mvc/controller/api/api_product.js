(function (global, $) {
  'use strict';

  var ENDPOINT = g5_ctrl_url + '/ctrl_product.php';
  var T = {
    LIST:   'PRODUCT_LIST',
    GET:    'PRODUCT_GET',
    ADD:    'PRODUCT_ADD',
    UPD:    'PRODUCT_UPD',
    DEL:    'PRODUCT_DEL'
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

    // 🔥 검색 필터를 지원하도록 개선
    list: function (params = {}, page, num) {
      var pg = pageParams(page, num);
      params.type = T.LIST;
      params.start = pg.start;
      params.num = pg.num;
      return call(params);
    },

    get: function (id) {
      return call({ type: T.GET, id: id });
    },

    add: function (params) {
      var p = { type: T.ADD };

      if (params.mb_id != null)        p.mb_id        = params.mb_id;
      if (params.name != null)         p.name         = params.name;
      if (params.type_code != null)    p.type_code    = params.type_code;
      if (params.description != null)  p.description  = params.description;
      if (params.base_amount != null)  p.base_amount  = parseInt(params.base_amount,10);
      if (params.period_type != null)  p.period_type  = params.period_type;
      if (params.is_active != null)    p.is_active    = params.is_active ? 1 : 0;
      if (params.sort_order != null)   p.sort_order   = parseInt(params.sort_order,10);
      if (params.it_id != null)        p.it_id        = params.it_id;

      return call(p);
    },

    update: function (id, fields) {
      var p = { type: T.UPD, id: id };

      if (fields.name != null)         p.name = fields.name;
      if (fields.type_code != null)    p.type_code = fields.type_code;
      if (fields.description != null)  p.description = fields.description;
      if (fields.base_amount != null)  p.base_amount = parseInt(fields.base_amount,10);
      if (fields.period_type != null)  p.period_type = fields.period_type;
      if (fields.is_active != null)    p.is_active = fields.is_active ? 1 : 0;
      if (fields.sort_order != null)   p.sort_order = parseInt(fields.sort_order,10);
      if (fields.it_id != null)        p.it_id = fields.it_id;

      return call(p);
    },

    remove: function (id) {
      return call({ type: T.DEL, id: id });
    },

    _endpoint: ENDPOINT,
    _T: T
  };

  global.productAPI = API;

})(window, jQuery);
