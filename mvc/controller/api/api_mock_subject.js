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

  // 공통 Ajax 호출 함수
  function call(params) {
    return $.ajax({
      url: ENDPOINT,
      method: 'POST',
      data: params,
      dataType: 'json'
    }).then(function(res){
      if (res && (res.result === 'SUCCESS' || res.result === true)) return res;
      return $.Deferred().reject(res || { result: 'FAIL' }).promise();
    });
  }

  // 페이지 파라미터 계산
  function pageParams(page, num) {
    var p = parseInt(page || 1, 10);
    var n = parseInt(num || 20, 10);
    return {
      start: (p - 1) * n,
      num: n
    };
  }

  // API 정의
  var API = {

    // 목록 조회
    list: function (page, num, extraParams) {
      var pg = pageParams(page, num);
      var params = Object.assign({
        type: T.LIST,
        start: pg.start,
        num: pg.num
      }, extraParams || {});

      return call(params);
    },

    // 단건 조회
    get: function (id) {
      return call({
        type: T.GET,
        id: id
      });
    },

    // 등록 (mock_id 제거 → subject_name만 필요)
    add: function (payload) {
      var params = Object.assign({
        type: T.ADD
      }, payload);  // payload = { subject_name: '국어' }

      return call(params);
    },

    // 수정
    update: function (id, fields) {
      var params = Object.assign({
        type: T.UPD,
        id: id
      }, fields);

      return call(params);
    },

    // 삭제(soft delete)
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
