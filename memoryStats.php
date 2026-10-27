<?php
session_start();
require_once 'includes/auth.php';
$csrfToken = getCsrfToken();
if (!isset($_SESSION['adminId']) || !isset($_SESSION['adminUserName'])) {
  header('Location: adminLogin.php');
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrfToken" content="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>" />
  <title>Memory Stats | Slam Book</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Merriweather:wght@700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <div class="dashboard-shell">
    <aside id="adminSidebar" class="dashboard-sidebar">
      <div class="sidebar-brand">
        <p class="eyebrow">Slam Book</p>
        <h2>Admin Desk</h2>
      </div>

      <nav class="sidebar-nav" aria-label="Admin navigation">
        <a class="sidebar-link" href="adminDashboard.php">Dashboard</a>
        <a class="sidebar-link active" href="memoryStats.php">Memory Stats</a>
        <a class="sidebar-link" href="responses.php">Responses</a>
        <a class="sidebar-link" href="index.php">Writer Form</a>
      </nav>

      <div class="sidebar-note">
        <p>Use this panel to review every memory and manage entries clearly.</p>
      </div>
    </aside>

    <button id="sidebarBackdrop" class="dashboard-sidebar-backdrop" type="button" aria-label="Close menu"></button>

    <div class="dashboard-main">
      <header class="dashboard-header">
        <button id="sidebarToggleBtn" class="sidebar-toggle-btn" type="button" aria-controls="adminSidebar" aria-expanded="false">Menu</button>
        <div>
          <p class="eyebrow">Admin Dashboard</p>
          <h1>Memory Stats</h1>
        </div>

        <div class="dashboard-header-actions">
          <a class="ghost link-btn" href="index.php">Writer Page</a>

          <div class="user-menu-wrap">
            <button id="userMenuBtn" class="user-menu-btn" type="button" aria-expanded="false" aria-controls="userDropdown">
              <span class="user-avatar"><?php echo strtoupper(substr($_SESSION['adminUserName'], 0, 1)); ?></span>
              <span class="user-meta">
                <strong><?php echo htmlspecialchars($_SESSION['adminUserName']); ?></strong>
                <small>Administrator</small>
              </span>
            </button>

            <div id="userDropdown" class="user-dropdown hidden">
              <p class="user-dropdown-name">Signed in as <?php echo htmlspecialchars($_SESSION['adminUserName']); ?></p>
              <button id="logoutBtn" class="ghost user-dropdown-btn" type="button">Logout</button>
            </div>
          </div>
        </div>
      </header>

      <main class="dashboard-content">
        <!-- <section class="welcomeCard">
          <div>
            <p class="eyebrow">Welcome Back</p>
            <h2 id="welcomeAdminName">Hello, Admin</h2>
            <p class="welcomeText">Review visual insights from your slam memories and track participation trends.</p>
          </div>
          <div class="welcomeMeta">
            <span class="statLabel">Total Memories</span>
            <strong id="welcomeTotalCount">0</strong>
          </div>
        </section> -->

        <section class="dashboardCountGrid" id="roleCountSection" aria-label="Role and memory counters">
          <article class="countCard"><p>Total</p><h3 id="countTotal">0</h3></article>
          <article class="countCard"><p>Friend</p><h3 id="countFriend">0</h3></article>
          <article class="countCard"><p>Junior</p><h3 id="countJunior">0</h3></article>
          <article class="countCard"><p>Classmate</p><h3 id="countClassmate">0</h3></article>
          <article class="countCard"><p>Colleague</p><h3 id="countColleague">0</h3></article>
          <article class="countCard"><p>Faculty</p><h3 id="countFaculty">0</h3></article>
          <article class="countCard"><p>This Month</p><h3 id="countThisMonth">0</h3></article>
        </section>

        <section class="panel" id="memoryStatsSection" aria-hidden="false">
          <div class="panel-head">
            <h2>Memory Stats Visualizations</h2>
            <p>Visual overview of role distribution and monthly memory activity.</p>
          </div>

          <div class="stats" id="statsBar"></div>

          <div class="memoryStatsGrid">
            <article class="chartCard">
              <h3>Role Distribution</h3>
              <div class="chartCanvasWrap">
                <canvas id="roleDistributionChart"></canvas>
              </div>
            </article>
            <article class="chartCard">
              <h3>Monthly Trend (6 Months)</h3>
              <div class="chartCanvasWrap">
                <canvas id="monthlyTrendChart"></canvas>
              </div>
            </article>
          </div>
        </section>
      </main>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="script.js"></script>
</body>
</html>
