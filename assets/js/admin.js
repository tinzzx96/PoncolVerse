console.log("Admin.js loaded");

let allMovies = [];
let currentEditId = null;
let castIndex = 0;

// ========== TOAST NOTIFICATION SYSTEM ==========
function showToast(type, title, message, duration = 3000) {
  const container = document.getElementById("toastContainer");
  const toast = document.createElement("div");
  toast.className = `toast ${type}`;

  const iconMap = {
    success: "fa-check-circle",
    error: "fa-exclamation-circle",
    info: "fa-info-circle",
  };

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

async function loadMovies() {
  try {
    const response = await fetch("../API/movies/get_movies.php");
    if (!response.ok) throw new Error("HTTP error! status: " + response.status);

    const text = await response.text();
    const movies = JSON.parse(text);
    allMovies = Array.isArray(movies) ? movies : [];
    renderMoviesTable();
  } catch (error) {
    console.error("❌ Error:", error);
    const tbody = document.getElementById("moviesTableBody");
    tbody.innerHTML =
      '<tr><td colspan="6" class="no-data">❌ Gagal memuat: ' +
      error.message +
      "</td></tr>";
    showToast("error", "Gagal Memuat", "Tidak dapat memuat daftar film");
  }
}

function renderMoviesTable() {
  const tbody = document.getElementById("moviesTableBody");

  if (!Array.isArray(allMovies) || allMovies.length === 0) {
    tbody.innerHTML =
      '<tr><td colspan="6" class="no-data">🎬 Belum ada film. Tambahkan film pertama!</td></tr>';
    return;
  }

  tbody.innerHTML = allMovies
    .map((movie) => {
      const genreText = Array.isArray(movie.genre)
        ? movie.genre.join(", ")
        : movie.genre || "-";
      const posterSrc =
        movie.poster || "https://via.placeholder.com/60x90?text=No+Image";

      return `
      <tr>
        <td><img src="${posterSrc}" alt="${movie.title}" onerror="this.src='https://via.placeholder.com/60x90?text=Error'" /></td>
        <td>${movie.title}</td>
        <td>${movie.rating}</td>
        <td>${movie.year}</td>
        <td>${genreText}</td>
        <td class="action-btns">
          <button onclick="editMovie(${movie.id})" class="edit-btn"><i class="fas fa-edit"></i> Edit</button>
          <button onclick="deleteMovie(${movie.id})" class="delete-btn"><i class="fas fa-trash"></i> Hapus</button>
        </td>
      </tr>
    `;
    })
    .join("");
}

function openAddModal() {
  currentEditId = null;
  document.getElementById("modalTitle").textContent = "Tambah Film Baru";
  document.getElementById("movieForm").reset();
  document.getElementById("movieId").value = "";
  document.getElementById("castContainer").innerHTML = "";
  document
    .querySelectorAll(".genre-checkbox input")
    .forEach((cb) => (cb.checked = false));
  document.getElementById("poster").setAttribute("required", "required");
  document.getElementById("movieModal").classList.add("active");
  document.body.style.overflow = "hidden";
}

function closeModal() {
  document.getElementById("movieModal").classList.remove("active");
  document.body.style.overflow = "auto";
}

function editMovie(id) {
  const movie = allMovies.find((m) => m.id === id);
  if (!movie) return;

  currentEditId = id;
  document.getElementById("modalTitle").textContent = "Edit Film";
  document.getElementById("movieId").value = id;
  document.getElementById("title").value = movie.title;
  document.getElementById("rating").value = movie.rating;
  document.getElementById("year").value = movie.year;
  document.getElementById("duration").value = movie.duration;
  document.getElementById("trailer").value = movie.trailer;
  document.getElementById("watchLink").value = movie.watchLink || "";
  document.getElementById("director").value = movie.director || "";
  document.getElementById("plot").value = movie.plot || "";

  document.querySelectorAll(".genre-checkbox input").forEach((cb) => {
    cb.checked = Array.isArray(movie.genre) && movie.genre.includes(cb.value);
  });

  document.getElementById("poster").removeAttribute("required");

  // Populate cast members
  const container = document.getElementById("castContainer");
  container.innerHTML = "";
  if (Array.isArray(movie.cast) && movie.cast.length > 0) {
    movie.cast.forEach((cast) => {
      const castItem = document.createElement("div");
      castItem.className = "cast-item";
      const photoPreview = cast.photo ? `<img src="../${cast.photo}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px; margin-top: 5px; display: block;">` : '';
      castItem.innerHTML = `
        <div class="cast-item-grid">
          <div class="form-group">
            <label>Nama Aktor</label>
            <input type="text" name="actor_names[]" value="${cast.name}" placeholder="Tom Holland">
          </div>
          <div class="form-group">
            <label>Nama Karakter</label>
            <input type="text" name="character_names[]" value="${cast.character}" placeholder="Peter Parker">
          </div>
          <div class="form-group">
            <label>Foto Aktor (Kosongkan jika tidak ingin ganti)</label>
            <input type="file" name="actor_photos[]" accept="image/*">
            <input type="hidden" name="existing_actor_photos[]" value="${cast.photo}">
            ${photoPreview}
          </div>
          <div class="form-group">
            <button type="button" class="remove-cast-btn" onclick="this.closest('.cast-item').remove()"><i class="fas fa-trash"></i></button>
          </div>
        </div>
      `;
      container.appendChild(castItem);
    });
  }

  document.getElementById("movieModal").classList.add("active");
  document.body.style.overflow = "hidden";
}

async function deleteMovie(id) {
  if (!confirm("Yakin ingin menghapus film ini?")) return;

  showToast("info", "Menghapus...", "Sedang menghapus film dari database");

  try {
    const formData = new FormData();
    formData.append("id", id);

    const response = await fetch("movies/delete_movie.php", {
      method: "POST",
      body: formData,
    });

    const result = await response.json();

    if (result.success) {
      showToast("success", "Berhasil!", result.message);
      loadMovies();
    } else {
      showToast("error", "Gagal!", result.message);
    }
  } catch (error) {
    showToast("error", "Error!", "Terjadi kesalahan: " + error.message);
  }
}

function addCastField() {
  const container = document.getElementById("castContainer");
  const castItem = document.createElement("div");
  castItem.className = "cast-item";
  castItem.innerHTML = `
    <div class="cast-item-grid">
      <div class="form-group">
        <label>Nama Aktor</label>
        <input type="text" name="actor_names[]" placeholder="Tom Holland">
      </div>
      <div class="form-group">
        <label>Nama Karakter</label>
        <input type="text" name="character_names[]" placeholder="Peter Parker">
      </div>
      <div class="form-group">
        <label>Foto Aktor</label>
        <input type="file" name="actor_photos[]" accept="image/*">
        <input type="hidden" name="existing_actor_photos[]" value="">
      </div>
      <div class="form-group">
        <button type="button" class="remove-cast-btn" onclick="this.closest('.cast-item').remove()"><i class="fas fa-trash"></i></button>
      </div>
    </div>
  `;
  container.appendChild(castItem);
  castIndex++;
}

document
  .getElementById("movieForm")
  .addEventListener("submit", async function (e) {
    e.preventDefault();

    const selectedGenres = Array.from(
      document.querySelectorAll(".genre-checkbox input:checked")
    ).map((cb) => cb.value);

    if (selectedGenres.length === 0) {
      showToast(
        "error",
        "Genre Kosong!",
        "Pilih minimal satu genre untuk film ini"
      );
      return;
    }

    const formData = new FormData(this);
    const movieId = document.getElementById("movieId").value;

    console.log("Form submission - Movie ID:", movieId);
    console.log("Has poster file:", formData.has("poster"));

    const posterFile = document.getElementById("poster").files[0];
    if (posterFile) {
      console.log("Poster file:", posterFile.name, posterFile.size, "bytes");
    }

    showToast("info", "Menyimpan...", "Sedang mengupload film ke server");

    try {
      const url = movieId ? 'movies/update_movie.php' : 'movies/add_movie.php';
      const response = await fetch(url, {
        method: "POST",
        body: formData,
      });

      const responseText = await response.text();
      console.log("Response:", responseText);

      const result = JSON.parse(responseText);

      if (result.success) {
        showToast("success", "Berhasil! 🎉", result.message);
        closeModal();
        setTimeout(() => loadMovies(), 500);
      } else {
        showToast("error", "Gagal!", result.message);
        console.error("Server error:", result.message);
      }
    } catch (error) {
      showToast("error", "Error!", "Terjadi kesalahan: " + error.message);
      console.error("Upload error:", error);
    }
  });

window.addEventListener("load", () => {
  loadMovies();
});

// ── FEATURED PANEL JS ──
let featuredAllMovies = [];
 
async function loadFeaturedPanel() {
  try {
    const res  = await fetch('movies/manage_featured.php?action=get_all');
    const data = await res.json();
    if (!data.success) return;
 
    featuredAllMovies = data.movies;
    renderFeaturedGrid(featuredAllMovies);
    updateFeaturedBadge();
  } catch (e) {
    console.error('Error loading featured panel:', e);
  }
}
 
function updateFeaturedBadge() {
  const count = featuredAllMovies.filter(m => m.is_featured).length;
  const el = document.getElementById('featuredCountBadge');
  if (el) el.textContent = count + ' / 20';
}
 
function renderFeaturedGrid(movies) {
  const grid = document.getElementById('featuredGrid');
  if (!movies || movies.length === 0) {
    grid.innerHTML = '<p style="color:#aaa;text-align:center;padding:2rem;grid-column:1/-1;">Belum ada film.</p>';
    return;
  }
 
  grid.innerHTML = movies.map(m => `
    <div class="f-card ${m.is_featured ? 'is-featured' : ''}"
         onclick="toggleFeatured(${m.id}, ${m.is_featured})"
         id="fcard-${m.id}"
         title="${m.is_featured ? 'Klik untuk hapus dari Film Populer' : 'Klik untuk tambah ke Film Populer'}">
      <img src="${m.poster}"
           alt="${m.title}"
           onerror="this.src='https://via.placeholder.com/150x200?text=No+Image'">
      <div class="f-card-badge ${m.is_featured ? 'on' : 'off'}">
        ${m.is_featured ? '★ Populer' : '+ Tambah'}
      </div>
      <div class="f-card-info">
        <div class="f-card-title">${m.title}</div>
        <div class="f-card-meta">
          <span>⭐ ${m.rating}</span>
          <span>${m.year}</span>
        </div>
      </div>
    </div>
  `).join('');
}
 
async function toggleFeatured(movieId, isFeatured) {
  const action = isFeatured ? 'remove' : 'add';
  const label  = isFeatured ? 'Menghapus...' : 'Menambahkan...';
 
  showToast('info', label, 'Mohon tunggu');
 
  try {
    const formData = new FormData();
    formData.append('action', action);
    formData.append('movie_id', movieId);
 
    const res  = await fetch('movies/manage_featured.php', { method: 'POST', body: formData });
    const data = await res.json();
 
    if (data.success) {
      showToast('success', 'Berhasil!', data.message);
 
      // Update local state
      const idx = featuredAllMovies.findIndex(m => m.id === movieId);
      if (idx !== -1) {
        featuredAllMovies[idx].is_featured = isFeatured ? 0 : 1;
      }
 
      // Re-render card saja (tidak reload semua)
      const card = document.getElementById('fcard-' + movieId);
      if (card) {
        const newFeatured = !isFeatured;
        card.className = 'f-card' + (newFeatured ? ' is-featured' : '');
        card.title     = newFeatured ? 'Klik untuk hapus dari Film Populer' : 'Klik untuk tambah ke Film Populer';
        card.onclick   = () => toggleFeatured(movieId, newFeatured);
        card.querySelector('.f-card-badge').className = 'f-card-badge ' + (newFeatured ? 'on' : 'off');
        card.querySelector('.f-card-badge').textContent = newFeatured ? '★ Populer' : '+ Tambah';
      }
 
      updateFeaturedBadge();
    } else {
      showToast('error', 'Gagal', data.message);
    }
  } catch (e) {
    showToast('error', 'Error', 'Terjadi kesalahan. Coba lagi.');
  }
}
 
function filterFeaturedList() {
  const q = document.getElementById('featuredSearch').value.toLowerCase().trim();
  if (!q) {
    renderFeaturedGrid(featuredAllMovies);
    return;
  }
  const filtered = featuredAllMovies.filter(m => m.title.toLowerCase().includes(q));
  renderFeaturedGrid(filtered);
}
 
// Load saat halaman siap
window.addEventListener('load', () => {
  loadFeaturedPanel();
});