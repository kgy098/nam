function apiConsultList(params = {}) {
  return $.ajax({
    url: './ctrl_consult.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'CONSULT_LIST' }, params)
  });
}

function apiConsultGet(id) {
  return $.ajax({
    url: './ctrl_consult.php',
    type: 'POST',
    dataType: 'json',
    data: { type: 'CONSULT_GET', id }
  });
}

function apiConsultCreate(payload) {
  return $.ajax({
    url: './ctrl_consult.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'CONSULT_CREATE' }, payload)
  });
}

function apiConsultUpdate(id, payload) {
  return $.ajax({
    url: './ctrl_consult.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'CONSULT_UPDATE', id }, payload)
  });
}

function apiConsultDelete(id) {
  return $.ajax({
    url: './ctrl_consult.php',
    type: 'POST',
    dataType: 'json',
    data: { type: 'CONSULT_DELETE', id }
  });
}
