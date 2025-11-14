function apiMockApplyList(params = {}) {
  return $.ajax({
    url: './ctrl_mock_apply.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'MOCK_APPLY_LIST' }, params)
  });
}

function apiMockApplyGet(id) {
  return $.ajax({
    url: './ctrl_mock_apply.php',
    type: 'POST',
    dataType: 'json',
    data: { type: 'MOCK_APPLY_GET', id }
  });
}

function apiMockApplyCreate(payload) {
  return $.ajax({
    url: './ctrl_mock_apply.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'MOCK_APPLY_CREATE' }, payload)
  });
}

function apiMockApplyUpdate(id, payload) {
  return $.ajax({
    url: './ctrl_mock_apply.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'MOCK_APPLY_UPDATE', id }, payload)
  });
}

function apiMockApplyDelete(id) {
  return $.ajax({
    url: './ctrl_mock_apply.php',
    type: 'POST',
    dataType: 'json',
    data: { type: 'MOCK_APPLY_DELETE', id }
  });
}
