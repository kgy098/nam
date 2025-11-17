/* ==========================================================
   Member API (serialize 기반, 화면 DOM 접근 없음)
   모든 화면은 serialize() 결과만 전달한다.
   ========================================================== */

// 목록 조회
function apiMemberList(params) {
  console.log( JSON.stringify(params) ); 
  return $.ajax({
    url: g5_ctrl_url + '/ctrl_member.php',
    type: 'POST',
    dataType: 'json',
    data: params,
    success: function(res) {
      if (typeof memberListCallback === 'function') {
        memberListCallback(res);
      }
    },
    error: function() {
      alert('통신 오류가 발생했습니다.');
    }
  });
}

// 단건 조회
function apiMemberGet(paramStr) {
  return $.ajax({
    url: g5_ctrl_url + '/ctrl_member.php',
    type: 'POST',
    dataType: 'json',
    data: paramStr,
    success: function(res) {
      if (typeof memberGetCallback === 'function') {
        memberGetCallback(res);
      }
    },
    error: function() {
      alert('통신 오류가 발생했습니다.');
    }
  });
}

// 생성
function apiMemberCreate(paramStr) {
  return $.ajax({
    url: g5_ctrl_url + '/ctrl_member.php',
    type: 'POST',
    dataType: 'json',
    data: paramStr,
    success: function(res) {
      if (res.result === 'SUCCESS') {
        alert('저장되었습니다.');
        location.href = './member_list.php';
      } else {
        alert('저장 실패: ' + res.data);
      }
    },
    error: function() {
      alert('통신 오류가 발생했습니다.');
    }
  });
}

// 수정
function apiMemberUpdate(paramStr) {
  return $.ajax({
    url: g5_ctrl_url + '/ctrl_member.php',
    type: 'POST',
    dataType: 'json',
    data: paramStr,
    success: function(res) {
      if (res.result === 'SUCCESS') {
        alert('수정되었습니다.');
        location.href = './member_list.php';
      } else {
        alert('수정 실패: ' + res.data);
      }
    },
    error: function() {
      alert('통신 오류가 발생했습니다.');
    }
  });
}

// 삭제
function apiMemberDelete(paramStr) {
  return $.ajax({
    url: g5_ctrl_url + '/ctrl_member.php',
    type: 'POST',
    dataType: 'json',
    data: paramStr,
    success: function(res) {
      if (res.result === 'SUCCESS') {
        alert('삭제되었습니다.');
        location.reload();
      } else {
        alert('삭제 실패: ' + res.data);
      }
    },
    error: function() {
      alert('통신 오류가 발생했습니다.');
    }
  });
}
