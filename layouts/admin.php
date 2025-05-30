<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header('Location: ../index.php');
  exit;
}
require_once '../config.php';

try {
  $userQuery = $conn->query("SELECT COUNT(*) as total FROM users");
  $userCount = $userQuery->fetch(PDO::FETCH_ASSOC)['total'];

  $seminarQuery = $conn->query("SELECT COUNT(*) as total FROM seminars WHERE status = 1");
  $seminarCount = $seminarQuery->fetch(PDO::FETCH_ASSOC)['total'];

  $speakerQuery = $conn->query("SELECT COUNT(*) as total FROM speakers WHERE status = 1");
  $speakerCount = $speakerQuery->fetch(PDO::FETCH_ASSOC)['total'];

  $locationQuery = $conn->query("SELECT COUNT(*) as total FROM locations WHERE status = 1");
  $locationCount = $locationQuery->fetch(PDO::FETCH_ASSOC)['total'];

  $messageQuery = $conn->query("SELECT COUNT(*) as total FROM contact");
  $messageCount = $messageQuery->fetch(PDO::FETCH_ASSOC)['total'];

  $stats = [
    'users' => $userCount,
    'seminars' => $seminarCount,
    'speakers' => $speakerCount,
    'locations' => $locationCount,
    'messages' => $messageCount
  ];
} catch (PDOException $e) {
  error_log("Admin page error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  <link rel="stylesheet" href="../assets/css/style-admin.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <title>Trang Admin</title>
</head>

<body>
  <?php include_once './admin_sidebar.php'; ?>

  <!-- Main Content -->
  <div class="content">
    <h1 class="mb-4">Trang chủ quản lý</h1>

    <div class="row">
      <div class="col-md-4 mb-4">
        <div class="dashboard-card">
          <i class="fas fa-users fa-2x text-primary mb-3"></i>
          <h5>Người dùng</h5>
          <p class="counter"><?php echo htmlspecialchars($stats['users']); ?></p>
          <a href="./admin_users.php" class="btn btn-sm btn-outline-primary mt-2">Quản lý</a>
        </div>
      </div>

      <div class="col-md-4 mb-4">
        <div class="dashboard-card">
          <i class="fas fa-chalkboard-teacher fa-2x text-success mb-3"></i>
          <h5>Hội thảo</h5>
          <p class="counter"><?php echo htmlspecialchars($stats['seminars']); ?></p>
          <a href="./admin_seminars.php" class="btn btn-sm btn-outline-success mt-2">Quản lý</a>
        </div>
      </div>

      <div class="col-md-4 mb-4">
        <div class="dashboard-card">
          <i class="fas fa-microphone fa-2x text-danger mb-3"></i>
          <h5>Diễn giả</h5>
          <p class="counter"><?php echo htmlspecialchars($stats['speakers']); ?></p>
          <a href="./admin_speakers.php" class="btn btn-sm btn-outline-danger mt-2">Quản lý</a>
        </div>
      </div>

      <div class="col-md-4 mb-4">
        <div class="dashboard-card">
          <i class="fas fa-map-marker-alt fa-2x text-warning mb-3"></i>
          <h5>Địa điểm</h5>
          <p class="counter"><?php echo htmlspecialchars($stats['locations']); ?></p>
          <a href="./admin_locations.php" class="btn btn-sm btn-outline-warning mt-2">Quản lý</a>
        </div>
      </div>

      <div class="col-md-4 mb-4">
        <div class="dashboard-card">
          <i class="fas fa-envelope fa-2x text-info mb-3"></i>
          <h5>Tin nhắn</h5>
          <p class="counter"><?php echo htmlspecialchars($stats['messages']); ?></p>
          <a href="./admin_messages.php" class="btn btn-sm btn-outline-info mt-2">Quản lý</a>
        </div>
      </div>

      <div class="col-md-4 mb-4">
        <div class="dashboard-card">
          <i class="fas fa-user-check fa-2x text-secondary mb-3"></i>
          <h5>Đăng ký tham gia</h5>
          <p class="counter">
            <?php
            $regQuery = $conn->query("SELECT COUNT(*) as total FROM registrations");
              echo htmlspecialchars($regQuery->fetch(PDO::FETCH_ASSOC)['total']);
            ?>
          </p>
          <a href="./admin_registrations.php" class="btn btn-sm btn-outline-info mt-2">Quản lý</a>
        </div>
      </div>
    </div>

    <!-- Thống kê bổ sung -->
    <div class="mt-5">
      <h2 class="mb-4">Thống kê chi tiết</h2>
      <div class="row">
        <div class="col-md-6">
          <div class="card">
            <div class="card-header bg-light">
              <h5 class="card-title mb-0">Hội thảo sắp tới</h5>
            </div>
            <div class="card-body">
              <?php
              try {
                $upcomingQuery = $conn->query("
                  SELECT COUNT(*) as total 
                  FROM seminars 
                  WHERE start_time > NOW()
                ");
                $upcomingCount = $upcomingQuery->fetch(PDO::FETCH_ASSOC)['total'];

                echo "<p>Tổng số: <strong>" . htmlspecialchars($upcomingCount) . "</strong></p>";

                $upcomingSeminars = $conn->query("
                  SELECT topic, start_time, seminar_id
                  FROM seminars
                  WHERE start_time > NOW()
                  ORDER BY start_time ASC
                  LIMIT 3
                ");

                if ($upcomingSeminars->rowCount() > 0) {
                  echo "<ul class='list-group list-group-flush'>";
                  while ($seminar = $upcomingSeminars->fetch(PDO::FETCH_ASSOC)) {
                    $date = new DateTime($seminar['start_time']);
                    echo "<li class='list-group-item d-flex justify-content-between align-items-center'>
                      <span>" . htmlspecialchars($seminar['topic']) . "</span>
                      <span class='badge badge-primary'>" . $date->format('d/m/Y') . "</span>
                    </li>";
                  }
                  echo "</ul>";
                } else {
                  echo "<p class='text-muted'>Không có hội thảo sắp tới</p>";
                }
              } catch (PDOException $e) {
                echo "<p class='text-danger'>Lỗi khi lấy dữ liệu</p>";
              }
              ?>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card">
            <div class="card-header bg-light">
              <h5 class="card-title mb-0">Người dùng mới</h5>
            </div>
            <div class="card-body">
              <?php
              try {
                $newUsers = $conn->query("
                  SELECT username, full_name, created_at 
                  FROM users 
                  ORDER BY created_at DESC 
                  LIMIT 5
                ");

                if ($newUsers->rowCount() > 0) {
                  echo "<ul class='list-group list-group-flush'>";
                  while ($user = $newUsers->fetch(PDO::FETCH_ASSOC)) {
                    $date = new DateTime($user['created_at']);
                    echo "<li class='list-group-item d-flex justify-content-between align-items-center'>
                      <div>
                        <strong>" . htmlspecialchars($user['username']) . "</strong><br>
                        <small class='text-muted'>" . htmlspecialchars($user['full_name']) . "</small>
                      </div>
                      <span class='badge badge-info'>" . $date->format('d/m/Y') . "</span>
                    </li>";
                  }
                  echo "</ul>";
                } else {
                  echo "<p class='text-muted'>Không có người dùng mới</p>";
                }
              } catch (PDOException $e) {
                echo "<p class='text-danger'>Lỗi khi lấy dữ liệu</p>";
              }
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS và jQuery -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
</body>

</html>