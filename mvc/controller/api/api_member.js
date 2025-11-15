function apiMemberList(params = {}) {
  return $.ajax({
    url: g5_ctrl_url + '/ctrl_member.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'MEMBER_LIST' }, params)
  });
}

function apiMemberGet(mb_id) {
  return $.ajax({
    url: g5_ctrl_url + '/ctrl_member.php',
    type: 'POST',
    dataType: 'json',
    data: { type: 'MEMBER_GET', mb_id }
  });
}

function apiMemberCreate(payload) {
  return $.ajax({
    url: g5_ctrl_url + '/ctrl_member.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'MEMBER_CREATE' }, payload)
  });
}

function apiMemberUpdate(mb_id, payload) {
  return $.ajax({
    url: g5_ctrl_url + '/ctrl_member.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'MEMBER_UPDATE', mb_id }, payload)
  });
}

function apiMemberDelete(mb_id) {
  return $.ajax({
    url: g5_ctrl_url + '/ctrl_member.php',
    type: 'POST',
    dataType: 'json',
    data: { type: 'MEMBER_DELETE', mb_id }
  });
}
