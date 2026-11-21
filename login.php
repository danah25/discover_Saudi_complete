<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if (($_SESSION['role'] ?? '') === 'admin') {
        header('Location: admin.php');
    } else {
        header('Location: dashboard.php');
    }
    exit;
}

$error = '';
$loginUsername = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $loginUsername = trim($_POST['username'] ?? '');
    $loginPassword = $_POST['password'] ?? '';

    if ($loginUsername === '' || $loginPassword === '') {
        $error = 'يرجى إدخال اسم المستخدم وكلمة المرور.';
    } else {
        try {
            require_once 'db.php';

            $sql = 'SELECT id, username, password_hash, role
                    FROM users
                    WHERE username = :username';

            // Bind the username so input stays separate from the SQL.
            $statement = $pdo->prepare($sql);
            $statement->execute([
                ':username' => $loginUsername
            ]);

            $user = $statement->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify(
                $loginPassword,
                $user['password_hash']
            )) {
                // Give the signed-in user a new ID to prevent session fixation.
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];

                    header('Location: admin.php');
                } else {
                    header('Location: dashboard.php');
                }

                exit;
            } else {
                $error = 'اسم المستخدم أو كلمة المرور غير صحيحة.';
            }

        } catch (PDOException $e) {
            $error = 'تعذر تسجيل الدخول. يرجى المحاولة مرة أخرى لاحقًا.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="تسجيل دخول المسؤول في موقع اكتشف السعودية.">
  <title>تسجيل دخول المسؤول | اكتشف السعودية</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body id="top">

<?php require __DIR__ . '/navigation.php'; ?>

  <main id="main-content">
    <section class="contact-banner">
      <div class="section-wrap">
        <h1 id="login-title">تسجيل دخول المسؤول</h1>
        <p>مرحبًا بك في إدارة موقع اكتشف السعودية.</p>
      </div>
    </section>
    <section class="contact-layout section-wrap">
      <div class="contact-introduction">
        <h2 id="login-form-title">دخول المسؤول</h2>
        <p>أدخل اسم المستخدم وكلمة المرور للوصول إلى لوحة الإدارة.</p>
      </div>
      <form class="contact-form" action="login.php" method="post">
        <div class="contact-field">
          <label for="username">اسم المستخدم</label>
          <input type="text" id="username" name="username" autocomplete="username" required value="<?php echo htmlspecialchars($loginUsername); ?>">
        </div>
        <div class="contact-field">
          <label for="password">كلمة المرور</label>
          <input type="password" id="password" name="password" autocomplete="current-password" required>
        </div>
        <button class="button button-sand" type="submit">تسجيل الدخول</button>
        <p class="contact-feedback"><?php echo htmlspecialchars($error); ?></p>
      </form>
    </section>
  </main>

  <footer class="site-footer">
    <div class="footer-main section-wrap">
      <div><a class="brand footer-brand" href="index.html"><img src="assets/palm.svg" alt="" width="44" height="54"><span>اكتشف السعودية<small>وجهات المملكة</small></span></a><p>دليلك للتعرّف على وجهات المملكة.</p></div>
      <nav><h2>روابط مفيدة</h2><a href="index.html#why">لماذا السعودية؟</a><a href="index.html#about">عن الموقع</a></nav>
      <div class="footer-actions"><a class="back-top" href="#top">العودة إلى الأعلى <span>↑</span></a></div>
    </div>
    <div class="footer-bottom section-wrap"><p>اكتشف السعودية © ٢٠٢٦</p></div>
  </footer>
  <script src="script.js"></script>
</body>
</html>
