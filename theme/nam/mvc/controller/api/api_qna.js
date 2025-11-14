function apiQnaList(params = {}) {
  return $.ajax({
    url: './ctrl_qna.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'QNA_LIST' }, params)
  });
}

function apiQnaGet(id) {
  return $.ajax({
    url: './ctrl_qna.php',
    type: 'POST',
    dataType: 'json',
    data: { type: 'QNA_GET', id }
  });
}

function apiQnaCreate(payload) {
  return $.ajax({
    url: './ctrl_qna.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'QNA_CREATE' }, payload)
  });
}

function apiQnaUpdate(id, payload) {
  return $.ajax({
    url: './ctrl_qna.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'QNA_UPDATE', id }, payload)
  });
}

function apiQnaDelete(id) {
  return $.ajax({
    url: './ctrl_qna.php',
    type: 'POST',
    dataType: 'json',
    data: { type: 'QNA_DELETE', id }
  });
}
