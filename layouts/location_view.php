<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header('Location: ../index.php');
  exit;
}

require_once '../config.php';

$location = null;
$seminars = [];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header('Location: ./admin_locations.php');
  exit;
}

$locationId = (int)$_GET['id'];

try {
  $stmt = $conn->prepare("SELECT * FROM locations WHERE location_id = :location_id");
  $stmt->bindParam(':location_id', $locationId, PDO::PARAM_INT);
  $stmt->execute();
  
  $location = $stmt->fetch(PDO::FETCH_ASSOC);
  
  if (!$location) {
    header('Location: ./admin_locations.php');
    exit;
  }
  
  $seminarStmt = $conn->prepare("
    SELECT seminar_id, topic, start_time, end_time 
    FROM seminars 
    WHERE location_id = :location_id 
    ORDER BY start_time DESC
  ");
  $seminarStmt->bindParam(':location_id', $locationId, PDO::PARAM_INT);
  $seminarStmt->execute();
  
  $seminars = $seminarStmt->fetchAll(PDO::FETCH_ASSOC);
  
} catch (PDOException $e) {
  $errorMessage = "Lỗi khi lấy thông tin: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <title>Chi tiết Địa điểm</title>
  <style>
    .location-image {
      max-width: 100%;
      max-height: 300px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      margin-bottom: 20px;
    }
  </style>
</head>

<body>
  <?php include '../layouts/header.php'; ?>
  <div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1>Chi tiết Địa điểm</h1>
      <a href="./locations.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
      </a>
    </div>
    
    <?php if (isset($errorMessage)): ?>
      <div class="alert alert-danger">
        <?php echo $errorMessage; ?>
      </div>
    <?php endif; ?>
    
    <?php if ($location): ?>
      <div class="card mb-4">
        <div class="card-header bg-primary text-white">
          <h4><i class="fas fa-map-marked-alt"></i> <?php echo htmlspecialchars($location['name']); ?></h4>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <?php if (!empty($location['photo']) && file_exists("../assets/images/" . $location['photo'])): ?>
                <img src="<?php echo '../assets/images/' . htmlspecialchars($location['photo']); ?>" alt="Hình ảnh địa điểm" class="location-image">
              <?php else: ?>
                <div class="alert alert-info">
                  <i class="fas fa-info-circle"></i> Không có hình ảnh cho địa điểm này.
                </div>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <h5 class="mb-3">Thông tin địa điểm</h5>
              
              <p>
                <strong><i class="fas fa-map-marker-alt"></i> Địa chỉ:</strong>
                <?php echo !empty($location['address']) ? 
                  nl2br(htmlspecialchars($location['address'])) : 
                  '<span class="text-muted">Chưa cập nhật</span>'; ?>
              </p>
              
              <p>
                <strong><i class="fas fa-id-card"></i> ID:</strong> 
                <?php echo htmlspecialchars($location['location_id']); ?>
              </p>
            </div>
          </div>
        </div>
      </div>
      
      <div class="card">
        <div class="card-header bg-info text-white">
          <h5><i class="fas fa-calendar-alt"></i> Các hội thảo tổ chức tại địa điểm này</h5>
        </div>
        <div class="card-body">
          <?php if (!empty($seminars)): ?>
            <div class="table-responsive">
              <table class="table table-bordered table-hover">
                <thead class="thead-light">
                  <tr>
                    <th width="5%">ID</th>
                    <th width="50%">Chủ đề</th>
                    <th width="20%">Bắt đầu</th>
                    <th width="20%">Kết thúc</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($seminars as $seminar): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($seminar['seminar_id']); ?></td>
                      <td><?php echo htmlspecialchars($seminar['topic']); ?></td>
                      <td><?php echo date('d/m/Y H:i', strtotime($seminar['start_time'])); ?></td>
                      <td><?php echo date('d/m/Y H:i', strtotime($seminar['end_time'])); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="alert alert-info">
              <i class="fas fa-info-circle"></i> Chưa có hội thảo nào được tổ chức tại địa điểm này.
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
  <?php include '../layouts/footer.php'; ?>
  <!-- Bootstrap JS và jQuery -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
</body>

</html>