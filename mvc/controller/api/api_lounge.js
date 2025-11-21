(function (global, $) {
  'use strict';

  // 컨트롤러 엔드포인트
  var ENDPOINT = g5_ctrl_url + '/ctrl_lounge.php';

  // type 상수
  var T = {
    LIST:   'LOUNGE_LIST',
    GET:    'LOUNGE_GET',
    ACTIVE: 'LOUNGE_ACTIVE',
    ADD:    'LOUNGE_ADD',
    UPD:    'LOUNGE_UPD',
    DEL:    'LOUNGE_DEL'
  };

  // 공통 Ajax 호출 함수
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

  // 페이지 파라미터 계산
  function pageParams(page, num) {
    var p = parseInt(page || 1, 10);
    var n = parseInt(num || 20, 10);
    return { start: (p - 1) * n, num: n };
  }

  var API = {

    // 라운지 리스트
    list: function (page, num) {
      var pg = pageParams(page, num);
      return call({ type: T.LIST, start: pg.start, num: pg.num });
    },

    // 단건 조회
    get: function (id) {
      return call({ type: T.GET, id: id });
    },

    // 활성/비활성 조회
    active: function (is_active, page, num) {
      var pg = pageParams(page, num);
      var payload = { type: T.ACTIVE, start: pg.start, num: pg.num };

      if (typeof is_active !== 'undefined' && is_active !== null)
        payload.is_active = parseInt(is_active, 10);

      return call(payload);
    },

    // 등록
    add: function (params) {
      var p = {
        type: T.ADD,
        name: params.name || ''
      };

      if (params.location != null)
        p.location = params.location;

      if (params.total_seats != null)
        p.total_seats = parseInt(params.total_seats, 10) || 0;

      if (params.is_active != null)
        p.is_active = parseInt(params.is_active, 10);

      return call(p);
    },

    // 수정
    update: function (id, fields) {
      var p = { type: T.UPD, id: id };

      if (fields.name != null)
        p.name = fields.name;

      if (fields.location != null)
        p.location = fields.location;

      if (fields.total_seats != null)
        p.total_seats = parseInt(fields.total_seats, 10) || 0;

      if (fields.is_active != null)
        p.is_active = parseInt(fields.is_active, 10);

      return call(p);
    },

    // 삭제 (soft delete)
    delete: function (id) {
      return call({ type: T.DEL, id: id });
    },

    _endpoint: ENDPOINT,
    _T: T
  };

  global.loungeAPI = API;

})(window, jQuery);
