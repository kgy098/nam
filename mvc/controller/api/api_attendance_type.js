(function (global, $) {
  'use strict';

  // 출결 구분 컨트롤러 엔드포인트
  var ENDPOINT = g5_ctrl_url + '/ctrl_attendance_type.php';

  // type 상수
  var T = {
    LIST:   'ATTENDANCE_TYPE_LIST',
    GET:    'ATTENDANCE_TYPE_GET',
    CREATE: 'ATTENDANCE_TYPE_CREATE',
    UPDATE: 'ATTENDANCE_TYPE_UPDATE',
    DELETE: 'ATTENDANCE_TYPE_DELETE'
  };

  // 공통 AJAX 호출 함수 (SUCCESS / FAIL 처리)
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

  // 페이지/행수 기본값 처리
  function pageParams(page, rows) {
    var p = parseInt(page || 1, 10);
    var r = parseInt(rows || 20, 10);
    if (p < 1) p = 1;
    if (r < 1) r = 20;
    return { page: p, rows: r };
  }

  var API = {
    // 리스트 조회
    // extra: { keyword: '지각', is_active: 1 } 같은 추가 필터용
    list: function (page, rows, extra) {
      var pg = pageParams(page, rows);
      var payload = $.extend(
        { type: T.LIST, page: pg.page, rows: pg.rows },
        extra || {}
      );
      return call(payload);
    },

    // 단건 조회
    get: function (id) {
      return call({ type: T.GET, id: id });
    },

    // 생성
    // payload 예: { name: '지각', description: '수업 시작 후 10분 이내', is_active: 1, sort_order: 10 }
    create: function (payload) {
      return call($.extend({ type: T.CREATE }, payload));
    },

    // 수정
    // payload 예: { name: '지각(10분)', description: '설명', is_active: 1, sort_order: 20 }
    update: function (id, payload) {
      return call($.extend({ type: T.UPDATE, id: id }, payload));
    },

    // 삭제 (현재는 물리 삭제, soft delete 쓸 때는 컨트롤러에서 is_active 변경용 type 따로 두면 됨)
    remove: function (id) {
      return call({ type: T.DELETE, id: id });
    },

    // 디버깅용
    _endpoint: ENDPOINT,
    _T: T
  };

  global.AttendanceTypeAPI = API;

})(window, jQuery);
