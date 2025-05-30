<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <title>Danh sách địa điểm</title>
  <style>
    .locations-section {
      max-width: 1200px;
      margin: 50px auto;
      padding: 20px;
    }

    .location-card {
      background: white;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      padding: 20px;
      margin-bottom: 20px;
      transition: transform 0.3s;
    }

    .location-card:hover {
      transform: translateY(-5px);
    }

    .location-card h5 {
      color: #1a2a6c;
    }

    .location-card p {
      color: #333;
      line-height: 1.6;
    }

    .pagination {
      justify-content: center;
      margin-top: 30px;
    }

    .page-item.active .page-link {
      background-color: #1a2a6c;
      border-color: #1a2a6c;
    }

    .page-link {
      color: #1a2a6c;
    }
  </style>
</head>

<body>
  <?php include '../layouts/header.php'; ?>

  <!-- Hero Section -->
  <section class="hero-section">
    <div class="container">
      <h1>Địa điểm tổ chức</h1>
      <p>Khám phá nơi tổ chức sự kiện của chúng tôi.</p>
    </div>
  </section>

  <?php
  include '../config.php';

  // Phân trang
  $itemsPerPage = 6;
  $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

  if ($currentPage < 1) {
    $currentPage = 1;
  }

  $offset = ($currentPage - 1) * $itemsPerPage;

  $countStmt = $conn->query("SELECT COUNT(*) as total FROM locations WHERE status = 1");
  $totalItems = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
  $totalPages = ceil($totalItems / $itemsPerPage);

  if ($currentPage > $totalPages && $totalPages > 0) {
    $currentPage = $totalPages;
    $offset = ($currentPage - 1) * $itemsPerPage;
  }

  $stmt = $conn->prepare("SELECT * FROM locations WHERE status = 1 LIMIT :limit OFFSET :offset");
  $stmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
  $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
  $stmt->execute();

  $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>

  <!-- Locations Section -->
  <section class="locations-section">
    <div class="row">
      <?php foreach ($locations as $location): ?>
        <a href="./location_view.php?id=<?php echo $location['location_id']; ?>" class="col-md-4 mb-4">
          <div class="location-card">
            <img src="<?php echo htmlspecialchars('../assets/images/' . (!empty($location['photo']) ? $location['photo'] : 'default-location.jpg')); ?>"
              alt="<?php echo htmlspecialchars($location['name']); ?>"
              class="img-fluid card-img-top mb-3"
              style="width: 100%; height: 200px; object-fit: cover;">
            <h5><?php echo htmlspecialchars($location['name']); ?></h5>
            <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($location['address']); ?></p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Phân trang -->
    <?php if ($totalPages > 1): ?>
      <nav aria-label="Page navigation">
        <ul class="pagination">
          <!-- Nút Previous -->
          <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $currentPage - 1; ?>" aria-label="Previous">
              <span aria-hidden="true">&laquo;</span>
            </a>
          </li>

          <!-- Các số trang -->
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
              <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
          <?php endfor; ?>

          <!-- Nút Next -->
          <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $currentPage + 1; ?>" aria-label="Next">
              <span aria-hidden="true">&raquo;</span>
            </a>
          </li>
        </ul>
      </nav>
    <?php endif; ?>
  </section>

  <?php include '../layouts/footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
</body>

</html>