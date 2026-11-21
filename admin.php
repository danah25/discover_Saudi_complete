<?php
session_start();

// Prevent the browser from caching this private admin page, so it cannot
// be restored from cache (e.g. via the Back button) after logout.
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

// Access control: the visitor must be logged in...
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check the role on the server even when someone opens the URL directly.
if (($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

$destinations = array();
$categoryNames = array(
    'central' => 'الوسطى',
    'western' => 'الغربية',
    'eastern' => 'الشرقية',
    'southern' => 'الجنوبية'
);
$error = '';
$deleteFeedback = '';
if (isset($_SESSION['delete_feedback'])) {
    $deleteFeedback = $_SESSION['delete_feedback'];
    unset($_SESSION['delete_feedback']);
}

try {
  // Load the destinations to display. The query uses fixed SQL with no user
  // input, and all output below is escaped to prevent XSS.
    require_once 'db.php';
    $sql = 'SELECT destination_id, destination_name, administrative_region,
                   geographical_category FROM destinations ORDER BY destination_id';
    $result = $pdo->query($sql);
    while ($destination = $result->fetch()) {
        $destinations[] = $destination;
    }
} catch (PDOException $e) {
    $error = 'تعذر تحميل الوجهات. يرجى المحاولة مرة أخرى لاحقًا.';
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
  <meta name="description" content="لوحة الإدارة في موقع اكتشف السعودية.">
  <title>لوحة الإدارة | اكتشف السعودية</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    .admin-destinations { margin-bottom: 32px; }
    .admin-table-wrap { overflow-x: auto; margin-top: 16px; }
    .admin-destinations table {
      width: 100%;
      border-collapse: collapse;
      background: #fcf8f0;
      text-align: right;
    }
    .admin-destinations th, .admin-destinations td {
      padding: 12px 16px;
      border: 1px solid #e3d8c5;
    }
    .admin-destinations th { background: #123c31; color: #fff6e6; }
  </style>
</head>
<body id="top">

<?php require __DIR__ . '/navigation.php'; ?>

  <main id="main-content">
    <section class="contact-banner">
      <div class="section-wrap">
        <h1 id="admin-title">لوحة الإدارة</h1>
        <p>مرحبًا، <?php echo htmlspecialchars($_SESSION['admin_username']); ?>.</p>
      </div>
    </section>
    <section class="contact-layout section-wrap">
      <div class="contact-introduction">
        <h2 id="welcome-title">أهلًا بك في لوحة الإدارة</h2>
        <p>تم تسجيل دخولك بنجاح إلى موقع اكتشف السعودية.</p>
      </div>
    </section>
    <section class="admin-destinations section-wrap">
      <h2 id="destinations-title">الوجهات المسجلة</h2>
      <a class="button button-sand" href="add_destination.php">إضافة وجهة جديدة</a>
      <?php if ($deleteFeedback !== '') { ?>
      <p><?php echo htmlspecialchars($deleteFeedback); ?></p>
      <?php } ?>
      <?php if ($error !== '') { ?>
      <p><?php echo htmlspecialchars($error); ?></p>
      <?php } elseif (empty($destinations)) { ?>
      <p>لا توجد وجهات مسجلة حاليًا.</p>
      <?php } else { ?>
      <div class="admin-table-wrap">
        <table>
          <thead>
            <tr>
              <th scope="col">رقم الوجهة</th>
              <th scope="col">اسم الوجهة</th>
              <th scope="col">المنطقة الإدارية</th>
              <th scope="col">التصنيف الجغرافي</th>
              <th scope="col">تعديل</th>
              <th scope="col">حذف</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($destinations as $destination) { ?>
            <tr>
              <td><?php echo htmlspecialchars($destination['destination_id']); ?></td>
              <td><?php echo htmlspecialchars($destination['destination_name']); ?></td>
              <td><?php echo htmlspecialchars($destination['administrative_region']); ?></td>
              <td><?php echo htmlspecialchars($categoryNames[$destination['geographical_category']]); ?></td>
              <td><a href="edit_destination.php?id=<?php echo $destination['destination_id']; ?>">تعديل</a></td>
              <td>
                <form action="delete_destination.php" method="get">
                  <input type="hidden" name="id" value="<?php echo $destination['destination_id']; ?>">
                  <button class="button button-sand" type="submit">حذف</button>
                </form>
              </td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
      <?php } ?>
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
