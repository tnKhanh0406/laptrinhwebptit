<?php
function buildPaginationUrl($page, $search, $categories) {
  $url = "?page=" . $page;
  if (!empty($search)) {
    $url .= "&search=" . urlencode($search);
  }
  if (!empty($categories)) {
    foreach ($categories as $category) {
      $url .= "&category[]=" . urlencode($category);
    }
  }
  return $url;
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <title>Hội thảo khoa học</title>
  <style>
    .seminars-section {
      max-width: 1200px;
      margin: 50px auto;
      padding: 20px;
    }

    .seminar-card {
      position: relative;
      background: white;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      padding: 20px;
      margin-bottom: 20px;
      transition: transform 0.3s;
      height: 100%;
    }

    .seminar-card:hover {
      transform: translateY(-5px);
    }

    .seminar-card h5 {
      color: #1a2a6c;
    }

    .seminar-card p {
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

    .seminar-img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 5px;
    }

    .search-filter-section {
      margin: 20px auto;
    }

    .category-filters {
      display: flex;
      flex-wrap: wrap;
    }

    .custom-control {
      margin-right: 10px;
      margin-bottom: 10px;
    }

    .no-results {
      padding: 50px 0;
      text-align: center;
    }

    .no-results h4 {
      margin-bottom: 15px;
      color: #6c757d;
    }
  </style>
</head>

<body>
  <?php include '../layouts/header.php'; ?>
  <?php include '../config.php';

  $search = isset($_GET['search']) ? trim($_GET['search']) : '';
  $selectedCategories = isset($_GET['category']) ? (array)$_GET['category'] : [];

  //phân trang
  $itemsPerPage = 6; 
  $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1; 

  if ($currentPage < 1) {
    $currentPage = 1;
  }

  // Xây dựng câu truy vấn cơ bản
  $baseQuery = "FROM seminars WHERE status = 1";
  $queryParams = [];

  if (!empty($search)) {
    $baseQuery .= " AND topic LIKE :search";
    $queryParams[':search'] = "%$search%";
  }

  if (!empty($selectedCategories)) {
    $placeholders = [];
    foreach ($selectedCategories as $index => $category) {
      $paramName = ":category$index";
      $placeholders[] = $paramName;
      $queryParams[$paramName] = $category;
    }
    $baseQuery .= " AND category IN (" . implode(', ', $placeholders) . ")";
  }

  $countQuery = "SELECT COUNT(*) as total " . $baseQuery;
  $countStmt = $conn->prepare($countQuery);
  foreach ($queryParams as $param => $value) {
    $countStmt->bindValue($param, $value);
  }
  $countStmt->execute();
  $totalItems = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
  $totalPages = ceil($totalItems / $itemsPerPage);

  if ($currentPage > $totalPages && $totalPages > 0) {
    $currentPage = $totalPages;
  }

  $offset = ($currentPage - 1) * $itemsPerPage;

  $dataQuery = "SELECT * " . $baseQuery . " ORDER BY start_time DESC LIMIT :limit OFFSET :offset";
  $stmt = $conn->prepare($dataQuery);
  foreach ($queryParams as $param => $value) {
    $stmt->bindValue($param, $value);
  }
  $stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
  $stmt->execute();

  $seminars = $stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>

  <!-- Hero Section -->
  <section class="hero-section">
    <div class="container">
      <h1>Các hội thảo</h1>
      <p>Khám phá các hội thảo sắp diễn ra và tham gia vào cộng đồng chia sẻ kiến thức.</p>
    </div>
  </section>

  <section class="search-filter-section">
    <div class="container">
      <div class="card mb-4">
        <div class="card-body">
          <form method="GET" action="" id="searchFilterForm">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="searchQuery">Tìm kiếm theo tên</label>
                <div class="input-group">
                  <input type="text" class="form-control" id="searchQuery" name="search"
                    value="<?php echo htmlspecialchars($search); ?>"
                    placeholder="Nhập tên hội thảo...">
                  <div class="input-group-append">
                    <button class="btn btn-primary" type="submit">
                      <i class="fas fa-search"></i> Tìm
                    </button>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <label>Phân loại</label>
                <div class="category-filters">
                  <?php
                  $categoryQuery = $conn->query("SELECT DISTINCT category FROM seminars ORDER BY category");
                  $categories = $categoryQuery->fetchAll(PDO::FETCH_COLUMN);

                  foreach ($categories as $category):
                    $isChecked = in_array($category, $selectedCategories);
                  ?>
                    <div class="custom-control custom-checkbox custom-control-inline">
                      <input type="checkbox" class="custom-control-input category-checkbox"
                        id="category-<?php echo htmlspecialchars($category); ?>"
                        name="category[]"
                        value="<?php echo htmlspecialchars($category); ?>"
                        <?php echo $isChecked ? 'checked' : ''; ?>>
                      <label class="custom-control-label" for="category-<?php echo htmlspecialchars($category); ?>">
                        <?php echo htmlspecialchars($category); ?>
                      </label>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>

  <!-- Seminars Section -->
  <section class="seminars-section">
    <div class="row">
      <?php if (empty($seminars)): ?>
        <div class="col-12 no-results">
          <h4>Không tìm thấy hội thảo nào</h4>
          <?php if (!empty($search) || !empty($selectedCategories)): ?>
            <p>Thử tìm kiếm với từ khóa khác hoặc thay đổi các bộ lọc.</p>
            <a href="seminars.php" class="btn btn-outline-primary">
              <i class="fas fa-sync-alt"></i> Xóa bộ lọc
            </a>
          <?php else: ?>
            <p>Hiện tại chưa có hội thảo nào được đăng ký.</p>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <?php
        $currentDateTime = new DateTime();
        foreach ($seminars as $seminar):
          $startDateTime = new DateTime($seminar['start_time']);
          $endDateTime = new DateTime($seminar['end_time']);
        ?>
          <div class="col-md-4 mb-4">
            <div class="seminar-card">
              <img src="<?php echo htmlspecialchars('../assets/images/' . (!empty($seminar['photo']) ? $seminar['photo'] : 'default-seminar.jpg')); ?>"
                alt="<?php echo htmlspecialchars($seminar['topic']); ?>"
                class="img-fluid seminar-img mb-3">

              <?php if ($startDateTime > $currentDateTime): ?>
                <span class="badge badge-success position-absolute" style="top: 10px; right: 10px; font-size: 14px; padding: 8px;">Sắp diễn ra</span>
              <?php elseif ($endDateTime < $currentDateTime): ?>
                <span class="badge badge-secondary position-absolute" style="top: 10px; right: 10px; font-size: 14px; padding: 8px;">Đã kết thúc</span>
              <?php else: ?>
                <span class="badge badge-danger position-absolute" style="top: 10px; right: 10px; font-size: 14px; padding: 8px;">Đang diễn ra</span>
              <?php endif; ?>

              <h5><?php echo htmlspecialchars($seminar['topic']); ?></h5>
              <p><strong>Thời gian:</strong>
                <?php
                echo $startDateTime->format('d/m/Y, H:i') . ' - ' . $endDateTime->format('H:i');
                ?></p>
              <p><strong>Số người tối đa:</strong> <?php echo htmlspecialchars($seminar['max_participants'] ?? 'N/A'); ?></p>
              <p><strong>Phân loại:</strong> <?php echo htmlspecialchars($seminar['category']); ?></p>
              <a href="seminar_detail.php?id=<?php echo $seminar['seminar_id']; ?>" class="btn btn-primary mt-2">Chi tiết</a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Phân trang -->
    <?php if ($totalPages > 1): ?>
      <nav aria-label="Seminar pagination" class="mt-4">
        <ul class="pagination">
          <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo buildPaginationUrl($currentPage - 1, $search, $selectedCategories); ?>" aria-label="Previous">
              <span aria-hidden="true">&laquo;</span>
            </a>
          </li>

          <?php
          // Hiển thị tối đa 5 số trang, với trang hiện tại ở giữa
          $startPage = max(1, min($currentPage - 2, $totalPages - 4));
          $endPage = min($totalPages, $startPage + 4);

          if ($startPage > 1): ?>
            <li class="page-item">
              <a class="page-link" href="<?php echo buildPaginationUrl(1, $search, $selectedCategories); ?>">1</a>
            </li>
            <?php if ($startPage > 2): ?>
              <li class="page-item disabled">
                <a class="page-link" href="#">...</a>
              </li>
            <?php endif; ?>
          <?php endif; ?>

          <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
            <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
              <a class="page-link" href="<?php echo buildPaginationUrl($i, $search, $selectedCategories); ?>"><?php echo $i; ?></a>
            </li>
          <?php endfor; ?>

          <?php if ($endPage < $totalPages): ?>
            <?php if ($endPage < $totalPages - 1): ?>
              <li class="page-item disabled">
                <a class="page-link" href="#">...</a>
              </li>
            <?php endif; ?>
            <li class="page-item">
              <a class="page-link" href="<?php echo buildPaginationUrl($totalPages, $search, $selectedCategories); ?>"><?php echo $totalPages; ?></a>
            </li>
          <?php endif; ?>

          <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo buildPaginationUrl($currentPage + 1, $search, $selectedCategories); ?>" aria-label="Next">
              <span aria-hidden="true">&raquo;</span>
            </a>
          </li>
        </ul>
      </nav>
    <?php endif; ?>
  </section>

  <?php include '../layouts/footer.php'; ?>

  <!-- Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Tự động gửi form khi checkbox thay đổi
      const categoryCheckboxes = document.querySelectorAll('.category-checkbox');
      categoryCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
          document.getElementById('searchFilterForm').submit();
        });
      });
    });
  </script>
</body>

</html>