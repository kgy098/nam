(function (global, $) {
  'use strict';

  var ENDPOINT = g5_ctrl_url + '/ctrl_mock_test.php';

  var T = {
    LIST: 'MOCK_TEST_LIST',
    GET: 'MOCK_TEST_GET',
    ADD: 'MOCK_TEST_CREATE',
    UPD: 'MOCK_TEST_UPDATE',
    DEL: 'MOCK_TEST_DELETE'
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

  function pageParams(page, rows) {
    var p = parseInt(page || 1, 10);
    var r = parseInt(rows || 20, 10);
    return {
      start: (p - 1) * r,
      rows: r
    };
  }

  global.apiMockTest = {

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
    }
  };

})(window, jQuery);
