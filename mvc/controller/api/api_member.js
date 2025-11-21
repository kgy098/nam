/* ==========================================================
   Member API (serialize 기반, 화면 DOM 접근 없음)
   모든 화면은 serialize() 결과만 전달한다.
   ========================================================== */

(function (global, $) {
  'use strict';

  var ENDPOINT = g5_ctrl_url + '/ctrl_member.php';

  /* ----------------------------------------------------------
     공통 AJAX
     (alert 제거, res 직접 반환)
  ---------------------------------------------------------- */
  function call(params, onSuccess) {

    return $.ajax({
      url: ENDPOINT,
      type: 'POST',
      dataType: 'json',
      data: params
    })
    .done(function(res) {
      if (typeof onSuccess === 'function') {
        onSuccess(res);
      }
      return res;
    })
    .fail(function(xhr) {
      console.error("통신 오류 발생", xhr);
      return {
        result: 'FAIL',
        data: 'NETWORK_ERROR'
      };
    });
  }

  /* ==========================================================
     Member API
     (ctrl_member.php 의 type 구조와 완전히 일치)
  ========================================================== */
  var API = {

    /* --------------------------------------
       1) 회원 목록 조회
       type = MEMBER_LIST
       → memberListCallback(res)
    -------------------------------------- */
    list: function(params) {
      params.type = 'MEMBER_LIST';

      return call(params, function(res) {
        if (typeof memberListCallback === 'function') {
          memberListCallback(res);
        }
      });
    },

    /* --------------------------------------
       2) 단건 조회
       type = MEMBER_GET
       → memberGetCallback(res)
    -------------------------------------- */
    get: function(params) {
      params.type = 'MEMBER_GET';

      return call(params, function(res) {
        if (typeof memberGetCallback === 'function') {
          memberGetCallback(res);
        }
      });
    },

    /* --------------------------------------
       3) 생성
       type = MEMBER_ADD
       (결과는 화면에서 처리)
    -------------------------------------- */
    create: function(params) {
      // serialize 문자열 전달 가능함
      if (typeof params === 'string') {
        params += '&type=MEMBER_ADD';
      } else {
        params.type = 'MEMBER_ADD';
      }
      return call(params);
    },

    /* --------------------------------------
       4) 수정
       type = MEMBER_UPD
    -------------------------------------- */
    update: function(params) {
      if (typeof params === 'string') {
        params += '&type=MEMBER_UPD';
      } else {
        params.type = 'MEMBER_UPD';
      }
      return call(params);
    },

    /* --------------------------------------
       5) 삭제
       type = MEMBER_DEL
    -------------------------------------- */
    delete: function(params) {
      if (typeof params === 'string') {
        params += '&type=MEMBER_DEL';
      } else {
        params.type = 'MEMBER_DEL';
      }
      return call(params);
    },

    _endpoint: ENDPOINT
  };

  global.memberAPI = API;

})(window, jQuery);
