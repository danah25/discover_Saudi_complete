<?php
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

if (!isset($_SESSION['user_id']) ||
    !in_array($_SESSION['role'] ?? '', ['user', 'admin'], true)) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

$sql = 'SELECT * FROM destinations ORDER BY destination_id';
$result = $pdo->query($sql);
$destinations = array();
while ($row = $result->fetch()) {
    $destinations[] = $row;
}
$totalDestinations = count($destinations);

$cardNames = array(
    1 => 'riyadh', 2 => 'diriyah', 3 => 'makkah', 4 => 'jeddah',
    5 => 'madinah', 6 => 'alula', 7 => 'dammam', 8 => 'khobar',
    9 => 'dhahran', 10 => 'fayfa', 11 => 'rijal-almaa', 12 => 'soudah'
);
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
  <meta name="description" content="استكشف <?php echo $totalDestinations; ?> وجهة سياحية في السعودية، من المواقع التاريخية إلى الجبال والشواطئ.">
  <title>وجهات السعودية | اكتشف السعودية</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body id="top" class="regions-page">

<?php require __DIR__ . '/navigation.php'; ?>

  <main id="main-content">
    <section class="regions-banner">
      <div class="section-wrap">
        <nav class="gallery-breadcrumb"><a href="index.html">الرئيسية</a> <span>/</span> <span>الوجهات</span></nav>
        <div class="regions-banner-inner">
          <div class="regions-banner-copy">
            <h1 id="regions-title">استكشف وجهات <span>السعودية</span></h1>
            <p>من المواقع التاريخية إلى الجبال والشواطئ، اكتشف تنوّع الوجهات السياحية في المملكة.</p>
          </div>
        </div>
      </div>
    </section>

    <section class="regions-gallery section-wrap" id="gallery">
      <div class="section-heading">
        <div><h2 id="gallery-title">اختر وجهتك <span>القادمة</span></h2></div>
        <p>تصفّح <?php echo $totalDestinations; ?> وجهة سياحية، أو اختر المنطقة التي تود استكشافها.</p>
      </div>
      <div class="gallery-toolbar js-only">
        <div class="gallery-filters">
          <button class="gallery-filter filter-selected" type="button" data-filter="all">الكل</button>
          <button class="gallery-filter" type="button" data-filter="central">المنطقة الوسطى</button>
          <button class="gallery-filter" type="button" data-filter="western">المنطقة الغربية</button>
          <button class="gallery-filter" type="button" data-filter="eastern">المنطقة الشرقية</button>
          <button class="gallery-filter" type="button" data-filter="southern">المنطقة الجنوبية</button>
        </div>
        <p id="gallery-count" class="gallery-count">الوجهات المعروضة: <?php echo $totalDestinations; ?> من <?php echo $totalDestinations; ?></p>
      </div>

      <div class="gallery-cards" id="gallery-cards">
        <?php foreach ($destinations as $row) { ?>
        <?php
        if (isset($cardNames[$row['destination_id']])) {
            $cardName = $cardNames[$row['destination_id']];
        } else {
            $cardName = 'id-' . $row['destination_id'];
        }
        $imageStyle = '';
        if ($row['destination_id'] == 1) {
            $imageStyle = 'width: 100%; height: auto; margin: 0;';
        } elseif ($row['destination_id'] == 3) {
            $imageStyle = 'width: 100%; height: 100%; margin: 0; object-fit: cover; object-position: center 85%;';
        } elseif ($row['destination_id'] == 4) {
            $imageStyle = 'width: 100%; height: auto; margin: 0;';
        } elseif ($row['destination_id'] == 5) {
            $imageStyle = 'width: 100%; height: 100%; margin: 0; object-fit: cover; object-position: center 47%;';
        }
        ?>
        <a href="destination.php?id=<?php echo $row['destination_id']; ?>" class="gallery-card gallery-<?php echo $cardName; ?>" data-region="<?php echo htmlspecialchars($row['geographical_category']); ?>" id="destination-<?php echo $cardName; ?>">
          <div class="gallery-photo"><img src="<?php echo htmlspecialchars($row['image_path']); ?>"<?php if ($imageStyle != '') { ?> style="<?php echo $imageStyle; ?>"<?php } ?> alt="<?php echo htmlspecialchars($row['image_alt']); ?>"></div>
          <div class="gallery-card-copy">
            <div class="card-meta"><span><?php echo htmlspecialchars($row['administrative_region']); ?></span></div>
            <h3 id="title-<?php echo $cardName; ?>"><?php echo htmlspecialchars($row['destination_name']); ?></h3>
            <p class="gallery-description"><?php echo htmlspecialchars($row['short_description']); ?></p>
          </div>
        </a>
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
