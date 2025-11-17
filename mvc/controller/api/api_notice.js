(function (global, $) {
  'use strict';

  var ENDPOINT = g5_ctrl_url + '/ctrl_notice.php';

  var T = {
    LIST: 'NOTICE_LIST',
    GET:  'NOTICE_GET',
    ADD:  'NOTICE_ADD',
    UPD:  'NOTICE_UPD',
    DEL:  'NOTICE_DEL'
  };

  function call(params) {
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

  function pageParams(page, num) {
    var p = parseInt(page || 1, 10);
    var n = parseInt(num || 20, 10);
    return { start: (p - 1) * n, num: n };
  }

  var API = {

    /* 공지사항 목록 */
    list: function (page, num) {
      var pg = pageParams(page, num);
      return call({ type: T.LIST, start: pg.start, num: pg.num });
    },

    /* 단건 조회 */
    get: function (id) {
      return call({ type: T.GET, id: id });
    },

    /* 등록 */
    add: function (params) {
      // params: { mb_id, writer_name, title, content, file_path?, file_name? }

      var p = {
        type: T.ADD,
        mb_id: params.mb_id,
        writer_name: params.writer_name,
        title: params.title,
        content: params.content
      };

      if (params.file_path) p.file_path = params.file_path;
      if (params.file_name) p.file_name = params.file_name;

      return call(p);
    },

    /* 수정 */
    update: function(id, fields) {
      var p = {
        type: T.UPD,
        id: id
      };

      if (fields.writer_name != null) p.writer_name = fields.writer_name;
      if (fields.title != null)       p.title       = fields.title;
      if (fields.content != null)     p.content     = fields.content;
      if (fields.file_path != null)   p.file_path   = fields.file_path;
      if (fields.file_name != null)   p.file_name   = fields.file_name;

      return call(p);
    },

    /* 삭제 */
    remove: function(id) {
      return call({ type: T.DEL, id: id });
    },

    _endpoint: ENDPOINT,
    _T: T
  };

  global.NoticeAPI = API;

})(window, jQuery);
