function apiStudyReportList(params = {}) {
  return $.ajax({
    url: './ctrl_study_report.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'STUDY_REPORT_LIST' }, params)
  });
}

function apiStudyReportGet(id) {
  return $.ajax({
    url: './ctrl_study_report.php',
    type: 'POST',
    dataType: 'json',
    data: { type: 'STUDY_REPORT_GET', id }
  });
}

function apiStudyReportCreate(payload) {
  return $.ajax({
    url: './ctrl_study_report.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'STUDY_REPORT_CREATE' }, payload)
  });
}

function apiStudyReportUpdate(id, payload) {
  return $.ajax({
    url: './ctrl_study_report.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'STUDY_REPORT_UPDATE', id }, payload)
  });
}

function apiStudyReportDelete(id) {
  return $.ajax({
    url: './ctrl_study_report.php',
    type: 'POST',
    dataType: 'json',
    data: { type: 'STUDY_REPORT_DELETE', id }
  });
}
