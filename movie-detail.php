<?php
require_once 'config/config.php';

// Get movie ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
  header('Location: index.php');
  exit;
}

$movie_id = intval($_GET['id']);

// HANYA AMBIL DARI DATABASE (NO TMDb!)
$sql = "SELECT * FROM movies WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$result = $stmt->get_result();
$movie = $result->fetch_assoc();

// Kalau film tidak ada, redirect
if (!$movie) {
  header('Location: index.php');
  exit;
}

// Parse genre
$genre_array = json_decode($movie['genre'], true);
if (!is_array($genre_array)) {
  $genre_array = array_map('trim', explode(',', $movie['genre']));
}
$movie['genre'] = implode(', ', $genre_array);

// Get cast members
$cast_array = [];
$sqlCast = "SELECT actor_name, actor_photo, character_name FROM cast_members WHERE movie_id = ?";
$stmtCast = $conn->prepare($sqlCast);
if ($stmtCast) {
  $stmtCast->bind_param("i", $movie_id);
  $stmtCast->execute();
  $resultCast = $stmtCast->get_result();
  while ($castRow = $resultCast->fetch_assoc()) {
    $cast_array[] = [
      'name' => $castRow['actor_name'],
      'photo' => $castRow['actor_photo'],
      'character' => $castRow['character_name'] ?? ''
    ];
  }
  $stmtCast->close();
}
$movie['cast_array'] = $cast_array;

// Set backdrop (kalau gak ada, pake poster)
if (empty($movie['backdrop'])) {
  $movie['backdrop'] = $movie['poster'];
}

// Get comments
$comments_sql = "SELECT mc.*, u.firstName, u.lastName 
                 FROM movie_comments mc
                 LEFT JOIN users u ON mc.user_id = u.id
                 WHERE mc.movie_id = ?
                 ORDER BY mc.created_at DESC
                 LIMIT 10";
$comments_stmt = $conn->prepare($comments_sql);
$comments_stmt->bind_param("i", $movie_id);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($movie['title']); ?> - PoncolVerse</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Poppins:wght@300;400;600&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/main.css">
  <link rel="stylesheet" href="assets/css/responsive.css">
  <style>
    /* Movie Detail Styles */
    .movie-detail-container {
      min-height: 100vh;
      background: #0a0a0a;
      padding-top: 80px;
    }

    .movie-backdrop {
      position: relative;
      width: 100%;
      height: 70vh;
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
    }

    .movie-backdrop::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(to bottom, rgba(10, 10, 10, 0.3) 0%, rgba(10, 10, 10, 0.8) 70%, rgba(10, 10, 10, 1) 100%);
    }

    .movie-header {
      position: relative;
      max-width: 1400px;
      margin: 0 auto;
      padding: 0 2rem;
      display: flex;
      align-items: flex-end;
      gap: 3rem;
      transform: translateY(100px);
    }

    .movie-poster-large {
      width: 300px;
      border-radius: 15px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.8);
      flex-shrink: 0;
      cursor: pointer;
      transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1), box-shadow 0.4s ease;
    }

    .movie-poster-large:hover {
      transform: scale(1.03) translateY(-5px);
      box-shadow: 0 25px 70px rgba(255, 0, 60, 0.25);
    }

    .movie-actions {
      display: flex;
      flex-direction: row;
      flex-wrap: wrap;
      gap: 1.2rem;
      margin-bottom: 2.5rem;
      align-items: center;
    }

    /* Poster Modal Specific Style */
    .poster-modal-content {
      max-width: 480px;
      background: transparent !important;
      box-shadow: none !important;
      border: none !important;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .full-poster-img {
      width: 100%;
      height: auto;
      border-radius: 12px;
      box-shadow: 0 15px 50px rgba(0, 0, 0, 0.95);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .movie-title-section h1 {
      font-family: 'Orbitron', sans-serif;
      font-size: 3rem;
      margin-bottom: 1rem;
      background: linear-gradient(135deg, #ff003c, #ff4d7a);
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
    }

    .movie-meta {
      display: flex;
      gap: 2rem;
      align-items: center;
      margin-bottom: 1.5rem;
      flex-wrap: wrap;
    }

    .movie-meta-item {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      color: #aaa;
    }

    .rating-badge {
      background: linear-gradient(135deg, #ff003c, #ff4d7a);
      padding: 0.5rem 1rem;
      border-radius: 50px;
      font-weight: 700;
      font-size: 1.2rem;
    }

    .movie-content {
      max-width: 1400px;
      margin: 0 auto;
      padding: 150px 2rem 4rem;
    }

    .btn-action {
      padding: 1rem 2rem;
      border-radius: 50px;
      border: none;
      font-weight: 700;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .btn-primary {
      background: linear-gradient(135deg, #ff003c, #ff4d7a);
      color: white;
    }

    .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 30px rgba(255, 0, 60, 0.5);
    }

    .btn-secondary {
      background: rgba(255, 255, 255, 0.1);
      color: white;
      border: 2px solid rgba(255, 255, 255, 0.2);
    }

    .btn-secondary:hover {
      background: rgba(255, 255, 255, 0.2);
    }

    .section-title {
      font-size: 2rem;
      margin: 3rem 0 2rem;
      font-family: 'Orbitron', sans-serif;
    }

    .cast-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
      gap: 2rem;
      margin-bottom: 3rem;
    }

    .cast-card {
      text-align: center;
    }

    .cast-photo {
      width: 100%;
      aspect-ratio: 2/3;
      object-fit: cover;
      border-radius: 10px;
      margin-bottom: 1rem;
      background: linear-gradient(135deg, #1a1a1a, #0a0a0a);
    }

    .cast-name {
      font-weight: 600;
      margin-bottom: 0.25rem;
    }

    .cast-character {
      color: #aaa;
      font-size: 0.9rem;
    }

    .comments-section {
      background: rgba(255, 255, 255, 0.05);
      border-radius: 15px;
      padding: 2rem;
      margin: 3rem 0;
    }

    .comment-form {
      margin-bottom: 2rem;
    }

    .comment-form textarea {
      width: 100%;
      padding: 1rem;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 10px;
      color: white;
      font-family: 'Poppins', sans-serif;
      resize: vertical;
      min-height: 100px;
      transition: all 0.3s ease;
    }

    .comment-form textarea:focus {
      outline: none;
      border-color: #ff003c;
      background: rgba(0, 0, 0, 0.4);
      box-shadow: 0 0 12px rgba(255, 0, 60, 0.35);
    }

    .comment-item {
      background: rgba(255, 255, 255, 0.05);
      padding: 1.5rem;
      border-radius: 10px;
      margin-bottom: 1rem;
    }

    .comment-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
    }

    .comment-author {
      font-weight: 600;
      color: #ff003c;
    }

    .comment-date {
      color: #aaa;
      font-size: 0.9rem;
    }

    .back-button {
      position: fixed;
      top: 100px;
      left: 2rem;
      z-index: 1000;
      background: rgba(0, 0, 0, 0.8);
      backdrop-filter: blur(10px);
      padding: 1rem 1.5rem;
      border-radius: 50px;
      color: white;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      transition: all 0.3s;
    }

    .back-button:hover {
      background: rgba(255, 0, 60, 0.8);
      transform: translateX(-5px);
    }

    /* ===== FILM SERUPA ===== */
    .similar-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 1.5rem;
        margin-bottom: 3rem;
    }
    
    .similar-card {
        background: #121212;
        border-radius: 12px;
        overflow: hidden;
        cursor: pointer;
        border: 1px solid rgba(255,255,255,0.05);
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    
    .similar-card:hover {
        transform: translateY(-8px) scale(1.03);
        border-color: rgba(255,0,60,0.4);
        box-shadow: 0 12px 30px rgba(255,0,60,0.25);
    }
    
    .similar-card img {
        width: 100%;
        height: 230px;
        object-fit: cover;
        display: block;
    }
    
    .similar-card-info {
        padding: 0.75rem;
    }
    
    .similar-card-title {
        font-size: 0.9rem;
        font-weight: 600;
        color: #fff;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 0.3rem;
    }
    
    .similar-card-meta {
        font-size: 0.8rem;
        color: #aaa;
        display: flex;
        justify-content: space-between;
    }

    /* ===== STAR RATING ===== */
    .star-rating-wrap {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      flex-wrap: wrap;
    }
    
    .star-rating-label {
      color: #aaa;
      font-size: 0.95rem;
      white-space: nowrap;
    }
    
    .star-rating-group {
      display: flex;
      gap: 4px;
    }
    
    .star-btn {
      font-size: 1.4rem;
      color: rgba(255, 255, 255, 0.15);
      cursor: pointer;
      transition: color 0.15s ease, transform 0.15s ease;
      line-height: 1;
    }
    
    .star-btn:hover,
    .star-btn.hovered,
    .star-btn.selected {
      color: #ffc107;
    }
    
    .star-btn:hover {
      transform: scale(1.2);
    }
    
    .star-rating-display {
      font-size: 0.85rem;
      font-weight: 600;
      color: #ffc107;
      min-width: 80px;
      opacity: 0.7;
      transition: opacity 0.2s;
    }
    
    .star-rating-display.active {
      opacity: 1;
    }

    /* ===== SPOILER TAG ===== */
  .spoiler-checkbox-wrap {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    cursor: pointer;
    user-select: none;
  }
  
  .spoiler-checkbox-wrap input[type="checkbox"] {
    display: none;
  }
  
  .spoiler-toggle-box {
    width: 18px;
    height: 18px;
    border: 2px solid rgba(255, 145, 0, 0.5);
    border-radius: 4px;
    background: transparent;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    flex-shrink: 0;
  }
  
  .spoiler-checkbox-wrap input:checked + .spoiler-toggle-box {
    background: #ff9100;
    border-color: #ff9100;
  }
  
  .spoiler-checkbox-wrap input:checked + .spoiler-toggle-box::after {
    content: '✓';
    font-size: 11px;
    color: #000;
    font-weight: 900;
    line-height: 1;
  }
  
  .spoiler-checkbox-label {
    font-size: 0.85rem;
    color: #ff9100;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.4rem;
  }
  
  .spoiler-checkbox-label i {
    font-size: 0.8rem;
  }
  
  /* ===== SPOILER COMMENT DISPLAY ===== */
  .comment-item.is-spoiler .comment-body {
    filter: blur(6px);
    user-select: none;
    cursor: pointer;
    transition: filter 0.3s ease;
    position: relative;
  }
  
  .comment-item.is-spoiler .comment-body.revealed {
    filter: none;
    user-select: text;
    cursor: default;
  }
  
  .spoiler-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    background: rgba(255, 145, 0, 0.12);
    border: 1px solid rgba(255, 145, 0, 0.35);
    color: #ff9100;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 0.2rem 0.6rem;
    border-radius: 20px;
    letter-spacing: 0.5px;
    margin-bottom: 0.6rem;
  }
  
  .spoiler-reveal-hint {
    display: block;
    font-size: 0.75rem;
    color: rgba(255, 145, 0, 0.7);
    margin-top: 0.4rem;
    font-style: italic;
  }
  
  .comment-item.is-spoiler .spoiler-reveal-hint.hidden {
    display: none;
  }
  </style>
</head>

<body>

  <div class="drawer-overlay" id="drawerOverlay" onclick="closeDrawer()"></div>
  <div class="mobile-drawer" id="mobileDrawer">
    <div class="drawer-header">
      <span class="drawer-logo">PoncolVerse</span>
      <button class="drawer-close" onclick="closeDrawer()">&#x2715;</button>
    </div>
    <div class="drawer-nav">
      <a href="index.php" class="drawer-nav-item"><i class="fas fa-home"></i> Beranda</a>
      <a href="index.php#film" class="drawer-nav-item"><i class="fas fa-fire"></i> Film Populer</a>
      <a href="index.php#semua-film" class="drawer-nav-item"><i class="fas fa-film"></i> Semua Film</a>
      <?php if (isset($_SESSION['user_id'])): ?>
        <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <a href="admin/index-admin.php" class="drawer-nav-item admin-item"><i class="fas fa-cog"></i> Admin Panel</a>
        <?php endif; ?>
        <button class="drawer-nav-item" onclick="toggleWatchlist(<?php echo $movie_id; ?>); closeDrawer();"><i class="fas fa-bookmark"></i> Toggle Watchlist</button>
      <?php else: ?>
        <button class="drawer-nav-item" onclick="openLogin(); closeDrawer();"><i class="fas fa-sign-in-alt"></i> Login</button>
      <?php endif; ?>
    </div>
  </div>

  <!-- Burger mobile di detail page -->
  <button class="burger-btn" onclick="openDrawer()" style="position:fixed;top:16px;right:16px;z-index:1001;background:rgba(0,0,0,0.7);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:0.5rem 0.7rem;">
    <i class="fas fa-bars"></i>
  </button>

  <a href="index.php" class="back-button">
    <i class="fas fa-arrow-left"></i> Kembali
  </a>

  <div class="movie-detail-container">
    <div class="movie-backdrop" style="background-image: url('<?php echo $movie['backdrop'] ?? $movie['poster']; ?>');">
      <div class="movie-header">
        <img src="<?php echo htmlspecialchars($movie['poster']); ?>"
          alt="<?php echo htmlspecialchars($movie['title']); ?>" class="movie-poster-large" onclick="openPosterModal()" title="Klik untuk memperbesar poster">

        <div class="movie-title-section">
          <h1><?php echo htmlspecialchars($movie['title']); ?></h1>

          <div class="movie-meta">
            <div class="rating-badge">
              <i class="fas fa-star"></i> <?php echo $movie['rating']; ?>
            </div>
            <div class="movie-meta-item">
              <i class="fas fa-calendar"></i>
              <span><?php echo $movie['year']; ?></span>
            </div>
            <div class="movie-meta-item">
              <i class="fas fa-clock"></i>
              <span><?php echo $movie['duration']; ?></span>
            </div>
            <div class="movie-meta-item">
              <i class="fas fa-tag"></i>
              <span><?php echo htmlspecialchars($movie['genre']); ?></span>
            </div>
          </div>

          <?php if (!empty($movie['director'])): ?>
            <div class="movie-meta-item">
              <i class="fas fa-user-tie"></i>
              <span><strong>Sutradara:</strong> <?php echo htmlspecialchars($movie['director']); ?></span>
            </div>
          <?php endif; ?>
          <?php if (!empty($movie['cast_array'])): ?>
            <div class="movie-meta-item" style="margin-top: 0.5rem; align-items: flex-start;">
              <i class="fas fa-users" style="margin-top: 0.25rem;"></i>
              <span><strong>Aktor:</strong> <?php 
                $actor_names = array_column($movie['cast_array'], 'name');
                echo htmlspecialchars(implode(', ', $actor_names)); 
              ?></span>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="movie-content">
      <?php
      // Check if movie is in user's watchlist
      $in_watchlist = false;
      if (isset($_SESSION['user_id'])) {
          $check_watchlist_sql = "SELECT id FROM watchlist WHERE user_id = ? AND movie_id = ?";
          $check_stmt = $conn->prepare($check_watchlist_sql);
          $check_stmt->bind_param("ii", $_SESSION['user_id'], $movie_id);
          $check_stmt->execute();
          $in_watchlist = $check_stmt->get_result()->num_rows > 0;
      }
      ?>
      
      <div class="movie-actions">
        <button class="btn-action btn-primary" onclick="openTrailer('<?php echo $movie['trailer']; ?>')">
          <i class="fas fa-play"></i> Tonton Trailer
        </button>
        
        <?php if (!empty($movie['watchLink']) && $movie['watchLink'] !== '#'): ?>
        <a href="javascript:void(0)" (event, onclick="startWatchRedirect(event, '<?php echo htmlspecialchars($movie['watchLink']); ?>', <?php echo $movie_id; ?>)" class="btn-action btn-primary" style="text-decoration: none;">
          <i class="fas fa-film"></i> Tonton Film
        </a>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['user_id'])): ?>
        <button class="btn-action btn-secondary" 
                onclick="toggleWatchlist(<?php echo $movie_id; ?>)" 
                data-movie-id="<?php echo $movie_id; ?>"
                style="<?php echo $in_watchlist ? 'background: linear-gradient(135deg, #ff003c, #ff4d7a); border-color: #ff003c;' : ''; ?>">
          <i class="<?php echo $in_watchlist ? 'fas' : 'far'; ?> fa-bookmark"></i> 
          <?php echo $in_watchlist ? 'Di Watchlist' : 'Tambah ke Watchlist'; ?>
        </button>
        <?php else: ?>
        <button class="btn-action btn-secondary" onclick="openLogin()">
          <i class="far fa-bookmark"></i> Tambah ke Watchlist
        </button>
        <?php endif; ?>
        
        <button class="btn-action btn-secondary" onclick="openShare(<?php echo $movie_id; ?>, '<?php echo addslashes($movie['title']); ?>')">
          <i class="fas fa-share-alt"></i> Bagikan
        </button>
      </div>
      <h2 class="section-title">Sinopsis</h2>
      <p style="line-height: 1.8; color: #ccc; font-size: 1.1rem;">
        <?php echo nl2br(htmlspecialchars($movie['plot'])); ?>
      </p>

      <?php if (!empty($movie['cast_array'])): ?>
        <h2 class="section-title">Pemeran</h2>
        <div class="cast-grid">
          <?php foreach ($movie['cast_array'] as $cast): ?>
            <div class="cast-card">
              <?php if (!empty($cast['photo'])): ?>
                <img src="<?php echo htmlspecialchars($cast['photo']); ?>"
                  alt="<?php echo htmlspecialchars($cast['name']); ?>" class="cast-photo">
              <?php else: ?>
                <div class="cast-photo"
                  style="display: flex; align-items: center; justify-content: center; font-size: 3rem; color: #ff003c;">
                  <i class="fas fa-user"></i>
                </div>
              <?php endif; ?>
              <div class="cast-name"><?php echo htmlspecialchars($cast['name']); ?></div>
              <?php if (!empty($cast['character'])): ?>
                <div class="cast-character"><?php echo htmlspecialchars($cast['character']); ?></div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

            <!-- Film Serupa -->
      <div id="similarMoviesSection" style="display:none;">
          <h2 class="section-title">Film Serupa</h2>
          <div class="similar-grid" id="similarGrid"></div>
      </div>

      <div class="comments-section">
        <h2 class="section-title" style="margin-top: 0;">Komentar</h2>

        <?php if (isset($_SESSION['user_id'])): ?>
          <div class="comment-form">
            <form id="commentForm">
              <textarea id="commentText" placeholder="Tulis komentar Anda..."></textarea>
              <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                <div class="star-rating-wrap">
                  <span class="star-rating-label">Rating:</span>
                  <div class="star-rating-group" id="starGroup">
                    <i class="fas fa-star star-btn" data-val="1"></i>
                    <i class="fas fa-star star-btn" data-val="2"></i>
                    <i class="fas fa-star star-btn" data-val="3"></i>
                    <i class="fas fa-star star-btn" data-val="4"></i>
                    <i class="fas fa-star star-btn" data-val="5"></i>
                    <i class="fas fa-star star-btn" data-val="6"></i>
                    <i class="fas fa-star star-btn" data-val="7"></i>
                    <i class="fas fa-star star-btn" data-val="8"></i>
                    <i class="fas fa-star star-btn" data-val="9"></i>
                    <i class="fas fa-star star-btn" data-val="10"></i>
                  </div>
                  <span class="star-rating-display" id="starDisplay">Pilih rating</span>
                  <input type="hidden" id="commentRating" value="">
                </div>
                <button type="submit" class="btn-action btn-primary">
                  <i class="fas fa-paper-plane"></i> Kirim
                </button>
              </div>
              <label class="spoiler-checkbox-wrap" title="Centang jika komentar mengandung spoiler cerita">
                <input type="checkbox" id="isSpoilerCheck">
                <div class="spoiler-toggle-box"></div>
                <span class="spoiler-checkbox-label">
                  <i class="fas fa-exclamation-triangle"></i> Mengandung Spoiler
                </span>
              </label>
            </form>
          </div>
        <?php else: ?>
          <p style="color: #aaa; text-align: center; padding: 2rem;">
            <a href="#" onclick="openLogin(); return false;" style="color: #ff003c;">Login</a> untuk memberikan komentar
          </p>
        <?php endif; ?>

        <div id="commentsList">
          <?php
          if ($comments_result->num_rows > 0):
            while ($comment = $comments_result->fetch_assoc()):
              $isSpoiler = !empty($comment['is_spoiler']) && $comment['is_spoiler'] == 1;
          ?>
              <div class="comment-item <?php echo $isSpoiler ? 'is-spoiler' : ''; ?>">
                <div class="comment-header">
                  <span class="comment-author">
                    <?php echo htmlspecialchars($comment['firstName'] . ' ' . $comment['lastName']); ?>
                  </span>
                  <span class="comment-date">
                    <?php echo date('d M Y, H:i', strtotime($comment['created_at'])); ?>
                  </span>
                </div>

                <?php if ($isSpoiler): ?>
                  <div class="spoiler-badge">
                    <i class="fas fa-exclamation-triangle"></i> SPOILER
                  </div>
                <?php endif; ?>

                <?php if (!empty($comment['rating'])): ?>
                  <div style="color: #ff003c; margin-bottom: 0.5rem;">
                    <i class="fas fa-star"></i> <?php echo $comment['rating']; ?>/10
                  </div>
                <?php endif; ?>

                <div class="comment-body <?php echo $isSpoiler ? '' : 'revealed'; ?>"
                    <?php echo $isSpoiler ? 'onclick="revealSpoiler(this)" title="Klik untuk lihat spoiler"' : ''; ?>>
                  <p style="color: #ccc; line-height: 1.6;">
                    <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                  </p>
                  <?php if ($isSpoiler): ?>
                    <span class="spoiler-reveal-hint">🔍 Klik untuk membaca spoiler</span>
                  <?php endif; ?>
                </div>
              </div>
          <?php
            endwhile;
          else:
          ?>
            <p style="color: #aaa; text-align: center; padding: 2rem;">
              Belum ada komentar. Jadilah yang pertama!
            </p>
          <?php endif; ?>
        </div>
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

  <!-- Share Modal (IMPROVED UI) -->
  <div id="shareModal" class="modal">
    <div class="modal-content share-modal-improved">
      <span class="close" onclick="closeShare()">&times;</span>
      
      <!-- Header -->
      <div class="share-modal-header">
        <div class="share-icon-wrapper">
          <i class="fas fa-share-nodes"></i>
        </div>
        <h2>Bagikan Film</h2>
        <p class="share-subtitle">Ajak teman nonton film seru ini!</p>
      </div>
      
      <!-- Movie Preview -->
      <div class="share-movie-preview">
        <img src="<?php echo htmlspecialchars($movie['poster']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="share-movie-poster">
        <div class="share-movie-info">
          <h3><?php echo htmlspecialchars($movie['title']); ?></h3>
          <div class="share-movie-meta">
            <span><i class="fas fa-star"></i> <?php echo $movie['rating']; ?></span>
            <span><i class="fas fa-calendar"></i> <?php echo $movie['year']; ?></span>
          </div>
        </div>
      </div>
      
      <!-- Share Link -->
      <div class="share-link-section">
        <label class="share-label">
          <i class="fas fa-link"></i> Link Film
        </label>
        <div class="share-link-wrapper">
          <input type="text" id="shareLink" readonly class="share-link-field">
          <button class="share-copy-btn" onclick="copyShareLink()">
            <i class="fas fa-copy"></i>
          </button>
        </div>
      </div>
      
      <!-- Social Share Buttons -->
      <div class="share-social-section">
        <label class="share-label">
          <i class="fas fa-share-alt"></i> Bagikan ke
        </label>
        <div class="share-social-grid">
          <button class="share-btn share-btn-whatsapp" onclick="shareToWhatsApp()">
            <i class="fab fa-whatsapp"></i>
            <span>WhatsApp</span>
          </button>
          <button class="share-btn share-btn-facebook" onclick="shareToFacebook()">
            <i class="fab fa-facebook"></i>
            <span>Facebook</span>
          </button>
          <button class="share-btn share-btn-twitter" onclick="shareToTwitter()">
            <i class="fab fa-twitter"></i>
            <span>Twitter</span>
          </button>
          <button class="share-btn share-btn-telegram" onclick="shareToTelegram()">
            <i class="fab fa-telegram"></i>
            <span>Telegram</span>
          </button>
          <button class="share-btn share-btn-story" onclick="shareToStory(currentShareMovieId, currentShareMovieTitle, '<?php echo htmlspecialchars($movie['poster']); ?>', '<?php echo $movie['rating']; ?>', '<?php echo $movie['year']; ?>', <?php echo json_encode($genre_array); ?>)">
            <i class="fas fa-image"></i>
            <span>Story Card</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Poster Modal -->
  <div id="posterModal" class="modal">
    <div class="modal-content poster-modal-content">
      <span class="close" onclick="closePosterModal()">&times;</span>
      <img src="<?php echo htmlspecialchars($movie['poster']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?> Poster" class="full-poster-img">
    </div>
  </div>

  <script>

        // ===== RECORD WATCH HISTORY =====
    async function recordWatchHistory(movieId) {
        try {
            const formData = new FormData();
            formData.append('movie_id', movieId);
            await fetch('API/watchhistory/record_watch.php', {
                method: 'POST',
                body: formData
            });
        } catch (e) {
            // silent fail — jangan ganggu user
        }
    }
    
    // ===== OVERRIDE startWatchRedirect agar catat history =====
    // Fungsi ini menggantikan fungsi startWatchRedirect dari main.js
    // Tambahkan movieId sebagai parameter ke-3
    function startWatchRedirect(event, link, movieId) {
        if (event) event.preventDefault();
    
        // Catat history jika ada movieId
        if (movieId) recordWatchHistory(movieId);
    
        let redirectModal = document.getElementById('watchRedirectModal');
        if (!redirectModal) {
            redirectModal = document.createElement('div');
            redirectModal.id = 'watchRedirectModal';
            redirectModal.style.cssText = 'position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.85);backdrop-filter:blur(15px);display:flex;justify-content:center;align-items:center;z-index:99999;opacity:0;transition:opacity 0.4s ease;';
            redirectModal.innerHTML = `
              <div style="background:linear-gradient(135deg,rgba(26,26,26,0.95),rgba(15,15,15,0.95));border:2px solid rgba(255,0,60,0.3);border-radius:20px;padding:3rem;max-width:450px;width:90%;text-align:center;box-shadow:0 20px 50px rgba(0,0,0,0.8);transform:scale(0.9);transition:transform 0.4s cubic-bezier(0.34,1.56,0.64,1);" id="watchRedirectContent">
                <div id="watchRedirectSpinner" style="margin-bottom:2rem;">
                  <div style="width:70px;height:70px;border:5px solid rgba(255,0,60,0.1);border-top:5px solid #ff003c;border-radius:50%;margin:0 auto;animation:watchSpin 1s linear infinite;box-shadow:0 0 15px rgba(255,0,60,0.4);"></div>
                </div>
                <h2 style="font-family:'Orbitron',sans-serif;font-size:1.8rem;margin-bottom:1rem;background:linear-gradient(135deg,#ff003c,#ff4d7a);-webkit-background-clip:text;background-clip:text;color:transparent;" id="watchRedirectTitle">Menghubungkan ke Server</h2>
                <p style="color:#ccc;font-size:1.1rem;line-height:1.6;" id="watchRedirectText">Mohon tunggu sebentar...</p>
              </div>`;
            const style = document.createElement('style');
            style.innerHTML = '@keyframes watchSpin{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}';
            document.head.appendChild(style);
            document.body.appendChild(redirectModal);
        }
    
        const content = document.getElementById('watchRedirectContent');
        const spinner = document.getElementById('watchRedirectSpinner');
        const title   = document.getElementById('watchRedirectTitle');
        const text    = document.getElementById('watchRedirectText');
    
        spinner.style.display = 'block';
        title.innerText = 'Menghubungkan ke Server';
        text.innerText  = 'Mohon tunggu sebentar...';
    
        redirectModal.style.display = 'flex';
        redirectModal.offsetHeight;
        redirectModal.style.opacity = '1';
        content.style.transform = 'scale(1)';
        document.body.style.overflow = 'hidden';
    
        setTimeout(() => {
            spinner.style.display = 'none';
            title.innerText = 'Mempersiapkan Pemutar';
            let countdown = 3;
            text.innerHTML = `Segera Diarahkan Ke link Nonton Pada: <strong style="color:#ff003c;font-size:1.3rem;">${countdown}</strong> detik`;
            const interval = setInterval(() => {
                countdown--;
                if (countdown > 0) {
                    text.innerHTML = `Segera Diarahkan Ke link Nonton Pada: <strong style="color:#ff003c;font-size:1.3rem;">${countdown}</strong> detik`;
                } else {
                    clearInterval(interval);
                    redirectModal.style.opacity = '0';
                    content.style.transform = 'scale(0.9)';
                    setTimeout(() => { redirectModal.style.display = 'none'; document.body.style.overflow = ''; }, 400);
                    window.open(link, '_blank');
                }
            }, 1000);
        }, 3000);
    }
    
    // Watchlist & Share Functions
    let currentShareMovieId = <?php echo $movie_id; ?>;
    let currentShareMovieTitle = '<?php echo addslashes($movie['title']); ?>';

    // Toggle watchlist
    async function toggleWatchlist(movieId) {
      try {
        const formData = new FormData();
        formData.append('movie_id', movieId);
        
        const response = await fetch('API/watchlist/add_to_watchlist.php', {
          method: 'POST',
          body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
          showToast('success', 
            result.action === 'added' ? 'Ditambahkan!' : 'Dihapus!', 
            result.message
          );
          
          // Update button
          const btn = document.querySelector(`[data-movie-id="${movieId}"]`);
          if (btn) {
            const icon = btn.querySelector('i');
            if (result.action === 'added') {
              icon.className = 'fas fa-bookmark';
              btn.style.background = 'linear-gradient(135deg, #ff003c, #ff4d7a)';
              btn.style.borderColor = '#ff003c';
              btn.innerHTML = '<i class="fas fa-bookmark"></i> Di Watchlist';
            } else {
              icon.className = 'far fa-bookmark';
              btn.style.background = 'rgba(255,255,255,0.1)';
              btn.style.borderColor = 'rgba(255,255,255,0.2)';
              btn.innerHTML = '<i class="far fa-bookmark"></i> Tambah ke Watchlist';
            }
          }
        } else {
          if (result.message.includes('login')) {
            showToast('error', 'Login Required', 'Silakan login terlebih dahulu!');
            setTimeout(() => window.location.href = 'index.php', 1500);
          } else {
            showToast('error', 'Gagal', result.message);
          }
        }
        
      } catch (error) {
        console.error('Error toggling watchlist:', error);
        showToast('error', 'Error', 'Terjadi kesalahan. Silakan coba lagi.');
      }
    }

    // Open share modal
    function openShare(movieId, movieTitle) {
      currentShareMovieId = movieId;
      currentShareMovieTitle = movieTitle;
      
      const modal = document.getElementById('shareModal');
      if (!modal) return;
      
      // Generate share link
      const shareUrl = window.location.href;
      
      const shareLinkInput = document.getElementById('shareLink');
      if (shareLinkInput) {
        shareLinkInput.value = shareUrl;
      }
      
      modal.classList.add('active');
      document.body.style.overflow = 'hidden';
    }

    // Close share modal
    function closeShare() {
      const modal = document.getElementById('shareModal');
      if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
      }
    }

    // Copy share link
    function copyShareLink() {
      const input = document.getElementById('shareLink');
      if (!input) return;
      
      input.select();
      input.setSelectionRange(0, 99999);
      
      try {
        document.execCommand('copy');
        showToast('success', 'Link Disalin!', 'Link film telah disalin ke clipboard');
      } catch (err) {
        navigator.clipboard.writeText(input.value).then(() => {
          showToast('success', 'Link Disalin!', 'Link film telah disalin ke clipboard');
        }).catch(() => {
          showToast('error', 'Gagal', 'Gagal menyalin link');
        });
      }
    }

    // Share to WhatsApp
    function shareToWhatsApp() {
      const shareUrl = document.getElementById('shareLink').value;
      const text = `Cek film keren ini di PoncolVerse: ${currentShareMovieTitle}`;
      const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(text + ' ' + shareUrl)}`;
      window.open(whatsappUrl, '_blank');
    }

    // Share to Facebook
    function shareToFacebook() {
      const shareUrl = document.getElementById('shareLink').value;
      const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`;
      window.open(facebookUrl, '_blank', 'width=600,height=400');
    }

    // Share to Twitter
    function shareToTwitter() {
      const shareUrl = document.getElementById('shareLink').value;
      const text = `Cek film keren ini di PoncolVerse: ${currentShareMovieTitle}`;
      const twitterUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(shareUrl)}`;
      window.open(twitterUrl, '_blank', 'width=600,height=400');
    }
    
    // Share to Telegram
    function shareToTelegram() {
      const shareUrl = document.getElementById('shareLink').value;
      const text = `Cek film keren ini di PoncolVerse: ${currentShareMovieTitle}`;
      const telegramUrl = `https://t.me/share/url?url=${encodeURIComponent(shareUrl)}&text=${encodeURIComponent(text)}`;
      window.open(telegramUrl, '_blank');
    }

    // Toast notification (copy dari main.js)
    function showToast(type, title, message, duration = 4000) {
      let container = document.getElementById("toastContainer");
      if (!container) {
        container = document.createElement("div");
        container.id = "toastContainer";
        container.className = "toast-container";
        document.body.appendChild(container);
      }

      const iconMap = {
        success: "fa-check-circle",
        error: "fa-exclamation-circle",
        info: "fa-info-circle",
        warning: "fa-exclamation-triangle",
      };

      const toast = document.createElement("div");
      toast.className = `toast ${type}`;

      toast.innerHTML = `
        <div class="toast-icon">
          <i class="fas ${iconMap[type]}"></i>
        </div>
        <div class="toast-content">
          <div class="toast-title">${title}</div>
          <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">
          <i class="fas fa-times"></i>
        </button>
        <div class="toast-progress"></div>
      `;

      container.appendChild(toast);

      setTimeout(() => {
        toast.style.animation = "slideOut 0.3s ease-out forwards";
        setTimeout(() => toast.remove(), 300);
      }, duration);
    }

    function openPosterModal() {
      const modal = document.getElementById('posterModal');
      if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
      }
    }

    function closePosterModal() {
      const modal = document.getElementById('posterModal');
      if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
      }
    }

    // Close poster modal when clicking outside of it
    window.addEventListener('click', function(event) {
      const modal = document.getElementById('posterModal');
      if (event.target === modal) {
        closePosterModal();
      }
    });
  </script>

  <!-- Share Modal & Trailer Modal udah ada, tambah Watchlist Script -->
  <script src="assets/js/main.js"></script>

    <script>
  // ===== LOAD FILM SERUPA =====
  async function loadSimilarMovies() {
      const genre = '<?php echo addslashes(json_encode($genre_array)); ?>';
      const movieId = <?php echo $movie_id; ?>;
  
      try {
          const res = await fetch(`API/movies/get_similar_movies.php?movie_id=${movieId}&genre=${encodeURIComponent(genre)}`);
          const movies = await res.json();
  
          if (!movies || movies.length === 0) return;
  
          const section = document.getElementById('similarMoviesSection');
          const grid    = document.getElementById('similarGrid');
  
          grid.innerHTML = movies.map(m => `
              <div class="similar-card" onclick="window.location.href='movie-detail.php?id=${m.id}'">
                  <img src="${m.poster}" alt="${m.title}" loading="lazy"
                      onerror="this.src='https://via.placeholder.com/160x230?text=No+Image'">
                  <div class="similar-card-info">
                      <div class="similar-card-title">${m.title}</div>
                      <div class="similar-card-meta">
                          <span>⭐ ${m.rating}</span>
                          <span>${m.year}</span>
                      </div>
                  </div>
              </div>
          `).join('');
  
          section.style.display = 'block';
  
      } catch (e) {
          console.error('Error loading similar movies:', e);
      }
  }
  
  // Jalankan saat page load
  window.addEventListener('load', loadSimilarMovies);
  </script>

  <script>
    // Load watchlist count for navbar
    loadWatchlistCount();
  </script>

  <script>
    function openTrailer(url) {
      document.getElementById('trailerContainer').innerHTML =
        `<iframe width="100%" height="600" src="${url}?autoplay=1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
      document.getElementById('trailerModal').classList.add('active');
      document.body.style.overflow = 'hidden';
    }

    function closeTrailer() {
      document.getElementById('trailerModal').classList.remove('active');
      document.getElementById('trailerContainer').innerHTML = '';
      document.body.style.overflow = '';
    }

      // ===== STAR RATING LOGIC =====
  (function() {
      const stars   = document.querySelectorAll('.star-btn');
      const display = document.getElementById('starDisplay');
      const hidden  = document.getElementById('commentRating');
  
      const labels = {
          1:'1 — Terrible', 2:'2 — Sangat Buruk', 3:'3 — Buruk',
          4:'4 — Kurang',   5:'5 — Biasa',        6:'6 — Cukup',
          7:'7 — Lumayan',  8:'8 — Bagus',         9:'9 — Sangat Bagus',
          10:'10 — Masterpiece'
      };
  
      let currentVal = 0;
  
      function paintStars(upTo) {
          stars.forEach(s => {
              const v = parseInt(s.dataset.val);
              if (v <= upTo) {
                  s.classList.add('hovered');
              } else {
                  s.classList.remove('hovered');
              }
          });
      }
  
      stars.forEach(star => {
          // Hover masuk
          star.addEventListener('mouseenter', () => {
              const v = parseInt(star.dataset.val);
              paintStars(v);
              display.textContent = labels[v];
              display.classList.add('active');
          });
  
          // Hover keluar — kembalikan ke selected
          star.addEventListener('mouseleave', () => {
              paintStars(currentVal);
              display.textContent = currentVal ? labels[currentVal] : 'Pilih rating';
              if (!currentVal) display.classList.remove('active');
          });
  
          // Klik — set nilai
          star.addEventListener('click', () => {
              const v = parseInt(star.dataset.val);
  
              // Klik bintang yang sama → reset (toggle off)
              if (currentVal === v) {
                  currentVal = 0;
                  hidden.value = '';
                  paintStars(0);
                  display.textContent = 'Pilih rating';
                  display.classList.remove('active');
                  stars.forEach(s => s.classList.remove('selected'));
                  return;
              }
  
              currentVal = v;
              hidden.value = v;
              paintStars(v);
              display.textContent = labels[v];
              display.classList.add('active');
  
              // Tandai selected
              stars.forEach(s => {
                  s.classList.toggle('selected', parseInt(s.dataset.val) <= v);
              });
          });
      });
  })();

   // Comment form handler
    document.getElementById('commentForm')?.addEventListener('submit', async (e) => {
      e.preventDefault();

      const comment = document.getElementById('commentText').value.trim();
      const rating = document.getElementById('commentRating').value;

      if (!comment) {
        showToast('error', 'Komentar Kosong', 'Komentar tidak boleh kosong!');
        return;
      }

      try {
        const formData = new FormData();
        formData.append('movie_id', <?php echo $movie_id; ?>);
        formData.append('comment', comment);
        if (rating) formData.append('rating', rating);

        const response = await fetch('API/comments/add_movie_comment.php', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        if (result.success) {
          showToast('success', 'Berhasil! 🎉', 'Komentar berhasil ditambahkan!');
          setTimeout(() => location.reload(), 1500);
        } else {
          showToast('error', 'Gagal', 'Gagal menambahkan komentar: ' + result.message);
        }
      } catch (error) {
        console.error('Error:', error);
        showToast('error', 'Error', 'Terjadi kesalahan. Silakan coba lagi.');
      }
    });
  </script>

  <script>
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
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDrawer(); });
  </script>
</body>

</html>