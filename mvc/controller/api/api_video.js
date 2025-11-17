(function (global, $) {
  'use strict';

  var ENDPOINT = g5_ctrl_url + '/ctrl_video.php';

  var T = {
    LIST:   'VIDEO_LIST',
    GET:    'VIDEO_GET',
    ADD:    'VIDEO_CREATE',
    UPD:    'VIDEO_UPDATE',
    DEL:    'VIDEO_DELETE'
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

    /* 영상 목록 */
    list: function (page, num, filters) {
      var pg = pageParams(page, num);
      var p = {
        type: T.LIST,
        start: pg.start,
        num: pg.num
      };

      // filters: { keyword, class_name, mb_id }
      if (filters) {
        if (filters.keyword)    p.keyword    = filters.keyword;
        if (filters.class_name) p.class_name = filters.class_name;
        if (filters.mb_id)      p.mb_id      = filters.mb_id;
      }

      return call(p);
    },

    /* 단건 조회 */
    get: function (id) {
      return call({ type: T.GET, id: id });
    },

    /* 등록 */
    add: function (params) {
      // params: { title, youtube_id, description, class_name, mb_id }

      var p = {
        type: T.ADD,
        title: params.title,
        youtube_id: params.youtube_id
      };

      if (params.description != null) p.description = params.description;
      if (params.class_name != null) p.class_name = params.class_name;
      if (params.mb_id != null)      p.mb_id      = params.mb_id;

      return call(p);
    },

    /* 수정 */
    update: function (id, fields) {
      var p = { type: T.UPD, id: id };

      if (fields.title != null)        p.title       = fields.title;
      if (fields.youtube_id != null)   p.youtube_id  = fields.youtube_id;
      if (fields.description != null)  p.description = fields.description;
      if (fields.class_name != null)   p.class_name  = fields.class_name;
      if (fields.mb_id != null)        p.mb_id       = fields.mb_id;

      return call(p);
    },

    /* 삭제 */
    remove: function (id) {
      return call({ type: T.DEL, id: id });
    },

    _endpoint: ENDPOINT,
    _T: T
  };

  global.VideoAPI = API;

})(window, jQuery);
