function apiNotifyLogList(params = {}) {
  return $.ajax({
    url: './ctrl_notify_log.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'NOTIFY_LOG_LIST' }, params)
  });
}

function apiNotifyLogGet(id) {
  return $.ajax({
    url: './ctrl_notify_log.php',
    type: 'POST',
    dataType: 'json',
    data: { type: 'NOTIFY_LOG_GET', id }
  });
}

function apiNotifyLogCreate(payload) {
  return $.ajax({
    url: './ctrl_notify_log.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'NOTIFY_LOG_CREATE' }, payload)
  });
}

function apiNotifyLogUpdate(id, payload) {
  return $.ajax({
    url: './ctrl_notify_log.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'NOTIFY_LOG_UPDATE', id }, payload)
  });
}

function apiNotifyLogDelete(id) {
  return $.ajax({
    url: './ctrl_notify_log.php',
    type: 'POST',
    dataType: 'json',
    data: { type: 'NOTIFY_LOG_DELETE', id }
  });
}
