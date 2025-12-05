(function (global, $) {
  'use strict';

  var ENDPOINT = g5_ctrl_url + '/ctrl_consult.php';

  var T = {
    TEACHER_LIST: 'CONSULT_TEACHER_LIST',
    DATE_LIST: 'CONSULT_DATE_LIST',
    AVAILABLE_TIMES: 'CONSULT_AVAILABLE_TIMES',
    RESERVE: 'CONSULT_RESERVE',
    CANCEL: 'CONSULT_CANCEL',
    MY_LIST: 'CONSULT_MY_LIST',

    // 기존 consult admin 기능
    LIST: 'CONSULT_LIST',
    GET: 'CONSULT_GET',
    DELETE: 'CONSULT_DELETE'
  };

  // 공통 ajax 호출
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

  // 페이징 파라미터
  function pageParams(page, num) {
    var p = parseInt(page || 1, 10);
    var n = parseInt(num || 20, 10);
    return { start: (p - 1) * n, num: n };
  }

  var API = {

    /* -----------------------------------------
     * 선생님 목록 (role=TEACHER)
     * ----------------------------------------- */
    teacherList: function () {
      return call({ type: T.TEACHER_LIST });
    },

    /* -----------------------------------------
     * 날짜 리스트 (14일치)
     * ----------------------------------------- */
    dateList: function () {
      return call({ type: T.DATE_LIST });
    },

    /* -----------------------------------------
     * 시간표 조회
     * params: { student_mb_id, teacher_mb_id, target_date }
     * ----------------------------------------- */
    times: function (params) {
      return call({
        type: T.AVAILABLE_TIMES,
        student_mb_id: params.student_mb_id,
        teacher_mb_id: params.teacher_mb_id,
        target_date: params.target_date,
        consult_type: params.consult_type
      });
    },

    /* -----------------------------------------
     * 예약
     * params: { student_mb_id, teacher_mb_id, scheduled_dt }
     * ----------------------------------------- */
    reserve: function (params) {
      console.log(JSON.stringify(params));
      return call({
        type: T.RESERVE,
        student_mb_id: params.student_mb_id,
        teacher_mb_id: params.teacher_mb_id,
        scheduled_dt: params.scheduled_dt,
        consult_type: params.consult_type
      });
    },

    /* -----------------------------------------
     * 예약 취소
     * params: { id, student_mb_id }
     * ----------------------------------------- */
    cancel: function (id) {
      return call({
        type: T.CANCEL,
        id: id
      });
    },

    /* -----------------------------------------
     * 내 상담 리스트
     * params: { student_mb_id }
     * ----------------------------------------- */
    myList: function (student_mb_id, consult_type) {
      return call({
        type: T.MY_LIST,
        student_mb_id: student_mb_id,
        consult_type: consult_type
      });
    },

    /* -----------------------------------------
     * (기존) 관리자용 리스트/조회/삭제
     * ----------------------------------------- */
    list: function (page, num) {
      var pg = pageParams(page, num);
      return call({
        type: T.LIST,
        start: pg.start,
        num: pg.num
      });
    },

    get: function (id) {
      return call({ type: T.GET, id: id });
    },

    remove: function (id) {
      return call({ type: T.DELETE, id: id });
    },

    teacherMyList: function (teacher_mb_id, consult_type, target_date) {
      return call({
        type: 'CONSULT_TEACHER_MY_LIST',
        teacher_mb_id: teacher_mb_id,
        consult_type: consult_type,
        target_date: target_date
      });
    },

    _endpoint: ENDPOINT,
    _T: T
  };

  global.ConsultAPI = API;

})(window, jQuery);
