<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

// Lấy danh sách hội thảo đã đăng ký nếu user đã đăng nhập
$userSeminars = [];
if (isset($_SESSION['user_id'])) {
  require_once dirname(__FILE__) . '/../config.php';

  $stmt = $conn->prepare("
            SELECT s.seminar_id, s.topic, s.start_time 
            FROM seminars s
            JOIN registrations r ON s.seminar_id = r.seminar_id
            WHERE r.user_id = :user_id
            ORDER BY s.start_time DESC
            LIMIT 5
        ");
  $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
  $stmt->execute();
  $userSeminars = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!-- Header section -->
<header class="header-section sticky-top">
  <nav class="navbar navbar-expand-lg navbar-light">
    <a class="navbar-brand" href="../../Bai032/index.php">LOGO</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ml-auto">
        <li class="nav-item">
          <a class="nav-link" href="../index.php">Trang chủ</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../layouts/about.php">Giới thiệu</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../layouts/locations.php">Địa điểm</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../layouts/speakers.php">Diễn giả</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../layouts/seminars.php">Hội thảo</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../layouts/contact.php">Liên hệ</a>
        </li>

        <?php if (isset($_SESSION['user_id'])): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="fas fa-user-circle mr-1"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
              <a class="dropdown-item" href="#">
                <i class="fas fa-id-card mr-2"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?>
              </a>

              <?php if ($_SESSION['role'] == 'admin'): ?>
                <a class="dropdown-item" href="../layouts/admin.php">
                  <i class="fas fa-cog mr-2"></i> Quản lý
                </a>
              <?php endif; ?>
              <a class="dropdown-item" href="../layouts/my_seminars.php">
                <i class="fas fa-user-edit mr-2"></i> Hội thảo của tôi
              </a>
              <a class="dropdown-item" href="../layouts/my_registrations.php">
                <i class="fas fa-list-alt mr-2"></i> Đăng ký của tôi
              <div class="dropdown-divider"></div>
              <a class="dropdown-item text-danger" href="../layouts/logout.php">
                <i class="fas fa-sign-out-alt mr-2"></i> Đăng xuất
              </a>
            </div>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="../layouts/login.php">
              <i class="fas fa-sign-in-alt mr-1"></i> Đăng nhập
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </nav>
</header>
<!-- End Header Section -->