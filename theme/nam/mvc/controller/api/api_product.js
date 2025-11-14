(function (global, $) {
  'use strict';

  var ENDPOINT = 'ctrl_product.php';
  var T = {
    LIST:   'PRODUCT_LIST',
    GET:    'PRODUCT_GET',
    ACTIVE: 'PRODUCT_ACTIVE',
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
      // params: { mb_id?, name, type_code?, description?, base_amount?, period_type?, is_active?, sort_order?, it_id? }
      var p = {
        type: T.ADD,
        name: params.name
      };
      if (params.mb_id)         p.mb_id        = params.mb_id;
      if (params.type_code)     p.type_code    = params.type_code;      // 'ROOM'|'PROGRAM'|'ETC'
      if (params.description)   p.description  = params.description;
      if (params.base_amount!=null) p.base_amount = parseInt(params.base_amount,10);
      if (params.period_type)   p.period_type  = params.period_type;    // 'MONTH'|'DAY'|'TERM'
      if (params.is_active!=null)  p.is_active = params.is_active?1:0;
      if (params.sort_order!=null) p.sort_order = parseInt(params.sort_order,10);
      if (params.it_id!=null)      p.it_id      = params.it_id;
      return call(p);
    },
    update: function (id, fields) {
      var p = { type: T.UPD, id: id };
      if (fields.name != null)         p.name = fields.name;
      if (fields.type_code != null)    p.type_code = fields.type_code;
      if (fields.description != null)  p.description = fields.description;
      if (fields.base_amount != null)  p.base_amount = parseInt(fields.base_amount,10);
      if (fields.period_type != null)  p.period_type = fields.period_type;
      if (fields.is_active != null)    p.is_active = fields.is_active?1:0;
      if (fields.sort_order != null)   p.sort_order = parseInt(fields.sort_order,10);
      if (fields.it_id != null)        p.it_id = fields.it_id; // '' 전달 시 null 처리 서버에서 수행
      return call(p);
    },
    remove: function (id) {
      return call({ type: T.DEL, id: id });
    },
    _endpoint: ENDPOINT,
    _T: T
  };

  global.ProductAPI = API;
})(window, jQuery);
