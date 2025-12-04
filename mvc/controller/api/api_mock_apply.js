(function (global, $) {
  'use strict';

  var ENDPOINT = g5_ctrl_url + '/ctrl_mock_apply.php';

  var T = {
    LIST: 'MOCK_APPLY_LIST',
    GET: 'MOCK_APPLY_GET',
    ADD: 'MOCK_APPLY_CREATE',
    UPD: 'MOCK_APPLY_UPDATE',
    DEL: 'MOCK_APPLY_DELETE',

    // 학생 전용
    MY_LIST: 'MOCK_APPLY_MY_LIST',
    MY_STATUS: 'MOCK_APPLY_MY_STATUS',
    TOGGLE: 'MOCK_APPLY_TOGGLE',
    OVERVIEW_LIST: 'MOCK_APPLY_MY_OVERVIEW_LIST',

    // 선생님
    TEACHER_SUMMARY: 'MOCK_APPLY_TEACHER_SUMMARY',
    TEACHER_LIST: 'MOCK_APPLY_TEACHER_LIST'
  };

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

  global.apiMockApply = {

    list: function (params) {
      return call($.extend({ type: T.LIST }, params));
    },

    get: function (id) {
      return call({ type: T.GET, id: id });
    },

    add: function (payload) {
      return call($.extend({ type: T.ADD }, payload));
    },

    update: function (id, payload) {
      return call($.extend({ type: T.UPD, id: id }, payload));
    },

    delete: function (id) {
      return call({ type: T.DEL, id: id });
    },

    myList: function () {
      return call({ type: T.MY_LIST });
    },

    myStatus: function (mock_id) {
      return call({ type: T.MY_STATUS, mock_id: mock_id });
    },

    toggle: function (mock_id, subject_id) {
      return call({
        type: T.TOGGLE,
        mock_id: mock_id,
        subject_id: subject_id
      });
    },

    overviewList: function (page, rows) {
      return call({
        type: T.OVERVIEW_LIST,
        page: page,
        rows: rows
      });
    },

     /* -----------------------------
        선생님 전용
    ----------------------------- */
    teacherSummary: function (params) {
      return call($.extend({ type: T.TEACHER_SUMMARY }, params));
    },

    teacherList: function (params) {
      return call($.extend({ type: T.TEACHER_LIST }, params));
    }
  };

})(window, jQuery);
