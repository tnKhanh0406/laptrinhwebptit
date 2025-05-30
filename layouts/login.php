<?php

session_start();

require_once '../config.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';

  if (empty($username) || empty($password)) {
    $error_message = 'Vui lòng nhập đầy đủ thông tin đăng nhập';
  } else {
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
      $user = $stmt->fetch(PDO::FETCH_ASSOC);
      if ($password === $user['password']) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;

        if ($user['role'] == 'admin') {
          header('Location: ../layouts/admin.php');
        } else {
          header('Location: ../index.php');
        }
        exit;
      } else {
        $error_message = 'Mật khẩu không chính xác';
      }
    } else {
      $error_message = 'Tài khoản không tồn tại';
    }
  }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đăng nhập - Hội thảo khoa học</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    .login-container {
      max-width: 450px;
      margin: 100px auto;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
      background-color: #fff;
    }

    .login-container h2 {
      margin-bottom: 30px;
      text-align: center;
      color: #1a2a6c;
    }

    .btn-login {
      background-color: #1a2a6c;
      color: white;
      width: 100%;
      font-weight: bold;
      padding: 10px;
      border: none;
    }

    .btn-login:hover {
      background-color: #0f1845;
      color: white;
    }

    .alert {
      margin-bottom: 20px;
    }

    .form-group {
      margin-bottom: 20px;
    }
  </style>
</head>

<body>

  <div class="container">
    <div class="login-container">
      <h2>Đăng nhập</h2>

      <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
      <?php endif; ?>

      <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
      <?php endif; ?>

      <form method="post" action="">
        <div class="form-group">
          <label for="username">Tên đăng nhập</label>
          <input type="text" class="form-control" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
        </div>

        <div class="form-group">
          <label for="password">Mật khẩu</label>
          <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <div class="form-group form-check">
          <input type="checkbox" class="form-check-input" id="remember" name="remember">
          <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
        </div>

        <button type="submit" class="btn btn-login">Đăng nhập</button>

        <div class="text-center mt-3">
          <p>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
          <p><a href="forgot_password.php">Quên mật khẩu?</a></p>
        </div>
      </form>
    </div>
  </div>
  <!-- Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
</body>

</html>