(function (global, $) {
  'use strict';

  var ENDPOINT = g5_ctrl_url + '/ctrl_study_report.php';
  var T = {
    LIST: 'STUDY_REPORT_LIST',
    MY_LIST: 'STUDY_REPORT_MY_LIST',          // 앱/학생용 (신규)
    GET: 'STUDY_REPORT_GET',
    CREATE: 'STUDY_REPORT_CREATE',
    UPDATE: 'STUDY_REPORT_UPDATE',
    DELETE: 'STUDY_REPORT_DELETE',
    FILE_UPLOAD: 'STUDY_REPORT_FILE_UPLOAD',
    FILE_DELETE: 'STUDY_REPORT_FILE_DELETE',
    FILE_LIST: 'STUDY_REPORT_FILE_LIST'
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

  function pageParams(page, num) {
    var p = parseInt(page || 1, 10);
    var n = parseInt(num || 20, 10);
    return { page: p, rows: n };
  }

  var API = {
    list: function (page, num, filters) {
      var pg = pageParams(page, num);
      var payload = { type: T.LIST, page: pg.page, rows: pg.rows };

      if (filters) {
        if (filters.mb_id) payload.mb_id = filters.mb_id;
        if (filters.class) payload.class = filters.class;
        if (filters.date_from) payload.date_from = filters.date_from;
        if (filters.date_to) payload.date_to = filters.date_to;
        if (filters.keyword) payload.keyword = filters.keyword;
      }

      return call(payload);
    },

    myList: function (page, num, filters) {
      var pg = pageParams(page, num);
      var payload = { type: T.MY_LIST, page: pg.page, rows: pg.rows };

      if (filters) {
        if (filters.subject_id) payload.subject_id = filters.subject_id;
        if (filters.date_from) payload.date_from = filters.date_from;
        if (filters.date_to) payload.date_to = filters.date_to;
      }

      return call(payload);
    },


    get: function (id) {
      return call({ type: T.GET, id: id });
    },

    create: function (fields) {
      var payload = { type: T.CREATE };

      if (fields.mb_id) payload.mb_id = fields.mb_id;
      if (fields.subject) payload.subject = fields.subject;
      if (fields.title) payload.title = fields.title;
      if (fields.content) payload.content = fields.content;
      if (fields.report_date) payload.report_date = fields.report_date;

      return call(payload);
    },

    update: function (id, fields) {
      var payload = { type: T.UPDATE, id: id };

      if (typeof fields.subject !== 'undefined') {
        payload.subject = fields.subject;
      }
      if (typeof fields.title !== 'undefined') {
        payload.title = fields.title;
      }
      if (typeof fields.content !== 'undefined') {
        payload.content = fields.content;
      }
      if (typeof fields.report_date !== 'undefined') {
        payload.report_date = fields.report_date;
      }

      return call(payload);
    },

    remove: function (id) {
      return call({ type: T.DELETE, id: id });
    },

    // FormData를 사용한 파일 포함 저장/수정
    submit: function (formData) {
      return $.ajax({
        url: './study_report_update.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json'
      }).then(function (res) {
        if (res && res.result === 'SUCCESS') return res;
        return $.Deferred().reject(res || { result: 'FAIL' }).promise();
      });
    },

    // 삭제 (FormData 사용)
    deleteWithFile: function (id, bo_table) {
      var formData = new FormData();
      formData.append('w', 'd');
      formData.append('id', id);
      formData.append('bo_table', bo_table);

      return $.ajax({
        url: './study_report_update.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json'
      }).then(function (res) {
        if (res && res.result === 'SUCCESS') return res;
        return $.Deferred().reject(res || { result: 'FAIL' }).promise();
      });
    },

    _endpoint: ENDPOINT,
    _T: T
  };

  global.StudyReportAPI = API;

})(window, jQuery);