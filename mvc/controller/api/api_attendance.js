/* api_attendance.js
 * ctrl_attendance.php 와 통신 전용 jQuery 래퍼
 * HTML/렌더링 없음. 성공/실패만 Promise로 반환.
 */
(function (global, $) {
  'use strict';

  // 컨트롤러 엔드포인트
  var ENDPOINT = g5_ctrl_url + '/ctrl_attendance.php';

  // 컨트롤러에서 쓰는 type 상수 문자열 (PHP define과 동일해야 함)
  var T = {
    LIST: 'ATT_LIST',
    GET: 'ATT_GET',
    ADD: 'ATT_ADD',
    UPD: 'ATT_UPD',
    DEL: 'ATT_DEL',
    OVERVIEW_LIST: 'ATT_OVERVIEW_LIST',
    ADMIN_LIST: 'ATT_ADMIN_LIST',
  };

  // 공통 Ajax 호출
  function call(params) {
    if (global.CSRF_TOKEN && !params.token) params.token = global.CSRF_TOKEN;

    return $.ajax({
      url: ENDPOINT,
      method: 'POST',
      data: params,
      dataType: 'json'
    }).then(function (res) {
      if (res && (res.result === 'SUCCESS' || res.result === true)) return res;
      return $.Deferred().reject(res || { result: 'FAIL' }).promise();
    });
  }

  // 페이지 계산
  function pageParams(page, num) {
    var p = parseInt(page || 1, 10);
    var n = parseInt(num || 20, 10);
    return {
      start: (p - 1) * n,
      num: n
    };
  }

  // ===== API =====
  var API = {

    // === 기존 출석 CRUD (그대로 유지) ===
    list: function (page, num) {
      var pg = pageParams(page, num);
      return call({ type: T.LIST, start: pg.start, num: pg.num });
    },

    get: function (id) {
      return call({ type: T.GET, id: id });
    },

    overviewList: function (page, rows, mb_id) {
      var p = pageParams(page, rows);

      return call({
        type: T.OVERVIEW_LIST,
        page: page,
        rows: rows,
        mb_id: mb_id
      });
    },

    adminList: function (filters) {
      return call({
        type: T.ADMIN_LIST,
        start_date: filters.start_date || '',
        end_date: filters.end_date || '',
        class_id: filters.class_id || '',
        attend_type_id: filters.attend_type_id || ''
      });
    },

    add: function (mb_id, opts) {
      opts = opts || {};
      var payload = {
        type: T.ADD,
        mb_id: mb_id,
        status: opts.status || '출석완료'
      };

      if (opts.attend_type_id != null)
        payload.attend_type_id = opts.attend_type_id;

      if (opts.date)
        payload.date = opts.date;   // ★ 날짜 전달 추가

      return call(payload);
    },

    update: function (id, fields) {
      fields = fields || {};

      var payload = { type: T.UPD, id: id };
      if (fields.attend_type_id != null) payload.attend_type_id = fields.attend_type_id;
      if (fields.attend_dt) payload.attend_dt = fields.attend_dt;
      if (fields.status) payload.status = fields.status;

      return call(payload);
    },

    remove: function (id) {
      return call({ type: T.DEL, id: id });
    },

    // 디버그용
    _endpoint: ENDPOINT,
    _T: T
  };

  global.AttendanceAPI = API;

})(window, jQuery);
