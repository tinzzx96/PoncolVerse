<?php 
require_once 'config/config.php';

// Refresh data user dari DB secara real-time (profile photo + subscription)
if (isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $sql_live = "SELECT u.profile_photo, u.subscription_status, u.subscription_end, u.subscription_start,
                        sp.name as subscription_plan_name
                 FROM users u
                 LEFT JOIN subscription_plans sp ON u.subscription_plan_id = sp.id
                 WHERE u.id = ?";
    $stmt_live = $conn->prepare($sql_live);
    $stmt_live->bind_param("i", $uid);
    $stmt_live->execute();
    $live_data = $stmt_live->get_result()->fetch_assoc();
    $stmt_live->close();

    if ($live_data) {
        $_SESSION['profile_photo'] = $live_data['profile_photo'] ?? null;
        $sub_end = $live_data['subscription_end'];
        if ($live_data['subscription_status'] === 'active' && !empty($sub_end) && strtotime($sub_end) < time()) {
            $conn->query("UPDATE users SET subscription_status = 'expired' WHERE id = {$uid}");
            $live_data['subscription_status'] = 'expired';
        }
        $_SESSION['subscription_status']    = $live_data['subscription_status'] ?? 'none';
        $_SESSION['subscription_end']       = $live_data['subscription_end'] ?? null;
        $_SESSION['subscription_plan_name'] = $live_data['subscription_plan_name'] ?? null;
    }
}

// Ambil film dari DB untuk poster strip hero (8 film dengan rating tinggi, berbeda-beda)
$hero_movies = [];
$hero_sql = "SELECT id, title, rating, poster FROM movies ORDER BY rating DESC LIMIT 12";
$hero_result = $conn->query($hero_sql);
if ($hero_result && $hero_result->num_rows > 0) {
    while ($row = $hero_result->fetch_assoc()) {
        $hero_movies[] = $row;
    }
}

// Fallback poster dari TMDB jika DB kosong
$fallback_posters = [
    ['title'=>'Avengers: Endgame',       'rating'=>'8.4', 'poster'=>'https://image.tmdb.org/t/p/w500/or06FN3Dka5tukK1e9sl16pB3iy.jpg',  'hot'=>true],
    ['title'=>'Inception',               'rating'=>'8.8', 'poster'=>'https://image.tmdb.org/t/p/w500/9gk7adHYeDvHkCSEqAvQNLV5Uge.jpg',  'hot'=>false],
    ['title'=>'The Shawshank Redemption','rating'=>'9.3', 'poster'=>'https://image.tmdb.org/t/p/w500/q6y0Go1tsGEsmtFryDOJo3dEmqu.jpg',  'hot'=>false],
    ['title'=>'Pulp Fiction',            'rating'=>'8.9', 'poster'=>'https://image.tmdb.org/t/p/w500/d5iIlFn5s0ImszYzBPb8JPIfbXD.jpg',  'hot'=>false],
    ['title'=>'The Dark Knight',         'rating'=>'9.0', 'poster'=>'https://image.tmdb.org/t/p/w500/qJ2tW6WMUDux911r6m7haRef0WH.jpg',  'hot'=>true],
    ['title'=>'Interstellar',            'rating'=>'8.6', 'poster'=>'https://image.tmdb.org/t/p/w500/gEU2QniE6E77NI6lCU6MxlNBvIx.jpg',  'hot'=>false],
    ['title'=>'The Matrix',              'rating'=>'8.7', 'poster'=>'https://image.tmdb.org/t/p/w500/f89U3ADr1oiB1s9GkdPOEpXUk5H.jpg',  'hot'=>false],
    ['title'=>'Forrest Gump',            'rating'=>'8.8', 'poster'=>'https://image.tmdb.org/t/p/w500/saHP97rTPS5eLmrLQEcANmKrsFl.jpg',  'hot'=>false],
    ['title'=>'Spider-Man: No Way Home', 'rating'=>'8.2', 'poster'=>'https://image.tmdb.org/t/p/w500/1g0dhYtq4irTY1GPXvft6k4YLjm.jpg',  'hot'=>true],
];

// Siapkan 9 poster unik untuk 3 kolom (3 poster per kolom)
// Kolom 1: index 0,1,2 | Kolom 2: index 3,4,5 | Kolom 3: index 6,7,8
if (count($hero_movies) >= 9) {
    // Pastikan tidak ada poster duplikat bersebelahan lintas kolom
    $posters_raw = array_slice($hero_movies, 0, 9);
    $hero_col1 = array_slice($posters_raw, 0, 3);
    $hero_col2 = array_slice($posters_raw, 3, 3);
    $hero_col3 = array_slice($posters_raw, 6, 3);
    $use_fallback = false;
} else {
    // Pakai fallback — sudah dipastikan 9 item unik tanpa duplikat bersebelahan
    $hero_col1 = array_slice($fallback_posters, 0, 3);
    $hero_col2 = array_slice($fallback_posters, 3, 3);
    $hero_col3 = array_slice($fallback_posters, 6, 3);
    $use_fallback = true;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PoncolVerse - Nonton Bioskop Online</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
  <link rel="stylesheet" href="assets/css/main.css">
  <link rel="stylesheet" href="assets/css/responsive.css">
  <style>
    /* =============================================
       HERO SECTION — NEW DESIGN
       Menggantikan .hero { ... } lama di main.css
       ============================================= */

    .hero {
      height: 100vh;
      min-height: 640px;
      position: relative;
      overflow: hidden;
      display: flex;
      align-items: center;
      background: #080810;
    }

    /* Grid background */
    .hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background-image:
        linear-gradient(rgba(255,0,60,0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,0,60,0.04) 1px, transparent 1px);
      background-size: 44px 44px;
      pointer-events: none;
      z-index: 0;
    }

    /* Glow orbs */
    .hero-glow-left {
      position: absolute;
      top: -100px; left: -100px;
      width: 500px; height: 500px;
      background: radial-gradient(circle, rgba(255,0,60,0.16) 0%, transparent 70%);
      border-radius: 50%;
      pointer-events: none;
      z-index: 1;
    }

    .hero-glow-right {
      position: absolute;
      bottom: -120px; right: 100px;
      width: 350px; height: 350px;
      background: radial-gradient(circle, rgba(255,0,60,0.07) 0%, transparent 70%);
      border-radius: 50%;
      pointer-events: none;
      z-index: 1;
    }

    /* Scanline animation */
    .hero-scanline {
      position: absolute;
      left: 0; right: 0;
      height: 1px;
      background: linear-gradient(to right, transparent, rgba(255,0,60,0.3), transparent);
      pointer-events: none;
      z-index: 2;
      animation: heroScan 7s ease-in-out infinite;
    }

    @keyframes heroScan {
      0%, 100% { top: 15%; opacity: 0; }
      20% { opacity: 1; }
      80% { opacity: 1; }
      100% { top: 85%; opacity: 0; }
    }

    /* ── POSTER STRIP (kanan, dimiringkan) ── */
    .hero-poster-strip {
      position: absolute;
      right: -50px;
      top: -80px;
      bottom: -80px;
      width: 460px;
      /* Kemiringan: rotate + skew untuk efek perspektif */
      transform: rotate(-8deg) skewX(-4deg);
      transform-origin: center center;
      display: flex;
      gap: 12px;
      align-items: flex-start;
      padding: 0 10px;
      overflow: hidden;
      z-index: 3;
    }

    /* Fade kiri — blend poster ke konten */
    .hero-poster-fade-left {
      position: absolute;
      top: 0; left: -10px; bottom: 0;
      width: 220px;
      background: linear-gradient(to right, #080810 30%, transparent 100%);
      pointer-events: none;
      z-index: 10;
      /* Counter-rotate agar garis fade tetap vertikal */
      transform: rotate(8deg) skewX(4deg) translateX(-25px);
    }

    /* Fade atas & bawah — sembunyikan tepi miring */
    .hero-poster-fade-top {
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 120px;
      background: linear-gradient(to bottom, #080810, transparent);
      pointer-events: none;
      z-index: 9;
    }

    .hero-poster-fade-bottom {
      position: absolute;
      bottom: 0; left: 0; right: 0;
      height: 120px;
      background: linear-gradient(to top, #080810, transparent);
      pointer-events: none;
      z-index: 9;
    }

    .hero-poster-col {
      display: flex;
      flex-direction: column;
      gap: 12px;
      flex-shrink: 0;
    }

    /* Offset vertikal tiap kolom agar terasa dinamis */
    .hero-poster-col:nth-child(4) { margin-top: 0; }
    .hero-poster-col:nth-child(5) { margin-top: 55px; }
    .hero-poster-col:nth-child(6) { margin-top: 25px; }

    .hero-poster-card {
      width: 110px;
      height: 162px;
      border-radius: 10px;
      background: #1a1a2e;
      border: 1px solid rgba(255,255,255,0.07);
      overflow: hidden;
      position: relative;
      flex-shrink: 0;
    }

    .hero-poster-card img {
      width: 100%; height: 100%;
      object-fit: cover;
      opacity: 0.72;
      display: block;
    }

    .hero-poster-hot {
      position: absolute;
      bottom: 7px; left: 7px;
      background: rgba(255,0,60,0.92);
      color: #fff;
      font-size: 8px;
      font-weight: 700;
      letter-spacing: 1px;
      text-transform: uppercase;
      padding: 3px 7px;
      border-radius: 4px;
      z-index: 3;
    }

    .hero-poster-rating {
      position: absolute;
      top: 7px; right: 7px;
      background: rgba(0,0,0,0.72);
      color: #ffc107;
      font-size: 9px;
      font-weight: 700;
      padding: 2px 6px;
      border-radius: 4px;
      z-index: 3;
    }

    /* ── HERO CONTENT (kiri) ── */
    .hero-content-wrap {
      position: relative;
      z-index: 10;
      padding: 0 5%;
      max-width: 620px;
    }

    .hero-eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      background: rgba(255,0,60,0.1);
      border: 1px solid rgba(255,0,60,0.3);
      border-radius: 20px;
      padding: 5px 15px;
      font-size: 11px;
      font-weight: 600;
      color: #ff4d7a;
      letter-spacing: 2px;
      text-transform: uppercase;
      margin-bottom: 1.5rem;
    }

    .hero-eyebrow-dot {
      width: 6px; height: 6px;
      background: #ff003c;
      border-radius: 50%;
      animation: heroBlink 1.8s infinite;
    }

    @keyframes heroBlink {
      0%, 100% { opacity: 1; }
      50%       { opacity: 0.15; }
    }

    .hero-title-new {
      font-family: 'Orbitron', sans-serif;
      font-size: clamp(2.4rem, 4vw, 3.2rem);
      font-weight: 900;
      line-height: 1.1;
      color: #ffffff;
      margin-bottom: 1.25rem;
      letter-spacing: -0.5px;
    }

    .hero-title-new .hero-accent {
      color: #ff003c;
      display: block;
    }

    .hero-desc-new {
      font-size: 1rem;
      color: rgba(255,255,255,0.52);
      line-height: 1.75;
      margin-bottom: 2rem;
      max-width: 420px;
      font-weight: 300;
    }

    /* CTA buttons */
    .hero-cta-row {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-bottom: 2.5rem;
      flex-wrap: wrap;
    }

    .btn-hero-primary {
      display: inline-flex;
      align-items: center;
      gap: 9px;
      background: #ff003c;
      color: #fff;
      font-family: 'Poppins', sans-serif;
      font-size: 0.9rem;
      font-weight: 600;
      padding: 0.8rem 1.9rem;
      border-radius: 50px;
      border: none;
      cursor: pointer;
      letter-spacing: 0.3px;
      box-shadow: 0 0 28px rgba(255,0,60,0.45);
      transition: all 0.25s;
    }

    .btn-hero-primary:hover {
      background: #e6003a;
      box-shadow: 0 0 40px rgba(255,0,60,0.65);
      transform: translateY(-2px);
    }

    .btn-hero-play-icon {
      width: 20px; height: 20px;
      background: rgba(255,255,255,0.22);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 7px;
    }

    .btn-hero-secondary {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: transparent;
      color: rgba(255,255,255,0.65);
      font-family: 'Poppins', sans-serif;
      font-size: 0.9rem;
      font-weight: 500;
      padding: 0.8rem 1.6rem;
      border-radius: 50px;
      border: 1px solid rgba(255,255,255,0.15);
      cursor: pointer;
      transition: all 0.25s;
    }

    .btn-hero-secondary:hover {
      border-color: rgba(255,255,255,0.3);
      color: #fff;
      background: rgba(255,255,255,0.05);
    }

    /* Stats row */
    .hero-stats-row {
      display: flex;
      align-items: center;
    }

    .hero-stat-item {
      padding-right: 1.5rem;
      margin-right: 1.5rem;
      border-right: 1px solid rgba(255,255,255,0.1);
    }

    .hero-stat-item:last-child {
      border-right: none;
      padding-right: 0;
      margin-right: 0;
    }

    .hero-stat-num {
      font-family: 'Orbitron', sans-serif;
      font-size: 1.4rem;
      font-weight: 700;
      color: #fff;
      line-height: 1;
      margin-bottom: 3px;
    }

    .hero-stat-num span { color: #ff003c; font-size: 1rem; }

    .hero-stat-lbl {
      font-size: 0.68rem;
      color: rgba(255,255,255,0.38);
      text-transform: uppercase;
      letter-spacing: 1.5px;
      font-weight: 500;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .hero-poster-strip { display: none; }
      .hero-content-wrap { max-width: 100%; padding: 0 1.5rem; }
      .hero-title-new { font-size: 2rem; }
    }

    /* ── FEATURED HORIZONTAL SECTION ── */
 /* ── SECTION WRAPPER ── */
    .movies-featured {
      padding: 5rem 0 3rem;
      overflow: hidden;
    }
    
    .movies-featured-title {
      font-family: 'Orbitron', sans-serif;
      font-size: 2rem;
      color: #ff003c;
      text-shadow: 0 0 12px rgba(255,0,60,0.4);
      margin-bottom: 1.75rem;
      padding: 0 5%;
    }
    
    /* Outer: posisi relatif agar tombol bisa absolute di kiri-kanan */
    .featured-scroll-outer {
      position: relative;
      display: flex;
      align-items: center;
    }
    
    /* ── TOMBOL KIRI & KANAN ── */
    .scroll-side-btn {
      flex-shrink: 0;
      width: 42px;
      height: 42px;
      border-radius: 50%;
      background: rgba(20, 20, 20, 0.92);
      border: 1.5px solid rgba(255, 0, 60, 0.35);
      color: #ff003c;
      font-size: 0.95rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s ease;
      z-index: 10;
      /* Posisi absolute di tengah vertikal track */
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      box-shadow: 0 4px 16px rgba(0,0,0,0.5);
    }
    
    .scroll-side-left  { left: 10px; }
    .scroll-side-right { right: 10px; }
    
    .scroll-side-btn:hover {
      background: rgba(255, 0, 60, 0.18);
      border-color: #ff003c;
      box-shadow: 0 0 16px rgba(255,0,60,0.4);
      transform: translateY(-50%) scale(1.08);
    }
    
    .scroll-side-btn:disabled {
      opacity: 0;
      pointer-events: none;
    }
    
    /* ── SCROLL TRACK WRAP ── */
    .featured-scroll-wrap {
      position: relative;
      flex: 1;
      min-width: 0;
      padding: 0 5%;
    }
    
    .featured-scroll-fade-left,
    .featured-scroll-fade-right {
      position: absolute;
      top: 0; bottom: 0;
      width: 80px;
      pointer-events: none;
      z-index: 5;
      transition: opacity 0.3s;
    }
    
    .featured-scroll-fade-left {
      left: 0;
      background: linear-gradient(to right, #000, transparent);
    }
    
    .featured-scroll-fade-right {
      right: 0;
      background: linear-gradient(to left, #000, transparent);
    }
    
    .featured-scroll-track {
      display: flex;
      gap: 1.4rem;
      overflow-x: auto;
      scroll-behavior: smooth;
      padding: 0.5rem 0 1.2rem;
      scrollbar-width: none;
      -ms-overflow-style: none;
    }
    
    .featured-scroll-track::-webkit-scrollbar { display: none; }
    
    /* ── INDIVIDUAL CARD ── */
    .featured-movie-card {
      flex-shrink: 0;
      width: 200px;
      background: #121212;
      border-radius: 12px;
      overflow: hidden;
      border: 1px solid rgba(255,255,255,0.05);
      transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      cursor: pointer;
      position: relative;
    }
    
    .featured-movie-card:hover {
      transform: translateY(-8px) scale(1.02);
      border-color: rgba(255,0,60,0.4);
      box-shadow: 0 12px 28px rgba(255,0,60,0.25);
      z-index: 10;
    }
    
    .featured-movie-card img {
      width: 100%;
      height: 290px;
      object-fit: cover;
      display: block;
      transition: transform 0.4s ease;
    }
    
    .featured-movie-card:hover img { transform: scale(1.05); }
    
    /* Overlay hover */
    .featured-movie-overlay {
      position: absolute;
      inset: 0;
      background: rgba(0,0,0,0.82);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 0.6rem;
      opacity: 0;
      transition: opacity 0.25s ease;
      backdrop-filter: blur(4px);
    }
    
    .featured-movie-card:hover .featured-movie-overlay { opacity: 1; }
    
    .featured-overlay-btn {
      width: 155px;
      padding: 0.55rem 0;
      border-radius: 30px;
      border: none;
      font-family: 'Poppins', sans-serif;
      font-size: 0.82rem;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      transition: all 0.2s;
      text-decoration: none;
    }
    
    .featured-overlay-btn.primary {
      background: linear-gradient(135deg, #ff003c, #ff4d7a);
      color: #fff;
      box-shadow: 0 4px 14px rgba(255,0,60,0.4);
    }
    
    .featured-overlay-btn.secondary {
      background: rgba(255,255,255,0.1);
      color: #fff;
      border: 1px solid rgba(255,255,255,0.2);
    }
    
    .featured-overlay-btn:hover { transform: translateY(-2px); }
    
    .featured-movie-info {
      padding: 0.9rem 1rem;
    }
    
    .featured-movie-title {
      font-size: 0.92rem;
      font-weight: 600;
      color: #fff;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      margin-bottom: 4px;
    }
    
    .featured-movie-rating {
      font-size: 0.82rem;
      color: rgba(255,255,255,0.7);
      display: flex;
      align-items: center;
      gap: 4px;
    }
    
    .featured-loading {
      color: #ff003c;
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 2rem 0;
      font-size: 1rem;
    }
    
    /* ── RESPONSIVE ── */
    @media (max-width: 768px) {
      .scroll-side-btn { width: 36px; height: 36px; font-size: 0.8rem; }
      .scroll-side-left  { left: 4px; }
      .scroll-side-right { right: 4px; }
      .featured-movie-card { width: 150px; }
      .featured-movie-card img { height: 220px; }
    }
  </style>
</head>

<body>
  <div class="toast-container" id="toastContainer"></div>
  <div class="particles" id="particles"></div>

  <nav class="navbar">
    <div class="logo-container">
      <a href="#" class="logo-text">PoncolVerse</a>
    </div>
    
    <ul class="nav-links">
      <li><a href="#beranda">Beranda</a></li>
      <li><a href="#film">Film Populer</a></li>
      <li class="genre-dropdown">
        <a href="#">Genre <i class="fas fa-chevron-down" style="font-size: 0.8rem;"></i></a>
        <div class="genre-menu">
          <div class="genre-item" onclick="filterAllMoviesByGenre('Action')">Action</div>
          <div class="genre-item" onclick="filterAllMoviesByGenre('Adventure')">Adventure</div>
          <div class="genre-item" onclick="filterAllMoviesByGenre('Sci-Fi')">Sci-Fi</div>
          <div class="genre-item" onclick="filterAllMoviesByGenre('Drama')">Drama</div>
          <div class="genre-item" onclick="filterAllMoviesByGenre('Fantasy')">Fantasy</div>
          <div class="genre-item" onclick="filterAllMoviesByGenre('Comedy')">Comedy</div>
          <div class="genre-item" onclick="filterAllMoviesByGenre('Horror')">Horror</div>
          <div class="genre-item" onclick="filterAllMoviesByGenre('Romance')">Romance</div>
          <div class="genre-item" onclick="filterAllMoviesByGenre('Thriller')">Thriller</div>
          <div class="genre-item" onclick="filterAllMoviesByGenre('Crime')">Crime</div>
          <div class="genre-item" onclick="filterAllMoviesByGenre('Mystery')">Mystery</div>
          <div class="genre-item" onclick="filterAllMoviesByGenre('Animation')">Animation</div>
        </div>
      </li>
      <li><a href="#semua-film">Semua Film</a></li>
      <?php if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin'): ?>
        <li><a href="#paket">Paket</a></li>
      <?php endif; ?>
      <li><a href="#tentang">Tentang</a></li>
    </ul>

    <div class="nav-actions">
      <button class="burger-btn" onclick="openDrawer()" aria-label="Menu">
        <i class="fas fa-bars"></i>
      </button>
      <div class="search-container">
        <i class="fas fa-search search-icon"></i>
        <input type="text" class="search-input" id="searchInput" placeholder="Cari film...">
      </div>

      <?php if (isset($_SESSION['user_id'])): ?>
        <button class="watchlist-btn" onclick="openWatchlist()" title="Watchlist Saya">
          <i class="fas fa-bookmark"></i>
          <span class="watchlist-count" id="watchlistCount">0</span>
        </button>
        <!-- Tombol History (dari fitur patch sebelumnya) -->
        <button class="history-btn" onclick="openHistory()" title="Riwayat Nonton">
          <i class="fas fa-history"></i>
        </button>
      <?php endif; ?>

      <div id="authButtonContainer">
        <?php if (isset($_SESSION['user_id'])): ?>
          <div class="user-action-group">
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
              <a href="admin/index-admin.php" class="btn-admin-icon" title="Admin Panel">
                <i class="fas fa-cog"></i>
              </a>
            <?php endif; ?>
            <div class="user-profile">
              <?php if (!empty($_SESSION['profile_photo'])): ?>
                <img src="<?php echo $_SESSION['profile_photo']; ?>" alt="Profile" class="user-avatar-img">
              <?php else: ?>
                <div class="user-avatar">
                  <?php echo strtoupper(substr($_SESSION['user_firstName'], 0, 1) . substr($_SESSION['user_lastName'], 0, 1)); ?>
                </div>
              <?php endif; ?>
              <div class="user-dropdown">
                <div class="user-info">
                  <?php if (!empty($_SESSION['profile_photo'])): ?>
                    <img src="<?php echo $_SESSION['profile_photo']; ?>" alt="Profile" class="user-avatar-img">
                  <?php else: ?>
                    <div class="user-avatar">
                      <?php echo strtoupper(substr($_SESSION['user_firstName'], 0, 1) . substr($_SESSION['user_lastName'], 0, 1)); ?>
                    </div>
                  <?php endif; ?>
                  <h3><?php echo $_SESSION['user_firstName'] . ' ' . $_SESSION['user_lastName']; ?></h3>
                  <button class="btn-change-photo" onclick="openPhotoUpload()" title="Change Profile Photo">
                    <i class="fas fa-camera"></i>
                  </button>
                </div>
                <div class="user-details">
                  <div class="user-detail">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value"><?php echo $_SESSION['user_email']; ?></span>
                  </div>
                  <div class="user-detail">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value"><?php echo $_SESSION['user_status']; ?></span>
                  </div>
                  <div class="user-detail">
                    <span class="detail-label">Subscription:</span>
                    <span class="detail-value" style="<?php echo (isset($_SESSION['subscription_status']) && $_SESSION['subscription_status'] === 'active') ? 'color: #00ff88;' : ''; ?>">
                      <?php 
                        if (isset($_SESSION['subscription_status']) && $_SESSION['subscription_status'] === 'active') {
                          echo isset($_SESSION['subscription_plan_name']) ? $_SESSION['subscription_plan_name'] : 'Active';
                        } else {
                          echo 'None';
                        }
                      ?>
                    </span>
                  </div>
                  <div class="user-detail">
                    <span class="detail-label">Subs End:</span>
                    <span class="detail-value">
                      <?php 
                        if (isset($_SESSION['subscription_end']) && !empty($_SESSION['subscription_end']) && $_SESSION['subscription_status'] === 'active') {
                          $end_ts   = strtotime($_SESSION['subscription_end']);
                          $now_ts   = time();
                          $diff_days = (int)ceil(($end_ts - $now_ts) / 86400);
                          echo date('d M Y', $end_ts);
                          if ($diff_days > 0)     echo ' <span style="color:#ff9800;font-size:0.8rem;">(' . $diff_days . ' hari lagi)</span>';
                          elseif ($diff_days === 0) echo ' <span style="color:#ff003c;font-size:0.8rem;">(Hari ini berakhir!)</span>';
                        } else {
                          echo '-';
                        }
                      ?>
                    </span>
                  </div>
                  <div class="user-detail">
                    <span class="detail-label">Bergabung:</span>
                    <span class="detail-value"><?php echo $_SESSION['user_joinDate']; ?></span>
                  </div>
                </div>
                <a href="auth/logout.php" class="logout-btn">Keluar</a>
              </div>
            </div>
          </div>
        <?php else: ?>
          <button class="btn-login" onclick="openLogin()">Login</button>
        <?php endif; ?>
      </div>
    </div>
  </nav>

  </nav>

  <!-- ── MOBILE DRAWER ── -->
  <div class="drawer-overlay" id="drawerOverlay" onclick="closeDrawer()"></div>
  <div class="mobile-drawer" id="mobileDrawer">
  
    <div class="drawer-header">
      <span class="drawer-logo">PoncolVerse</span>
      <button class="drawer-close" onclick="closeDrawer()">&#x2715;</button>
    </div>

    <!-- Search -->
    <div class="drawer-search">
      <div class="drawer-search-wrap">
        <i class="fas fa-search"></i>
        <input type="text" class="drawer-search-input" id="drawerSearchInput" placeholder="Cari film...">
      </div>
    </div>

    <!-- Nav Links -->
    <div class="drawer-nav">
      <a href="#beranda" class="drawer-nav-item" onclick="closeDrawer()">
        <i class="fas fa-home"></i> Beranda
      </a>
      <a href="#film" class="drawer-nav-item" onclick="closeDrawer()">
        <i class="fas fa-fire"></i> Film Populer
      </a>
      <a href="#semua-film" class="drawer-nav-item" onclick="closeDrawer()">
        <i class="fas fa-film"></i> Semua Film
      </a>
      <?php if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin'): ?>
      <a href="#paket" class="drawer-nav-item" onclick="closeDrawer()">
        <i class="fas fa-crown"></i> Paket
      </a>
      <?php endif; ?>
      <a href="#tentang" class="drawer-nav-item" onclick="closeDrawer()">
        <i class="fas fa-info-circle"></i> Tentang
      </a>

      <!-- Genre dropdown -->
      <button class="drawer-genre-toggle" onclick="toggleDrawerGenre(this)">
        <span><i class="fas fa-tags" style="margin-right:0.6rem;color:#666;font-size:0.95rem;"></i>Genre</span>
        <i class="fas fa-chevron-down chevron"></i>
      </button>
      <div class="drawer-genre-list" id="drawerGenreList">
        <?php
        $genres = ['Action','Adventure','Sci-Fi','Drama','Fantasy','Comedy','Horror','Romance','Thriller','Crime','Mystery','Animation'];
        foreach ($genres as $g):
        ?>
        <button class="drawer-genre-item" onclick="filterAllMoviesByGenre('<?php echo $g; ?>'); closeDrawer(); document.getElementById('semua-film').scrollIntoView({behavior:'smooth'});">
          <?php echo $g; ?>
        </button>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if (isset($_SESSION['user_id'])): ?>
    <!-- User section -->
    <div class="drawer-user-section">
      <div class="drawer-user-card">
        <div class="drawer-avatar">
          <?php if (!empty($_SESSION['profile_photo'])): ?>
            <img src="<?php echo $_SESSION['profile_photo']; ?>" alt="Profile">
          <?php else: ?>
            <?php echo strtoupper(substr($_SESSION['user_firstName'], 0, 1) . substr($_SESSION['user_lastName'], 0, 1)); ?>
          <?php endif; ?>
        </div>
        <div class="drawer-user-info">
          <strong><?php echo $_SESSION['user_firstName'] . ' ' . $_SESSION['user_lastName']; ?></strong>
          <span><?php echo $_SESSION['user_email']; ?></span>
          <?php if (isset($_SESSION['subscription_status']) && $_SESSION['subscription_status'] === 'active'): ?>
            <div class="drawer-subs-badge"><?php echo $_SESSION['subscription_plan_name'] ?? 'Active'; ?></div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Actions -->
      <div class="drawer-nav" style="padding:0;border:none;">
        <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <a href="admin/index-admin.php" class="drawer-nav-item admin-item">
          <i class="fas fa-cog"></i> Admin Panel
        </a>
        <?php endif; ?>
        <button class="drawer-nav-item" onclick="openWatchlist(); closeDrawer();">
          <i class="fas fa-bookmark"></i> Watchlist
        </button>
        <button class="drawer-nav-item" onclick="openHistory(); closeDrawer();">
          <i class="fas fa-history"></i> Riwayat Nonton
        </button>
        <button class="drawer-nav-item" onclick="openPhotoUpload(); closeDrawer();">
          <i class="fas fa-camera"></i> Ganti Foto Profil
        </button>
      </div>

      <a href="auth/logout.php" class="drawer-logout">
        <i class="fas fa-sign-out-alt"></i> Keluar
      </a>
    </div>

    <?php else: ?>
    <!-- Guest -->
    <button class="drawer-login-btn" onclick="openLogin(); closeDrawer();">
      Login ke PoncolVerse
    </button>
    <?php endif; ?>

  </div>
  <!-- END MOBILE DRAWER -->

  <!-- =============================================
       HERO SECTION — NEW DESIGN
       ============================================= -->
  <header class="hero" id="beranda">

    <!-- Glow orbs -->
    <div class="hero-glow-left"></div>
    <div class="hero-glow-right"></div>
    <div class="hero-scanline"></div>

    <!-- Poster strip kanan (miring) -->
    <div class="hero-poster-strip">
      <div class="hero-poster-fade-top"></div>
      <div class="hero-poster-fade-bottom"></div>
      <div class="hero-poster-fade-left"></div>

      <?php
      // Render 3 kolom poster
      $cols = [$hero_col1, $hero_col2, $hero_col3];
      foreach ($cols as $col_idx => $col):
      ?>
        <div class="hero-poster-col">
          <?php foreach ($col as $p_idx => $p):
            // Tandai film pertama di kolom pertama sebagai "Hot"
            $is_hot = ($col_idx === 0 && $p_idx === 0);
            $rating = $use_fallback ? $p['rating'] : number_format((float)$p['rating'], 1);
            $poster = $p['poster'];
            $title  = htmlspecialchars($p['title']);
          ?>
            <div class="hero-poster-card">
              <img src="<?php echo $poster; ?>" alt="<?php echo $title; ?>"
                   loading="lazy"
                   onerror="this.src='https://via.placeholder.com/110x162?text=No+Image'">
              <div class="hero-poster-rating">★ <?php echo $rating; ?></div>
              <?php if ($is_hot): ?>
                <div class="hero-poster-hot">Hot</div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Hero content (kiri) -->
    <div class="hero-content-wrap">
      <div class="hero-eyebrow">
        <div class="hero-eyebrow-dot"></div>
        Platform Streaming Terbaik
      </div>

      <h1 class="hero-title-new">
        Dunia Film
        <span class="hero-accent">Ada Di Sini.</span>
      </h1>

      <p class="hero-desc-new">
        Ribuan film berkualitas tinggi — dari blockbuster hingga indie terbaik — siap ditonton kapan saja. Tanpa batas, tanpa kompromi.
      </p>

      <div class="hero-cta-row">
        <button class="btn-hero-primary" onclick="scrollToMovies()">
          <div class="btn-hero-play-icon">▶</div>
          Mulai Nonton
        </button>
        <button class="btn-hero-secondary" onclick="document.getElementById('semua-film').scrollIntoView({behavior:'smooth'})">
          Lihat Koleksi <i class="fas fa-arrow-right" style="font-size:0.8rem;"></i>
        </button>
      </div>

      <div class="hero-stats-row">
        <div class="hero-stat-item">
          <div class="hero-stat-num">1K<span>+</span></div>
          <div class="hero-stat-lbl">Film</div>
        </div>
        <div class="hero-stat-item">
          <div class="hero-stat-num">50K<span>+</span></div>
          <div class="hero-stat-lbl">Pengguna</div>
        </div>
        <div class="hero-stat-item">
          <div class="hero-stat-num">4.9<span>★</span></div>
          <div class="hero-stat-lbl">Rating</div>
        </div>
      </div>
    </div>

  </header>
  <!-- END HERO SECTION -->

<section class="movies-featured" id="film">
  
  <h2 class="movies-featured-title">Film Populer Hari Ini</h2>
  
  <!-- Wrapper yang menampung tombol + track -->
  <div class="featured-scroll-outer">
  
    <!-- Tombol KIRI -->
    <button class="scroll-side-btn scroll-side-left" id="scrollLeft"
            onclick="scrollFeatured(-1)" aria-label="Scroll kiri">
      <i class="fas fa-chevron-left"></i>
    </button>
  
    <!-- Track film -->
    <div class="featured-scroll-wrap">
      <div class="featured-scroll-fade-left"  id="fadeFeaturedLeft"></div>
      <div class="featured-scroll-fade-right" id="fadeFeaturedRight"></div>
      <div class="featured-scroll-track" id="featuredTrack">
        <div class="featured-loading">
          <div class="spinner"></div> Memuat film populer...
        </div>
      </div>
    </div>
  
    <!-- Tombol KANAN -->
    <button class="scroll-side-btn scroll-side-right" id="scrollRight"
            onclick="scrollFeatured(1)" aria-label="Scroll kanan">
      <i class="fas fa-chevron-right"></i>
    </button>
  
  </div>
</section>

  <section class="all-movies" id="semua-film">
    <h2>Semua Film</h2>
    <div class="movie-grid" id="allMoviesGrid">
      <div class="loading">
        <div class="spinner"></div>Memuat semua film...
      </div>
    </div>
  </section>

  <?php
  $hasActiveSubscription = isset($_SESSION['subscription_status']) && $_SESSION['subscription_status'] === 'active';
  $isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
  
  if (!$isAdmin && !$hasActiveSubscription):
    $sql_plans   = "SELECT * FROM subscription_plans ORDER BY price ASC";
    $plans_result = $conn->query($sql_plans);
    $iconMap = [
      'Basic'    => 'fa-play-circle',
      'Standard' => 'fa-star',
      'Premium'  => 'fa-crown'
    ];
    ?>
    <section class="pricing-section" id="paket">
      <h2>Pilih Paket Berlangganan</h2>
      <p class="pricing-subtitle">Nikmati pengalaman menonton tanpa batas dengan berbagai keuntungan eksklusif</p>
      <div class="pricing-cards">
        <?php
        if ($plans_result && $plans_result->num_rows > 0):
          while ($plan = $plans_result->fetch_assoc()):
            $features      = explode(',', $plan['features']);
            $isRecommended = $plan['name'] === 'Standard';
            ?>
            <div class="pricing-card <?php echo $isRecommended ? 'recommended' : ''; ?>">
              <?php if ($isRecommended): ?>
                <div class="recommended-badge"><i class="fas fa-fire" style="margin-right: 4px;"></i> Terpopuler</div>
              <?php endif; ?>
              <div class="plan-icon">
                <i class="fas <?php echo $iconMap[$plan['name']] ?? 'fa-film'; ?>"></i>
              </div>
              <h3 class="plan-name"><?php echo htmlspecialchars($plan['name']); ?></h3>
              <div class="plan-price">
                Rp<?php echo number_format($plan['price'], 0, ',', '.'); ?>
                <span>/bulan</span>
              </div>
              <p class="plan-duration"><?php echo $plan['duration_days']; ?> hari akses</p>
              <ul class="plan-features">
                <?php foreach ($features as $feature): ?>
                  <li>
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars(trim($feature)); ?></span>
                  </li>
                <?php endforeach; ?>
              </ul>
              <button class="subscribe-btn"
                onclick="subscribePlan(<?php echo $plan['id']; ?>, '<?php echo htmlspecialchars($plan['name']); ?>', <?php echo $plan['price']; ?>)">
                Berlangganan Sekarang
              </button>
            </div>
            <?php
          endwhile;
        else: ?>
          <div class="no-movies">Tidak ada paket tersedia</div>
        <?php endif; ?>
      </div>
    </section>
  <?php endif; ?>

  <section class="about" id="tentang">
    <div class="about-container">
      <div class="about-header" data-aos="fade-up">
        <h2>Tentang PoncolVerse</h2>
        <p class="about-subtitle">Platform streaming film terdepan di Indonesia</p>
      </div>
      <div class="about-main-content">
        <div class="about-left" data-aos="fade-right">
          <div class="about-image-wrapper">
            <div class="image-glow"></div>
            <img src="assets/images/branding.png" alt="PoncolVerse Experience" class="about-main-image">
            <div class="image-overlay"></div>
          </div>
          <div class="about-stats">
            <div class="stat-card" data-aos="zoom-in" data-aos-delay="100">
              <div class="stat-icon"><i class="fas fa-film"></i></div>
              <div class="stat-number" data-count="1000">0</div>
              <div class="stat-label">Film Terlengkap</div>
            </div>
            <div class="stat-card" data-aos="zoom-in" data-aos-delay="200">
              <div class="stat-icon"><i class="fas fa-users"></i></div>
              <div class="stat-number" data-count="5000">0</div>
              <div class="stat-label">Pengguna Aktif</div>
            </div>
            <div class="stat-card" data-aos="zoom-in" data-aos-delay="300">
              <div class="stat-icon"><i class="far fa-star"></i></i></div>
              <div class="stat-number" data-count="4.6">0</div>
              <div class="stat-label">Rating Platform</div>
            </div>
          </div>
        </div>
        <div class="about-right" data-aos="fade-left">
          <div class="about-description">
            <p class="about-text-intro">
              PoncolVerse adalah platform streaming film terdepan di Indonesia yang menyajikan pengalaman menonton terbaik
              dengan kualitas sinematik tinggi. Kami menyediakan berbagai film dari berbagai genre untuk memenuhi kebutuhan hiburan Anda.
            </p>
            <p class="about-text-detail">
              Dengan antarmuka yang user-friendly dan koleksi film yang terus diperbarui, PoncolVerse menjadi pilihan utama
              bagi para pecinta film untuk menikmati konten berkualitas kapan saja dan di mana saja.
            </p>
          </div>
          <div class="about-features-grid">
            <div class="feature-card" data-aos="flip-left" data-aos-delay="100">
              <div class="feature-card-icon"><i class="fas fa-film"></i></div>
              <h3 class="feature-card-title">Kualitas HD & 4K</h3>
              <p class="feature-card-desc">Nikmati film dengan kualitas gambar terbaik hingga 4K resolution</p>
            </div>
            <div class="feature-card" data-aos="flip-left" data-aos-delay="200">
              <div class="feature-card-icon"><i class="fas fa-mobile-alt"></i></div>
              <h3 class="feature-card-title">Akses Multi-Device</h3>
              <p class="feature-card-desc">Tonton di smartphone, tablet, laptop, atau smart TV Anda</p>
            </div>
            <div class="feature-card" data-aos="flip-left" data-aos-delay="300">
              <div class="feature-card-icon"><i class="fas fa-sync"></i></div>
              <h3 class="feature-card-title">Update Konten Rutin</h3>
              <p class="feature-card-desc">Film baru ditambahkan setiap minggu untuk Anda</p>
            </div>
            <div class="feature-card" data-aos="flip-left" data-aos-delay="400">
              <div class="feature-card-icon"><i class="fas fa-download"></i></div>
              <h3 class="feature-card-title">Download Offline</h3>
              <p class="feature-card-desc">Download dan tonton film favorit tanpa koneksi internet</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- TESTIMONIALS SECTION -->
  <section class="testimonials-section" id="testimonials">
    <div class="testimonials-header-container">
      <h2>Apa Kata Mereka?</h2>
      <p class="testimonials-subtitle">Testimoni dari pengguna setia PoncolVerse</p>
      <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
        <a href="admin/testimonials/testimonials-admin.php" class="btn-admin-testimonials">
          <i class="fas fa-cog"></i> Kelola Testimonials
        </a>
      <?php endif; ?>
    </div>
    <?php
    $testimonials_sql = "SELECT wt.*, u.firstName, u.lastName 
                          FROM website_testimonials wt
                          LEFT JOIN users u ON wt.user_id = u.id
                          WHERE wt.is_approved = 1
                          ORDER BY wt.created_at DESC
                          LIMIT 10";
    $testimonials_result = $conn->query($testimonials_sql);
    $testimonials = [];
    if ($testimonials_result && $testimonials_result->num_rows > 0) {
      while ($row = $testimonials_result->fetch_assoc()) {
        $testimonials[] = $row;
      }
    }
    $row1 = array_slice($testimonials, 0, 5);
    $row2 = array_slice($testimonials, 5, 5);
    ?>

    <?php if (count($testimonials) > 0): ?>
      <div class="testimonials-marquee marquee-ltr">
        <div class="testimonials-track">
          <?php foreach (array_merge($row1, $row1) as $testimonial): ?>
            <div class="testimonial-card-marquee">
              <div class="testimonial-stars">
                <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?><i class="fas fa-star"></i><?php endfor; ?>
              </div>
              <p class="testimonial-text">"<?php echo htmlspecialchars($testimonial['message']); ?>"</p>
              <div class="testimonial-author">
                <div class="author-avatar">
                  <?php echo strtoupper(substr($testimonial['firstName'] ?? 'U', 0, 1) . substr($testimonial['lastName'] ?? 'S', 0, 1)); ?>
                </div>
                <div class="author-info">
                  <strong><?php echo htmlspecialchars($testimonial['user_name']); ?></strong>
                  <small>Pengguna PoncolVerse</small>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <?php if (count($row2) > 0): ?>
        <div class="testimonials-marquee marquee-rtl">
          <div class="testimonials-track">
            <?php foreach (array_merge($row2, $row2) as $testimonial): ?>
              <div class="testimonial-card-marquee">
                <div class="testimonial-stars">
                  <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?><i class="fas fa-star"></i><?php endfor; ?>
                </div>
                <p class="testimonial-text">"<?php echo htmlspecialchars($testimonial['message']); ?>"</p>
                <div class="testimonial-author">
                  <div class="author-avatar">
                    <?php echo strtoupper(substr($testimonial['firstName'] ?? 'U', 0, 1) . substr($testimonial['lastName'] ?? 'S', 0, 1)); ?>
                  </div>
                  <div class="author-info">
                    <strong><?php echo htmlspecialchars($testimonial['user_name']); ?></strong>
                    <small>Pengguna PoncolVerse</small>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <div class="add-testimonial-container">
      <h3>Bagikan Pengalaman Anda!</h3>
      <p>Ceritakan pengalaman menonton di PoncolVerse</p>
      <?php if (isset($_SESSION['user_id'])): ?>
        <form id="testimonialForm" class="testimonial-form">
          <div class="form-group">
            <div class="rating-label-wrapper">
              <i class="fas fa-star"></i>
              <label>Rating Anda</label>
            </div>
            <select id="testimonialRating" required>
              <option value="">Pilih Rating</option>
              <option value="5">⭐⭐⭐⭐⭐ - Luar Biasa!</option>
              <option value="4">⭐⭐⭐⭐ - Sangat Bagus</option>
              <option value="3">⭐⭐⭐ - Bagus</option>
              <option value="2">⭐⭐ - Cukup</option>
              <option value="1">⭐ - Kurang</option>
            </select>
          </div>
          <div class="form-group">
            <label>Pesan Anda</label>
            <textarea id="testimonialMessage" rows="5" placeholder="Tulis pengalaman Anda menggunakan PoncolVerse..." required></textarea>
          </div>
          <button type="submit" class="btn-submit-testimonial">
            <i class="fas fa-paper-plane"></i> Kirim Testimonial
          </button>
        </form>
      <?php else: ?>
        <p style="text-align: center; color: #aaa; padding: 2rem;">
          <a href="#" onclick="openLogin(); return false;" style="color: #ff003c; font-weight: 600;">Login</a> untuk memberikan testimonial
        </p>
      <?php endif; ?>
    </div>
  </section>

  <footer class="footer">
    <div class="footer-content">
      <div class="footer-column">
        <h3>PoncolVerse</h3>
        <p style="color: #aaa; line-height: 1.6;">Platform streaming film terbaik dengan pengalaman menonton yang tak terlupakan.</p>
        <div class="social-links">
          <a class="social-link"><i class="fab fa-facebook-f"></i></a>
          <a class="social-link"><i class="fab fa-twitter"></i></a>
          <a class="social-link"><i class="fab fa-instagram"></i></a>
          <a class="social-link"><i class="fab fa-youtube"></i></a>
        </div>
      </div>
      <div class="footer-column">
        <h3>Menu</h3>
        <ul class="footer-links">
          <li><a>Beranda</a></li>
          <li><a>Film</a></li>
          <li><a>Semua Film</a></li>
          <li><a>Tentang</a></li>
          <li><a>Kontak</a></li>
        </ul>
      </div>
      <div class="footer-column">
        <h3>Genre</h3>
        <ul class="footer-links">
          <li><a>Action</a></li>
          <li><a>Adventure</a></li>
          <li><a>Sci-Fi</a></li>
          <li><a>Drama</a></li>
          <li><a>Fantasy</a></li>
        </ul>
      </div>
      <div class="footer-column">
        <h3>Bantuan</h3>
        <ul class="footer-links">
          <li><a>FAQ</a></li>
          <li><a>Cara Berlangganan</a></li>
          <li><a>Pusat Bantuan</a></li>
          <li><a>Syarat & Ketentuan</a></li>
          <li><a>Kebijakan Privasi</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2025 PoncolVerse. All rights reserved. Made with <i class="fas fa-heart" style="color: #ff003c;"></i> for movie lovers.</p>
    </div>
  </footer>

  <!-- Login Modal -->
  <div id="loginModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeLogin()">&times;</span>
      <h2 style="text-align:center; color:#ff003c; margin-bottom:1rem;">Login ke PoncolVerse</h2>
      <form id="loginForm">
        <div class="form-group">
          <label for="loginEmail">Email</label>
          <input type="email" id="loginEmail" required>
          <div class="error-message" id="loginEmailError">Email tidak valid</div>
        </div>
        <div class="form-group">
          <label for="loginPassword">Password</label>
          <input type="password" id="loginPassword" required>
          <div class="error-message" id="loginPasswordError">Password salah</div>
        </div>
        <button type="submit" class="form-btn primary">Masuk</button>
      </form>
      <!-- Tombol Login Google -->
      <div style="text-align:center; margin-top:1rem;">
        <a href="login-google/google-login.php"
           style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.7rem 1.5rem;background:white;color:#333;border-radius:30px;text-decoration:none;font-weight:600;box-shadow:0 2px 10px rgba(0,0,0,0.3);font-size:0.9rem;">
          <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" width="18"> Login dengan Google
        </a>
      </div>
      <div class="form-footer">
        <p>Belum punya akun? <a id="showRegister">Daftar sekarang</a></p>
      </div>
    </div>
  </div>

  <!-- Register Modal -->
  <div id="registerModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeRegister()">&times;</span>
      <h2 style="text-align:center; color:#ff003c; margin-bottom:1rem;">Daftar ke PoncolVerse</h2>
      <form id="registerForm">
        <div class="form-group">
          <label for="firstName">Nama Depan</label>
          <input type="text" id="firstName" required>
          <div class="error-message" id="firstNameError">Nama depan harus diisi</div>
        </div>
        <div class="form-group">
          <label for="lastName">Nama Belakang</label>
          <input type="text" id="lastName" required>
          <div class="error-message" id="lastNameError">Nama belakang harus diisi</div>
        </div>
        <div class="form-group">
          <label for="registerEmail">Email</label>
          <input type="email" id="registerEmail" required>
          <div class="error-message" id="registerEmailError">Email tidak valid</div>
        </div>
        <div class="form-group">
          <label for="registerPassword">Password</label>
          <input type="password" id="registerPassword" required>
          <div class="error-message" id="registerPasswordError">Password harus minimal 6 karakter</div>
        </div>
        <div class="form-group">
          <label for="recoveryEmail">Email Pemulihan</label>
          <input type="email" id="recoveryEmail" required>
          <div class="error-message" id="recoveryEmailError">Email pemulihan tidak valid</div>
        </div>
        <button type="submit" class="form-btn primary">Daftar Sekarang</button>
      </form>
      <div class="form-footer">
        <p>Sudah punya akun? <a id="showLogin">Masuk</a></p>
      </div>
    </div>
  </div>

  <!-- Trailer Modal -->
  <div id="trailerModal" class="modal">
    <div class="modal-content trailer">
      <span class="close" onclick="closeTrailer()">&times;</span>
      <div id="trailerContainer"></div>
    </div>
  </div>

  <!-- Watchlist Modal -->
  <div id="watchlistModal" class="modal">
    <div class="modal-content watchlist-modal-content">
      <div class="modal-header">
        <h2><i class="fas fa-bookmark"></i> Watchlist Saya</h2>
        <span class="close" onclick="closeWatchlist()">&times;</span>
      </div>
      <div class="watchlist-grid" id="watchlistGrid">
        <div class="loading"><div class="spinner"></div>Memuat watchlist...</div>
      </div>
    </div>
  </div>

  <!-- History Modal (dari fitur patch sebelumnya) -->
  <div id="historyModal" class="modal">
    <div class="modal-content watchlist-modal-content">
      <div class="modal-header">
        <h2><i class="fas fa-history"></i> Riwayat Nonton</h2>
        <span class="close" onclick="closeHistory()">&times;</span>
      </div>
      <div class="watchlist-grid" id="historyGrid">
        <div class="loading"><div class="spinner"></div>Memuat riwayat...</div>
      </div>
    </div>
  </div>

  <!-- Share Modal -->
  <div id="shareModal" class="modal">
    <div class="modal-content share-modal-content">
      <span class="close" onclick="closeShare()">&times;</span>
      <h2 style="text-align:center; color:#ff003c; margin-bottom:1.5rem;">
        <i class="fas fa-share-alt"></i> Bagikan Film
      </h2>
      <div class="share-content">
        <div class="share-link-container">
          <input type="text" id="shareLink" readonly class="share-link-input">
          <button class="copy-btn" onclick="copyShareLink()">
            <i class="fas fa-copy"></i> Copy
          </button>
        </div>
        <div class="share-social">
          <button class="share-social-btn whatsapp" onclick="shareToWhatsApp()">
            <i class="fab fa-whatsapp"></i> WhatsApp
          </button>
          <button class="share-social-btn facebook" onclick="shareToFacebook()">
            <i class="fab fa-facebook"></i> Facebook
          </button>
          <button class="share-social-btn twitter" onclick="shareToTwitter()">
            <i class="fab fa-twitter"></i> Twitter
          </button>
          <button class="share-social-btn story" onclick="openStoryFromShare()">
            <i class="fas fa-image"></i> Story Card
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Profile Photo Upload Modal -->
  <div id="photoUploadModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closePhotoUpload()">&times;</span>
      <h2 style="text-align:center; color:#ff003c; margin-bottom:1.5rem;">
        <i class="fas fa-camera"></i> Update Profile Photo
      </h2>
      <div class="photo-upload-container">
        <div class="photo-preview-wrapper">
          <div class="photo-preview" id="photoPreview">
            <?php if (!empty($_SESSION['profile_photo'])): ?>
              <img src="<?php echo $_SESSION['profile_photo']; ?>" alt="Current Photo" id="previewImage">
            <?php else: ?>
              <div class="photo-placeholder">
                <i class="fas fa-user"></i>
                <p>No photo yet</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <form id="photoUploadForm" enctype="multipart/form-data">
          <div class="file-input-wrapper">
            <input type="file" id="photoInput" name="profile_photo" accept="image/*" required>
            <label for="photoInput" class="file-input-label">
              <i class="fas fa-upload"></i> Choose Photo
            </label>
          </div>
          <p class="file-info">Max size: 2MB. Supported: JPG, PNG, GIF, WEBP</p>
          <button type="submit" class="btn-upload-photo" id="uploadPhotoBtn">
            <i class="fas fa-check"></i> Upload Photo
          </button>
        </form>
      </div>
    </div>
  </div>

  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script src="assets/js/main.js"></script>

  <script>
  // ── MOBILE DRAWER ──
  function openDrawer() {
    document.getElementById('mobileDrawer').classList.add('open');
    document.getElementById('drawerOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function closeDrawer() {
    document.getElementById('mobileDrawer').classList.remove('open');
    document.getElementById('drawerOverlay').classList.remove('open');
    document.body.style.overflow = '';
  }
  function toggleDrawerGenre(btn) {
    btn.classList.toggle('open');
    document.getElementById('drawerGenreList').classList.toggle('open');
  }

  // Drawer search — mirror ke main search & trigger scroll
  document.getElementById('drawerSearchInput').addEventListener('input', function(e) {
    const val = e.target.value;
    const mainInput = document.getElementById('searchInput');
    if (mainInput) {
      mainInput.value = val;
      mainInput.dispatchEvent(new Event('input'));
    }
    if (val.trim().length >= 2) closeDrawer();
  });

  // Close drawer on ESC
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDrawer(); });
  </script>

  <!-- History JS (dari fitur patch sebelumnya) -->
  <script>
  async function openHistory() {
    const modal = document.getElementById('historyModal');
    if (!modal) return;
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    const grid = document.getElementById('historyGrid');
    grid.innerHTML = '<div class="loading"><div class="spinner"></div>Memuat riwayat...</div>';
    try {
      const res    = await fetch('API/watchhistory/get_watch_history.php');
      const movies = await res.json();
      if (!movies || movies.length === 0) {
        grid.innerHTML = '<div class="no-movies">Belum ada riwayat nonton. Klik "Tonton Film" untuk mulai!</div>';
        return;
      }
      grid.innerHTML = movies.map(m => {
        const date    = new Date(m.watched_at);
        const dateStr = date.toLocaleDateString('id-ID', { day:'numeric', month:'short', year:'numeric' });
        return `
          <div class="watchlist-item" onclick="window.location.href='movie-detail.php?id=${m.id}'">
            <img src="${m.poster}" alt="${m.title}" onerror="this.src='https://via.placeholder.com/200x300?text=No+Image'">
            <div class="watchlist-item-info">
              <div class="watchlist-item-title">${m.title}</div>
              <div class="watchlist-item-meta">
                <span>⭐ ${m.rating}</span>
                <span>${m.year}</span>
              </div>
              <div style="font-size:0.75rem;color:#888;margin-top:0.3rem;">
                <i class="fas fa-clock" style="margin-right:3px;"></i>${dateStr}
              </div>
            </div>
          </div>`;
      }).join('');
    } catch (e) {
      grid.innerHTML = '<div class="no-movies">Gagal memuat riwayat.</div>';
    }
  }
  function closeHistory() {
    const modal = document.getElementById('historyModal');
    if (modal) { modal.classList.remove('active'); document.body.style.overflow = ''; }
  }

  /* CSS tambahan untuk history-btn */
  const historyStyle = document.createElement('style');
  historyStyle.innerHTML = `
    .history-btn {
      position: relative;
      background: rgba(30,144,255,0.08);
      border: 1.5px solid rgba(30,144,255,0.25);
      border-radius: 50%;
      width: 40px; height: 40px;
      display: flex; align-items: center; justify-content: center;
      color: #1e90ff; font-size: 1.1rem;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .history-btn:hover {
      background: rgba(30,144,255,0.15);
      border-color: #1e90ff;
      transform: scale(1.05);
    }
  `;
  document.head.appendChild(historyStyle);
  </script>

  <script>
// ── FEATURED MOVIES — HORIZONTAL SCROLL ──
async function loadFeaturedMovies() {
  const track = document.getElementById('featuredTrack');
  if (!track) return;
 
  try {
    const res    = await fetch('API/movies/get_featured_movies.php');
    const movies = await res.json();
 
    if (!movies || movies.length === 0) {
      track.innerHTML = '<p style="color:#aaa;padding:2rem;">Belum ada film populer dipilih admin.</p>';
      return;
    }
  
    track.innerHTML = movies.map(m => {
      const watchBtn = (m.watchLink && m.watchLink !== '#')
        ? `<a href="javascript:void(0)"
              onclick="startWatchRedirect(event,'${m.watchLink}',${m.id})"
              class="featured-overlay-btn primary">
            <i class="fas fa-play"></i> Tonton
          </a>`
        : `<button class="featured-overlay-btn primary" style="opacity:0.4;cursor:default;">
            <i class="fas fa-play"></i> Tonton
          </button>`;
  
      return `
        <div class="featured-movie-card">
          <img src="${m.poster}" alt="${m.title}"
              onerror="this.src='https://via.placeholder.com/200x290?text=No+Image'">
          <div class="featured-movie-overlay">
            ${watchBtn}
            <button class="featured-overlay-btn secondary"
                    onclick="openTrailer('${m.trailer}')">
              <i class="fab fa-youtube"></i> Trailer
            </button>
            <button class="featured-overlay-btn secondary"
                    onclick="openMovieDetail(${m.id})">
              <i class="fas fa-info-circle"></i> Detail
            </button>
          </div>
          <div class="featured-movie-info">
            <div class="featured-movie-title">${m.title}</div>
            <div class="featured-movie-rating">
              <i class="fas fa-star" style="color:#ffc107;font-size:0.75rem;"></i>
              ${m.rating} &bull; ${m.year}
            </div>
          </div>
        </div>`;
    }).join('');
  
    // Update tombol nav + fade setelah render
    updateFeaturedNav();
  
  } catch (e) {
    console.error('Error loading featured movies:', e);
    track.innerHTML = '<p style="color:#aaa;padding:2rem;">Gagal memuat film populer.</p>';
  }
}
  
// Scroll handler
function scrollFeatured(dir) {
  const track = document.getElementById('featuredTrack');
  if (!track) return;
  // Scroll 3 card sekaligus (card width 200 + gap 22 ≈ 666px)
  track.scrollBy({ left: dir * 666, behavior: 'smooth' });
  setTimeout(updateFeaturedNav, 350);
}
  
// Update disabled state tombol & fade visibility
function updateFeaturedNav() {
  const track = document.getElementById('featuredTrack');
  const btnL  = document.getElementById('scrollLeft');
  const btnR  = document.getElementById('scrollRight');
  const fadeL = document.getElementById('fadeFeaturedLeft');
  const fadeR = document.getElementById('fadeFeaturedRight');
  if (!track) return;
  
  const atStart = track.scrollLeft <= 10;
  const atEnd   = track.scrollLeft + track.clientWidth >= track.scrollWidth - 10;
  
  if (btnL) btnL.disabled = atStart;
  if (btnR) btnR.disabled = atEnd;
  if (fadeL) fadeL.style.opacity = atStart ? '0' : '1';
  if (fadeR) fadeR.style.opacity = atEnd   ? '0' : '1';
}
  
// Listen scroll untuk update nav realtime
document.addEventListener('DOMContentLoaded', () => {
  const track = document.getElementById('featuredTrack');
  if (track) {
    track.addEventListener('scroll', updateFeaturedNav);
    // Touch/drag support
    let isDown = false, startX, scrollStart;
    track.addEventListener('mousedown', e => {
      isDown = true; startX = e.pageX; scrollStart = track.scrollLeft;
      track.style.cursor = 'grabbing';
    });
    track.addEventListener('mouseleave', () => { isDown = false; track.style.cursor = ''; });
    track.addEventListener('mouseup',    () => { isDown = false; track.style.cursor = ''; });
    track.addEventListener('mousemove',  e => {
      if (!isDown) return;
      e.preventDefault();
      track.scrollLeft = scrollStart - (e.pageX - startX);
    });
  }
});
  
// Panggil saat load
window.addEventListener('load', loadFeaturedMovies);
</script>
</body>
</html>