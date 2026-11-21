<?php
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

if (!isset($_SESSION['user_id']) ||
    !in_array($_SESSION['role'] ?? '', ['user', 'admin'], true)) {
    header('Location: login.php');
    exit;
}

$destination = false;
$message = 'معرّف الوجهة مفقود أو غير صالح.';

$destinationLabels = array(
    1 => 'التاريخ والحداثة',
    2 => 'التراث والثقافة',
    3 => 'السياحة الدينية',
    4 => 'البحر والتراث',
    5 => 'السياحة الدينية',
    6 => 'التاريخ والطبيعة',
    7 => 'البحر والترفيه',
    8 => 'البحر والترفيه',
    9 => 'الثقافة والمعرفة',
    10 => 'الجبال والطبيعة',
    11 => 'التراث والثقافة',
    12 => 'الطبيعة والجبال'
);

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    if (!empty($id)) {
        require_once 'db.php';

        $sql = 'SELECT * FROM destinations WHERE destination_id = :id';
        $statement = $pdo->prepare($sql);
        $statement->bindValue(':id', $id);
        $statement->execute();
        $destination = $statement->fetch();

        if (!$destination || $id !== ('' . $destination['destination_id'])) {
            $destination = false;
            $message = 'الوجهة غير موجودة أو معرّفها غير صالح.';
        }
    }
}

if ($destination) {
    if (isset($destinationLabels[$destination['destination_id']])) {
        $destinationLabel = $destinationLabels[$destination['destination_id']];
    } else {
        $destinationLabel = $destination['administrative_region'];
        if ($destinationLabel === '') {
            $destinationLabel = 'وجهة سياحية';
        }
    }

    $description = $destination['short_description'];
    if (!empty($destination['detailed_description'])) {
        $description = $destination['detailed_description'];
    }

    $location = $destination['administrative_region'];
    if (!empty($destination['location'])) {
        $location = $destination['location'];
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
  <meta name="description" content="<?php if ($destination) { ?><?php echo htmlspecialchars($destination['short_description']); ?><?php } else { ?>تفاصيل الوجهة<?php } ?>">
  <title><?php if ($destination) { ?><?php echo htmlspecialchars($destination['destination_name']); ?><?php } else { ?>تفاصيل الوجهة<?php } ?> | اكتشف السعودية</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="destination.css">
</head>
<body id="top" class="destination-page">

<?php require __DIR__ . '/navigation.php'; ?>

  <main id="main-content" class="destination-main section-wrap">
    <?php if ($destination) { ?>
    <article class="destination-details">
      <figure class="destination-details-image">
        <img src="<?php echo htmlspecialchars($destination['image_path']); ?>" alt="<?php echo htmlspecialchars($destination['image_alt']); ?>">
      </figure>

      <div class="destination-details-copy">
        <header>
          <p class="eyebrow"><?php echo htmlspecialchars($destinationLabel); ?></p>
          <h1 id="destination-title"><?php echo htmlspecialchars($destination['destination_name']); ?></h1>
          <p class="destination-location">الموقع: <?php echo htmlspecialchars($location); ?></p>
        </header>

        <p class="destination-summary"><?php echo htmlspecialchars($description); ?></p>

        <?php if (!empty($destination['highlights'])) { ?>
        <section class="destination-highlights">
          <h2 id="highlights-title">أبرز المعالم</h2>
          <ul>
            <?php
            // Escape the stored text to prevent XSS, then restore plain list item tags.
            $escapedHighlights = htmlspecialchars($destination['highlights'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            echo strtr($escapedHighlights, ['&lt;li&gt;' => '<li>', '&lt;/li&gt;' => '</li>']);
            ?>
          </ul>
        </section>
        <?php } ?>

        <a class="destination-back" href="regions.php"><span>→</span> العودة إلى الوجهات</a>
      </div>
    </article>
    <?php } else { ?>
    <p><?php echo htmlspecialchars($message); ?></p>
    <?php } ?>
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
