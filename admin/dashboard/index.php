<?php
require_once '../../config/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — PoncolVerse Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&family=Syne:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
:root {
  --red:       #ff003c;
  --red-dim:   rgba(255,0,60,0.12);
  --red-glow:  rgba(255,0,60,0.35);
  --green:     #00e676;
  --blue:      #2979ff;
  --orange:    #ff9100;
  --purple:    #d500f9;
  --bg:        #080810;
  --bg2:       #0e0e1a;
  --bg3:       #13131f;
  --border:    rgba(255,255,255,0.06);
  --text:      #f0f0f8;
  --muted:     rgba(240,240,248,0.45);
  --card-r:    16px;
}

*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

html { scroll-behavior: smooth; }

body {
  font-family: 'Syne', sans-serif;
  background: var(--bg);
  color: var(--text);
  min-height: 100vh;
  overflow-x: hidden;
}

/* ── GRID BG ── */
body::before {
  content: '';
  position: fixed; inset: 0;
  background-image:
    linear-gradient(rgba(255,0,60,0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,0,60,0.03) 1px, transparent 1px);
  background-size: 48px 48px;
  pointer-events: none;
  z-index: 0;
}

/* ── SIDEBAR ── */
.sidebar {
  position: fixed;
  top: 0; left: 0;
  width: 240px; height: 100vh;
  background: var(--bg2);
  border-right: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  z-index: 100;
  padding: 0 0 2rem;
}

.sidebar-logo {
  padding: 1.75rem 1.5rem 1.5rem;
  border-bottom: 1px solid var(--border);
  margin-bottom: 1.25rem;
}

.sidebar-logo span {
  font-family: 'Orbitron', sans-serif;
  font-size: 1.25rem;
  font-weight: 900;
  color: var(--red);
  text-shadow: 0 0 20px var(--red-glow);
  letter-spacing: 1px;
}

.sidebar-logo small {
  display: block;
  font-size: 0.7rem;
  color: var(--muted);
  margin-top: 2px;
  letter-spacing: 2px;
  text-transform: uppercase;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1.5rem;
  color: var(--muted);
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: 500;
  transition: all 0.2s;
  border-left: 3px solid transparent;
  margin: 1px 0;
}

.nav-item i { width: 18px; text-align: center; font-size: 0.95rem; }

.nav-item:hover {
  color: var(--text);
  background: rgba(255,255,255,0.04);
}

.nav-item.active {
  color: var(--red);
  background: var(--red-dim);
  border-left-color: var(--red);
}

.nav-section {
  padding: 1.25rem 1.5rem 0.5rem;
  font-size: 0.65rem;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: rgba(240,240,248,0.25);
  font-weight: 600;
}

.sidebar-footer {
  margin-top: auto;
  padding: 1rem 1.5rem 0;
  border-top: 1px solid var(--border);
}

.sidebar-footer a {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  color: var(--muted);
  text-decoration: none;
  font-size: 0.85rem;
  transition: color 0.2s;
}

.sidebar-footer a:hover { color: var(--red); }

/* ── MAIN ── */
.main {
  margin-left: 240px;
  min-height: 100vh;
  position: relative;
  z-index: 1;
}

/* ── TOPBAR ── */
.topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 2rem;
  border-bottom: 1px solid var(--border);
  background: rgba(8,8,16,0.85);
  backdrop-filter: blur(12px);
  position: sticky;
  top: 0;
  z-index: 50;
}

.topbar-left h1 {
  font-family: 'Orbitron', sans-serif;
  font-size: 1.2rem;
  font-weight: 700;
  color: var(--text);
  letter-spacing: 1px;
}

.topbar-left p {
  font-size: 0.8rem;
  color: var(--muted);
  margin-top: 2px;
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.refresh-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: var(--red-dim);
  border: 1px solid rgba(255,0,60,0.3);
  border-radius: 8px;
  color: var(--red);
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  font-family: 'Syne', sans-serif;
}

.refresh-btn:hover {
  background: rgba(255,0,60,0.2);
  box-shadow: 0 0 15px var(--red-glow);
}

.refresh-btn i { transition: transform 0.5s; }
.refresh-btn.spinning i { transform: rotate(360deg); }

.topbar-time {
  font-size: 0.8rem;
  color: var(--muted);
  font-variant-numeric: tabular-nums;
}

/* ── PAGE CONTENT ── */
.page { padding: 2rem; }

/* ── STAT CARDS ── */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.stat-card {
  background: var(--bg3);
  border: 1px solid var(--border);
  border-radius: var(--card-r);
  padding: 1.25rem 1.5rem;
  position: relative;
  overflow: hidden;
  transition: all 0.25s;
  cursor: default;
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 2px;
  background: var(--accent-color, var(--red));
  opacity: 0.7;
}

.stat-card::after {
  content: '';
  position: absolute;
  bottom: -20px; right: -20px;
  width: 80px; height: 80px;
  background: var(--accent-color, var(--red));
  opacity: 0.04;
  border-radius: 50%;
  transition: all 0.3s;
}

.stat-card:hover {
  border-color: rgba(255,255,255,0.12);
  transform: translateY(-2px);
  box-shadow: 0 8px 30px rgba(0,0,0,0.3);
}

.stat-card:hover::after { opacity: 0.08; transform: scale(1.4); }

.stat-label {
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  color: var(--muted);
  font-weight: 600;
  margin-bottom: 0.6rem;
}

.stat-value {
  font-family: 'Orbitron', sans-serif;
  font-size: 1.8rem;
  font-weight: 700;
  color: var(--text);
  line-height: 1;
  margin-bottom: 0.5rem;
}

.stat-sub {
  font-size: 0.75rem;
  color: var(--muted);
  display: flex;
  align-items: center;
  gap: 0.3rem;
}

.stat-sub.up   { color: var(--green); }
.stat-sub.warn { color: var(--orange); }

.stat-icon-bg {
  position: absolute;
  top: 1.1rem; right: 1.25rem;
  font-size: 1.4rem;
  opacity: 0.18;
  color: var(--accent-color, var(--red));
}

/* ── CHART ROW ── */
.charts-row {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.card {
  background: var(--bg3);
  border: 1px solid var(--border);
  border-radius: var(--card-r);
  overflow: hidden;
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.1rem 1.5rem;
  border-bottom: 1px solid var(--border);
}

.card-title {
  font-size: 0.85rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: var(--text);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.card-title i { color: var(--red); font-size: 0.9rem; }

.card-badge {
  font-size: 0.7rem;
  padding: 0.2rem 0.6rem;
  background: var(--red-dim);
  color: var(--red);
  border-radius: 20px;
  font-weight: 600;
  letter-spacing: 0.5px;
}

.card-body { padding: 1.5rem; }

/* ── REVENUE CHART ── */
.chart-wrap {
  position: relative;
  height: 220px;
}

/* ── PIE CHART / PLAN DIST ── */
.pie-wrap {
  position: relative;
  height: 200px;
  display: flex;
  justify-content: center;
}

/* ── BOTTOM ROW ── */
.bottom-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

/* ── TABLE ── */
.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  font-size: 0.65rem;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  color: var(--muted);
  font-weight: 600;
  padding: 0 1rem 0.75rem;
  text-align: left;
  border-bottom: 1px solid var(--border);
}

.data-table td {
  padding: 0.75rem 1rem;
  font-size: 0.82rem;
  border-bottom: 1px solid rgba(255,255,255,0.03);
  vertical-align: middle;
}

.data-table tr:last-child td { border-bottom: none; }

.data-table tr:hover td { background: rgba(255,255,255,0.02); }

/* ── TOP WATCHLISTED ── */
.movie-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.65rem 0;
  border-bottom: 1px solid rgba(255,255,255,0.03);
}

.movie-row:last-child { border-bottom: none; }

.movie-rank {
  font-family: 'Orbitron', sans-serif;
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--muted);
  width: 22px;
  text-align: center;
  flex-shrink: 0;
}

.movie-rank.top { color: var(--red); }

.movie-thumb {
  width: 36px;
  height: 54px;
  object-fit: cover;
  border-radius: 6px;
  flex-shrink: 0;
  background: var(--bg2);
}

.movie-info-row { flex: 1; min-width: 0; }

.movie-info-row strong {
  display: block;
  font-size: 0.82rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 2px;
}

.movie-info-row span {
  font-size: 0.72rem;
  color: var(--muted);
}

.watchlist-bar-wrap {
  width: 80px;
  flex-shrink: 0;
}

.watchlist-bar {
  height: 4px;
  background: rgba(255,255,255,0.07);
  border-radius: 99px;
  overflow: hidden;
  margin-bottom: 3px;
}

.watchlist-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--red), #ff4d7a);
  border-radius: 99px;
  transition: width 0.8s ease;
}

.watchlist-bar-count {
  font-size: 0.7rem;
  color: var(--muted);
  text-align: right;
}

/* ── PLAN LEGEND ── */
.plan-legend {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-top: 1rem;
}

.plan-legend-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 0.8rem;
}

.plan-legend-dot {
  width: 8px; height: 8px;
  border-radius: 50%;
  margin-right: 0.5rem;
  flex-shrink: 0;
}

.plan-legend-left {
  display: flex;
  align-items: center;
  color: var(--muted);
}

.plan-legend-val {
  font-weight: 700;
  font-family: 'Orbitron', sans-serif;
  font-size: 0.75rem;
}

/* ── ALERT BANNER ── */
.alert-banner {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1.25rem;
  background: rgba(255,145,0,0.1);
  border: 1px solid rgba(255,145,0,0.3);
  border-radius: 10px;
  margin-bottom: 1.5rem;
  font-size: 0.82rem;
  color: var(--orange);
  animation: pulse-border 2s infinite;
}

@keyframes pulse-border {
  0%, 100% { border-color: rgba(255,145,0,0.3); }
  50%       { border-color: rgba(255,145,0,0.7); }
}

.alert-banner i { font-size: 1rem; flex-shrink: 0; }
.alert-banner span strong { color: #ffb74d; }

/* ── SKELETON LOADER ── */
.skeleton {
  background: linear-gradient(90deg, var(--bg2) 25%, rgba(255,255,255,0.04) 50%, var(--bg2) 75%);
  background-size: 200% 100%;
  animation: shimmer-load 1.4s infinite;
  border-radius: 6px;
  height: 1.8rem;
}

@keyframes shimmer-load {
  0%   { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* ── MONEY FORMAT ── */
.money { font-family: 'Orbitron', sans-serif; }

/* ── PLAN BADGE ── */
.plan-badge {
  padding: 0.2rem 0.6rem;
  border-radius: 20px;
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.5px;
}

.plan-badge.basic    { background: rgba(41,121,255,0.15); color: var(--blue); }
.plan-badge.standard { background: rgba(255,0,60,0.12);   color: var(--red); }
.plan-badge.premium  { background: rgba(213,0,249,0.12);  color: var(--purple); }

/* ── STATUS DOT ── */
.dot-green { color: var(--green); font-size: 0.6rem; margin-right: 4px; }

/* ── RESPONSIVE ── */
@media (max-width: 1200px) {
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
  .charts-row { grid-template-columns: 1fr; }
  .bottom-row { grid-template-columns: 1fr; }
}

@media (max-width: 768px) {
  .sidebar { display: none; }
  .main    { margin-left: 0; }
  .stats-grid { grid-template-columns: 1fr 1fr; }
}
</style>
</head>
<body>

<!-- ── SIDEBAR ── -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <span>PoncolVerse</span>
    <small>Admin Panel</small>
  </div>

  <span class="nav-section">Overview</span>
  <a href="#" class="nav-item active">
    <i class="fas fa-chart-line"></i> Dashboard
  </a>

  <span class="nav-section">Manajemen</span>
  <a href="../index-admin.php" class="nav-item">
    <i class="fas fa-film"></i> Kelola Film
  </a>
  <a href="../testimonials/testimonials-admin.php" class="nav-item">
    <i class="fas fa-comments"></i> Testimonials
    <?php
    // Badge pending testimonials — query langsung
    $pending = $conn->query("SELECT COUNT(*) as c FROM website_testimonials WHERE is_approved=0")->fetch_assoc()['c'];
    if ($pending > 0) echo "<span style='margin-left:auto;background:var(--red);color:#fff;font-size:0.65rem;padding:1px 7px;border-radius:99px;font-weight:700;'>{$pending}</span>";
    ?>
  </a>

  <span class="nav-section">Akun</span>
  <div class="sidebar-footer">
    <a href="../../index.php">
      <i class="fas fa-arrow-left"></i> Kembali ke Website
    </a>
  </div>
</aside>

<!-- ── MAIN ── -->
<main class="main">

  <!-- TOPBAR -->
  <div class="topbar">
    <div class="topbar-left">
      <h1>Dashboard</h1>
      <p>Selamat datang, <?php echo $_SESSION['user_firstName']; ?> — ringkasan platform hari ini</p>
    </div>
    <div class="topbar-right">
      <span class="topbar-time" id="liveClock"></span>
      <button class="refresh-btn" onclick="loadDashboard(true)" id="refreshBtn">
        <i class="fas fa-sync-alt"></i> Refresh
      </button>
    </div>
  </div>

  <!-- PAGE -->
  <div class="page" id="pageContent">
    <div style="text-align:center;padding:4rem;color:var(--muted);">
      <i class="fas fa-circle-notch fa-spin" style="font-size:2rem;color:var(--red);"></i>
      <p style="margin-top:1rem;font-size:0.9rem;">Memuat data...</p>
    </div>
  </div>

</main>

<script>
// ===== LIVE CLOCK =====
function updateClock() {
    const now  = new Date();
    const opts = { weekday:'short', day:'numeric', month:'short', hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:false };
    document.getElementById('liveClock').textContent = now.toLocaleString('id-ID', opts);
}
setInterval(updateClock, 1000);
updateClock();

// ===== FORMAT HELPERS =====
function fmtRupiah(n) {
    return 'Rp' + Math.floor(n).toLocaleString('id-ID');
}

function fmtNum(n) {
    if (n >= 1000) return (n/1000).toFixed(1) + 'K';
    return n.toString();
}

function getPlanClass(name) {
    const n = name.toLowerCase();
    if (n.includes('basic'))   return 'basic';
    if (n.includes('premium')) return 'premium';
    return 'standard';
}

// ===== CHART INSTANCES (untuk destroy saat refresh) =====
let revenueChart = null;
let pieChart     = null;

// ===== MAIN LOAD =====
async function loadDashboard(isRefresh = false) {
    const btn = document.getElementById('refreshBtn');
    btn.classList.add('spinning');

    try {
        const res  = await fetch('api/get_stats.php?t=' + Date.now());
        const data = await res.json();

        if (data.error) {
            document.getElementById('pageContent').innerHTML = `<p style="color:var(--red);padding:2rem;">Error: ${data.error}</p>`;
            return;
        }

        renderPage(data);

    } catch (e) {
        console.error(e);
        document.getElementById('pageContent').innerHTML = `<p style="color:var(--red);padding:2rem;">Gagal memuat data. Cek koneksi server.</p>`;
    } finally {
        setTimeout(() => btn.classList.remove('spinning'), 500);
    }
}

// ===== RENDER PAGE =====
function renderPage(d) {
    const expiringHtml = d.expiring_soon > 0
        ? `<div class="alert-banner">
            <i class="fas fa-exclamation-triangle"></i>
            <span><strong>${d.expiring_soon} subscriber</strong> akan berakhir dalam 7 hari ke depan. Pertimbangkan kirim reminder.</span>
           </div>`
        : '';

    document.getElementById('pageContent').innerHTML = `
        ${expiringHtml}

        <!-- STAT CARDS -->
        <div class="stats-grid" id="statsGrid"></div>

        <!-- CHARTS ROW -->
        <div class="charts-row">
            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class="fas fa-chart-bar"></i> Pendapatan 6 Bulan</span>
                    <span class="card-badge">Monthly</span>
                </div>
                <div class="card-body">
                    <div class="chart-wrap">
                        <canvas id="revenueCanvas"></canvas>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class="fas fa-circle-dot"></i> Distribusi Paket</span>
                </div>
                <div class="card-body">
                    <div class="pie-wrap">
                        <canvas id="pieCanvas"></canvas>
                    </div>
                    <div class="plan-legend" id="planLegend"></div>
                </div>
            </div>
        </div>

        <!-- BOTTOM ROW -->
        <div class="bottom-row">
            <!-- Recent Transactions -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class="fas fa-receipt"></i> Transaksi Terbaru</span>
                </div>
                <div class="card-body" style="padding:0 0 0.5rem;">
                    <table class="data-table" id="txnTable"></table>
                </div>
            </div>
            <!-- Top Watchlisted -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class="fas fa-bookmark"></i> Film Terbanyak di Watchlist</span>
                </div>
                <div class="card-body" id="topMovies" style="padding:0.5rem 1.5rem;"></div>
            </div>
        </div>
    `;

    renderStatCards(d);
    renderRevenueChart(d.revenue_chart);
    renderPieChart(d.plan_distribution);
    renderTransactions(d.recent_transactions);
    renderTopMovies(d.top_watchlisted);
}

// ===== STAT CARDS =====
function renderStatCards(d) {
    const cards = [
        {
            label: 'Total Film',
            value: d.total_movies,
            icon: 'fa-film',
            color: 'var(--red)',
            sub: `<i class="fas fa-circle-dot dot-green"></i> Aktif di katalog`,
        },
        {
            label: 'Total Pengguna',
            value: d.total_users,
            icon: 'fa-users',
            color: 'var(--blue)',
            sub: `<span class="up"><i class="fas fa-arrow-up"></i> ${d.new_users_7d} user baru minggu ini</span>`,
        },
        {
            label: 'Subscriber Aktif',
            value: d.active_subscribers,
            icon: 'fa-crown',
            color: 'var(--purple)',
            sub: d.expiring_soon > 0
                ? `<span class="warn"><i class="fas fa-clock"></i> ${d.expiring_soon} exp. 7 hari</span>`
                : `<i class="fas fa-circle-dot dot-green"></i> Semua aktif`,
        },
        {
            label: 'Total Pendapatan',
            value: fmtRupiah(d.total_revenue),
            icon: 'fa-wallet',
            color: 'var(--green)',
            sub: `<i class="fas fa-receipt"></i> ${d.total_transactions} transaksi sukses`,
            isMoney: true,
        },
        {
            label: 'Total Watchlist',
            value: d.total_watchlist,
            icon: 'fa-bookmark',
            color: 'var(--orange)',
            sub: `<i class="fas fa-film"></i> Across all movies`,
        },
        {
            label: 'Total Komentar',
            value: d.total_comments,
            icon: 'fa-comments',
            color: '#00e5ff',
            sub: `<i class="fas fa-users"></i> Dari user`,
        },
        {
            label: 'Testimoni Pending',
            value: d.pending_testimonials,
            icon: 'fa-clock',
            color: 'var(--orange)',
            sub: d.pending_testimonials > 0
                ? `<span class="warn"><i class="fas fa-exclamation-circle"></i> Perlu di-review</span>`
                : `<i class="fas fa-check-circle" style="color:var(--green)"></i> Semua selesai`,
        },
        {
            label: 'Transaksi Sukses',
            value: d.total_transactions,
            icon: 'fa-check-double',
            color: 'var(--green)',
            sub: `<i class="fas fa-circle-dot dot-green"></i> Settlement`,
        },
    ];

    const grid = document.getElementById('statsGrid');
    grid.innerHTML = cards.map(c => `
        <div class="stat-card" style="--accent-color:${c.color};">
            <div class="stat-label">${c.label}</div>
            <div class="stat-value ${c.isMoney ? 'money' : ''}" style="font-size:${c.isMoney ? '1.1rem' : '1.8rem'};">
                ${c.value}
            </div>
            <div class="stat-sub">${c.sub}</div>
            <i class="fas ${c.icon} stat-icon-bg"></i>
        </div>
    `).join('');
}

// ===== REVENUE CHART =====
function renderRevenueChart(chartData) {
    if (revenueChart) revenueChart.destroy();

    const ctx = document.getElementById('revenueCanvas').getContext('2d');
    const labels  = chartData.map(d => d.month);
    const values  = chartData.map(d => d.revenue);

    const gradient = ctx.createLinearGradient(0, 0, 0, 220);
    gradient.addColorStop(0, 'rgba(255,0,60,0.35)');
    gradient.addColorStop(1, 'rgba(255,0,60,0.01)');

    revenueChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Pendapatan',
                data: values,
                backgroundColor: gradient,
                borderColor: '#ff003c',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#13131f',
                    borderColor: 'rgba(255,0,60,0.4)',
                    borderWidth: 1,
                    titleColor: '#f0f0f8',
                    bodyColor: '#ff003c',
                    callbacks: {
                        label: ctx => ' ' + fmtRupiah(ctx.parsed.y)
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(255,255,255,0.04)' },
                    ticks: { color: 'rgba(240,240,248,0.45)', font: { size: 11 } }
                },
                y: {
                    grid: { color: 'rgba(255,255,255,0.04)' },
                    ticks: {
                        color: 'rgba(240,240,248,0.45)',
                        font: { size: 11 },
                        callback: v => 'Rp' + (v/1000).toFixed(0) + 'K'
                    }
                }
            }
        }
    });
}

// ===== PIE CHART =====
function renderPieChart(dist) {
    if (pieChart) pieChart.destroy();

    if (!dist || dist.length === 0) {
        document.getElementById('pieCanvas').parentElement.innerHTML =
            '<p style="text-align:center;color:var(--muted);padding:3rem 0;font-size:0.85rem;">Belum ada subscriber aktif</p>';
        return;
    }

    const COLORS = ['#2979ff', '#ff003c', '#d500f9', '#ff9100'];
    const labels = dist.map(d => d.name);
    const values = dist.map(d => parseInt(d.count));

    const ctx = document.getElementById('pieCanvas').getContext('2d');
    pieChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: COLORS.slice(0, dist.length),
                borderColor: '#13131f',
                borderWidth: 3,
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#13131f',
                    borderColor: 'rgba(255,255,255,0.1)',
                    borderWidth: 1,
                    titleColor: '#f0f0f8',
                    bodyColor: 'rgba(240,240,248,0.7)',
                }
            }
        }
    });

    // Legend
    const COLORS_MAP = { Basic: '#2979ff', Standard: '#ff003c', Premium: '#d500f9' };
    document.getElementById('planLegend').innerHTML = dist.map((d, i) => `
        <div class="plan-legend-item">
            <div class="plan-legend-left">
                <div class="plan-legend-dot" style="background:${COLORS[i]};"></div>
                ${d.name}
            </div>
            <span class="plan-legend-val" style="color:${COLORS[i]};">${d.count}</span>
        </div>
    `).join('');
}

// ===== RECENT TRANSACTIONS =====
function renderTransactions(txns) {
    const table = document.getElementById('txnTable');
    if (!txns || txns.length === 0) {
        table.innerHTML = '<tr><td style="padding:2rem;color:var(--muted);text-align:center;">Belum ada transaksi</td></tr>';
        return;
    }

    table.innerHTML = `
        <thead>
            <tr>
                <th>User</th>
                <th>Paket</th>
                <th>Nominal</th>
                <th>Waktu</th>
            </tr>
        </thead>
        <tbody>
            ${txns.map(t => {
                const date = new Date(t.created_at);
                const dateStr = date.toLocaleDateString('id-ID', { day:'numeric', month:'short', year:'2-digit' });
                return `
                <tr>
                    <td>${t.firstName} ${t.lastName}</td>
                    <td><span class="plan-badge ${getPlanClass(t.plan_name)}">${t.plan_name}</span></td>
                    <td class="money" style="color:var(--green);font-size:0.78rem;">${fmtRupiah(t.gross_amount)}</td>
                    <td style="color:var(--muted);">${dateStr}</td>
                </tr>`;
            }).join('')}
        </tbody>
    `;
}

// ===== TOP WATCHLISTED MOVIES =====
function renderTopMovies(movies) {
    const container = document.getElementById('topMovies');
    if (!movies || movies.length === 0) {
        container.innerHTML = '<p style="color:var(--muted);text-align:center;padding:2rem 0;font-size:0.85rem;">Belum ada data watchlist</p>';
        return;
    }

    const maxCount = Math.max(...movies.map(m => parseInt(m.count)), 1);

    container.innerHTML = movies.map((m, i) => {
        const pct = Math.round((parseInt(m.count) / maxCount) * 100);
        return `
        <div class="movie-row">
            <div class="movie-rank ${i === 0 ? 'top' : ''}">${i + 1}</div>
            <img class="movie-thumb" src="${m.poster}"
                 onerror="this.src='https://via.placeholder.com/36x54?text=?'" alt="${m.title}">
            <div class="movie-info-row">
                <strong title="${m.title}">${m.title}</strong>
                <span>⭐ ${parseFloat(m.rating).toFixed(1)}</span>
            </div>
            <div class="watchlist-bar-wrap">
                <div class="watchlist-bar">
                    <div class="watchlist-bar-fill" style="width:${pct}%;"></div>
                </div>
                <div class="watchlist-bar-count">${m.count} simpan</div>
            </div>
        </div>`;
    }).join('');
}

// ===== INIT =====
loadDashboard();
</script>
</body>
</html>