<?php require_once 'config/config.php'; ?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PoncolVerse - Nonton Bioskop Online</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Poppins:wght@300;400;600&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- AOS Animation Library -->
  <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
  <link rel="stylesheet" href="assets/css/main.css">

  <!-- yang belom diganti itu semua file masih config yg lama ya tin, blm lu ganti semua, ini cuma jadi pengingat kalo lu buka vscode lagi oke tin -->
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
      <div class="search-container">
        <i class="fas fa-search search-icon"></i>
        <input type="text" class="search-input" id="searchInput" placeholder="Cari film...">
      </div>

      <!-- WATCHLIST BUTTON (HANYA UNTUK USER LOGIN) -->
      <?php if (isset($_SESSION['user_id'])): ?>
        <button class="watchlist-btn" onclick="openWatchlist()" title="Watchlist Saya">
          <i class="fas fa-bookmark"></i>
          <span class="watchlist-count" id="watchlistCount">0</span>
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
                        if (isset($_SESSION['subscription_end']) && !empty($_SESSION['subscription_end'])) {
                          echo date('d M Y', strtotime($_SESSION['subscription_end'])); 
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

  <header class="hero" id="beranda">
    <h1>Welcome To PoncolVerse</h1>
    <p>Nikmati pengalaman menonton film blockbuster dengan kualitas sinematik terbaik — semua dalam satu platform.</p>
    <button class="btn-watch" onclick="scrollToMovies()">Mulai Nonton Sekarang</button>
  </header>

  <section class="movies" id="film">
    <h2>Film Populer Hari Ini</h2>
    <div class="movie-grid" id="movieGrid">
      <div class="loading">
        <div class="spinner"></div>Memuat film...
      </div>
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

  </section>

  <?php
  // PASTE INI DI INDEX.PHP, GANTI BAGIAN <section class="pricing-section">
  // Hide package section if user has active subscription OR is admin
  $hasActiveSubscription = isset($_SESSION['subscription_status']) && $_SESSION['subscription_status'] === 'active';
  $isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
  
  if (!$isAdmin && !$hasActiveSubscription):
    // Fetch plans langsung dari database (INSTANT LOAD!)
    $sql_plans = "SELECT * FROM subscription_plans ORDER BY price ASC";
    $plans_result = $conn->query($sql_plans);

    $iconMap = [
      'Basic' => 'fa-play-circle',
      'Standard' => 'fa-star',
      'Premium' => 'fa-crown'
    ];
    ?>
    <section class="pricing-section" id="paket">
      <h2>Pilih Paket Berlangganan</h2>
      <p class="pricing-subtitle">Nikmati pengalaman menonton tanpa batas dengan berbagai keuntungan eksklusif</p>

      <div class="pricing-cards">
        <?php
        if ($plans_result && $plans_result->num_rows > 0):
          while ($plan = $plans_result->fetch_assoc()):
            $features = explode(',', $plan['features']);
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
        else:
          ?>
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
        <!-- Left Column: Image & Stats -->
        <div class="about-left" data-aos="fade-right">
          <div class="about-image-wrapper">
            <div class="image-glow"></div>
            <img
              src="assets/images/branding.png"
              alt="PoncolVerse Experience"
              class="about-main-image">
            <div class="image-overlay"></div>
          </div>

          <!-- Animated Stats -->
          <div class="about-stats">
            <div class="stat-card" data-aos="zoom-in" data-aos-delay="100">
              <div class="stat-icon"><i class="fas fa-film"></i></div>
              <div class="stat-number" data-count="1000">0</div>
              <div class="stat-label">Film Terlengkap</div>
            </div>
            <div class="stat-card" data-aos="zoom-in" data-aos-delay="200">
              <div class="stat-icon"><i class="fas fa-users"></i></div>
              <div class="stat-number" data-count="50000">0</div>
              <div class="stat-label">Pengguna Aktif</div>
            </div>
            <div class="stat-card" data-aos="zoom-in" data-aos-delay="300">
              <div class="stat-icon"><i class="fas fa-star"></i></div>
              <div class="stat-number" data-count="4.9">0</div>
              <div class="stat-label">Rating Platform</div>
            </div>
          </div>
        </div>

        <!-- Right Column: Description & Features -->
        <div class="about-right" data-aos="fade-left">
          <div class="about-description">
            <p class="about-text-intro">
              PoncolVerse adalah platform streaming film terdepan di Indonesia yang menyajikan pengalaman menonton terbaik
              dengan kualitas sinematik tinggi. Kami menyediakan berbagai film dari berbagai genre untuk memenuhi kebutuhan
              hiburan Anda.
            </p>
            <p class="about-text-detail">
              Dengan antarmuka yang user-friendly dan koleksi film yang terus diperbarui, PoncolVerse menjadi pilihan utama
              bagi para pecinta film untuk menikmati konten berkualitas kapan saja dan di mana saja.
            </p>
          </div>

          <div class="about-features-grid">
            <div class="feature-card" data-aos="flip-left" data-aos-delay="100">
              <div class="feature-card-icon">
                <i class="fas fa-hd-video"></i>
              </div>
              <h3 class="feature-card-title">Kualitas HD & 4K</h3>
              <p class="feature-card-desc">Nikmati film dengan kualitas gambar terbaik hingga 4K resolution</p>
            </div>

            <div class="feature-card" data-aos="flip-left" data-aos-delay="200">
              <div class="feature-card-icon">
                <i class="fas fa-mobile-alt"></i>
              </div>
              <h3 class="feature-card-title">Akses Multi-Device</h3>
              <p class="feature-card-desc">Tonton di smartphone, tablet, laptop, atau smart TV Anda</p>
            </div>

            <div class="feature-card" data-aos="flip-left" data-aos-delay="300">
              <div class="feature-card-icon">
                <i class="fas fa-sync"></i>
              </div>
              <h3 class="feature-card-title">Update Konten Rutin</h3>
              <p class="feature-card-desc">Film baru ditambahkan setiap minggu untuk Anda</p>
            </div>

            <div class="feature-card" data-aos="flip-left" data-aos-delay="400">
              <div class="feature-card-icon">
                <i class="fas fa-download"></i>
              </div>
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
    // Get approved testimonials
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

    // Split into 2 rows (5 each)
    $row1 = array_slice($testimonials, 0, 5);
    $row2 = array_slice($testimonials, 5, 5);
    ?>

    <?php if (count($testimonials) > 0): ?>
      <!-- Row 1: Left to Right -->
      <div class="testimonials-marquee marquee-ltr">
        <div class="testimonials-track">
          <?php foreach ($row1 as $testimonial): ?>
            <div class="testimonial-card-marquee">
              <div class="testimonial-stars">
                <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>
                  <i class="fas fa-star"></i>
                <?php endfor; ?>
              </div>
              <p class="testimonial-text">"<?php echo htmlspecialchars($testimonial['message']); ?>"</p>
              <div class="testimonial-author">
                <div class="author-avatar">
                  <?php
                  $initials = strtoupper(substr($testimonial['firstName'] ?? 'U', 0, 1) . substr($testimonial['lastName'] ?? 'S', 0, 1));
                  echo $initials;
                  ?>
                </div>
                <div class="author-info">
                  <strong><?php echo htmlspecialchars($testimonial['user_name']); ?></strong>
                  <small>Pengguna PoncolVerse</small>
                </div>
              </div>
            </div>
          <?php endforeach; ?>

          <!-- Duplicate untuk seamless loop -->
          <?php foreach ($row1 as $testimonial): ?>
            <div class="testimonial-card-marquee">
              <div class="testimonial-stars">
                <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>
                  <i class="fas fa-star"></i>
                <?php endfor; ?>
              </div>
              <p class="testimonial-text">"<?php echo htmlspecialchars($testimonial['message']); ?>"</p>
              <div class="testimonial-author">
                <div class="author-avatar">
                  <?php
                  $initials = strtoupper(substr($testimonial['firstName'] ?? 'U', 0, 1) . substr($testimonial['lastName'] ?? 'S', 0, 1));
                  echo $initials;
                  ?>
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

      <!-- Row 2: Right to Left -->
      <?php if (count($row2) > 0): ?>
        <div class="testimonials-marquee marquee-rtl">
          <div class="testimonials-track">
            <?php foreach ($row2 as $testimonial): ?>
              <div class="testimonial-card-marquee">
                <div class="testimonial-stars">
                  <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>
                    <i class="fas fa-star"></i>
                  <?php endfor; ?>
                </div>
                <p class="testimonial-text">"<?php echo htmlspecialchars($testimonial['message']); ?>"</p>
                <div class="testimonial-author">
                  <div class="author-avatar">
                    <?php
                    $initials = strtoupper(substr($testimonial['firstName'] ?? 'U', 0, 1) . substr($testimonial['lastName'] ?? 'S', 0, 1));
                    echo $initials;
                    ?>
                  </div>
                  <div class="author-info">
                    <strong><?php echo htmlspecialchars($testimonial['user_name']); ?></strong>
                    <small>Pengguna PoncolVerse</small>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>

            <!-- Duplicate untuk seamless loop -->
            <?php foreach ($row2 as $testimonial): ?>
              <div class="testimonial-card-marquee">
                <div class="testimonial-stars">
                  <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>
                    <i class="fas fa-star"></i>
                  <?php endfor; ?>
                </div>
                <p class="testimonial-text">"<?php echo htmlspecialchars($testimonial['message']); ?>"</p>
                <div class="testimonial-author">
                  <div class="author-avatar">
                    <?php
                    $initials = strtoupper(substr($testimonial['firstName'] ?? 'U', 0, 1) . substr($testimonial['lastName'] ?? 'S', 0, 1));
                    echo $initials;
                    ?>
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

    <!-- Add Testimonial Form -->
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
            <textarea id="testimonialMessage" rows="5" placeholder="Tulis pengalaman Anda menggunakan PoncolVerse..."
              required></textarea>
          </div>

          <button type="submit" class="btn-submit-testimonial">
            <i class="fas fa-paper-plane"></i> Kirim Testimonial
          </button>
        </form>
      <?php else: ?>
        <p style="text-align: center; color: #aaa; padding: 2rem;">
          <a href="#" onclick="openLogin(); return false;" style="color: #ff003c; font-weight: 600;">Login</a> untuk
          memberikan testimonial
        </p>
      <?php endif; ?>
    </div>
  </section>

  <footer class="footer">
    <div class="footer-content">
      <div class="footer-column">
        <h3>PoncolVerse</h3>
        <p style="color: #aaa; line-height: 1.6;">Platform streaming film terbaik dengan pengalaman menonton yang tak
          terlupakan. Nikmati berbagai film berkualitas dengan mudah.</p>
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
      <p>&copy; 2025 PoncolVerse. All rights reserved. Made with <i class="fas fa-heart" style="color: #ff003c;"></i>
        for movie lovers.</p>
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
        <div class="loading">
          <div class="spinner"></div>Memuat watchlist...
        </div>
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

  <!-- AOS Animation Library -->
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script src="assets/js/main.js"></script>
</body>

</html>