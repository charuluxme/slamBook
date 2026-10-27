<?php
session_start();
require_once 'includes/auth.php';
$csrfToken = getCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrfToken" content="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>" />
  <title>Slam Book Memories</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Merriweather:wght@700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <div class="page-shell">
    <header class="topbar">
      <div>
        <p class="eyebrow">Memories Vault</p>
        <h1>Unforgettable Slam Book</h1>
      </div>
      <div class="top-actions">
        <a class="ghost link-btn" href="adminLogin.php">Admin Login</a>
      </div>
    </header>

    <main>
      <section class="panel">
        <div class="panel-head">
          <h2>Write a Memory</h2>
          <p>Friends and juniors can leave meaningful pages here.</p>
        </div>

        <form id="slamForm" class="slam-form" novalidate>
          <div class="grid two">
            <label>
              Your Name
              <input type="text" name="name" placeholder="Ex: Aisha Khan" required />
            </label>
            <label>
              You Are
              <select name="relation" required>
                <option value="" selected disabled>Select one</option>
                <option value="Friend">Friend</option>
                <option value="Junior">Junior</option>
                <option value="Classmate">Classmate</option>
                <option value="Colleague">Colleague</option>
                <option value="Faculty">Faculty</option>
              </select>
            </label>
          </div>

          <div class="grid two">
            <label>
              Date of Birth
              <input type="date" name="dateOfBirth" required />
            </label>
            <label>
              Upload Photo
              <input type="file" name="photo" accept="image/*" required />
            </label>
          </div>

          <div class="grid two">
            <label>
              Nickname for Me
              <input type="text" name="nickname" placeholder="Ex: Captain" required />
            </label>
            <label>
              First Impression
              <input type="text" name="impression" placeholder="Ex: Calm and focused" required />
            </label>
          </div>

          <label>
            Favorite Memory With Me
            <textarea name="memory" rows="4" placeholder="Share one unforgettable moment..." required></textarea>
          </label>

          <label>
            One Thing You Learned From Me
            <textarea name="learning" rows="3" placeholder="Ex: To stay consistent even on difficult days" required></textarea>
          </label>

          <label>
            Message for the Future
            <textarea name="futureMessage" rows="3" placeholder="Write a heartfelt note..." required></textarea>
          </label>

          <div class="grid two">
            <label>
              Contact
              <input type="text" name="contact" placeholder="Ex: @username or number" required />
            </label>
            <label>
              Year / Batch
              <input type="text" name="batch" placeholder="Ex: 2026" required />
            </label>
          </div>

          <label class="check">
            <input type="checkbox" name="consent" required />
            <span>I confirm this message can be saved in your slam book.</span>
          </label>

          <div class="form-actions">
            <button type="submit" class="primary">Save Memory</button>
            <button type="reset" class="ghost">Reset</button>
          </div>

          <p id="formFeedback" class="feedback" aria-live="polite"></p>
        </form>
      </section>
    </main>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  <script src="script.js"></script>
</body>
</html>
