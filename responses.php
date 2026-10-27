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
  <title>Responses | Slam Book</title>
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
        <a class="sidebar-link" href="memoryStats.php">Memory Stats</a>
        <a class="sidebar-link active" href="responses.php">Responses</a>
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
          <h1>Response Reader</h1>
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
        <section class="panel" id="responseSection" aria-hidden="false">
          <div class="panel-head">
            <h2>Response Cards</h2>
            <p>Each writer appears as a card. Click a card to open full details in modal.</p>
          </div>

          <div class="admin-controls">
            <label>
              Search by name or text
              <input id="searchInput" type="search" placeholder="Type to search entries..." />
            </label>

            <label>
              Filter role
              <select id="filterRole">
                <option value="All">All</option>
                <option value="Friend">Friend</option>
                <option value="Junior">Junior</option>
                <option value="Classmate">Classmate</option>
                <option value="Colleague">Colleague</option>
                <option value="Faculty">Faculty</option>
              </select>
            </label>

          </div>

          <div class="stats" id="statsBar"></div>
          <div id="entriesList" class="entries-list"></div>
          <div class="paginationBar">
            <button id="paginationPrev" class="ghost" type="button">Previous</button>
            <span id="paginationInfo" class="paginationInfo">Page 1 of 1</span>
            <button id="paginationNext" class="ghost" type="button">Next</button>
          </div>
        </section>
      </main>
    </div>
  </div>

  <template id="entryTemplate">
    <article class="writer-card" tabindex="0" role="button" aria-expanded="false">
      <div class="writer-card-front">
        <img class="writer-card-image" alt="Writer photo" />
        <div class="writer-card-meta">
          <h3 class="writer-card-name"></h3>
          <p class="writer-card-sub"></p>
          <time class="entry-time"></time>
        </div>
      </div>
    </article>
  </template>

  <div id="writerModal" class="modal-overlay hidden" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="modalWriterName">
      <button id="modalCloseBtn" class="modal-close" type="button" aria-label="Close">x</button>

      <div class="modal-header">
        <img id="modalWriterImage" class="modal-writer-image" alt="Writer photo" />
        <div>
          <h3 id="modalWriterName"></h3>
          <p id="modalWriterSub" class="writer-card-sub"></p>
          <time id="modalCreatedAt" class="entry-time"></time>
        </div>
      </div>

      <div class="modal-body">
        <p class="entry-line"><strong>Date of Birth:</strong> <span id="modalDateOfBirth"></span></p>
        <p class="entry-line"><strong>Nickname:</strong> <span id="modalNickname"></span></p>
        <p class="entry-line"><strong>First Impression:</strong> <span id="modalImpression"></span></p>
        <p class="entry-block"><strong>Favorite Memory:</strong> <span id="modalMemory"></span></p>
        <p class="entry-block"><strong>Learned:</strong> <span id="modalLearning"></span></p>
        <p class="entry-block"><strong>Future Message:</strong> <span id="modalFutureMessage"></span></p>
        <p class="entry-line"><strong>Batch:</strong> <span id="modalBatch"></span></p>
        <p class="entry-line"><strong>Contact:</strong> <span id="modalContact"></span></p>
      </div>

    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  <script src="script.js"></script>
</body>
</html>
