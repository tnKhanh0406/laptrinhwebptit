<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header('Location: ../index.php');
  exit;
}

require_once '../config.php';

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
  try {
    $contactId = (int)$_GET['id'];
    
    $stmt = $conn->prepare("DELETE FROM contact WHERE contact_id = :contact_id");
    $stmt->bindParam(':contact_id', $contactId, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
      $deleteMessage = "Đã xóa tin nhắn thành công!";
      $deleteStatus = "success";
    } else {
      $deleteMessage = "Không tìm thấy tin nhắn để xóa!";
      $deleteStatus = "warning";
    }
  } catch (PDOException $e) {
    $deleteMessage = "Lỗi khi xóa tin nhắn: " . $e->getMessage();
    $deleteStatus = "danger";
  }
}

try {
  // Phân trang
  $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  $limit = 10; 
  $offset = ($currentPage - 1) * $limit;
  
  $countStmt = $conn->query("SELECT COUNT(*) FROM contact");
  $totalContacts = $countStmt->fetchColumn();
  $totalPages = ceil($totalContacts / $limit);
  
  $search = isset($_GET['search']) ? trim($_GET['search']) : '';
  $whereClause = "";
  
  if (!empty($search)) {
    $whereClause = "WHERE name LIKE :search OR email LIKE :search OR topic LIKE :search OR message LIKE :search";
    $searchTerm = "%{$search}%";
  }
  
  $countSql = "SELECT COUNT(*) FROM contact " . $whereClause;
  
  if (!empty($search)) {
    $countStmt = $conn->prepare($countSql);
    $countStmt->bindParam(':search', $searchTerm);
    $countStmt->execute();
  } else {
    $countStmt = $conn->query($countSql);
  }
  
  $totalContacts = $countStmt->fetchColumn();
  $totalPages = ceil($totalContacts / $limit);
  
  $sql = "SELECT contact_id, name, email, topic, message, created_at FROM contact " . $whereClause . " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
  
  $stmt = $conn->prepare($sql);
  
  if (!empty($search)) {
    $stmt->bindParam(':search', $searchTerm);
  }
  
  $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
  $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
  $stmt->execute();
  $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $errorMessage = "Lỗi khi lấy danh sách tin nhắn: " . $e->getMessage();
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <title>Quản lý Tin nhắn Liên hệ</title>
  <style>
    .message-preview {
      max-height: 80px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    .message-full {
      white-space: pre-line;
    }
    .badge-new {
      background-color: #28a745;
      color: white;
    }
    .contact-card {
      margin-bottom: 20px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .contact-card .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .contact-time {
      font-size: 0.85rem;
      color: #6c757d;
    }
  </style>
</head>

<body>
  <?php include_once './admin_sidebar.php'; ?>

  <div class="content">
    <h1 class="mb-4">Quản lý Tin nhắn Liên hệ</h1>
    
    <?php if (isset($deleteMessage)): ?>
      <div class="alert alert-<?php echo $deleteStatus; ?> alert-dismissible fade show" role="alert">
        <?php echo $deleteMessage; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
    <?php endif; ?>
    
    <?php if (isset($errorMessage)): ?>
      <div class="alert alert-danger">
        <?php echo $errorMessage; ?>
      </div>
    <?php endif; ?>
    
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h5>
          <i class="fas fa-envelope"></i> 
          Tổng số tin nhắn: <span class="badge badge-primary"><?php echo $totalContacts; ?></span>
        </h5>
      </div>
      
      <form class="form-inline">
        <?php if (isset($_GET['page'])): ?>
          <input type="hidden" name="page" value="<?php echo (int)$_GET['page']; ?>">
        <?php endif; ?>
        
        <?php
        foreach ($_GET as $key => $value) {
          if ($key !== 'search' && $key !== 'page') {
            echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
          }
        }
        ?>
        
        <input class="form-control mr-sm-2" type="search" name="search" placeholder="Tìm tin nhắn..."
          aria-label="Search" value="<?php echo htmlspecialchars($search); ?>">
        <button class="btn btn-outline-success my-2 my-sm-0" type="submit">
          <i class="fas fa-search"></i> Tìm kiếm
        </button>
        
        <?php if (!empty($search)): ?>
          <a href="?<?php echo http_build_query(array_diff_key($_GET, ['search' => ''])); ?>" class="btn btn-outline-secondary ml-2">
            <i class="fas fa-times"></i> Xóa bộ lọc
          </a>
        <?php endif; ?>
      </form>
    </div>
    
    <div class="row">
      <?php if (isset($contacts) && !empty($contacts)): ?>
        <?php foreach ($contacts as $contact): ?>
          <div class="col-md-6">
            <div class="card contact-card">
              <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                  <i class="fas fa-user"></i> <?php echo htmlspecialchars($contact['name']); ?>
                </h5>
                <div>
                  <a href="#message-<?php echo $contact['contact_id']; ?>" class="btn btn-sm btn-light" data-toggle="collapse" title="Xem/Ẩn nội dung">
                    <i class="fas fa-eye"></i>
                  </a>
                  <a href="?action=delete&id=<?php echo $contact['contact_id']; ?>" class="btn btn-sm btn-danger" title="Xóa tin nhắn"
                    onclick="return confirm('Bạn có chắc chắn muốn xóa tin nhắn này?')">
                    <i class="fas fa-trash"></i>
                  </a>
                </div>
              </div>
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <h6 class="card-subtitle mb-2 text-muted">
                    <i class="fas fa-envelope"></i> 
                    <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>">
                      <?php echo htmlspecialchars($contact['email']); ?>
                    </a>
                  </h6>
                  <span class="contact-time">
                    <i class="fas fa-clock"></i>
                    <?php echo date('d/m/Y H:i', strtotime($contact['created_at'])); ?>
                  </span>
                </div>
                
                <h5 class="card-title mt-3">
                  <i class="fas fa-tag"></i> 
                  <?php echo htmlspecialchars($contact['topic']); ?>
                </h5>
                
                <div class="message-preview">
                  <?php echo htmlspecialchars(substr($contact['message'], 0, 100) . (strlen($contact['message']) > 100 ? '...' : '')); ?>
                </div>
                
                <div class="collapse mt-3" id="message-<?php echo $contact['contact_id']; ?>">
                  <div class="card card-body bg-light message-full">
                    <?php echo nl2br(htmlspecialchars($contact['message'])); ?>
                  </div>
                </div>
              </div>
              <div class="card-footer text-muted">
                Contact ID: <?php echo $contact['contact_id']; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12">
          <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Không tìm thấy tin nhắn nào.
          </div>
        </div>
      <?php endif; ?>
    </div>
    
    <!-- Phân trang -->
    <?php if ($totalPages > 1): ?>
      <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
          <?php
          $queryParams = $_GET;
          
          $queryParams['page'] = $currentPage - 1;
          $prevPageUrl = '?' . http_build_query($queryParams);
          
          $queryParams['page'] = $currentPage + 1;
          $nextPageUrl = '?' . http_build_query($queryParams);
          ?>
          
          <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo $currentPage <= 1 ? '#' : $prevPageUrl; ?>" aria-label="Previous">
              <span aria-hidden="true">&laquo;</span>
            </a>
          </li>
          
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php
            $queryParams['page'] = $i;
            $pageUrl = '?' . http_build_query($queryParams);
            ?>
            <li class="page-item <?php echo $i == $currentPage ? 'active' : ''; ?>">
              <a class="page-link" href="<?php echo $pageUrl; ?>"><?php echo $i; ?></a>
            </li>
          <?php endfor; ?>
          
          <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo $currentPage >= $totalPages ? '#' : $nextPageUrl; ?>" aria-label="Next">
              <span aria-hidden="true">&raquo;</span>
            </a>
          </li>
        </ul>
      </nav>
    <?php endif; ?>
    
  </div>
  
  <!-- Bootstrap JS và jQuery -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
</body>

</html>