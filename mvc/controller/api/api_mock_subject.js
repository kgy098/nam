(function (global, $) {
  'use strict';

  var ENDPOINT = g5_ctrl_url + '/ctrl_mock_subject.php';

  var T = {
    LIST:  'MOCK_SUBJECT_LIST',
    GET:   'MOCK_SUBJECT_GET',
    ADD:   'MOCK_SUBJECT_CREATE',
    UPD:   'MOCK_SUBJECT_UPDATE',
    DEL:   'MOCK_SUBJECT_DELETE'
  };

  // 공통 Ajax 호출
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

  // 페이지 계산
  function pageParams(page, num) {
    var p = parseInt(page || 1, 10);
    var n = parseInt(num || 20, 10);
    return {
      start: (p - 1) * n,
      num: n
    };
  }


  /* -------------------------------------------------------------
   * API
   * ------------------------------------------------------------- */

  var API = {

    /* --------------------------------------------------
     * 목록 조회
     * extraParams = { subject_type: 'MOCK', keyword: ... }
     * -------------------------------------------------- */
    list: function (page, num, extraParams) {

      var pg = pageParams(page, num);

      var params = Object.assign({
        type: T.LIST,
        start: pg.start,
        num: pg.num
      }, extraParams);

      return call(params);
    },


    /* --------------------------------------------------
     * 단건 조회
     * -------------------------------------------------- */
    get: function (id) {
      return call({
        type: T.GET,
        id: id
      });
    },


    /* --------------------------------------------------
     * 등록
     * payload = { subject_name: '국어', subject_type: 'MOCK' }
     * -------------------------------------------------- */
    add: function (payload) {

      if (!payload || !payload.subject_name || !payload.subject_type) {
        console.error("subject_name & subject_type required");
        return $.Deferred().reject({ result: 'FAIL', msg: 'required' }).promise();
      }

      var params = Object.assign({
        type: T.ADD
      }, payload);

      return call(params);
    },


    /* --------------------------------------------------
     * 수정
     * fields = { subject_name?: '', subject_type?: '' }
     * -------------------------------------------------- */
    update: function (id, fields) {
      var p = {
        type: T.UPD,
        id: id
      };

      if (fields) {
        Object.assign(p, fields);
      }

      return call(p);
    },


    /* --------------------------------------------------
     * 삭제 (soft delete)
     * -------------------------------------------------- */
    remove: function (id) {
      return call({
        type: T.DEL,
        id: id
      });
    },

    _endpoint: ENDPOINT,
    _T: T
  };

  global.apiMockSubject = API;

})(window, jQuery);
