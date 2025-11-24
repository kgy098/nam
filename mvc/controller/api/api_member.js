/* ==========================================================
   Member API (serialize 기반, 화면 DOM 접근 없음)
   모든 화면은 serialize() 결과만 전달한다.
========================================================== */

(function ($) {
  'use strict';

  var ENDPOINT = g5_ctrl_url + '/ctrl_member.php';

  /* ----------------------------------------------------------
     공통 call()
     - 항상 Promise 반환
     - alert 없음
     - 실패도 resolve 로 반환
  ---------------------------------------------------------- */
  function call(params, onSuccess) {

    return $.ajax({
      url: ENDPOINT,
      type: 'POST',
      data: params,
      dataType: 'json'
    })
    .done(function (res) {
      if (typeof onSuccess === 'function') {
        onSuccess(res);
      }
      return res;
    })
    .fail(function (xhr) {
      console.error("통신 오류", xhr);
      return {
        result: 'FAIL',
        data: 'NETWORK_ERROR'
      };
    });
  }

  /* ==========================================================
     Member API
  ========================================================== */
  var API = {

    /* --------------------------------------
       1) 목록 조회
       → memberListCallback(res)
    -------------------------------------- */
    list: function (params) {
      params.type = 'MEMBER_LIST';

      return call(params, function (res) {
        if (typeof memberListCallback === 'function') {
          memberListCallback(res);
        }
      });
    },

    /* --------------------------------------
       2) 단건 조회
       → memberGetCallback(res)
    -------------------------------------- */
    get: function (params) {
      params.type = 'MEMBER_GET';

      return call(params, function (res) {
        if (typeof memberGetCallback === 'function') {
          memberGetCallback(res);
        }
      });
    },

    /* --------------------------------------
       3) 생성
       결과는 화면에서 직접 처리
    -------------------------------------- */
    create: function (params) {

      if (typeof params === 'string') {
        params += '&type=MEMBER_ADD';
      } else {
        params.type = 'MEMBER_ADD';
      }

      return call(params);
    },

    /* --------------------------------------
       4) 수정
    -------------------------------------- */
    update: function (params) {

      if (typeof params === 'string') {
        params += '&type=MEMBER_UPD';
      } else {
        params.type = 'MEMBER_UPD';
      }

      return call(params);
    },

    /* --------------------------------------
       5) 삭제
    -------------------------------------- */
    delete: function (params) {

      if (typeof params === 'string') {
        params += '&type=MEMBER_DEL';
      } else {
        params.type = 'MEMBER_DEL';
      }

      return call(params);
    },

    _endpoint: ENDPOINT
  };

  // global 제거 규칙 반영
  window.memberAPI = API;

})(jQuery);
