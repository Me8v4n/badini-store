<?php
session_start();

$admin_password = 'me8v4n';
$error_msg = '';

if (isset($_POST['login'])) {
    if (isset($_POST['password']) && $_POST['password'] === $admin_password) {
        $_SESSION['admin_logged'] = true;
        header("Location: admin.php");
        exit();
    } else {
        $error_msg = 'پاسوردا ناتەندرۆستە! هەوڵ بدەوە.';
    }
}

if (isset($_GET['logout'])) {
    unset($_SESSION['admin_logged']);
    header("Location: admin.php");
    exit();
}

if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true):
?>
<!DOCTYPE html>
<html lang="ku" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>چوونەژوورێ ئەدمین - Badini Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @font-face {
            font-family: 'Rudaw';
            src: url('font/rudaw.woff2') format('woff2');
            font-weight: normal;
            font-style: normal;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Rudaw', sans-serif; }
        body {
            background: radial-gradient(circle at top, #0f172a 0%, #07090e 100%);
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(56, 189, 248, 0.2);
            padding: 30px;
            border-radius: 24px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            backdrop-filter: blur(16px);
            text-align: center;
        }
        .login-card i { font-size: 40px; color: #38bdf8; margin-bottom: 15px; }
        .login-card h2 { font-size: 18px; color: #fff; margin-bottom: 20px; }
        .login-input {
            width: 100%; padding: 13px 16px; background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; color: #fff;
            font-size: 14px; margin-bottom: 15px; outline: none; text-align: center; letter-spacing: 2px;
        }
        .login-input:focus { border-color: #38bdf8; box-shadow: 0 0 10px rgba(56,189,248,0.3); }
        .login-btn {
            background: linear-gradient(135deg, #0284c7, #2563eb); color: white; border: none;
            padding: 13px; width: 100%; border-radius: 12px; font-weight: bold; cursor: pointer; font-size: 14px;
        }
        .error-box { background: rgba(220, 38, 38, 0.2); color: #f87171; border: 1px solid #dc2626; padding: 10px; border-radius: 10px; font-size: 12px; margin-bottom: 15px; }
        .back-home { display: block; margin-top: 15px; color: #94a3b8; font-size: 12px; text-decoration: none; }
        .back-home:hover { color: #38bdf8; }
    </style>
</head>
<body>
    <div class="login-card">
        <i class="fa-solid fa-lock"></i>
        <h2>پاسوردا ئەدمینی داخل کە</h2>
        <?php if (!empty($error_msg)): ?><div class="error-box"><?php echo $error_msg; ?></div><?php endif; ?>
        <form method="POST">
            <input type="password" name="password" class="login-input" placeholder="••••••••" required autofocus>
            <button type="submit" name="login" class="login-btn">چوونەژوورێ</button>
        </form>
        <a href="index.php" class="back-home">← ڤەگەر بۆ ستوری سەرەکی</a>
    </div>
</body>
</html>
<?php 
exit();
endif; 

$host = 'sql213.infinityfree.com';
$dbname = 'if0_37075886_badinistore';
$username = 'if0_37075886';
$password = 'Al46MjxGfu';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
$conn->set_charset("utf8mb4");

// داتابەیس و خشتە
$conn->query("CREATE TABLE IF NOT EXISTS store_apps_pro (
    id INT AUTO_INCREMENT PRIMARY KEY,
    app_name VARCHAR(255) NOT NULL,
    bundle_id VARCHAR(255) NOT NULL,
    category VARCHAR(50) NOT NULL,
    version VARCHAR(50) DEFAULT '1.0.0',
    file_size VARCHAR(50) DEFAULT '50 MB',
    icon_url TEXT NOT NULL,
    download_link TEXT NOT NULL,
    screenshot1 TEXT DEFAULT '',
    screenshot2 TEXT DEFAULT '',
    screenshot3 TEXT DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS store_ipas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    app_name VARCHAR(255) NOT NULL,
    version VARCHAR(50) DEFAULT '1.0.0',
    file_size VARCHAR(50) DEFAULT '50 MB',
    icon_url TEXT NOT NULL,
    download_link TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS app_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    app_name VARCHAR(255) NOT NULL,
    reviewer_name VARCHAR(100) DEFAULT 'بکارهێنەر',
    review_text TEXT NOT NULL,
    rating INT DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$message = '';
$msgType = '';
$adminTab = $_GET['adat'] ?? 'apps';

// بارکرنا وێنەیان
function uploadFile($fileKey, $targetDir = 'uploads/') {
    if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
        if (!is_dir($targetDir)) { mkdir($targetDir, 0755, true); }
        $fileName = basename($_FILES[$fileKey]['name']);
        $filePath = $targetDir . uniqid() . '_' . preg_replace("/[^a-zA-Z0-9\._-]/", "", $fileName);
        if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $filePath)) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            return $protocol . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/' . $filePath;
        }
    }
    return '';
}

// زێدەکرن یان نویکرنا ئەپێ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_app'])) {
    $appId = $_POST['app_id'] ?? '';
    $appName = trim($_POST['appName']);
    $bundleId = trim($_POST['bundleId']);
    $category = trim($_POST['category']);
    $version = trim($_POST['version']);
    $fileSize = trim($_POST['fileSize']);
    $downloadLink = trim($_POST['downloadLink']);
    
    $iconUrl = $_POST['old_icon'] ?? '';
    $newIcon = uploadFile('iconFile');
    if (!empty($newIcon)) { $iconUrl = $newIcon; }

    $s1 = $_POST['old_s1'] ?? '';
    $ns1 = uploadFile('screenshot1');
    if (!empty($ns1)) { $s1 = $ns1; }

    $s2 = $_POST['old_s2'] ?? '';
    $ns2 = uploadFile('screenshot2');
    if (!empty($ns2)) { $s2 = $ns2; }

    $s3 = $_POST['old_s3'] ?? '';
    $ns3 = uploadFile('screenshot3');
    if (!empty($ns3)) { $s3 = $ns3; }

    if (!empty($appName) && !empty($downloadLink)) {
        if (!empty($appId)) {
            $stmt = $conn->prepare("UPDATE store_apps_pro SET app_name=?, bundle_id=?, category=?, version=?, file_size=?, icon_url=?, download_link=?, screenshot1=?, screenshot2=?, screenshot3=? WHERE id=?");
            $stmt->bind_param("ssssssssssi", $appName, $bundleId, $category, $version, $fileSize, $iconUrl, $downloadLink, $s1, $s2, $s3, $appId);
            $stmt->execute();
            $message = "ئەپ ب سەرکەفتن هاتە نویکرن!";
            $msgType = "success";
            $stmt->close();
        } else {
            $stmt = $conn->prepare("INSERT INTO store_apps_pro (app_name, bundle_id, category, version, file_size, icon_url, download_link, screenshot1, screenshot2, screenshot3) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssss", $appName, $bundleId, $category, $version, $fileSize, $iconUrl, $downloadLink, $s1, $s2, $s3);
            $stmt->execute();
            $message = "ئەپ ب سەرکەفتن هاتە زێدەکرن!";
            $msgType = "success";
            $stmt->close();
        }
    } else {
        $message = "ناڤێ ئەپێ و لینکا دابەزاندنێ پێتڤییە!";
        $msgType = "error";
    }
}

// زێدەکرنا IPA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_ipa'])) {
    $ipaName = trim($_POST['ipaName']);
    $ipaVersion = trim($_POST['ipaVersion']);
    $ipaSize = trim($_POST['ipaSize']);
    $ipaLink = trim($_POST['ipaLink']);
    $ipaIcon = uploadFile('ipaIcon');

    if (!empty($ipaName) && !empty($ipaLink)) {
        $stmt = $conn->prepare("INSERT INTO store_ipas (app_name, version, file_size, icon_url, download_link) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $ipaName, $ipaVersion, $ipaSize, $ipaIcon, $ipaLink);
        $stmt->execute();
        $message = "فایلێ IPA ب سەرکەفتن هاتە زێدەکرن!";
        $msgType = "success";
        $stmt->close();
    } else {
        $message = "ناڤ و لینکا IPA پێتڤییە!";
        $msgType = "error";
    }
}

// ژێبرن
if (isset($_GET['delete_id'])) {
    $delId = intval($_GET['delete_id']);
    $conn->query("DELETE FROM store_apps_pro WHERE id = $delId");
    header("Location: admin.php?adat=apps");
    exit();
}
if (isset($_GET['delete_ipa'])) {
    $delIpa = intval($_GET['delete_ipa']);
    $conn->query("DELETE FROM store_ipas WHERE id = $delIpa");
    header("Location: admin.php?adat=ipas");
    exit();
}
if (isset($_GET['delete_review'])) {
    $delRev = intval($_GET['delete_review']);
    $conn->query("DELETE FROM app_reviews WHERE id = $delRev");
    header("Location: admin.php?adat=reviews");
    exit();
}

$editApp = null;
if (isset($_GET['edit_id'])) {
    $editId = intval($_GET['edit_id']);
    $res = $conn->query("SELECT * FROM store_apps_pro WHERE id = $editId");
    if ($res->num_rows > 0) { $editApp = $res->fetch_assoc(); }
}

$appsResult = $conn->query("SELECT * FROM store_apps_pro ORDER BY id DESC");
$ipasResult = $conn->query("SELECT * FROM store_ipas ORDER BY id DESC");
$reviewsResult = $conn->query("SELECT * FROM app_reviews ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="ku" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لاپەرێ ئەدمینی - Badini Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @font-face {
            font-family: 'Rudaw';
            src: url('font/rudaw.woff2') format('woff2');
            font-weight: normal;
            font-style: normal;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Rudaw', sans-serif; }
        body {
            background: radial-gradient(circle at top, #0f172a 0%, #07090e 100%);
            color: #f8fafc;
            min-height: 100vh;
            padding: 20px 10px 80px 10px;
        }
        .container { max-width: 650px; margin: 0 auto; }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(12px);
            padding: 14px 20px;
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.06);
            margin-bottom: 20px;
        }
        header h2 { color: #38bdf8; font-size: 15px; font-weight: 700; }
        .header-actions { display: flex; gap: 8px; }
        .nav-btn { color: #38bdf8; text-decoration: none; font-size: 11.5px; background: rgba(56, 189, 248, 0.1); padding: 7px 12px; border-radius: 10px; border: 1px solid rgba(56,189,248,0.3); transition: 0.2s; }
        .nav-btn:hover { background: #38bdf8; color: #0f172a; }
        .logout-btn { color: #f87171; border-color: rgba(248,113,113,0.3); background: rgba(248,113,113,0.1); }
        .logout-btn:hover { background: #ef4444; color: #fff; }

        /* تابان ئەدمینی */
        .admin-tabs { display: flex; gap: 8px; margin-bottom: 20px; }
        .admin-tab-btn {
            flex: 1; padding: 10px; text-align: center; background: rgba(30, 41, 59, 0.5);
            border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; color: #94a3b8;
            font-size: 12px; text-decoration: none; font-weight: bold; transition: 0.2s;
        }
        .admin-tab-btn.active, .admin-tab-btn:hover {
            background: rgba(56, 189, 248, 0.15); color: #38bdf8; border-color: rgba(56, 189, 248, 0.4);
        }

        .form-box {
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid rgba(56, 189, 248, 0.25);
            padding: 20px;
            border-radius: 20px;
            margin-bottom: 25px;
            backdrop-filter: blur(10px);
        }
        .form-box h3 { color: #38bdf8; font-size: 14px; margin-bottom: 15px; font-weight: 700; }
        
        input[type="text"], select, input[type="file"] {
            width: 100%; padding: 12px 15px; background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; color: #fff;
            font-size: 13px; margin-bottom: 12px; outline: none; transition: 0.2s;
        }
        input:focus, select:focus { border-color: #38bdf8; box-shadow: 0 0 10px rgba(56,189,248,0.2); }
        select option { background: #0f172a; color: #fff; }

        .btn {
            background: linear-gradient(135deg, #0284c7, #2563eb); color: white; border: none;
            padding: 13px; width: 100%; border-radius: 12px; font-weight: bold; cursor: pointer; font-size: 14px;
        }
        .btn:hover { opacity: 0.9; transform: translateY(-1px); }

        .app-list { display: flex; flex-direction: column; gap: 12px; }
        .app-item {
            background: rgba(30, 41, 59, 0.4); border: 1px solid rgba(255,255,255,0.06);
            padding: 12px 16px; border-radius: 16px; display: flex; align-items: center; justify-content: space-between;
        }
        .app-info-left { display: flex; align-items: center; gap: 14px; }
        .app-thumb { width: 48px; height: 48px; border-radius: 12px; object-fit: cover; border: 1px solid rgba(255,255,255,0.1); }
        .app-item h4 { font-size: 14px; color: #fff; margin-bottom: 3px; font-weight: 700; }
        .app-item p { font-size: 11px; color: #94a3b8; }
        
        .actions { display: flex; gap: 8px; }
        .edit-btn { background: rgba(2, 132, 199, 0.15); color: #38bdf8; border: 1px solid rgba(56,189,248,0.3); padding: 7px 12px; border-radius: 10px; text-decoration: none; font-size: 11.5px; font-weight: bold; }
        .del-btn { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.3); padding: 7px 12px; border-radius: 10px; text-decoration: none; font-size: 11.5px; font-weight: bold; }

        .toast { padding: 12px; border-radius: 12px; text-align: center; font-size: 13px; margin-bottom: 20px; font-weight: bold; }
        .toast-success { background: rgba(22, 163, 74, 0.2); color: #4ade80; border: 1px solid #16a34a; }
        .toast-error { background: rgba(220, 38, 38, 0.2); color: #f87171; border: 1px solid #dc2626; }
        
        .review-admin-card {
            background: rgba(30, 41, 59, 0.4); border: 1px solid rgba(255,255,255,0.06);
            padding: 14px; border-radius: 14px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h2><i class="fa-solid fa-shield-halved"></i> بەڕێوەبرینا ستوری</h2>
            <div class="header-actions">
                <a href="index.php" class="nav-btn"><i class="fa-solid fa-globe"></i> ستۆر</a>
                <a href="admin.php?logout=true" class="nav-btn logout-btn"><i class="fa-solid fa-right-from-bracket"></i> دەرچوون</a>
            </div>
        </header>

        <?php if (!empty($message)): ?>
            <div class="toast toast-<?php echo $msgType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="admin-tabs">
            <a href="admin.php?adat=apps" class="admin-tab-btn <?php echo ($adminTab === 'apps') ? 'active' : ''; ?>"><i class="fa-solid fa-gamepad"></i> ئەپ و یاری</a>
            <a href="admin.php?adat=ipas" class="admin-tab-btn <?php echo ($adminTab === 'ipas') ? 'active' : ''; ?>"><i class="fa-solid fa-file-arrow-down"></i> فایگێن IPA</a>
            <a href="admin.php?adat=reviews" class="admin-tab-btn <?php echo ($adminTab === 'reviews') ? 'active' : ''; ?>"><i class="fa-regular fa-comments"></i> ڕا و بۆچوون</a>
        </div>

        <?php if ($adminTab === 'apps'): ?>
            <div class="form-box">
                <h3><?php echo $editApp ? '<i class="fa-solid fa-pen-to-square"></i> دەستکاری کرن: ' . htmlspecialchars($editApp['app_name']) : '<i class="fa-solid fa-circle-plus"></i> زێدەکرنا ئەپەکێ نوی بۆ ستوری'; ?></h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="app_id" value="<?php echo $editApp['id'] ?? ''; ?>">
                    <input type="hidden" name="old_icon" value="<?php echo $editApp['icon_url'] ?? ''; ?>">
                    <input type="hidden" name="old_s1" value="<?php echo $editApp['screenshot1'] ?? ''; ?>">
                    <input type="hidden" name="old_s2" value="<?php echo $editApp['screenshot2'] ?? ''; ?>">
                    <input type="hidden" name="old_s3" value="<?php echo $editApp['screenshot3'] ?? ''; ?>">

                    <input type="text" name="appName" placeholder="ناڤێ ئەپێ یان یاریێ" value="<?php echo htmlspecialchars($editApp['app_name'] ?? ''); ?>" required>
                    <input type="text" name="bundleId" placeholder="Bundle ID" value="<?php echo htmlspecialchars($editApp['bundle_id'] ?? ''); ?>" required>
                    
                    <select name="category" required>
                        <option value="Games" <?php echo (isset($editApp['category']) && $editApp['category']=='Games')?'selected':''; ?>>🎮 ياری (Games)</option>
                        <option value="Apps" <?php echo (isset($editApp['category']) && $editApp['category']=='Apps')?'selected':''; ?>>📱 بەرنامە (Apps)</option>
                        <option value="Jailbreak" <?php echo (isset($editApp['category']) && $editApp['category']=='Jailbreak')?'selected':''; ?>>🔓 جیلبریک (Jailbreak)</option>
                    </select>

                    <input type="text" name="version" placeholder="ڤێرژن (بۆ نموونە: v56.21.1)" value="<?php echo htmlspecialchars($editApp['version'] ?? '1.0.0'); ?>">
                    <input type="text" name="fileSize" placeholder="قەبارە (بۆ نموونە: 150 MB)" value="<?php echo htmlspecialchars($editApp['file_size'] ?? '100 MB'); ?>">
                    
                    <label style="font-size:11px; color:#94a3b8; display:block; margin-bottom:4px;"><i class="fa-solid fa-image"></i> وێنەیێ ئایکۆنێ (ژ گەلەریێ):</label>
                    <input type="file" name="iconFile" accept=".png,.jpg,.jpeg">

                    <label style="font-size:11px; color:#94a3b8; display:block; margin-bottom:4px;"><i class="fa-solid fa-images"></i> سکربینشۆت ١ (وێنەیێ پێشکەشکرنێ):</label>
                    <input type="file" name="screenshot1" accept=".png,.jpg,.jpeg">

                    <label style="font-size:11px; color:#94a3b8; display:block; margin-bottom:4px;"><i class="fa-solid fa-images"></i> سکربینشۆت ٢:</label>
                    <input type="file" name="screenshot2" accept=".png,.jpg,.jpeg">

                    <label style="font-size:11px; color:#94a3b8; display:block; margin-bottom:4px;"><i class="fa-solid fa-images"></i> سکربینشۆت ٣:</label>
                    <input type="file" name="screenshot3" accept=".png,.jpg,.jpeg">

                    <input type="text" name="downloadLink" placeholder="لینکا دابەزاندنێ (OTA Link / Direct Link)" value="<?php echo htmlspecialchars($editApp['download_link'] ?? ''); ?>" required>
                    
                    <button type="submit" name="save_app" class="btn"><?php echo $editApp ? 'نویکرنا گۆهڕکاریان' : 'تۆمارکرن ل ستوری'; ?></button>
                </form>
            </div>

            <h3 style="color:#38bdf8; font-size:14px; margin-bottom:12px; font-weight:700;"><i class="fa-solid fa-list-check"></i> لیستا ئەپ و یاریان</h3>
            <div class="app-list">
                <?php if ($appsResult && $appsResult->num_rows > 0): ?>
                    <?php while($app = $appsResult->fetch_assoc()): ?>
                        <div class="app-item">
                            <div class="app-info-left">
                                <img src="<?php echo htmlspecialchars($app['icon_url']); ?>" class="app-thumb" alt="Icon">
                                <div>
                                    <h4><?php echo htmlspecialchars($app['app_name']); ?></h4>
                                    <p><?php echo htmlspecialchars($app['category']); ?> • v<?php echo htmlspecialchars($app['version']); ?></p>
                                </div>
                            </div>
                            <div class="actions">
                                <a href="admin.php?adat=apps&edit_id=<?php echo $app['id']; ?>" class="edit-btn"><i class="fa-solid fa-pen"></i> دەستکاری</a>
                                <a href="admin.php?delete_id=<?php echo $app['id']; ?>" class="del-btn" onclick="return confirm('تە باوەڕە دتەوێ ڤی ئەپی ژێ ببڕی؟')"><i class="fa-solid fa-trash"></i> ژێبرن</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #64748b; font-size: 13px; padding: 20px 0;">چ ئەپ نینن.</p>
                <?php endif; ?>
            </div>

        <?php elseif ($adminTab === 'ipas'): ?>
            <div class="form-box">
                <h3><i class="fa-solid fa-circle-plus"></i> زێدەکرنا فایلەکێ نوی یێ IPA</h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="text" name="ipaName" placeholder="ناڤێ بەرنامە یان یاریێ (بۆ نموونە: Instagram++)" required>
                    <input type="text" name="ipaVersion" placeholder="ڤێرژن (بۆ نموونە: v300.0)" value="1.0.0">
                    <input type="text" name="ipaSize" placeholder="قەبارە (بۆ نموونە: 95 MB)" value="50 MB">
                    
                    <label style="font-size:11px; color:#94a3b8; display:block; margin-bottom:4px;">وێنەیێ ئایکۆنێ (ژ گەلەریێ):</label>
                    <input type="file" name="ipaIcon" accept=".png,.jpg,.jpeg">

                    <input type="text" name="ipaLink" placeholder="لینکا دابەزاندنا IPA (Direct Link)" required>
                    <button type="submit" name="save_ipa" class="btn">تۆمارکرن ل بەشێ IPA</button>
                </form>
            </div>

            <h3 style="color:#38bdf8; font-size:14px; margin-bottom:12px; font-weight:700;"><i class="fa-solid fa-list-check"></i> لیستا فایلێن IPA</h3>
            <div class="app-list">
                <?php if ($ipasResult && $ipasResult->num_rows > 0): ?>
                    <?php while($ipa = $ipasResult->fetch_assoc()): ?>
                        <div class="app-item">
                            <div class="app-info-left">
                                <img src="<?php echo htmlspecialchars($ipa['icon_url']); ?>" class="app-thumb" alt="Icon">
                                <div>
                                    <h4><?php echo htmlspecialchars($ipa['app_name']); ?></h4>
                                    <p>IPA File • v<?php echo htmlspecialchars($ipa['version']); ?> • <?php echo htmlspecialchars($ipa['file_size']); ?></p>
                                </div>
                            </div>
                            <div class="actions">
                                <a href="admin.php?adat=ipas&delete_ipa=<?php echo $ipa['id']; ?>" class="del-btn" onclick="return confirm('تە باوەڕە دتەوێ ڤی فایلی ژێ ببڕی؟')"><i class="fa-solid fa-trash"></i> ژێبرن</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #64748b; font-size: 13px; padding: 20px 0;">چ فایلێن IPA نینن.</p>
                <?php endif; ?>
            </div>

        <?php elseif ($adminTab === 'reviews'): ?>
            <h3 style="color:#38bdf8; font-size:14px; margin-bottom:12px; font-weight:700;"><i class="fa-regular fa-comments"></i> ڕا و بۆچوونێن بکارهێنەראن</h3>
            <div>
                <?php if ($reviewsResult && $reviewsResult->num_rows > 0): ?>
                    <?php while($rev = $reviewsResult->fetch_assoc()): ?>
                        <div class="review-admin-card">
                            <div>
                                <h4 style="color:#fff; font-size:13.5px; margin-bottom:3px;"><?php echo htmlspecialchars($rev['app_name']); ?> <span style="color:#fbbf24; font-size:11px; margin-right:6px;">★ <?php echo $rev['rating']; ?>/5</span></h4>
                                <p style="color:#cbd5e1; font-size:12px; margin-bottom:3px;"><?php echo htmlspecialchars($rev['review_text']); ?></p>
                                <p style="color:#64748b; font-size:10.5px;">بکارهێنەر: <?php echo htmlspecialchars($rev['reviewer_name']); ?> • <?php echo $rev['created_at']; ?></p>
                            </div>
                            <a href="admin.php?adat=reviews&delete_review=<?php echo $rev['id']; ?>" class="del-btn" onclick="return confirm('ئاخۆ دتەوێ ڤێ ڕایێ ژێ ببڕی؟')"><i class="fa-solid fa-trash"></i></a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #64748b; font-size: 13px; padding: 30px 0;">هێشتا چ ڕا و بۆچوون نەهاتینە نڤیسین.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
