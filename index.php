<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  <link rel="stylesheet" href="./assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <title>Hội thảo khoa học</title>
</head>

<body>
  <?php include './layouts/header.php'; ?>

  <!-- Hero Section -->
  <div class="hero-section pt-5">
    <div class="container position-relative">
      <div class="row">
        <div class="col-xl-6 col-lg-6 col-md-6 col-12">
          <div class="inner-content">
            <h3 class="inner-title mb-4">ABC Events</h3>
            <p class="inner-desc mb-4">Tất cả các sự kiện của ABC Events tại các địa điểm khác nhau</p>
            <a href="./layouts/seminars.php" class="button">Xem tất cả sự kiện</a>
          </div>
        </div>
        <div class="col-xl-6 col-lg-6 col-md-6 col-12">
          <div class="inner-img mt-4">
            <img src="./assets/images/landing-hero-6.png" alt="speakers">
          </div>
        </div>
      </div>
      <div class="search-section position-absolute">
        <div class="search-content">
          <div class="search-title mb-2">Tìm kiếm sự kiện</div>
          <h3 class="search-desc mb-3">Tìm kiếm tất cả các sự kiện đã và sắp diễn ra</h3>
          <div class="search-form">
            <form method="GET" action="./layouts/seminars.php" id="searchForm">
              <div class="input-group">
                <input type="text" class="form-control" id="search" name="search"
                  placeholder="Nhập tên hội thảo..." aria-label="Tìm kiếm sự kiện">
                <div class="input-group-append">
                  <button class="btn btn-primary" type="submit">
                    <i class="fas fa-search"></i> Tìm kiếm
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- End Hero Section -->

  <!-- carousel section -->
  <div class="carousel-section">
    <div class="container">
      <div class="carousel-heading d-flex align-items-center">
        <h4>Hội nghị và meetings</h4>
        <a class="ml-auto" href="./layouts/seminars.php">Xem tất cả hội nghị và meetings</a>
      </div>

      <?php
      require_once './config.php';

      $currentDateTime = new DateTime();

      try {
        $stmt = $conn->prepare("
          SELECT s.seminar_id, s.topic, s.photo, s.start_time, s.end_time, s.category,
                 l.name as location_name 
          FROM seminars s
          LEFT JOIN locations l ON s.location_id = l.location_id
          ORDER BY s.start_time DESC
          LIMIT 9
        ");
        $stmt->execute();
        $seminars = $stmt->fetchAll(PDO::FETCH_ASSOC);
      ?>

        <div id="carouselIndicators" class="carousel slide mb-5" data-ride="carousel">
          <ol class="carousel-indicators">
            <li data-target="#carouselIndicators" data-slide-to="0" class="active"></li>
            <?php for ($i = 1; $i < ceil(count($seminars) / 3); $i++): ?>
              <li data-target="#carouselIndicators" data-slide-to="<?php echo $i; ?>"></li>
            <?php endfor; ?>
          </ol>

          <div class="carousel-inner">
            <?php
            $chunkedSeminars = array_chunk($seminars, 3);

            foreach ($chunkedSeminars as $index => $group):
            ?>
              <div class="carousel-item <?php echo ($index === 0) ? 'active' : ''; ?>">
                <div class="wraper d-flex justify-content-around">
                  <?php foreach ($group as $seminar):
                    $startTime = new DateTime($seminar['start_time']);
                    $endTime = new DateTime($seminar['end_time']);

                    // Xác định trạng thái hội thảo
                    $status = ($startTime > $currentDateTime) ? 'SẮP DIỄN RA' : (($endTime < $currentDateTime) ? 'ĐÃ KẾT THÚC' : 'ĐANG DIỄN RA');

                    $statusClass = ($startTime > $currentDateTime) ? 'upcoming' : (($endTime < $currentDateTime) ? 'past' : 'ongoing');
                  ?>
                    <a class="carousel-card" href="./layouts/seminar_detail.php?id=<?php echo $seminar['seminar_id']; ?>">
                      <div class="img position-relative">
                        <img src="./assets/images/<?php echo !empty($seminar['photo']) ? $seminar['photo'] : 'default-seminar.jpg'; ?>">
                        <p class="<?php echo $statusClass; ?> position-absolute"><?php echo $status; ?></p>
                      </div>
                      <div class="date">
                        <?php echo $startTime->format('d') . ' Tháng ' . $startTime->format('m, Y') . ' | ' .
                          $startTime->format('H:i') . ' - ' .
                          $endTime->format('H:i');
                        ?>
                      </div>
                      <div class="desc font-weight-bold">
                        <?php echo htmlspecialchars($seminar['topic']); ?>
                      </div>
                      <div class="location">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo htmlspecialchars($seminar['location_name'] ?? 'Chưa có địa điểm'); ?>
                      </div>
                    </a>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <a class="carousel-control-prev rounded-circle" href="#carouselIndicators" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
          </a>
          <a class="carousel-control-next rounded-circle" href="#carouselIndicators" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
          </a>
        </div>

      <?php
      } catch (PDOException $e) {
        echo 'Error: ' . $e->getMessage();
      }
      ?>
    </div>
  </div>
  <!-- End carousel section -->

  <!-- Explore section -->
  <div class="explore-section">
    <div class="container">
      <div class="row">
        <div class="col-xl-6 col-lg-6 col-12">
          <div class="wrapper d-flex">
            <img src="./assets/images/1714057810345.jpg" alt="job">
            <div class="inner-content ml-4 text-light">
              <h4 class="inner-title font-weight-bold">Hội thảo việc làm</h4>
              <div class="inner-desc mb-4">Khám phá con đường sự nghiệp và quản lý công việc của bạn</div>
              <a href="./layouts/seminars.php?category[]=Việc làm" class="text-light">Xem tất cả <i class="fas fa-arrow-right"></i></a>
            </div>
          </div>
        </div>
        <div class="col-xl-6 col-lg-6 col-12">
          <div class="wrapper d-flex">
            <img src="./assets/images/1705417288072.jpg" alt="fundraising">
            <div class="inner-content ml-4 text-light">
              <h4 class="inner-title font-weight-bold">Hội thảo gây quỹ</h4>
              <div class="inner-desc mb-4">Tìm kiếm hội thảo liên quan đến gây quỹ</div>
              <a href="./layouts/seminars.php?category[]=Gây quỹ" class="text-light">Xem tất cả <i class="fas fa-arrow-right"></i></a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- End Explore section -->

  <!-- topic section -->
  <div class="topic-section">
    <div class="container">
      <div class="row d-flex align-items-center">
        <div class="col-xl-4 col-lg-4 col-12 mb-lg-0 mb-md-5">
          <h2 class="font-weight-bold mb-4" style="font-size: 40px;">TÌM KIẾM TẤT CẢ CHỦ ĐỀ SỰ KIỆN</h2>
          <a href="./layouts/seminars.php" class="button">Tìm kiếm tất cả</a>
        </div>
        <div class="col-xl-7 col-lg-7 col-12 ml-auto">
          <div class="wraper d-flex flex-wrap">
            <a href="#" type="button" class="btn btn-light badge-pill">Phân tích</a>
            <a href="./layouts/seminars.php?category[]=Vật lý" type="button" class="btn btn-light badge-pill">Vật lý</a>
            <a href="./layouts/seminars.php?category[]=Sinh học và y tế" type="button" class="btn btn-light badge-pill">Sinh học và y tế</a>
            <a href="#" type="button" class="btn btn-light badge-pill">Nguyên liệu khoa học</a>
            <a href="#" type="button" class="btn btn-light badge-pill">Vật liệu nano</a>
            <a href="./layouts/seminars.php?category[]=Việc làm" type="button" class="btn btn-light badge-pill">Việc làm</a>
            <a href="./layouts/seminars.php?category[]=Gây quỹ" type="button" class="btn btn-light badge-pill">Gây quỹ</a>
            <a href="./layouts/seminars.php?category[]=Hữu cơ" type="button" class="btn btn-light badge-pill">Hữu cơ</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- end topic section -->

  <!-- logo section -->
  <div class="logo-section">
    <div class="container">
      <div class="title text-center mb-5 font-weight-bold text-center">Được tin tưởng bởi các tổ chức</div>
      <div class="row">
        <div class="col-xl-2 col-lg-2 col-md-2 col-sm-4 col-6">
          <img src="./assets/images/logo-bo-thong-tin-va-truyen-thong-2.png" alt="">
        </div>
        <div class="col-xl-2 col-lg-2 col-md-2 col-sm-4 col-6">
          <img src="./assets/images/file.png" alt="">
        </div>
        <div class="col-xl-2 col-lg-2 col-md-2 col-sm-4 col-6">
          <img src="./assets/images/TED-logo.webp" alt="">
        </div>
        <div class="col-xl-2 col-lg-2 col-md-2 col-sm-4 col-6">
          <img src="./assets/images/th (1).jpg" alt="">
        </div>
        <div class="col-xl-2 col-lg-2 col-md-2 col-sm-4 col-6">
          <img src="./assets/images/th.jpg" alt="">
        </div>
        <div class="col-xl-2 col-lg-2 col-md-2 col-sm-4 col-6">
          <img src="./assets/images/ptit-logo-inkythuatso-17-14-54-16.jpg" alt="">
        </div>
      </div>
    </div>
  </div>
  <!-- end logo section -->

  <!-- contact form -->
  <div class="contact">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <div class="inner-head">
            <div class="inner-title">Liên hệ chúng tôi</div>
          </div>
        </div>
        <div class="col-12">
          <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2887.7800359724224!2d105.83544363667666!3d21.0368562406069!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3135aba15ec15d17%3A0x620e85c2cfe14d4c!2sHo%20Chi%20Minh&#39;s%20Mausoleum!5e0!3m2!1sen!2s!4v1736163666446!5m2!1sen!2s" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
        <div class="col-xl-4 col-lg-4 col-12">
          <div class="inner-content">
            <div class="inner-card">
              <div class="inner-icon">
                <i class="fa-solid fa-location-dot"></i>
              </div>
              <div class="inner-address">
                <div class="inner-title-adr">Địa chỉ</div>
                <div class="inner-adr">Số 123, Đường ABC, Hà Nội, Việt Nam</div>
              </div>
            </div>
            <div class="inner-card">
              <div class="inner-icon">
                <i class="fa-solid fa-phone"></i>
              </div>
              <div class="inner-address">
                <div class="inner-title-adr">Liên hệ chúng tôi</div>
                <div class="inner-adr">+84 123456789</div>
              </div>
            </div>
            <div class="inner-card">
              <div class="inner-icon">
                <i class="fa-solid fa-envelope"></i>
              </div>
              <div class="inner-address">
                <div class="inner-title-adr">Email</div>
                <div class="inner-adr">abc@gmail.com</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-8 col-lg-8 col-12">
          <?php
          $message = '';
          if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
            $name = htmlspecialchars(trim($_POST['name'] ?? ''));
            $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
            $topic = htmlspecialchars(trim($_POST['topic'] ?? ''));
            $message_content = htmlspecialchars(trim($_POST['message'] ?? ''));

            if ($name && $email && $topic && $message_content) {

              $stmt = $conn->prepare("INSERT INTO contact (name, email, topic, message) 
                                       VALUES (:name, :email, :topic, :message)");
              $stmt->bindParam(':name', $name);
              $stmt->bindParam(':email', $email);
              $stmt->bindParam(':topic', $topic);
              $stmt->bindParam(':message', $message_content);
              $stmt->execute();

              $message = '<div class="alert alert-success">
                  <i class="fas fa-check-circle mr-2"></i> Tin nhắn của bạn đã được gửi thành công! 
                  Chúng tôi sẽ phản hồi qua email của bạn trong thời gian sớm nhất.
                </div>';

              $name = $email = $topic = $message_content = "";
            } else {
              $message = '<div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle mr-2"></i> Vui lòng điền đầy đủ thông tin và đúng định dạng.
              </div>';
            }
          }
          ?>
          <?php echo $message; ?>
          <form method="POST" action="" id="contactForm">
            <div class="row">
              <div class="col-xl-6 col-lg-6 col-md-12">
                <div class="form-group">
                  <label for="name" class="form-label">Họ và tên</label>
                  <input type="text" class="form-control" id="name" name="name" required
                    value="<?php echo htmlspecialchars($name ?? ''); ?>" placeholder="Họ và tên">
                </div>
              </div>
              <div class="col-xl-6 col-lg-6 col-md-12">
                <div class="form-group">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" class="form-control" id="email" name="email" required
                    value="<?php echo htmlspecialchars($email ?? ''); ?>" placeholder="Email">
                </div>
              </div>
              <div class="col-12">
                <div class="form-group">
                  <label for="topic" class="form-label">Chủ đề</label>
                  <input type="text" class="form-control" id="topic" name="topic" required
                    value="" placeholder="Chủ đề">
                </div>
              </div>
              <div class="col-12">
                <div class="form-group">
                  <label for="message" class="form-label">Tin nhắn</label>
                  <textarea class="form-control" id="message" name="message" rows="5" required
                    placeholder="Tin nhắn"><?php echo htmlspecialchars($message_content ?? ''); ?></textarea>
                </div>
              </div>
            </div>
            <div class="inner-btn text-center">
              <button type="submit" name="contact_submit" class="btn btn-primary">
                <i class="fas fa-paper-plane mr-2"></i> Gửi tin nhắn
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!-- end contact form -->

  <?php include './layouts/footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
</body>

</html>