<?php
$host = 'sql213.infinityfree.com';
$dbname = 'if0_37075886_badinistore';
$username = 'if0_37075886';
$password = 'Al46MjxGfu';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
$conn->set_charset("utf8mb4");

$selectedCat = $_GET['cat'] ?? 'all';
$searchQuery = $_GET['q'] ?? '';
$activeTab = $_GET['tab'] ?? 'home';

$sql = "SELECT * FROM store_apps_pro WHERE 1=1";
if ($selectedCat !== 'all') {
    $catEscaped = $conn->real_escape_string($selectedCat);
    $sql .= " AND category = '$catEscaped'";
}
if (!empty($searchQuery)) {
    $qEscaped = $conn->real_escape_string($searchQuery);
    $sql .= " AND app_name LIKE '%$qEscaped%'";
}
$sql .= " ORDER BY id DESC";
$appsResult = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="ku" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BADINI STORE - ئایفۆن ستۆر</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @font-face {
            font-family: 'Rudaw';
            src: url('font/rudaw.woff2') format('woff2');
            font-weight: normal;
            font-style: normal;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Rudaw', sans-serif; -webkit-tap-highlight-color: transparent; }
        
        body {
            background: radial-gradient(circle at top, #0f172a 0%, #07090e 100%);
            color: #f8fafc;
            min-height: 100vh;
            padding-bottom: 100px;
        }

        header {
            padding: 16px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            color: #38bdf8;
            font-size: 18px;
            letter-spacing: 0.8px;
            font-weight: 800;
            text-shadow: 0 0 15px rgba(56,189,248,0.4);
        }

        .main-container {
            max-width: 580px;
            margin: 20px auto;
            padding: 0 15px;
        }
        
        /* گەڕان */
        .search-box {
            margin-bottom: 18px;
            position: relative;
        }
        .search-box i {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 14px;
        }
        .search-box input {
            width: 100%;
            padding: 13px 45px 13px 18px;
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px;
            color: #fff;
            font-size: 13.5px;
            outline: none;
            transition: all 0.3s ease;
        }
        .search-box input:focus {
            border-color: #38bdf8;
            background: rgba(30, 41, 59, 0.9);
            box-shadow: 0 0 12px rgba(56, 189, 248, 0.25);
        }

        /* بەشێن پۆلەندا (Categories Tabs) */
        .categories-nav {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 12px;
            margin-bottom: 20px;
            scrollbar-width: none;
        }
        .categories-nav::-webkit-scrollbar { display: none; }

        .cat-btn {
            background: rgba(30, 41, 59, 0.5);
            color: #94a3b8;
            border: 1px solid rgba(255,255,255,0.06);
            padding: 8px 18px;
            border-radius: 22px;
            font-size: 13px;
            text-decoration: none;
            white-space: nowrap;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .cat-btn.active, .cat-btn:hover {
            background: linear-gradient(135deg, #0284c7, #2563eb);
            color: #ffffff;
            border-color: transparent;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(2, 132, 199, 0.4);
        }

        /* کاردێن ئەپان */
        .app-list { display: flex; flex-direction: column; gap: 14px; }

        .app-card {
            background: rgba(30, 41, 59, 0.45);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 20px;
            padding: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            backdrop-filter: blur(10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }

        .app-card:hover {
            border-color: rgba(56, 189, 248, 0.35);
            background: rgba(30, 41, 59, 0.75);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
        }

        .app-info { display: flex; align-items: center; gap: 15px; }

        .app-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            object-fit: cover;
            border: 1px solid rgba(255,255,255,0.12);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }

        .app-details h4 { font-size: 15.5px; color: #fff; margin-bottom: 4px; font-weight: 700; }
        .app-details p { font-size: 11.5px; color: #94a3b8; margin-bottom: 3px; }
        .badge-cat { color: #38bdf8; font-weight: 600; }

        .get-btn {
            background: rgba(56, 189, 248, 0.1);
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.4);
            padding: 7px 24px;
            border-radius: 24px;
            font-weight: bold;
            font-size: 13px;
            text-decoration: none;
            transition: all 0.25s ease;
            text-align: center;
            box-shadow: 0 2px 10px rgba(56, 189, 248, 0.1);
        }
        .get-btn:hover { background: #38bdf8; color: #0f172a; box-shadow: 0 4px 15px rgba(56, 189, 248, 0.5); }

        /* پەنەلا خوارێ (Bottom Navigation Bar) */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-top: 1px solid rgba(255,255,255,0.08);
            display: flex;
            justify-content: space-around;
            padding: 12px 0;
            z-index: 1000;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.4);
        }
        .nav-item {
            color: #64748b;
            text-decoration: none;
            font-size: 11.5px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            transition: 0.2s;
        }
        .nav-item i { font-size: 18px; }
        .nav-item.active, .nav-item:hover { color: #38bdf8; }

        /* شاشەیا خۆمالی (App Details Modal) */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(7, 9, 14, 0.85);
            backdrop-filter: blur(12px);
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }
        .modal-card {
            background: #111827;
            border: 1px solid rgba(56, 189, 248, 0.3);
            width: 100%;
            max-width: 500px;
            border-radius: 24px;
            padding: 22px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 20px 40px rgba(0,0,0,0.6);
        }
        .close-modal {
            position: absolute;
            top: 15px; left: 15px;
            background: rgba(255,255,255,0.08);
            border: none; color: #fff;
            width: 32px; height: 32px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 14px;
        }
        .modal-header { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; }
        .modal-icon { width: 75px; height: 75px; border-radius: 18px; object-fit: cover; border: 1px solid rgba(255,255,255,0.1); }
        
        /* سکربینشۆت و ڤیدیۆ */
        .screenshots-scroll {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 8px;
            margin-bottom: 20px;
            scrollbar-width: none;
        }
        .screenshots-scroll::-webkit-scrollbar { display: none; }
        .screenshot-item {
            width: 130px;
            height: 220px;
            border-radius: 14px;
            object-fit: cover;
            border: 1px solid rgba(255,255,255,0.1);
            background: #1e293b;
            flex-shrink: 0;
        }

        /* ڕایێن بکارهێنەران و ستێرک (Rating & Reviews) */
        .rating-box {
            background: rgba(30, 41, 59, 0.5);
            border-radius: 14px;
            padding: 14px;
            margin-bottom: 15px;
            text-align: center;
        }
        .stars { color: #fbbf24; font-size: 14px; margin-bottom: 5px; }
        .reviews-title { font-size: 13px; color: #94a3b8; margin-bottom: 10px; text-align: right; }
        .review-input {
            width: 100%;
            padding: 10px 14px;
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 10px;
            color: #fff;
            font-size: 12.5px;
            margin-bottom: 8px;
            outline: none;
        }
        .submit-review {
            background: #38bdf8; color: #0f172a; border: none; padding: 7px 16px; border-radius: 10px; font-weight: bold; font-size: 12px; cursor: pointer; float: left;
        }

        .empty-state { text-align: center; padding: 60px 20px; color: #64748b; }
        .empty-state i { font-size: 40px; margin-bottom: 12px; color: #334155; }

        /* سێتیگ و IPA پەڕە */
        .page-content { display: none; }
        .page-content.active { display: block; }
        .setting-card {
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .setting-card h4 { font-size: 14px; color: #fff; margin-bottom: 3px; }
        .setting-card p { font-size: 11.5px; color: #94a3b8; }
    </style>
</head>
<body>

    <header>
        <h1><i class="fa-solid fa-store" style="margin-left: 6px;"></i> BADINI STORE</h1>
        <a href="admin.php" style="color: #64748b; font-size: 12px; text-decoration: none;"><i class="fa-solid fa-gear"></i></a>
    </header>

    <div class="main-container">

        <div id="tab-home" class="page-content <?php echo ($activeTab === 'home') ? 'active' : ''; ?>">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <form method="GET">
                    <input type="text" name="q" placeholder="گەڕان لدووڤ ناڤێ یاری یان بەرنامەی..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                    <input type="hidden" name="cat" value="<?php echo htmlspecialchars($selectedCat); ?>">
                    <input type="hidden" name="tab" value="home">
                </form>
            </div>

            <div class="categories-nav">
                <a href="index.php?cat=all&tab=home" class="cat-btn <?php echo ($selectedCat === 'all') ? 'active' : ''; ?>"><i class="fa-solid fa-border-all"></i> هەمی</a>
                <a href="index.php?cat=Games&tab=home" class="cat-btn <?php echo ($selectedCat === 'Games') ? 'active' : ''; ?>"><i class="fa-solid fa-gamepad"></i> یاری</a>
                <a href="index.php?cat=Apps&tab=home" class="cat-btn <?php echo ($selectedCat === 'Apps') ? 'active' : ''; ?>"><i class="fa-solid fa-mobile-screen-button"></i> بەرنامە</a>
                <a href="index.php?cat=Jailbreak&tab=home" class="cat-btn <?php echo ($selectedCat === 'Jailbreak') ? 'active' : ''; ?>"><i class="fa-solid fa-unlock-keyhole"></i> جیلبریک</a>
            </div>

            <div class="app-list">
                <?php if ($appsResult && $appsResult->num_rows > 0): ?>
                    <?php while($app = $appsResult->fetch_assoc()): ?>
                        <div class="app-card" onclick="openAppModal(<?php echo htmlspecialchars(json_encode($app)); ?>)">
                            <div class="app-info">
                                <img src="<?php echo htmlspecialchars($app['icon_url']); ?>" class="app-icon" alt="Icon">
                                <div class="app-details">
                                    <h4><?php echo htmlspecialchars($app['app_name']); ?></h4>
                                    <p><?php echo htmlspecialchars($app['bundle_id']); ?></p>
                                    <p>
                                        <i class="fa-regular fa-circle-down" style="font-size:10px;"></i> <?php echo htmlspecialchars($app['version']); ?> • 
                                        <i class="fa-solid fa-hard-drive" style="font-size:10px;"></i> <?php echo htmlspecialchars($app['file_size']); ?> • 
                                        <span class="badge-cat"><?php echo htmlspecialchars($app['category']); ?></span>
                                    </p>
                                </div>
                            </div>
                            <span class="get-btn" onclick="event.stopPropagation(); window.location.href='<?php echo htmlspecialchars($app['download_link']); ?>'">GET</span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fa-regular fa-folder-open"></i>
                        <p>چ یاری یان بەرنامە نەهاتە دیتن!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div id="tab-ipa" class="page-content <?php echo ($activeTab === 'ipa') ? 'active' : ''; ?>">
            <h3 style="color:#38bdf8; font-size:16px; margin-bottom:15px;"><i class="fa-solid fa-file-arrow-down"></i> فایگێن IPA و سایدلۆدینگ</h3>
            <div class="setting-card">
                <div>
                    <h4>برنامەیا Sideloadly</h4>
                    <p>باشترین بەرنامە بۆ ڤەگوهاستنا فایلێن IPA بۆ سەر ئایفۆنی ب ڕێکا کۆمپیوتەری.</p>
                </div>
                <a href="https://sideloadly.io" target="_blank" class="get-btn">دابەزاندن</a>
            </div>
            <div class="setting-card">
                <div>
                    <h4>فایلێن IPA یێن تایبەت</h4>
                    <p>دبیتە ڕێکا تە بۆ ئینستالکرنا یاریێن مۆدکرى ب بێ جیلبریک.</p>
                </div>
                <span class="get-btn" style="opacity: 0.5;">ب زوویی</span>
            </div>
        </div>

        <div id="tab-settings" class="page-content <?php echo ($activeTab === 'settings') ? 'active' : ''; ?>">
            <h3 style="color:#38bdf8; font-size:16px; margin-bottom:15px;"><i class="fa-solid fa-gear"></i> رێکخستنێن ستوری</h3>
            <div class="setting-card">
                <div>
                    <h4>دۆخێ تاری (Dark Mode)</h4>
                    <p>دۆخێ فەرمی یێ تاری چاڤان ناخۆرێت و پاتریێ دپارێزێت.</p>
                </div>
                <span style="color:#38bdf8; font-weight:bold;">فەعالە ✅</span>
            </div>
            <div class="setting-card">
                <div>
                    <h4>ڤێرژنا ستوری</h4>
                    <p>Badini Store v3.5 Pro - Ultimate Edition</p>
                </div>
                <span style="color:#94a3b8; font-size:12px;">مۆدێرن</span>
            </div>
        </div>

    </div>

    <div id="appModal" class="modal-overlay" onclick="closeAppModal(event)">
        <div class="modal-card">
            <button class="close-modal" onclick="document.getElementById('appModal').style.display='none'"><i class="fa-solid fa-xmark"></i></button>
            <div class="modal-header">
                <img id="m-icon" src="" class="modal-icon" alt="">
                <div>
                    <h3 id="m-name" style="color:#fff; font-size:17px; margin-bottom:4px;"></h3>
                    <p id="m-bundle" style="color:#94a3b8; font-size:11.5px; margin-bottom:4px;"></p>
                    <p style="color:#38bdf8; font-size:12px;"><span id="m-ver"></span> • <span id="m-size"></span></p>
                </div>
            </div>

            <div class="screenshots-scroll">
                <div class="screenshot-item" style="display:flex; align-items:center; justify-content:center; color:#64748b; font-size:11px;">وێنەیێ ١</div>
                <div class="screenshot-item" style="display:flex; align-items:center; justify-content:center; color:#64748b; font-size:11px;">وێنەیێ ٢</div>
                <div class="screenshot-item" style="display:flex; align-items:center; justify-content:center; color:#64748b; font-size:11px;">وێنەیێ ٣</div>
            </div>

            <div class="rating-box">
                <div class="stars">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star-half-stroke"></i>
                </div>
                <p style="font-size:12px; color:#cbd5e1;">4.8 / 5.0 (زیاتری 1,200 دابەزاندن)</p>
            </div>

            <div class="reviews-title"><i class="fa-regular fa-comment"></i> ڕا و بۆچوونێن بکارهێنەری:</div>
            <input type="text" class="review-input" placeholder="رایا خۆ ل دۆر ڤی ئەپی بنڤیسە...">
            <button class="submit-review" onclick="alert('سوپاس بۆ رایا تە!')">ناردن</button>

            <div style="clear:both; margin-top:20px;">
                <a id="m-link" href="#" class="get-btn" style="display:block; text-align:center; padding:12px; border-radius:14px; font-size:14px;">دابەزاندنا ڕاستەوخۆ (GET)</a>
            </div>
        </div>
    </div>

    <div class="bottom-nav">
        <a href="index.php?tab=home" class="nav-item <?php echo ($activeTab === 'home') ? 'active' : ''; ?>">
            <i class="fa-solid fa-house"></i>
            <span>سەرەکی</span>
        </a>
        <a href="index.php?tab=ipa" class="nav-item <?php echo ($activeTab === 'ipa') ? 'active' : ''; ?>">
            <i class="fa-solid fa-file-arrow-down"></i>
            <span>فایلێن IPA</span>
        </a>
        <a href="index.php?tab=settings" class="nav-item <?php echo ($activeTab === 'settings') ? 'active' : ''; ?>">
            <i class="fa-solid fa-sliders"></i>
            <span>ڕێکخستن</span>
        </a>
    </div>

    <script>
        function openAppModal(app) {
            document.getElementById('m-icon').src = app.icon_url;
            document.getElementById('m-name').innerText = app.app_name;
            document.getElementById('m-bundle').innerText = app.bundle_id;
            document.getElementById('m-ver').innerText = 'ڤێرژن: ' + app.version;
            document.getElementById('m-size').innerText = 'قەبارە: ' + app.file_size;
            document.getElementById('m-link').href = app.download_link;
            document.getElementById('appModal').style.display = 'flex';
        }
        function closeAppModal(e) {
            if(e.target.id === 'appModal') {
                document.getElementById('appModal').style.display = 'none';
            }
        }
    </script>
</body>
</html>
