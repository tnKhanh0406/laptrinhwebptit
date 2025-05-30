<?php
session_start();

require_once '../config.php';

$message = '';
$messageType = '';
$user = null;

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header('Location: admin_users.php');
  exit;
}

$userId = (int)$_GET['id'];

try {
  $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
  $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
  $stmt->execute();

  $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $message = "Lỗi khi lấy thông tin người dùng: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fullName = trim($_POST['full_name'] ?? '');
  $role = $_POST['role'] ?? 'user';
  $password = $_POST['password'] ?? '';

  if (empty($password)) {
    $stmt = $conn->prepare("
          UPDATE users 
          SET full_name = :full_name, 
              role = :role 
          WHERE user_id = :user_id
        ");
  } else {
    $stmt = $conn->prepare("
          UPDATE users 
          SET full_name = :full_name, 
              role = :role, 
              password = :password 
          WHERE user_id = :user_id
        ");
    $stmt->bindParam(':password', $password);
  }

  $stmt->bindParam(':full_name', $fullName);
  $stmt->bindParam(':role', $role);
  $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

  $stmt->execute();

  $message = "Thông tin người dùng đã được cập nhật thành công!";
  $messageType = "success";

  $user['full_name'] = $fullName;
  $user['role'] = $role;
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
  <title>Chỉnh sửa Người Dùng</title>
</head>

<body>
  <?php include_once './admin_sidebar.php'; ?>

  <div class="content">
    <h1 class="mb-4">Chỉnh sửa Người Dùng</h1>

    <?php if (!empty($message)) : ?>
      <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header bg-primary text-white">
        <i class="fas fa-user-edit"></i> Chỉnh sửa thông tin người dùng: <?php echo htmlspecialchars($user['username']); ?>
      </div>
      <div class="card-body">
        <form method="POST" action="">
          <div class="form-group">
            <label for="username">
              <i class="fas fa-user"></i> Username
            </label>
            <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly disabled>
          </div>

          <div class="form-group">
            <label for="password">
              <i class="fas fa-lock"></i> Mật khẩu mới
            </label>
            <input type="password" class="form-control" id="password" name="password">
          </div>

          <div class="form-group">
            <label for="full_name">
              <i class="fas fa-address-card"></i> Họ và tên
            </label>
            <input type="text" class="form-control" id="full_name" name="full_name"
              value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
          </div>

          <div class="form-group">
            <label for="role">
              <i class="fas fa-user-tag"></i> Vai trò <span class="text-danger">*</span>
            </label>
            <select class="form-control" id="role" name="role" required>
              <option value="user" <?php echo ($user['role'] === 'user') ? 'selected' : ''; ?>>
                Người dùng
              </option>
              <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>
                Quản trị viên
              </option>
            </select>
          </div>

          <div class="form-group">
            <label>
              <i class="fas fa-calendar-alt"></i> Ngày tạo
            </label>
            <input type="text" class="form-control" value="<?php echo date('d/m/Y H:i:s', strtotime($user['created_at'])); ?>" readonly disabled>
          </div>

          <div class="form-group">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i> Lưu thay đổi
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS và jQuery -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
</body>

</html>