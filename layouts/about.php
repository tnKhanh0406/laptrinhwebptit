<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <title>Về chúng tôi</title>
  <style>
    .content-section {
      max-width: 800px;
      margin: 50px auto;
      padding: 20px;
      background: white;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .content-section h2 {
      color: #1a2a6c;
      margin-bottom: 20px;
    }

    .content-section p {
      line-height: 1.8;
      color: #333;
    }

    .cta-button {
      background-color: #1a2a6c;
      color: white;
      padding: 15px 30px;
      border-radius: 5px;
      text-decoration: none;
      display: inline-block;
      margin-top: 20px;
      transition: background-color 0.3s;
    }

    .cta-button:hover {
      background-color: #ddd;
    }
  </style>
</head>

<body>
  <?php include 'header.php'; ?>
  <!-- Hero Section -->
  <section class="hero-section">
    <div class="container">
      <h1>Về chúng tôi</h1>
      <p>Kết nối cộng đồng khoa học thông qua tri thức và đổi mới</p>
    </div>
  </section>

  <!-- Content Section -->
  <section class="content-section">
    <h2>Chào mừng đến với ABC Events</h2>
    <p>
      Chào mừng đến với ABC Events, nền tảng hàng đầu kết nối cộng đồng khoa học thông qua các hội thảo chất lượng và hấp dẫn.
      Chúng tôi tự hào mang đến một không gian trực tuyến nơi các nhà nghiên cứu, chuyên gia và những người yêu khoa học có thể khám phá,
      tham gia và chia sẻ tri thức từ nhiều hội thảo khoa học đa dạng.
    </p>
    <p>
      Tại Scientific Seminar Hub, bạn có thể dễ dàng tra cứu thông tin chi tiết về các hội thảo như nội dung, diễn giả, lịch trình,
      địa điểm và đăng ký tham dự chỉ với vài bước đơn giản.
      Sứ mệnh của chúng tôi là thúc đẩy sự kết nối, học hỏi và đổi mới trong cộng đồng khoa học, tạo ra cơ hội để những ý tưởng được lan tỏa
      và phát triển. 
    </p>
    <p>
      Hãy tham gia cùng chúng tôi ngay hôm nay để khám phá thế giới khoa học và đồng hành trong những sự kiện truyền cảm hứng!
    </p>
    <a href="../../Bai032/layouts/seminars.php" class="cta-button">Khám phá các hội thảo ngay</a>
  </section>
  <?php include 'footer.php'; ?>
</body>

</html>