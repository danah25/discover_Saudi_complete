<?php
session_start();
require_once 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate the password on the SERVER (not just in the browser) so the rules
  // can't be bypassed: at least 8 chars, upper, lower, number, and special char.
    if ($username === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } elseif (strlen($password) > 72) {
         // bcrypt only uses the first 72 bytes, so reject anything longer.
        $error = 'Password must not exceed 72 bytes.';
    } elseif (
        strlen($password) < 8 ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[a-z]/', $password) ||
        !preg_match('/[0-9]/', $password) ||
        !preg_match('/[^A-Za-z0-9]/', $password)
    ) {
        $error = 'Password must contain at least 8 characters, uppercase, lowercase, number, and special character.';
    } else {
        // Weak-password-storage fix: hash with bcrypt (PHP built-in). bcrypt adds a
        // unique salt automatically and is deliberately slow, resisting brute-force
       $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        try {
          // SQL-injection fix: prepared statement with bound parameters keeps the
          // username and hash separate from the SQL command.
            $sql = 'INSERT INTO users (username, password_hash, role)
                    VALUES (:username, :password_hash, :role)';

            $statement = $pdo->prepare($sql);
            $statement->execute([
                ':username' => $username,
                ':password_hash' => $passwordHash,
                ':role' => 'user'
            ]);

            $success = 'Account created successfully! You can now log in.';
        } catch (PDOException $e) {
            $error = 'Unable to create account. The username may already exist.';
        }
    }
}
?>


<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="إنشاء حساب في موقع اكتشف السعودية.">
  <title>إنشاء حساب | اكتشف السعودية</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body id="top">

<?php require __DIR__ . '/navigation.php'; ?>

  <main id="main-content">

    <section class="contact-banner">
      <div class="section-wrap">
        <h1>إنشاء حساب جديد</h1>
        <p>مرحبًا بك في موقع اكتشف السعودية.</p>
      </div>
    </section>

    <section class="contact-layout section-wrap">

      <div class="contact-introduction">
        <h2>إنشاء حساب</h2>
        <p>
          أدخل اسم المستخدم وكلمة المرور
          لإنشاء حساب جديد في الموقع.
        </p>
      </div>

      <form class="contact-form"
            action="register.php"
            method="post">

        <div class="contact-field">
          <label for="username">اسم المستخدم</label>
          <input
            type="text"
            id="username"
            name="username"
            autocomplete="username"
            maxlength="50"
            required>
        </div>

        <div class="contact-field">
          <label for="password">كلمة المرور</label>
          <input
            type="password"
            id="password"
            name="password"
            autocomplete="new-password"
            minlength="8"
            required>
        </div>

        <p>
          يجب أن تحتوي كلمة المرور على 8 أحرف على الأقل،
          وحرف كبير، وحرف صغير، ورقم، ورمز خاص.
        </p>

        <button class="button button-sand" type="submit">
          إنشاء الحساب
        </button>

        <?php if ($error !== ''): ?>
          <p class="contact-feedback">
            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
          </p>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
          <p class="contact-feedback">
            <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
          </p>
        <?php endif; ?>

      </form>
    </section>
  </main>

  <footer class="site-footer">
    <div class="footer-main section-wrap">
      <div>
        <a class="brand footer-brand" href="index.html">
          <img src="assets/palm.svg" alt="" width="44" height="54">
          <span>اكتشف السعودية<small>وجهات المملكة</small></span>
        </a>
        <p>دليلك للتعرّف على وجهات المملكة.</p>
      </div>

      <nav>
        <h2>روابط مفيدة</h2>
        <a href="index.html#why">لماذا السعودية؟</a>
        <a href="index.html#about">عن الموقع</a>
      </nav>

      <div class="footer-actions">
        <a class="back-top" href="#top">
          العودة إلى الأعلى <span>↑</span>
        </a>
      </div>
    </div>

    <div class="footer-bottom section-wrap">
      <p>اكتشف السعودية © ٢٠٢٦</p>
    </div>
  </footer>

  <script src="script.js"></script>
</body>
</html>
