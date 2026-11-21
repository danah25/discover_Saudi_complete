<?php
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
if (!isset($_SESSION['user_id'], $_SESSION['admin_id']) ||
    ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    die('');
}

$error = '';
$destination = false;
$id = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id'])) {
        $id = $_POST['id'];
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
    }
}

if (empty($id)) {
    $error = 'معرّف الوجهة مفقود أو غير صالح.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    (!isset($_POST['confirm_delete']) || $_POST['confirm_delete'] !== 'yes')) {
    $error = 'لم يتم الحذف. يجب تأكيد رغبتك في حذف الوجهة.';
} else {
    try {
        require_once 'db.php';
        $statement = $pdo->prepare('SELECT destination_id, destination_name FROM destinations WHERE destination_id = :id');
        $statement->bindValue(':id', $id);
        $statement->execute();
        $destination = $statement->fetch();

        if (!$destination || $id !== ('' . $destination['destination_id'])) {
            $destination = false;
            $error = 'الوجهة غير موجودة أو معرّفها غير صالح.';
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $statement = $pdo->prepare('DELETE FROM destinations WHERE destination_id = :id');
            $statement->bindValue(':id', $id);
            $statement->execute();

            $_SESSION['delete_feedback'] = 'تم حذف الوجهة المحددة بنجاح.';
            header('Location: admin.php');
            die('');
        }
    } catch (PDOException $e) {
        $error = 'تعذر إكمال الطلب. يرجى المحاولة مرة أخرى لاحقًا.';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <script>
    window.addEventListener('pageshow', function (event) {
      if (event.persisted) {
        window.location.reload();
      }
    });
  </script>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="تأكيد حذف الوجهة في موقع اكتشف السعودية.">
  <title>تأكيد حذف الوجهة | اكتشف السعودية</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body id="top">

<?php require __DIR__ . '/navigation.php'; ?>

  <main id="main-content">
    <section class="contact-banner">
      <div class="section-wrap">
        <h1 id="delete-title">تأكيد حذف الوجهة</h1>
        <p>راجع الوجهة المحددة قبل تأكيد الحذف.</p>
      </div>
    </section>
    <section class="contact-layout section-wrap">
      <div class="contact-introduction">
        <h2 id="confirmation-title">حذف الوجهة</h2>
        <p>سيؤدي التأكيد إلى حذف سجل الوجهة نهائيًا من قاعدة البيانات.</p>
        <a class="button" href="admin.php">إلغاء والعودة إلى لوحة الإدارة</a>
      </div>
      <div class="contact-form">
        <?php if ($error !== '') { ?>
        <p><?php echo htmlspecialchars($error); ?></p>
        <?php } elseif ($destination) { ?>
        <p>هل تريد حذف الوجهة «<?php echo htmlspecialchars($destination['destination_name']); ?>» (رقم <?php echo $id; ?>)؟</p>
        <form action="delete_destination.php" method="post">
          <input type="hidden" name="id" value="<?php echo $id; ?>">
          <p>
            <label><input type="checkbox" name="confirm_delete" value="yes" required> أؤكد رغبتي في حذف هذه الوجهة نهائيًا.</label>
          </p>
          <button class="button button-sand" type="submit">تأكيد الحذف</button>
        </form>
        <?php } ?>
      </div>
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
