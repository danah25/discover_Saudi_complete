<?php
session_start();

// Keep the browser from caching pages that contain private data.
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
// Only logged-in regular users can access this page
if (!isset($_SESSION['user_id']) ||
    ($_SESSION['role'] ?? '') !== 'user') {
    header('Location: login.php');
    exit;
}

// Escape the username so the browser displays it as text.
$username = htmlspecialchars(
    $_SESSION['username'],
    ENT_QUOTES,
    'UTF-8'
);

const PRIVATE_NOTE_MAX_BYTES = 4096;
// AES-GCM keeps stored notes private and detects tampering.
const PRIVATE_NOTE_CIPHER = 'aes-256-gcm';

$_SESSION['private_note_csrf'] ??= bin2hex(random_bytes(32));
$note = '';
$noteError = '';
$viewNote = ($_GET['note'] ?? '') === 'view';
$savingNote = $_SERVER['REQUEST_METHOD'] === 'POST';

if ($viewNote || $savingNote) {
    // Use the session user ID for both reads and writes so users can only access their own note.
    $userId = (int) $_SESSION['user_id'];
    $submittedNote = $_POST['private_note'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if ($savingNote && (!is_string($token) || !hash_equals($_SESSION['private_note_csrf'], $token))) {
        http_response_code(403);
        $noteError = 'تعذر التحقق من الطلب. أعد تحميل الصفحة وحاول مجددًا.';
    } elseif ($savingNote && (!is_string($submittedNote) || trim($submittedNote) === '' ||
        strlen($submittedNote) > PRIVATE_NOTE_MAX_BYTES || preg_match('//u', $submittedNote) !== 1)) {
        http_response_code(400);
        $noteError = 'أدخل ملاحظة نصية لا تتجاوز 4096 بايت.';
    } else {
        try {
            $key = @file_get_contents((getenv('DISCOVER_SAUDI_CONFIG_DIR') ?: 'C:/xampp/apache/conf/setup') . '/discover-saudi-private-note.key');            if ($key === false || strlen($key) !== 32) {
                throw new RuntimeException('Private note key unavailable.');
            }

            require_once 'db.php';

            if ($savingNote) {
                // Use a fresh nonce for every save and keep the tag to verify the note on read.
                $nonce = random_bytes(12);
                $tag = '';
                $ciphertext = openssl_encrypt(
                    $submittedNote, PRIVATE_NOTE_CIPHER, $key, OPENSSL_RAW_DATA, $nonce, $tag, (string) $userId
                );
                if ($ciphertext === false) {
                    throw new RuntimeException('Private note encryption failed.');
                }

                $statement = $pdo->prepare(
                    'INSERT INTO private_notes (user_id, ciphertext, nonce, auth_tag)
                     VALUES (:user_id, :ciphertext, :nonce, :auth_tag)
                     ON DUPLICATE KEY UPDATE ciphertext = :updated_ciphertext,
                         nonce = :updated_nonce, auth_tag = :updated_tag'
                );
                $statement->execute([
                    'user_id' => $userId,
                    'ciphertext' => $ciphertext,
                    'nonce' => $nonce,
                    'auth_tag' => $tag,
                    'updated_ciphertext' => $ciphertext,
                    'updated_nonce' => $nonce,
                    'updated_tag' => $tag
                ]);
                header('Location: dashboard.php?note=saved', true, 303);
                exit;
            }

            $statement = $pdo->prepare(
                'SELECT ciphertext, nonce, auth_tag FROM private_notes WHERE user_id = :user_id'
            );
            $statement->execute(['user_id' => $userId]);
            $storedNote = $statement->fetch();
            if ($storedNote) {
                $note = openssl_decrypt(
                    $storedNote['ciphertext'], PRIVATE_NOTE_CIPHER, $key, OPENSSL_RAW_DATA,
                    $storedNote['nonce'], $storedNote['auth_tag'], (string) $userId
                );
                if ($note === false) {
                    $note = '';
                    throw new RuntimeException('Private note authentication failed.');
                }
            }
        } catch (RuntimeException $e) {
            http_response_code(500);
            $noteError = 'تعذر حفظ الملاحظة أو عرضها.';
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
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">
    <title>لوحة المستخدم | اكتشف السعودية</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<?php require __DIR__ . '/navigation.php'; ?>

<main>
    <section class="contact-banner">
        <div class="section-wrap">
            <h1>لوحة المستخدم</h1>
            <p>مرحبًا بك في اكتشف السعودية</p>
        </div>
    </section>

    <section class="section-wrap">
        <h2>مرحبًا، <?php echo $username; ?>!</h2>
        <p>تم تسجيل دخولك بنجاح.</p>
        <p>يمكنك الآن استكشاف وجهات المملكة.</p>

    </section>
    <section class="section-wrap">
        <form class="contact-form" action="dashboard.php" method="post">
            <h2>ملاحظة خاصة</h2>
            <?php if ($noteError !== '') { ?>
                <p class="contact-feedback" role="alert"><?php echo htmlspecialchars($noteError, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php } elseif (($_GET['note'] ?? '') === 'saved') { ?>
                <p class="contact-feedback" role="status">تم حفظ الملاحظة.</p>
            <?php } elseif ($viewNote && $note === '') { ?>
                <p class="contact-feedback">لا توجد ملاحظة محفوظة.</p>
            <?php } ?>
            <div class="contact-field">
                <label for="private_note">نص الملاحظة</label>
                <textarea id="private_note" name="private_note" rows="4" maxlength="4096" required><?php echo htmlspecialchars($note, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></textarea>
            </div>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['private_note_csrf'], ENT_QUOTES, 'UTF-8'); ?>">
            <button class="button button-sand" type="submit">حفظ الملاحظة</button>
            <a class="button button-sand" href="dashboard.php?note=view">عرض الملاحظة</a>
        </form>
    </section>
</main>

<script src="script.js"></script>
</body>
</html>
