function apiMemberFeeList(params = {}) {
  return $.ajax({
    url: './ctrl_member_fee.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'MEMBER_FEE_LIST' }, params)
  });
}

function apiMemberFeeGet(id) {
  return $.ajax({
    url: './ctrl_member_fee.php',
    type: 'POST',
    dataType: 'json',
    data: { type: 'MEMBER_FEE_GET', id }
  });
}

function apiMemberFeeCreate(payload) {
  return $.ajax({
    url: './ctrl_member_fee.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'MEMBER_FEE_CREATE' }, payload)
  });
}

function apiMemberFeeUpdate(id, payload) {
  return $.ajax({
    url: './ctrl_member_fee.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'MEMBER_FEE_UPDATE', id }, payload)
  });
}

function apiMemberFeeDelete(id) {
  
  return $.ajax({
    url: './ctrl_member_fee.php',
    type: 'POST',
    dataType: 'json',
    data: { type: 'MEMBER_FEE_DELETE', id }
  });
}
