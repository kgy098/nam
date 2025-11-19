<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Kling AI 테스트</title>
</head>
<body>
  <h2>이미지 → 애니메이션 생성</h2>

  <form action="process.php" method="post" enctype="multipart/form-data">
      <input type="file" name="image" accept="image/*" required>
      <br><br>
      <button type="submit">애니메이션 생성하기</button>
  </form>

</body>
</html>
