/* api_attendance.js
 * 컨트롤러(예: attendance_ajax.php)와 통신 전용 jQuery 래퍼
 * HTML/렌더링 없음. 성공/실패만 Promise로 반환.
 */
(function (global, $) {
  'use strict';

  // 컨트롤러 엔드포인트 (파일명 맞게 조정)
  var ENDPOINT = 'attendance_ajax.php';

  // 컨트롤러에서 쓰는 type 상수 문자열 (PHP define과 동일해야 함)
  var T = {
    LIST:       'ATT_LIST',
    GET:        'ATT_GET',
    BY_STUDENT: 'ATT_BY_STUDENT',
    BETWEEN:    'ATT_BETWEEN',
    ADD:        'ATT_ADD',
    UPD:        'ATT_UPD',
    DEL:        'ATT_DEL'
  };

  // 공통 AJAX 호출 (POST/JSON)
  function call(params) {
    // CSRF 토큰을 쓰면 여기서 자동 부착 (없으면 무시)
    if (global.CSRF_TOKEN && !params.token) params.token = global.CSRF_TOKEN;

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

  // 페이지/페이징 유틸 (옵션)
  function pageParams(page, num) {
    var p = parseInt(page || 1, 10);
    var n = parseInt(num || 20, 10);
    var start = (p - 1) * n;
    return { start: start, num: n };
  }

  // ===== API =====
  var API = {
    // 출결 전체 목록
    list: function (page, num) {
      var pg = pageParams(page, num);
      return call({ type: T.LIST, start: pg.start, num: pg.num });
    },

    // 단건 조회
    get: function (id) {
      return call({ type: T.GET, id: id });
    },

    // 학생별 목록
    byStudent: function (mb_id, page, num) {
      var pg = pageParams(page, num);
      return call({ type: T.BY_STUDENT, mb_id: mb_id, start: pg.start, num: pg.num });
    },

    // 기간별 조회 (필터: mb_id, type, status, attend_type_id)
    between: function (from_dt, to_dt, opts) {
      opts = opts || {};
      var page = opts.page, num = opts.num;
      var pg = pageParams(page, num);
      var payload = {
        type: T.BETWEEN,
        from_dt: from_dt,
        to_dt: to_dt,
        start: pg.start,
        num: pg.num
      };
      if (opts.mb_id) payload.mb_id = opts.mb_id;
      if (opts.atype) payload.atype = opts.atype;     // '입실' | '퇴실' | '외출' | '복귀'
      if (opts.status) payload.status = opts.status; // '출석' | '지각' | '결석'
      if (opts.attend_type_id != null) payload.attend_type_id = opts.attend_type_id;
      return call(payload);
    },

    // 출결 추가
    add: function (mb_id, attend_dt, opts) {
      opts = opts || {};
      var payload = {
        type: T.ADD,
        mb_id: mb_id,
        attend_dt: attend_dt,
        atype: opts.atype || '입실',
        status: opts.status || '출석'
      };
      if (opts.attend_type_id != null) payload.attend_type_id = opts.attend_type_id;
      return call(payload);
    },

    // 출결 수정
    update: function (id, fields) {
      // fields: { attend_type_id?, attend_dt?, atype?, status? }
      var payload = { type: T.UPD, id: id };
      if (fields.attend_type_id != null) payload.attend_type_id = fields.attend_type_id;
      if (fields.attend_dt) payload.attend_dt = fields.attend_dt;
      if (fields.atype) payload.atype = fields.atype;
      if (fields.status) payload.status = fields.status;
      return call(payload);
    },

    // 출결 삭제
    remove: function (id) {
      return call({ type: T.DEL, id: id });
    },

    // 엔드포인트/상수 노출 (필요하면 사용)
    _endpoint: ENDPOINT,
    _T: T
  };

  // 전역 공개
  global.AttendanceAPI = API;

})(window, jQuery);

/* ===== 사용 예시 (렌더링은 각자 콜백에서) =====
AttendanceAPI.list(1, 20)
  .then(function(res){ console.log('list', res.data); })
  .fail(function(err){ console.warn('list fail', err); });

AttendanceAPI.get(123)
  .then(function(res){ console.log('get', res.data); });

AttendanceAPI.byStudent('student01', 1, 10)
  .then(function(res){ console.log('byStudent', res.data); });

AttendanceAPI.between('2025-11-01 00:00:00', '2025-11-30 23:59:59', { mb_id:'student01', atype:'입실', page:1, num:50 })
  .then(function(res){ console.log('between', res.data); });

AttendanceAPI.add('student01', '2025-11-12 08:05:00', { attend_type_id: 2, atype:'입실', status:'출석' })
  .then(function(){ console.log('add ok'); });

AttendanceAPI.update(321, { status:'지각' })
  .then(function(){ console.log('update ok'); });

AttendanceAPI.remove(321)
  .then(function(){ console.log('delete ok'); });
===== */
