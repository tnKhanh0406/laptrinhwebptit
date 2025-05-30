<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <title>Danh sách diễn giả</title>
  <style>
    .speakers-section {
      max-width: 1200px;
      margin: 50px auto;
      padding: 20px;
    }

    .speaker-card {
      background: white;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      padding: 20px;
      margin-bottom: 20px;
      transition: transform 0.3s;
    }

    .speaker-card:hover {
      transform: translateY(-5px);
    }

    .speaker-card h5 {
      color: #1a2a6c;
    }

    .speaker-card p {
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
  <section class="hero-section">
    <div class="container">
      <h1>Các diễn giả của chúng tôi</h1>
      <p>Gặp gỡ những chuyên gia và những nhà lãnh đạo dẫn đầu xu thế đổi mới.</p>
    </div>
  </section>
  <?php
  include '../config.php';

  //phân trang
  $itemsPerPage = 9;
  $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

  if ($currentPage < 1) {
    $currentPage = 1;
  }

  $offset = ($currentPage - 1) * $itemsPerPage;

  $countStmt = $conn->query("SELECT COUNT(*) as total FROM speakers");
  $totalItems = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
  $totalPages = ceil($totalItems / $itemsPerPage);

  if ($currentPage > $totalPages && $totalPages > 0) {
    $currentPage = $totalPages;
    $offset = ($currentPage - 1) * $itemsPerPage;
  }

  $stmt = $conn->prepare("SELECT * FROM speakers LIMIT :limit OFFSET :offset");
  $stmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
  $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
  $stmt->execute();

  $speakers = $stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>

  <section class="speakers-section">
    <div class="row">
      <?php foreach ($speakers as $speaker): ?>
        <a href="./speaker_view.php?id=<?php echo $speaker['speaker_id']; ?>" class="col-md-4 d-flex mb-4">
          <div class="speaker-card h-100 w-100">
            <img src="<?php echo htmlspecialchars('../assets/images/' . (!empty($speaker['photo']) ? $speaker['photo'] : 'default-speaker.jpg')); ?>"
              alt="<?php echo htmlspecialchars($speaker['full_name']); ?>" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px;">
            <h5><?php echo htmlspecialchars($speaker['full_name']); ?></h5>
            <p class="font-weight-bold"><?php echo htmlspecialchars(substr($speaker['bio'] ?? '', 0, 150)) . (strlen($speaker['bio'] ?? '') > 150 ? '...' : ''); ?></p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Phân trang -->
    <?php if ($totalPages > 1): ?>
      <nav aria-label="Speaker pagination">
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