function apiAttendanceTypeList(params = {}) {
  return $.ajax({
    url: './ctrl_attendance_type.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'ATTENDANCE_TYPE_LIST' }, params)
  });
}

function apiAttendanceTypeGet(id) {
  return $.ajax({
    url: './ctrl_attendance_type.php',
    type: 'POST',
    dataType: 'json',
    data: { type: 'ATTENDANCE_TYPE_GET', id }
  });
}

function apiAttendanceTypeCreate(payload) {
  return $.ajax({
    url: './ctrl_attendance_type.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'ATTENDANCE_TYPE_CREATE' }, payload)
  });
}

function apiAttendanceTypeUpdate(id, payload) {
  return $.ajax({
    url: './ctrl_attendance_type.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'ATTENDANCE_TYPE_UPDATE', id }, payload)
  });
}

function apiAttendanceTypeDelete(id) {
  return $.ajax({
    url: './ctrl_attendance_type.php',
    type: 'POST',
    dataType: 'json',
    data: { type: 'ATTENDANCE_TYPE_DELETE', id }
  });
}
