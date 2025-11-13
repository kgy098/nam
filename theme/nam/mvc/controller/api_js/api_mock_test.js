function apiMockTestList(params = {}) {
  return $.ajax({
    url: './ctrl_mock_test.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'MOCK_TEST_LIST' }, params)
  });
}

function apiMockTestGet(id) {
  return $.ajax({
    url: './ctrl_mock_test.php',
    type: 'POST',
    dataType: 'json',
    data: { type: 'MOCK_TEST_GET', id }
  });
}

function apiMockTestCreate(payload) {
  return $.ajax({
    url: './ctrl_mock_test.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'MOCK_TEST_CREATE' }, payload)
  });
}

function apiMockTestUpdate(id, payload) {
  return $.ajax({
    url: './ctrl_mock_test.php',
    type: 'POST',
    dataType: 'json',
    data: Object.assign({ type: 'MOCK_TEST_UPDATE', id }, payload)
  });
}

function apiMockTestDelete(id) {
  return $.ajax({
    url: './ctrl_mock_test.php',
    type: 'POST',
    dataType: 'json',
    data: { type: 'MOCK_TEST_DELETE', id }
  });
}
