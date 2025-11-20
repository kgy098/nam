(function (global, $) {
  'use strict';

  var ENDPOINT = g5_ctrl_url + '/ctrl_mock_apply.php';

  var T = {
    LIST: 'MOCK_APPLY_LIST',
    GET: 'MOCK_APPLY_GET',
    ADD: 'MOCK_APPLY_CREATE',
    UPD: 'MOCK_APPLY_UPDATE',
    DEL: 'MOCK_APPLY_DELETE'
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
    }
  };

})(window, jQuery);
