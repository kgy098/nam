(function (global, $) {
  'use strict';

  var ENDPOINT = g5_ctrl_url + '/ctrl_qna.php';

  var T = {
    LIST:   'QNA_LIST',
    GET:    'QNA_GET',
    CREATE: 'QNA_CREATE',
    UPDATE: 'QNA_UPDATE',
    DELETE: 'QNA_DELETE',
    ANSWER: 'QNA_ANSWER'
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
     * Q&A 목록
     * params: { page, rows, student_mb_id, teacher_mb_id, status, keyword }
     * ----------------------------------------- */
    list: function (params) {
      params = params || {};
      params.type = T.LIST;
      return call(params);
    },

    /* -----------------------------------------
     * 단건조회
     * ----------------------------------------- */
    get: function (id) {
      return call({
        type: T.GET,
        id: id
      });
    },

    /* -----------------------------------------
     * 질문 등록
     * params: { student_mb_id, title, question, teacher_mb_id(optional) }
     * ----------------------------------------- */
    create: function (payload) {
      payload = payload || {};
      payload.type = T.CREATE;
      return call(payload);
    },

    /* -----------------------------------------
     * 질문 수정
     * params: { id, title, question, teacher_mb_id(optional), status(optional) }
     * ----------------------------------------- */
    update: function (id, payload) {
      payload = payload || {};
      payload.type = T.UPDATE;
      payload.id = id;
      return call(payload);
    },

    /* -----------------------------------------
     * 삭제
     * ----------------------------------------- */
    remove: function (id) {
      return call({
        type: T.DELETE,
        id: id
      });
    },

    /* -----------------------------------------
     * 선생님 답변 입력
     * params: { id, teacher_mb_id, answer, answered_dt(optional) }
     * ----------------------------------------- */
    answer: function (id, payload) {
      payload = payload || {};
      payload.type = T.ANSWER;
      payload.id = id;
      return call(payload);
    },

    _endpoint: ENDPOINT,
    _T: T
  };

  global.QnaAPI = API;

})(window, jQuery);
