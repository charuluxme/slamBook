<?php
session_start();
require_once 'includes/auth.php';
$csrfToken = getCsrfToken();
if (isset($_SESSION['adminId']) && isset($_SESSION['adminUserName'])) {
  header('Location: adminDashboard.php');
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrfToken" content="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>" />
  <title>Admin Login | Slam Book</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Merriweather:wght@700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <div class="page-shell">
    <header class="topbar">
      <div>
        <p class="eyebrow">Admin Access</p>
        <h1>Slam Book Login</h1>
      </div>
      <div class="top-actions">
        <a class="ghost link-btn" href="index.php">Writer Page</a>
      </div>
    </header>

    <main>
      <section class="panel auth-panel">
        <div class="panel-head">
          <h2>Sign In as Admin</h2>
          <p>Only admin can open the memory dashboard.</p>
        </div>

        <form id="adminLoginForm" class="slam-form" novalidate>
          <label>
            Username
            <input type="text" name="username" placeholder="Enter admin username" required />
          </label>

          <label>
            Password
            <input type="password" name="password" placeholder="Enter password" required />
          </label>

          <div class="form-actions">
            <button type="submit" class="primary">Login</button>
            <button type="reset" class="ghost">Reset</button>
          </div>

          <p id="loginFeedback" class="feedback" aria-live="polite"></p>
          <p class="note">Default credentials: admin / slam@2026</p>
        </form>
      </section>
    </main>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  <script src="script.js"></script>
</body>
</html>
