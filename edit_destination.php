<?php
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
if (!isset($_SESSION['user_id'], $_SESSION['admin_id']) ||
    ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    die('');
}

$errors = array();
$success = '';
$values = array(
    'destination_name' => '',
    'geographical_category' => '',
    'administrative_region' => '',
    'short_description' => '',
    'image_path' => '',
    'image_alt' => '',
    'detailed_description' => '',
    'location' => '',
    'highlights' => ''
 );
$fields = array(
    'destination_name',
    'geographical_category',
    'administrative_region',
    'short_description',
    'image_path',
    'image_alt',
    'detailed_description',
    'location',
    'highlights'
);
$categories = array(
    'central' => 'الوسطى',
    'western' => 'الغربية',
    'eastern' => 'الشرقية',
    'southern' => 'الجنوبية'
);

$destination = false;
$id = '';
if (isset($_GET['id'])) {
    $id = $_GET['id'];
}
if (empty($id)) {
    $errors[] = 'معرّف الوجهة مفقود أو غير صالح.';
} else {
    try {
        require_once 'db.php';
        $statement = $pdo->prepare('SELECT * FROM destinations WHERE destination_id = :id');
        $statement->bindValue(':id', $id);
        $statement->execute();
        $destination = $statement->fetch();
        if (!$destination || $id !== ('' . $destination['destination_id'])) {
            $destination = false;
            $errors[] = 'الوجهة غير موجودة أو معرّفها غير صالح.';
        } else {
            foreach ($fields as $field) {
                if ($destination[$field] !== null) {
                    $values[$field] = $destination[$field];
                }
            }
            if (isset($_SESSION['edited_destination_id']) && $_SESSION['edited_destination_id'] === $id) {
                $success = 'تم حفظ تعديلات الوجهة بنجاح.';
                unset($_SESSION['edited_destination_id']);
            }
        }
    } catch (PDOException $e) {
        $errors[] = 'تعذر تحميل الوجهة. يرجى المحاولة مرة أخرى لاحقًا.';
    }
}

if ($destination && $_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($fields as $field) {
        $values[$field] = '';
        if (isset($_POST[$field])) {
            $submittedValue = filter_var($_POST[$field]);
            if ($submittedValue !== false) {
                $values[$field] = $submittedValue;
            }
        }
    }

    if ($values['destination_name'] === '') {
        $errors[] = 'يرجى إدخال اسم الوجهة.';
    }
    if ($values['geographical_category'] === '') {
        $errors[] = 'يرجى إدخال التصنيف الجغرافي.';
    }
    if ($values['administrative_region'] === '') {
        $errors[] = 'يرجى إدخال المنطقة الإدارية.';
    }
    if ($values['short_description'] === '') {
        $errors[] = 'يرجى إدخال الوصف المختصر.';
    }
    if ($values['image_path'] === '') {
        $errors[] = 'يرجى إدخال مسار الصورة.';
    }
    if ($values['image_alt'] === '') {
        $errors[] = 'يرجى إدخال النص البديل للصورة.';
    }

    if ($values['geographical_category'] !== '' && !isset($categories[$values['geographical_category']])) {
        $errors[] = 'يرجى اختيار تصنيف جغرافي صحيح.';
    }

    if (empty($errors)) {
        try {
            $sql = 'UPDATE destinations SET
                destination_name = :destination_name,
                geographical_category = :geographical_category,
                administrative_region = :administrative_region,
                short_description = :short_description,
                image_path = :image_path,
                image_alt = :image_alt,
                detailed_description = :detailed_description,
                location = :location,
                highlights = :highlights
                WHERE destination_id = :id';
            $statement = $pdo->prepare($sql);
            $statement->bindValue(':destination_name', $values['destination_name']);
            $statement->bindValue(':geographical_category', $values['geographical_category']);
            $statement->bindValue(':administrative_region', $values['administrative_region']);
            $statement->bindValue(':short_description', $values['short_description']);
            $statement->bindValue(':image_path', $values['image_path']);
            $statement->bindValue(':image_alt', $values['image_alt']);
            $statement->bindValue(':detailed_description', $values['detailed_description']);
            $statement->bindValue(':location', $values['location']);
            $statement->bindValue(':highlights', $values['highlights']);
            $statement->bindValue(':id', $id);
            $statement->execute();
            $_SESSION['edited_destination_id'] = $id;
            header('Location: edit_destination.php?id=' . $id);
            die('');
        } catch (PDOException $e) {
            $errors[] = 'تعذر حفظ تعديلات الوجهة. يرجى المحاولة مرة أخرى لاحقًا.';
        }
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
  <meta name="description" content="تعديل الوجهة في موقع اكتشف السعودية.">
  <title>تعديل الوجهة | اكتشف السعودية</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body id="top">

<?php require __DIR__ . '/navigation.php'; ?>

  <main id="main-content">
    <section class="contact-banner">
      <div class="section-wrap">
        <h1 id="edit-title">تعديل الوجهة</h1>
        <p>عدّل بيانات الوجهة ثم احفظ التغييرات.</p>
      </div>
    </section>
    <section class="contact-layout section-wrap">
      <div class="contact-introduction">
        <h2 id="form-title">بيانات الوجهة</h2>
        <p>الحقول المعلّمة بنجمة مطلوبة. أدخل مسار صورة موجودة في المشروع، واكتب كل معلم داخل &lt;li&gt; و&lt;/li&gt;.</p>
        <a class="button" href="admin.php">العودة إلى لوحة الإدارة</a>
      </div>
      <?php if ($destination) { ?>
      <form id="destination-form" class="contact-form" action="edit_destination.php?id=<?php echo $id; ?>" method="post">
        <?php if ($success !== '') { ?>
        <p class="contact-feedback"><?php echo htmlspecialchars($success); ?></p>
        <?php } ?>
        <?php if (!empty($errors)) { ?>
        <ul>
          <?php foreach ($errors as $error) { ?>
          <li><?php echo htmlspecialchars($error); ?></li>
          <?php } ?>
        </ul>
        <?php } ?>
        <div class="contact-field">
          <label for="destination_name">اسم الوجهة *</label>
          <input type="text" id="destination_name" name="destination_name" maxlength="100" value="<?php echo htmlspecialchars($values['destination_name']); ?>">
        </div>
        <div class="contact-field">
          <label for="geographical_category">التصنيف الجغرافي *</label>
          <select id="geographical_category" name="geographical_category">
            <option value="">اختر التصنيف الجغرافي</option>
            <?php foreach (array('central', 'western', 'eastern', 'southern') as $category) { $label = $categories[$category]; ?>
            <option value="<?php echo htmlspecialchars($category); ?>"<?php if ($values['geographical_category'] === $category) { echo ' selected'; } ?>><?php echo htmlspecialchars($label); ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="contact-field">
          <label for="administrative_region">المنطقة الإدارية *</label>
          <input type="text" id="administrative_region" name="administrative_region" maxlength="100" value="<?php echo htmlspecialchars($values['administrative_region']); ?>">
        </div>
        <div class="contact-field">
          <label for="short_description">الوصف المختصر *</label>
          <textarea id="short_description" name="short_description" rows="4"><?php echo htmlspecialchars($values['short_description']); ?></textarea>
        </div>
        <div class="contact-field">
          <label for="image_path">مسار الصورة *</label>
          <input type="text" id="image_path" name="image_path" maxlength="255" value="<?php echo htmlspecialchars($values['image_path']); ?>">
        </div>
        <div class="contact-field">
          <label for="image_alt">النص البديل للصورة *</label>
          <input type="text" id="image_alt" name="image_alt" maxlength="255" value="<?php echo htmlspecialchars($values['image_alt']); ?>">
        </div>
        <div class="contact-field">
          <label for="detailed_description">الوصف التفصيلي</label>
          <textarea id="detailed_description" name="detailed_description" rows="4"><?php echo htmlspecialchars($values['detailed_description']); ?></textarea>
        </div>
        <div class="contact-field">
          <label for="location">الموقع</label>
          <input type="text" id="location" name="location" maxlength="255" value="<?php echo htmlspecialchars($values['location']); ?>">
        </div>
        <div class="contact-field">
          <label for="highlights">أبرز المعالم</label>
          <textarea id="highlights" name="highlights" rows="4"><?php echo htmlspecialchars($values['highlights']); ?></textarea>
        </div>
        <button class="button button-sand" type="submit">حفظ التعديلات</button>
      </form>
      <?php } else { ?>
      <div class="contact-form">
        <?php foreach ($errors as $error) { ?>
        <p><?php echo htmlspecialchars($error); ?></p>
        <?php } ?>
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
