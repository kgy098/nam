function apiVideoList(params = {}) {
  return $.ajax({
    url: './ctrl_video.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'VIDEO_LIST' }, params)
  });
}

function apiVideoGet(id) {
  return $.ajax({
    url: './ctrl_video.php',
    type: 'POST',
    dataType: 'json',
    data: { type: 'VIDEO_GET', id }
  });
}

function apiVideoCreate(payload) {
  return $.ajax({
    url: './ctrl_video.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'VIDEO_CREATE' }, payload)
  });
}

function apiVideoUpdate(id, payload) {
  return $.ajax({
    url: './ctrl_video.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'VIDEO_UPDATE', id }, payload)
  });
}

function apiVideoDelete(id) {
  return $.ajax({
    url: './ctrl_video.php',
    type: 'POST',
    dataType: 'json',
    data: { type: 'VIDEO_DELETE', id }
  });
}
