// ===== GLOBAL VARIABLES =====
let allMovies = [];
let searchTimeout;

// ===== PARTICLE ANIMATION =====
function createParticles() {
  const particlesContainer = document.getElementById("particles");
  if (!particlesContainer) return; // Safety check
  
  for (let i = 0; i < 30; i++) {
    const particle = document.createElement("div");
    particle.className = "particle";
    const left = Math.random() * 100;
    const delay = Math.random() * 15;
    const duration = 15 + Math.random() * 10;
    particle.style.left = `${left}%`;
    particle.style.animationDelay = `${delay}s`;
    particle.style.animationDuration = `${duration}s`;
    particlesContainer.appendChild(particle);
  }
}

// ===== SCROLL FUNCTIONS =====
function scrollToMovies() {
  const filmSection = document.getElementById("film");
  if (filmSection) {
    filmSection.scrollIntoView({ behavior: "smooth" });
  }
}

// ===== LOAD POPULAR MOVIES =====
async function loadPopularMovies() {
  const grid = document.getElementById("movieGrid");
  if (!grid) return;
  
  try {
    const response = await fetch("API/movies/get_popular_movies.php");
    const movies = await response.json();
    renderPopularMovies(movies);
  } catch (error) {
    console.error("Error loading popular movies:", error);
    grid.innerHTML = '<div class="no-movies">Gagal memuat film populer. Silakan refresh halaman.</div>';
  }
}

// ===== LOAD ALL MOVIES =====
async function loadAllMovies() {
  const grid = document.getElementById("allMoviesGrid");
  if (!grid) return;
  
  try {
    const response = await fetch("API/movies/get_all_movies.php");
    const movies = await response.json();
    allMovies = movies;
    renderAllMovies(movies);
  } catch (error) {
    console.error("Error loading all movies:", error);
    grid.innerHTML = '<div class="no-movies">Gagal memuat film. Silakan refresh halaman.</div>';
  }
}

// ===== RENDER FUNCTIONS =====
function renderPopularMovies(movies) {
  const grid = document.getElementById("movieGrid");
  if (!grid) return;
  
  if (movies.length === 0) {
    grid.innerHTML = '<div class="no-movies">Belum ada film populer.</div>';
    return;
  }
  
  grid.innerHTML = movies.map(movie => createMovieCard(movie)).join("");
}

function renderAllMovies(movies) {
  const grid = document.getElementById("allMoviesGrid");
  if (!grid) return;
  
  if (movies.length === 0) {
    grid.innerHTML = '<div class="no-movies">Belum ada film.</div>';
    return;
  }
  
  grid.innerHTML = movies.map(movie => createMovieCard(movie)).join("");
}

// ===== CREATE MOVIE CARD =====
function createMovieCard(movie) {
  const watchButton = movie.watchLink && movie.watchLink !== "#"
    ? `<a href="javascript:void(0)" onclick="startWatchRedirect(event, '${movie.watchLink}')" class="watch-btn"><i class="fas fa-play"></i> Tonton</a>`
    : '<button class="watch-btn" onclick="alert(\'Link nonton belum tersedia\')"><i class="fas fa-play"></i> Tonton</button>';
  
  return `
    <div class="movie-card">
      <img src="${movie.poster}" alt="${movie.title}" class="movie-poster">
      <div class="overlay">
        ${watchButton}
        <div class="overlay-row">
          <button class="trailer-btn" onclick="openTrailer('${movie.trailer}')"><i class="fab fa-youtube"></i> Trailer</button>
          <button class="detail-btn" onclick="openMovieDetail(${movie.id})"><i class="fas fa-info-circle"></i> Detail</button>
        </div>
      </div>
      <div class="movie-info">
        <h3 class="movie-title">${movie.title}</h3>
        <div class="movie-rating"><i class="fas fa-star" style="color: #ffc107; margin-right: 4px;"></i>${movie.rating}</div>
      </div>
    </div>
  `;
}

// ===== SEARCH FUNCTIONALITY =====
const searchInput = document.getElementById("searchInput");
if (searchInput) {
  searchInput.addEventListener("input", (e) => {
    const searchTerm = e.target.value.trim();
    clearTimeout(searchTimeout);

    if (searchTerm.length < 2) {
      loadAllMovies();
      return;
    }

    searchTimeout = setTimeout(async () => {
      try {
        const response = await fetch(`API/movies/search_movies.php?query=${encodeURIComponent(searchTerm)}`);
        const movies = await response.json();
        renderAllMovies(movies);
      } catch (error) {
        console.error("Error searching movies:", error);
        const grid = document.getElementById("allMoviesGrid");
        if (grid) {
          grid.innerHTML = '<div class="no-movies">Gagal melakukan pencarian. Silakan coba lagi.</div>';
        }
      }
    }, 500);
  });
}

// ===== FILTER BY GENRE =====
function filterAllMoviesByGenre(genreName) {
  const filteredMovies = allMovies.filter(
    (movie) => Array.isArray(movie.genre) && movie.genre.includes(genreName)
  );
  renderAllMovies(filteredMovies);
  const semuaFilmSection = document.getElementById("semua-film");
  if (semuaFilmSection) {
    semuaFilmSection.scrollIntoView({ behavior: "smooth" });
  }
}

// ===== MOVIE DETAIL =====
function openMovieDetail(movieId) {
  window.location.href = `movie-detail.php?id=${movieId}`;
}

// ===== MODAL FUNCTIONS =====
function openLogin() {
  const modal = document.getElementById("loginModal");
  if (modal) {
    modal.classList.add("active");
    document.body.style.overflow = "hidden";
  }
}

function closeLogin() {
  const modal = document.getElementById("loginModal");
  if (modal) {
    modal.classList.remove("active");
    document.body.style.overflow = "";
  }
}

function openRegister() {
  const modal = document.getElementById("registerModal");
  if (modal) {
    modal.classList.add("active");
    document.body.style.overflow = "hidden";
  }
}

function closeRegister() {
  const modal = document.getElementById("registerModal");
  if (modal) {
    modal.classList.remove("active");
    document.body.style.overflow = "";
  }
}

function openTrailer(url) {
  const container = document.getElementById("trailerContainer");
  const modal = document.getElementById("trailerModal");
  
  if (container && modal) {
    container.innerHTML = `<iframe width="100%" height="600" src="${url}?autoplay=1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
    modal.classList.add("active");
    document.body.style.overflow = "hidden";
  }
}

function closeTrailer() {
  const container = document.getElementById("trailerContainer");
  const modal = document.getElementById("trailerModal");
  
  if (container && modal) {
    modal.classList.remove("active");
    container.innerHTML = "";
    document.body.style.overflow = "";
  }
}

// ===== VALIDATION =====
function validateEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// ===== LOGIN FORM =====
const loginForm = document.getElementById("loginForm");
if (loginForm) {
  loginForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    
    const errorMessages = document.querySelectorAll("#loginForm .error-message");
    errorMessages.forEach((el) => (el.style.display = "none"));

    const email = document.getElementById("loginEmail").value;
    const password = document.getElementById("loginPassword").value;

    if (!validateEmail(email)) {
      const emailError = document.getElementById("loginEmailError");
      if (emailError) emailError.style.display = "block";
      showToast("error", "Email Tidak Valid", "Silakan masukkan email yang benar");
      return;
    }

    showToast("info", "Memproses...", "Sedang memverifikasi akun Anda");

    try {
      const formData = new FormData();
      formData.append("email", email);
      formData.append("password", password);

      const response = await fetch("auth/login.php", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      if (result.success) {
        showToast("success", "Login Berhasil! 🎉", `Selamat datang kembali, ${result.user.firstName}!`);
        closeLogin();
        setTimeout(() => {
          window.location.reload();
        }, 1500);
      } else {
        showToast("error", "Login Gagal", result.message);

        if (result.message.includes("Email")) {
          const emailError = document.getElementById("loginEmailError");
          if (emailError) {
            emailError.textContent = result.message;
            emailError.style.display = "block";
          }
        } else {
          const passwordError = document.getElementById("loginPasswordError");
          if (passwordError) {
            passwordError.textContent = result.message;
            passwordError.style.display = "block";
          }
        }
      }
    } catch (error) {
      showToast("error", "Terjadi Kesalahan", "Tidak dapat terhubung ke server. Silakan coba lagi.");
      console.error("Login error:", error);
    }
  });
}

// ===== REGISTER FORM =====
const registerForm = document.getElementById("registerForm");
if (registerForm) {
  registerForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    
    const errorMessages = document.querySelectorAll("#registerForm .error-message");
    errorMessages.forEach((el) => (el.style.display = "none"));

    const firstName = document.getElementById("firstName").value.trim();
    const lastName = document.getElementById("lastName").value.trim();
    const email = document.getElementById("registerEmail").value;
    const password = document.getElementById("registerPassword").value;
    const recoveryEmail = document.getElementById("recoveryEmail").value;

    let isValid = true;

    if (!firstName) {
      const firstNameError = document.getElementById("firstNameError");
      if (firstNameError) firstNameError.style.display = "block";
      isValid = false;
    }

    if (!lastName) {
      const lastNameError = document.getElementById("lastNameError");
      if (lastNameError) lastNameError.style.display = "block";
      isValid = false;
    }

    if (!validateEmail(email)) {
      const registerEmailError = document.getElementById("registerEmailError");
      if (registerEmailError) registerEmailError.style.display = "block";
      isValid = false;
    }

    if (password.length < 6) {
      const registerPasswordError = document.getElementById("registerPasswordError");
      if (registerPasswordError) registerPasswordError.style.display = "block";
      isValid = false;
    }

    if (!validateEmail(recoveryEmail)) {
      const recoveryEmailError = document.getElementById("recoveryEmailError");
      if (recoveryEmailError) recoveryEmailError.style.display = "block";
      isValid = false;
    }

    if (email === recoveryEmail) {
      const recoveryEmailError = document.getElementById("recoveryEmailError");
      if (recoveryEmailError) {
        recoveryEmailError.textContent = "Email pemulihan tidak boleh sama dengan email utama";
        recoveryEmailError.style.display = "block";
      }
      isValid = false;
    }

    if (!isValid) {
      showToast("error", "Form Tidak Lengkap", "Silakan lengkapi semua field yang diperlukan");
      return;
    }

    showToast("info", "Mendaftar...", "Sedang membuat akun baru untuk Anda");

    try {
      const formData = new FormData();
      formData.append("firstName", firstName);
      formData.append("lastName", lastName);
      formData.append("email", email);
      formData.append("password", password);
      formData.append("recoveryEmail", recoveryEmail);

      const response = await fetch("auth/register.php", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      if (result.success) {
        showToast("success", "Pendaftaran Berhasil! 🎉", "Akun Anda telah dibuat. Silakan login");
        closeRegister();
        registerForm.reset();

        setTimeout(() => {
          openLogin();
        }, 1000);
      } else {
        showToast("error", "Pendaftaran Gagal", result.message);
      }
    } catch (error) {
      showToast("error", "Terjadi Kesalahan", "Tidak dapat terhubung ke server. Silakan coba lagi.");
      console.error("Registration error:", error);
    }
  });
}

// ===== MODAL TOGGLE LINKS =====
const showRegisterLink = document.getElementById("showRegister");
if (showRegisterLink) {
  showRegisterLink.addEventListener("click", () => {
    closeLogin();
    openRegister();
  });
}

const showLoginLink = document.getElementById("showLogin");
if (showLoginLink) {
  showLoginLink.addEventListener("click", () => {
    closeRegister();
    openLogin();
  });
}

// ===== TOAST NOTIFICATIONS =====
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

// ===== USER PROFILE DROPDOWN =====
document.addEventListener("DOMContentLoaded", () => {
  const userProfile = document.querySelector(".user-profile");
  const userDropdown = document.querySelector(".user-dropdown");

  if (userProfile && userDropdown) {
    userProfile.addEventListener("click", (e) => {
      e.stopPropagation();
      userDropdown.classList.toggle("active");
    });

    document.addEventListener("click", (e) => {
      if (!userProfile.contains(e.target)) {
        userDropdown.classList.remove("active");
      }
    });

    userDropdown.addEventListener("click", (e) => {
      e.stopPropagation();
    });
  }
});

// ===== SUBSCRIBE TO PLAN =====
function subscribePlan(planId, planName, price) {
  const buttons = document.querySelectorAll('.subscribe-btn');
  buttons.forEach(btn => btn.disabled = true);
  
  showToast('info', 'Processing...', 'Mohon tunggu');
  
  fetch('auth/check_login.php', {
    method: 'GET',
    cache: 'no-cache',
    headers: {
      'Cache-Control': 'no-cache'
    }
  })
    .then(res => {
      if (!res.ok) throw new Error('Network error');
      return res.json();
    })
    .then(data => {
      if (!data.logged_in) {
        showToast('error', 'Login Required', 'Silakan login terlebih dahulu!');
        buttons.forEach(btn => btn.disabled = false);
        setTimeout(() => openLogin(), 300);
        return;
      }

      window.location.href = `payment.php?plan_id=${planId}&plan_name=${encodeURIComponent(planName)}&price=${price}`;
    })
    .catch(error => {
      console.error('Error:', error);
      showToast('error', 'Error', 'Gagal terhubung ke server. Coba lagi.');
      buttons.forEach(btn => btn.disabled = false);
    });
}

// ===== INITIALIZE ON PAGE LOAD =====
window.addEventListener("load", () => {
  createParticles();
  loadPopularMovies();
  loadAllMovies();
  window.scrollTo(0, 0);
});

// ===== WATCHLIST FUNCTIONS =====
let currentShareMovieId = null;
let currentShareMovieTitle = null;

// Load watchlist count
async function loadWatchlistCount() {
  const countElement = document.getElementById('watchlistCount');
  if (!countElement) return;
  
  try {
    const response = await fetch('API/watchlist/get_watchlist.php');
    const movies = await response.json();
    countElement.textContent = movies.length;
  } catch (error) {
    console.error('Error loading watchlist count:', error);
  }
}

// Open watchlist modal
async function openWatchlist() {
  const modal = document.getElementById('watchlistModal');
  if (!modal) return;
  
  modal.classList.add('active');
  document.body.style.overflow = 'hidden';
  
  // Load watchlist
  const grid = document.getElementById('watchlistGrid');
  grid.innerHTML = '<div class="loading"><div class="spinner"></div>Memuat watchlist...</div>';
  
  try {
    const response = await fetch('API/watchlist/get_watchlist.php');
    const movies = await response.json();
    
    if (movies.length === 0) {
      grid.innerHTML = '<div class="no-movies">Watchlist kosong. Tambahkan film favorit Anda!</div>';
      return;
    }
    
    grid.innerHTML = movies.map(movie => `
      <div class="watchlist-item" onclick="window.location.href='movie-detail.php?id=${movie.id}'">
        <img src="${movie.poster}" alt="${movie.title}">
        <button class="remove-watchlist-btn" onclick="event.stopPropagation(); removeFromWatchlist(${movie.id})" title="Hapus dari watchlist">
          <i class="fas fa-times"></i>
        </button>
        <div class="watchlist-item-info">
          <div class="watchlist-item-title">${movie.title}</div>
          <div class="watchlist-item-meta">
            <span>⭐ ${movie.rating}</span>
            <span>${movie.year}</span>
          </div>
        </div>
      </div>
    `).join('');
    
  } catch (error) {
    console.error('Error loading watchlist:', error);
    grid.innerHTML = '<div class="no-movies">Gagal memuat watchlist. Silakan coba lagi.</div>';
  }
}

// Close watchlist modal
function closeWatchlist() {
  const modal = document.getElementById('watchlistModal');
  if (modal) {
    modal.classList.remove('active');
    document.body.style.overflow = '';
  }
}

// Add/Remove from watchlist (Toggle)
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
      
      // Update count
      loadWatchlistCount();
      
      // Update button icon if on detail page
      updateWatchlistButton(movieId, result.action);
    } else {
      if (result.message.includes('login')) {
        showToast('error', 'Login Required', 'Silakan login terlebih dahulu!');
        setTimeout(() => openLogin(), 1000);
      } else {
        showToast('error', 'Gagal', result.message);
      }
    }
    
  } catch (error) {
    console.error('Error toggling watchlist:', error);
    showToast('error', 'Error', 'Terjadi kesalahan. Silakan coba lagi.');
  }
}

// Remove from watchlist (for watchlist modal)
async function removeFromWatchlist(movieId) {
  await toggleWatchlist(movieId);
  
  // Reload watchlist modal
  setTimeout(() => {
    if (document.getElementById('watchlistModal').classList.contains('active')) {
      openWatchlist();
    }
  }, 500);
}

// Update watchlist button icon
function updateWatchlistButton(movieId, action) {
  const buttons = document.querySelectorAll(`[data-movie-id="${movieId}"]`);
  buttons.forEach(btn => {
    const icon = btn.querySelector('i');
    if (icon) {
      if (action === 'added') {
        icon.className = 'fas fa-bookmark';
        btn.style.background = 'linear-gradient(135deg, #ff003c, #ff4d7a)';
      } else {
        icon.className = 'far fa-bookmark';
        btn.style.background = 'rgba(255, 255, 255, 0.1)';
      }
    }
  });
}

// ===== SHARE FUNCTIONS =====
function openShare(movieId, movieTitle) {
  currentShareMovieId = movieId;
  currentShareMovieTitle = movieTitle;
  
  const modal = document.getElementById('shareModal');
  if (!modal) return;
  
  // Generate share link
  const shareUrl = `${window.location.origin}${window.location.pathname.replace('index.php', '')}movie-detail.php?id=${movieId}`;
  
  const shareLinkInput = document.getElementById('shareLink');
  if (shareLinkInput) {
    shareLinkInput.value = shareUrl;
  }
  
  modal.classList.add('active');
  document.body.style.overflow = 'hidden';
}

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
  input.setSelectionRange(0, 99999); // For mobile
  
  try {
    document.execCommand('copy');
    showToast('success', 'Link Disalin!', 'Link film telah disalin ke clipboard');
  } catch (err) {
    // Fallback for modern browsers
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

// Initialize watchlist count on page load
window.addEventListener('load', () => {
  loadWatchlistCount();
});

// ===== TESTIMONIAL FORM SUBMISSION =====
const testimonialForm = document.getElementById('testimonialForm');
if (testimonialForm) {
  testimonialForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const rating = document.getElementById('testimonialRating').value;
    const message = document.getElementById('testimonialMessage').value.trim();
    
    if (!rating) {
      showToast('error', 'Rating Wajib', 'Silakan pilih rating terlebih dahulu');
      return;
    }
    
    if (message.length < 10) {
      showToast('error', 'Pesan Terlalu Pendek', 'Minimal 10 karakter untuk testimonial');
      return;
    }
    
    showToast('info', 'Mengirim...', 'Sedang mengirim testimonial Anda');
    
    try {
      const formData = new FormData();
      formData.append('rating', rating);
      formData.append('message', message);
      
      const response = await fetch('API/testimonials/add_testimonial.php', {
        method: 'POST',
        body: formData
      });
      
      const result = await response.json();
      
      if (result.success) {
        showToast('success', 'Terkirim! 🎉', 'Testimonial Anda berhasil dikirim. Menunggu approval admin.');
        testimonialForm.reset();
      } else {
        showToast('error', 'Gagal', result.message);
      }
    } catch (error) {
      console.error('Error submitting testimonial:', error);
      showToast('error', 'Error', 'Terjadi kesalahan. Silakan coba lagi.');
    }
  });
}

// ===== PROFILE PHOTO UPLOAD FUNCTIONS =====
function openPhotoUpload() {
  const modal = document.getElementById('photoUploadModal');
  if (modal) {
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
  }
}

function closePhotoUpload() {
  const modal = document.getElementById('photoUploadModal');
  if (modal) {
    modal.classList.remove('active');
    document.body.style.overflow = '';
    
    // Reset form
    const form = document.getElementById('photoUploadForm');
    if (form) form.reset();
    
    // Reset preview if no existing photo
    const preview = document.getElementById('photoPreview');
    const previewImg = document.getElementById('previewImage');
    if (preview && !previewImg) {
      preview.innerHTML = `
        <div class="photo-placeholder">
          <i class="fas fa-user"></i>
          <p>No photo yet</p>
        </div>
      `;
    }
  }
}

// Preview photo before upload
const photoInput = document.getElementById('photoInput');
if (photoInput) {
  photoInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
      showToast('error', 'Format Tidak Didukung', 'Gunakan JPG, PNG, GIF, atau WEBP');
      e.target.value = '';
      return;
    }
    
    // Validate file size (2MB = 2097152 bytes)
    if (file.size > 2097152) {
      showToast('error', 'File Terlalu Besar', 'Maksimal ukuran file adalah 2MB');
      e.target.value = '';
      return;
    }
    
    // Show preview
    const reader = new FileReader();
    reader.onload = function(event) {
      const preview = document.getElementById('photoPreview');
      if (preview) {
        preview.innerHTML = `<img src="${event.target.result}" alt="Preview" id="previewImage">`;
      }
    };
    reader.readAsDataURL(file);
  });
}

// Upload photo form submission
const photoUploadForm = document.getElementById('photoUploadForm');
if (photoUploadForm) {
  photoUploadForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const fileInput = document.getElementById('photoInput');
    const file = fileInput.files[0];
    
    if (!file) {
      showToast('error', 'Pilih File', 'Silakan pilih foto terlebih dahulu');
      return;
    }
    
    const submitBtn = document.getElementById('uploadPhotoBtn');
    const originalBtnText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
    
    showToast('info', 'Uploading...', 'Sedang mengupload foto profil Anda');
    
    try {
      const formData = new FormData();
      formData.append('profile_photo', file);
      
      const response = await fetch('API/user/upload_profile_photo.php', {
        method: 'POST',
        body: formData
      });
      
      const result = await response.json();
      
      if (result.success) {
        showToast('success', 'Berhasil! 🎉', result.message);
        
        // Update all avatar images on the page
        const photoUrl = result.photo_url;
        updateAllAvatars(photoUrl);
        
        // Close modal after short delay
        setTimeout(() => {
          closePhotoUpload();
        }, 1000);
        
      } else {
        showToast('error', 'Upload Gagal', result.message);
      }
      
    } catch (error) {
      console.error('Error uploading photo:', error);
      showToast('error', 'Error', 'Terjadi kesalahan saat upload. Silakan coba lagi.');
    } finally {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalBtnText;
    }
  });
}

// Update all avatar instances on the page
function updateAllAvatars(photoUrl) {
  // Update navbar avatar
  const navbarProfile = document.querySelector('.navbar .user-profile');
  if (navbarProfile) {
    const existingAvatar = navbarProfile.querySelector('.user-avatar-img');
    const avatarInitials = navbarProfile.querySelector('.user-avatar');
    
    if (existingAvatar) {
      existingAvatar.src = photoUrl;
    } else if (avatarInitials) {
      // Replace initials with photo
      avatarInitials.outerHTML = `<img src="${photoUrl}" alt="Profile" class="user-avatar-img">`;
    }
  }
  
  // Update dropdown avatar
  const dropdownInfo = document.querySelector('.user-dropdown .user-info');
  if (dropdownInfo) {
    const existingAvatar = dropdownInfo.querySelector('.user-avatar-img');
    const avatarInitials = dropdownInfo.querySelector('.user-avatar');
    
    if (existingAvatar) {
      existingAvatar.src = photoUrl;
    } else if (avatarInitials) {
      // Replace initials with photo
    }
  }
}

// ===== AOS (ANIMATE ON SCROLL) INITIALIZATION =====
document.addEventListener('DOMContentLoaded', function() {
  // Initialize AOS
  if (typeof AOS !== 'undefined') {
    AOS.init({
      duration: 800,
      easing: 'ease-out-cubic',
      once: true,
      offset: 100,
      delay: 100
    });
  }
  
  // Animated Stats Counter
  const observerOptions = {
    threshold: 0.5,
    rootMargin: '0px'
  };
  
  const animateCounter = (element) => {
    const target = parseFloat(element.getAttribute('data-count'));
    const duration = 2000; // 2 seconds
    const increment = target / (duration / 16); // 60fps
    let current = 0;
    
    const updateCounter = () => {
      current += increment;
      if (current < target) {
        // Format number with comma separator for thousands
        if (target >= 1000) {
          element.textContent = Math.floor(current).toLocaleString('id-ID');
        } else {
          element.textContent = current.toFixed(1);
        }
        requestAnimationFrame(updateCounter);
      } else {
        // Final value
        if (target >= 1000) {
          element.textContent = Math.floor(target).toLocaleString('id-ID');
        } else {
          element.textContent = target.toFixed(1);
        }
      }
    };
    
    updateCounter();
  };
  
  const statsObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const statNumbers = entry.target.querySelectorAll('.stat-number[data-count]');
        statNumbers.forEach(stat => {
          if (!stat.classList.contains('animated')) {
            stat.classList.add('animated');
            animateCounter(stat);
          }
        });
        statsObserver.unobserve(entry.target);
      }
    });
  }, observerOptions);
  
  // Observe stats section
  const aboutStats = document.querySelector('.about-stats');
  if (aboutStats) {
    statsObserver.observe(aboutStats);
  }
});

// ===== WATCH REDIRECT MODAL =====
function startWatchRedirect(event, link) {
  if (event) {
    event.preventDefault();
  }
  
  // Create modal container if not exists
  let redirectModal = document.getElementById('watchRedirectModal');
  if (!redirectModal) {
    redirectModal = document.createElement('div');
    redirectModal.id = 'watchRedirectModal';
    redirectModal.style.position = 'fixed';
    redirectModal.style.top = '0';
    redirectModal.style.left = '0';
    redirectModal.style.width = '100vw';
    redirectModal.style.height = '100vh';
    redirectModal.style.backgroundColor = 'rgba(0, 0, 0, 0.85)';
    redirectModal.style.backdropFilter = 'blur(15px)';
    redirectModal.style.display = 'flex';
    redirectModal.style.justifyContent = 'center';
    redirectModal.style.alignItems = 'center';
    redirectModal.style.zIndex = '99999';
    redirectModal.style.opacity = '0';
    redirectModal.style.transition = 'opacity 0.4s ease';
    
    redirectModal.innerHTML = `
      <div style="
        background: linear-gradient(135deg, rgba(26, 26, 26, 0.95), rgba(15, 15, 15, 0.95));
        border: 2px solid rgba(255, 0, 60, 0.3);
        border-radius: 20px;
        padding: 3rem;
        max-width: 450px;
        width: 90%;
        text-align: center;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.8), 0 0 30px rgba(255, 0, 60, 0.15);
        transform: scale(0.9);
        transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
      " id="watchRedirectContent">
        <!-- Spinner Container -->
        <div id="watchRedirectSpinner" style="margin-bottom: 2rem;">
          <div style="
            width: 70px;
            height: 70px;
            border: 5px solid rgba(255, 0, 60, 0.1);
            border-top: 5px solid #ff003c;
            border-radius: 50%;
            margin: 0 auto;
            animation: watchSpin 1s linear infinite;
            box-shadow: 0 0 15px rgba(255, 0, 60, 0.4);
          "></div>
        </div>
        <h2 style="
          font-family: 'Orbitron', sans-serif;
          font-size: 1.8rem;
          margin-bottom: 1rem;
          background: linear-gradient(135deg, #ff003c, #ff4d7a);
          -webkit-background-clip: text;
          background-clip: text;
          color: transparent;
          text-shadow: 0 0 10px rgba(255, 0, 60, 0.3);
        " id="watchRedirectTitle">Menghubungkan ke Server</h2>
        <p style="
          color: #ccc;
          font-size: 1.1rem;
          line-height: 1.6;
        " id="watchRedirectText">Mohon tunggu sebentar...</p>
      </div>
    `;
    
    // Add CSS spinner animation dynamically
    const style = document.createElement('style');
    style.innerHTML = `
      @keyframes watchSpin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }
    `;
    document.head.appendChild(style);
    document.body.appendChild(redirectModal);
  }
  
  const content = document.getElementById('watchRedirectContent');
  const spinner = document.getElementById('watchRedirectSpinner');
  const title = document.getElementById('watchRedirectTitle');
  const text = document.getElementById('watchRedirectText');
  
  // Reset contents to initial spinner state
  spinner.style.display = 'block';
  title.innerText = 'Menghubungkan ke Server';
  text.innerText = 'Mohon tunggu sebentar...';
  
  // Show modal
  redirectModal.style.display = 'flex';
  // Force a reflow
  redirectModal.offsetHeight;
  redirectModal.style.opacity = '1';
  content.style.transform = 'scale(1)';
  document.body.style.overflow = 'hidden';
  
  // Step 1: Wait for 3 seconds of spinner loading
  setTimeout(() => {
    // Hide spinner
    spinner.style.display = 'none';
    title.innerText = 'Mempersiapkan Pemutar';
    
    // Step 2: Countdown 3 seconds
    let countdown = 3;
    text.innerHTML = `Segera Diarahkan Ke link Nonton Pada: <strong style="color: #ff003c; font-size: 1.3rem;">${countdown}</strong> detik`;
    
    const interval = setInterval(() => {
      countdown--;
      if (countdown > 0) {
        text.innerHTML = `Segera Diarahkan Ke link Nonton Pada: <strong style="color: #ff003c; font-size: 1.3rem;">${countdown}</strong> detik`;
      } else {
        clearInterval(interval);
        
        // Hide modal
        redirectModal.style.opacity = '0';
        content.style.transform = 'scale(0.9)';
        setTimeout(() => {
          redirectModal.style.display = 'none';
          document.body.style.overflow = '';
        }, 400);
        
        // Open watch link in new tab
        window.open(link, '_blank');
      }
    }, 1000);
    
  }, 3000);
}

