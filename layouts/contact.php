<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <title>Hội thảo khoa học</title>

  <style>
    .contact-info,
    .contact-form {
      max-width: 600px;
      margin: 30px auto;
      padding: 20px;
      background: white;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .contact-info h2,
    .contact-form h2 {
      color: #1a2a6c;
      margin-bottom: 20px;
    }

    .contact-info p,
    .contact-info a {
      line-height: 1.8;
      color: #333;
    }

    .contact-form .form-control {
      border-radius: 5px;
    }

    .contact-form .btn-primary {
      background-color: #1a2a6c;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      transition: background-color 0.3s;
    }

    .contact-form .btn-primary:hover {
      background-color: #b21f2d;
    }

    .map-container {
      margin: 30px 0;
      text-align: center;
    }

    .map-container iframe {
      border-radius: 10px;
      max-width: 100%;
    }
  </style>
</head>

<body>
  <?php include 'header.php'; ?>
  <!-- Hero Section -->
  <section class="hero-section">
    <div class="container">
      <h1>Liên hệ chúng tôi</h1>
      <p>Chúng tôi rất muốn nghe từ bạn! Đặt bất kì câu hỏi hay đánh giá.</p>
    </div>
  </section>

  <!-- Contact Information -->
  <section class="contact-info">
    <h2>Thông tin liên hệ chi tiết</h2>
    <p><strong>Email:</strong> <a href="#">abc@gmail.com</a></p>
    <p><strong>Số điện thoại:</strong> +84 123 456 789</p>
    <p><strong>Địa chỉ:</strong> 123 đường ABC, Hà Nội, Việt Nam</p>
    <div class="map-container">
      <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3724.096289053564!2d105.84113231504174!3d21.028511985998207!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjHCsDAxJzQyLjciTiAxMDXCsDUwJzI4LjEiRQ!5e0!3m2!1sen!2s!4v1634567890123" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
    </div>
  </section>

  <!-- Contact Form -->
  <section class="contact-form">
    <h2>Gửi tin nhắn cho chúng tôi</h2>
    <?php
    $message = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $name = htmlspecialchars(trim($_POST['name'] ?? ''));
      $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
      $topic = htmlspecialchars(trim($_POST['topic'] ?? ''));
      $message_content = htmlspecialchars(trim($_POST['message'] ?? ''));
  
      if ($name && $email && $topic && $message_content) {
        try {
          // Lưu vào CSDL
          $stmt = $conn->prepare("INSERT INTO contact (name, email, topic, message) 
                                 VALUES (:name, :email, :topic, :message)");
          $stmt->bindParam(':name', $name);
          $stmt->bindParam(':email', $email);
          $stmt->bindParam(':topic', $topic);
          $stmt->bindParam(':message', $message_content);
          $stmt->execute();
  
          // Hiển thị thông báo thành công
          $message = '<div class="alert alert-success">
            <i class="fas fa-check-circle mr-2"></i> Tin nhắn của bạn đã được gửi thành công! 
            Chúng tôi sẽ phản hồi qua email của bạn trong thời gian sớm nhất.
          </div>';
  
          // Reset form sau khi gửi thành công
          $name = $email = $topic = $message_content = "";
          
        } catch (PDOException $e) {
          // Xử lý lỗi và hiển thị thông báo lỗi
          $message = '<div class="alert alert-danger">
            <i class="fas fa-exclamation-circle mr-2"></i> Có lỗi xảy ra khi gửi tin nhắn. Vui lòng thử lại sau.
          </div>';
  
          // Log lỗi để debug sau (không hiển thị cho người dùng)
          error_log("Contact form error: " . $e->getMessage());
        }
      } else {
        $message = '<div class="alert alert-danger">
          <i class="fas fa-exclamation-triangle mr-2"></i> Vui lòng điền đầy đủ thông tin và đúng định dạng.
        </div>';
      }
    }
    ?>
    <?php echo $message; ?>
    <form method="POST" action="" id="contactForm">
      <div class="mb-3">
        <label for="name" class="form-label">Họ và tên</label>
        <input type="text" class="form-control" id="name" name="name" required
          value="<?php echo htmlspecialchars($name ?? ''); ?>">
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" required
          value="<?php echo htmlspecialchars($email ?? ''); ?>">
      </div>
      <div class="mb-3">
        <label for="topic" class="form-label">Chủ đề</label>
        <input type="text" class="form-control" id="topic" name="topic" required
          value="">
      </div>
      <div class="mb-3">
        <label for="message" class="form-label">Tin nhắn</label>
        <textarea class="form-control" id="message" name="message" rows="5" required><?php echo htmlspecialchars($message_content ?? ''); ?></textarea>
      </div>
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-paper-plane mr-2"></i> Gửi tin nhắn
      </button>
    </form>
  </section>
  <?php include 'footer.php'; ?>
</body>

</html>