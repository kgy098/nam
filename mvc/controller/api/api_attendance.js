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
    BY_STUDENT: 'ATT_BY_STUDENT',
    BETWEEN: 'ATT_BETWEEN',
    ADD: 'ATT_ADD',
    UPD: 'ATT_UPD',
    DEL: 'ATT_DEL',

    STATUS_LIST: 'ATT_STATUS_LIST',   // ★ 출결현황 (학생×출결구분 OUTER JOIN)
    STATUS_CNT: 'ATT_STATUS_CNT'     // ★ 출결현황 카운트
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

    byStudent: function (mb_id, page, num) {
      var pg = pageParams(page, num);
      return call({
        type: T.BY_STUDENT,
        mb_id: mb_id,
        start: pg.start,
        num: pg.num
      });
    },

    between: function (from_dt, to_dt, opts) {
      opts = opts || {};
      var pg = pageParams(opts.page, opts.num);

      var payload = {
        type: T.BETWEEN,
        from_dt: from_dt,
        to_dt: to_dt,
        start: pg.start,
        num: pg.num
      };

      if (opts.mb_id) payload.mb_id = opts.mb_id;
      if (opts.status) payload.status = opts.status;

      return call(payload);
    },

    add: function (mb_id, attend_dt, opts) {
      opts = opts || {};
      var payload = {
        type: T.ADD,
        mb_id: mb_id,
        attend_dt: attend_dt,
        status: opts.status || '출석'
      };
      if (opts.attend_type_id != null) payload.attend_type_id = opts.attend_type_id;
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

    // === ★ 새 출결현황 API (학생 × 출결구분 OUTER JOIN + 기간검색) ===
    statusList: function (start_date, end_date, opts) {
      opts = opts || {};
      var pg = pageParams(opts.page, opts.num);

      var payload = {
        type: T.STATUS_LIST,
        start_date: start_date,
        end_date: end_date,
        start: pg.start,
        num: pg.num
      };
      // console.log("param: " + JSON.stringify(payload)); return;

      if (opts.class != null && opts.class !== '') {
        payload.class = opts.class;
      }

      if (opts.attend_type_id != null && opts.attend_type_id !== '') {
        payload.attend_type_id = opts.attend_type_id;
      }

      return call(payload);
    },

    statusCount: function (start_date, end_date, opts) {
      opts = opts || {};

      var payload = {
        type: T.STATUS_CNT,
        start_date: start_date,
        end_date: end_date
      };

      if (opts.class != null && opts.class !== '') {
        payload.class = opts.class;
      }
      if (opts.attend_type_id != null && opts.attend_type_id !== '') {
        payload.attend_type_id = opts.attend_type_id;
      }

      return call(payload);
    },

    // 디버그용
    _endpoint: ENDPOINT,
    _T: T
  };

  global.AttendanceAPI = API;

})(window, jQuery);
