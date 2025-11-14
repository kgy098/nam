function apiMockSubjectList(params = {}) {
  return $.ajax({
    url: './ctrl_mock_subject.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'MOCK_SUBJECT_LIST' }, params)
  });
}

function apiMockSubjectGet(id) {
  return $.ajax({
    url: './ctrl_mock_subject.php',
    type: 'POST',
    dataType: 'json',
    data: { type: 'MOCK_SUBJECT_GET', id }
  });
}

function apiMockSubjectCreate(payload) {
  return $.ajax({
    url: './ctrl_mock_subject.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'MOCK_SUBJECT_CREATE' }, payload)
  });
}

function apiMockSubjectUpdate(id, payload) {
  return $.ajax({
    url: './ctrl_mock_subject.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'MOCK_SUBJECT_UPDATE', id }, payload)
  });
}

function apiMockSubjectDelete(id) {
  return $.ajax({
    url: './ctrl_mock_subject.php',
    type: 'POST',
    dataType: 'json',
    data: { type: 'MOCK_SUBJECT_DELETE', id }
  });
}
