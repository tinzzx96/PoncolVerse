<?php 
require_once '../../config/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

// Get all testimonials
$sql = "SELECT wt.*, u.firstName, u.lastName, u.email 
        FROM website_testimonials wt
        LEFT JOIN users u ON wt.user_id = u.id
        ORDER BY wt.created_at DESC";
$result = $conn->query($sql);

// Count approved
$approved_count_sql = "SELECT COUNT(*) as count FROM website_testimonials WHERE is_approved = 1";
$approved_count = $conn->query($approved_count_sql)->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Testimonials - PoncolVerse</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="../assets/css/main.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #0a0a0a 0%, #1a0a0f 100%);
      min-height: 100vh;
      padding: 2rem;
    }
    
    .admin-testimonials-container {
      max-width: 1400px;
      margin: 0 auto;
    }
    
    .back-btn-testimonials {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.75rem 1.5rem;
      background: rgba(255, 255, 255, 0.1);
      border: 2px solid rgba(255, 255, 255, 0.2);
      border-radius: 50px;
      color: white;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s;
      margin-bottom: 2rem;
    }
    
    .back-btn-testimonials:hover {
      background: rgba(255, 0, 60, 0.2);
      border-color: #ff003c;
      transform: translateX(-5px);
    }
    
    .admin-testimonials-header {
      text-align: center;
      margin-bottom: 3rem;
    }
    
    .admin-testimonials-header h1 {
      font-family: 'Orbitron', sans-serif;
      font-size: 3rem;
      background: linear-gradient(135deg, #ff003c, #ff4d7a);
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
      margin-bottom: 0.5rem;
    }
    
    .admin-testimonials-header p {
      color: #aaa;
      font-size: 1.1rem;
    }
    
    .stats-container {
      background: linear-gradient(145deg, rgba(255, 0, 60, 0.1), rgba(255, 77, 122, 0.1));
      border: 2px solid rgba(255, 0, 60, 0.3);
      border-radius: 15px;
      padding: 1.5rem;
      margin-bottom: 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .stat-item {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    
    .stat-icon {
      width: 60px;
      height: 60px;
      background: linear-gradient(135deg, #ff003c, #ff4d7a);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      color: white;
    }
    
    .stat-info strong {
      display: block;
      font-size: 2rem;
      color: #ff003c;
    }
    
    .stat-info span {
      color: #aaa;
      font-size: 0.9rem;
    }
    
    .max-warning {
      background: linear-gradient(145deg, rgba(255, 152, 0, 0.2), rgba(255, 152, 0, 0.1));
      border: 2px solid #ff9800;
      border-radius: 15px;
      padding: 1.5rem;
      margin-bottom: 2rem;
      color: #ff9800;
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    
    .max-warning-icon {
      font-size: 2.5rem;
      flex-shrink: 0;
    }
    
    .testimonials-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
      gap: 2rem;
    }
    
    .testimonial-card {
      background: linear-gradient(145deg, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.02));
      border: 2px solid rgba(255, 255, 255, 0.1);
      border-radius: 20px;
      padding: 2rem;
      transition: all 0.3s;
      position: relative;
      overflow: hidden;
    }
    
    .testimonial-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 5px;
      background: linear-gradient(90deg, transparent, rgba(255, 0, 60, 0.5), transparent);
      opacity: 0;
      transition: opacity 0.3s;
    }
    
    .testimonial-card:hover {
      border-color: rgba(255, 0, 60, 0.5);
      transform: translateY(-10px);
      box-shadow: 0 20px 50px rgba(255, 0, 60, 0.3);
    }
    
    .testimonial-card:hover::before {
      opacity: 1;
    }
    
    .testimonial-card.approved {
      border-color: rgba(0, 255, 0, 0.5);
      background: linear-gradient(145deg, rgba(0, 255, 0, 0.05), rgba(0, 255, 0, 0.02));
    }
    
    .testimonial-card.approved::before {
      background: linear-gradient(90deg, transparent, rgba(0, 255, 0, 0.5), transparent);
    }
    
    .testimonial-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
    }
    
    .testimonial-user {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    
    .user-avatar-card {
      width: 60px;
      height: 60px;
      background: linear-gradient(135deg, #ff003c, #ff4d7a);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 700;
      font-size: 1.3rem;
      flex-shrink: 0;
    }
    
    .user-info strong {
      display: block;
      color: white;
      font-size: 1.1rem;
      margin-bottom: 0.25rem;
    }
    
    .user-info small {
      color: #aaa;
      font-size: 0.85rem;
    }
    
    .status-badge {
      padding: 0.5rem 1rem;
      border-radius: 20px;
      font-weight: 700;
      font-size: 0.85rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .status-badge.approved {
      background: linear-gradient(135deg, #00ff00, #00cc00);
      color: #000;
    }
    
    .status-badge.pending {
      background: linear-gradient(135deg, #ff9800, #ff6f00);
      color: #000;
    }
    
    .rating-stars {
      color: #ffc107;
      font-size: 1.3rem;
      margin: 1rem 0;
    }
    
    .testimonial-message {
      color: #ccc;
      line-height: 1.8;
      margin-bottom: 1.5rem;
      font-style: italic;
      padding: 1rem;
      background: rgba(0, 0, 0, 0.2);
      border-radius: 10px;
      border-left: 3px solid #ff003c;
    }
    
    .testimonial-actions {
      display: flex;
      gap: 0.75rem;
      flex-wrap: wrap;
    }
    
    .action-btn {
      flex: 1;
      min-width: 120px;
      padding: 0.75rem 1.25rem;
      border: none;
      border-radius: 10px;
      color: white;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      font-size: 0.9rem;
    }
    
    .action-btn:hover {
      transform: translateY(-2px);
    }
    
    .btn-approve {
      background: linear-gradient(135deg, #00ff00, #00cc00);
      color: #000;
    }
    
    .btn-approve:hover {
      box-shadow: 0 10px 30px rgba(0, 255, 0, 0.4);
    }
    
    .btn-approve:disabled {
      opacity: 0.5;
      cursor: not-allowed;
      transform: none;
    }
    
    .btn-unapprove {
      background: linear-gradient(135deg, #ff9800, #ff6f00);
      color: #000;
    }
    
    .btn-unapprove:hover {
      box-shadow: 0 10px 30px rgba(255, 152, 0, 0.4);
    }
    
    .btn-delete {
      background: linear-gradient(135deg, #ff0000, #cc0000);
    }
    
    .btn-delete:hover {
      box-shadow: 0 10px 30px rgba(255, 0, 0, 0.4);
    }
    
    .no-testimonials {
      text-align: center;
      padding: 4rem 2rem;
      color: #aaa;
    }
    
    .no-testimonials i {
      font-size: 4rem;
      margin-bottom: 1rem;
      color: #ff003c;
    }
    
    @media (max-width: 768px) {
      .testimonials-grid {
        grid-template-columns: 1fr;
      }
      
      .stats-container {
        flex-direction: column;
        gap: 1rem;
      }
      
      .action-btn {
        min-width: 100%;
      }
    }
  </style>
</head>
<body>
  <div class="toast-container" id="toastContainer"></div>
  
  <div class="admin-testimonials-container">
    <a href="../../index.php#testimonials" class="back-btn-testimonials">
      <i class="fas fa-arrow-left"></i> Kembali ke Website
    </a>
    
    <div class="admin-testimonials-header">
      <h1><i class="fas fa-comments"></i> Kelola Testimonials</h1>
      <p>Pilih maksimal 10 testimonial terbaik untuk ditampilkan di homepage</p>
    </div>
    
    <div class="stats-container">
      <div class="stat-item">
        <div class="stat-icon">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
          <strong><?php echo $approved_count; ?> / 10</strong>
          <span>Testimonials Aktif</span>
        </div>
      </div>
      
      <div class="stat-item">
        <div class="stat-icon">
          <i class="fas fa-comments"></i>
        </div>
        <div class="stat-info">
          <strong><?php echo $result->num_rows; ?></strong>
          <span>Total Testimonials</span>
        </div>
      </div>
    </div>
    
    <?php if ($approved_count >= 10): ?>
    <div class="max-warning">
      <div class="max-warning-icon">
        <i class="fas fa-exclamation-triangle"></i>
      </div>
      <div>
        <strong style="display: block; margin-bottom: 0.5rem;">Limit Tercapai!</strong>
        Anda sudah memilih <?php echo $approved_count; ?> testimonial. Batalkan approval beberapa testimonial untuk menambah yang baru.
      </div>
    </div>
    <?php endif; ?>
    
    <?php if ($result && $result->num_rows > 0): ?>
    <div class="testimonials-grid">
      <?php while($testimonial = $result->fetch_assoc()): ?>
      <div class="testimonial-card <?php echo $testimonial['is_approved'] ? 'approved' : ''; ?>" data-id="<?php echo $testimonial['id']; ?>">
        <div class="testimonial-header">
          <div class="testimonial-user">
            <div class="user-avatar-card">
              <?php 
              $initials = strtoupper(substr($testimonial['firstName'] ?? 'U', 0, 1) . substr($testimonial['lastName'] ?? 'S', 0, 1));
              echo $initials;
              ?>
            </div>
            <div class="user-info">
              <strong><?php echo htmlspecialchars($testimonial['user_name']); ?></strong>
              <small><i class="far fa-clock"></i> <?php echo date('d M Y, H:i', strtotime($testimonial['created_at'])); ?></small>
            </div>
          </div>
          
          <div class="status-badge <?php echo $testimonial['is_approved'] ? 'approved' : 'pending'; ?>">
            <i class="fas fa-<?php echo $testimonial['is_approved'] ? 'check' : 'clock'; ?>"></i>
            <?php echo $testimonial['is_approved'] ? 'Ditampilkan' : 'Pending'; ?>
          </div>
        </div>
        
        <div class="rating-stars">
          <?php for($i = 0; $i < $testimonial['rating']; $i++): ?>
            <i class="fas fa-star"></i>
          <?php endfor; ?>
          <?php for($i = $testimonial['rating']; $i < 5; $i++): ?>
            <i class="far fa-star"></i>
          <?php endfor; ?>
        </div>
        
        <div class="testimonial-message">
          "<?php echo nl2br(htmlspecialchars($testimonial['message'])); ?>"
        </div>
        
        <div class="testimonial-actions">
          <?php if ($testimonial['is_approved']): ?>
            <button class="action-btn btn-unapprove" onclick="confirmToggleApproval(<?php echo $testimonial['id']; ?>, 0, 'batalkan approval')">
              <i class="fas fa-times"></i> Batalkan
            </button>
          <?php else: ?>
            <button class="action-btn btn-approve" onclick="confirmToggleApproval(<?php echo $testimonial['id']; ?>, 1, 'approve')" <?php echo $approved_count >= 10 ? 'disabled' : ''; ?>>
              <i class="fas fa-check"></i> Approve
            </button>
          <?php endif; ?>
          
          <button class="action-btn btn-delete" onclick="confirmDelete(<?php echo $testimonial['id']; ?>)">
            <i class="fas fa-trash"></i> Hapus
          </button>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="no-testimonials">
      <i class="fas fa-inbox"></i>
      <h3>Belum Ada Testimonial</h3>
      <p>User belum memberikan testimonial apapun.</p>
    </div>
    <?php endif; ?>
  </div>
  
  <script>
    // Confirmation dengan Toast (BUKAN ALERT!)
    function confirmToggleApproval(id, status, action) {
      const message = `Yakin ingin ${action} testimonial ini?`;
      
      if (showConfirmToast(message, () => toggleApproval(id, status))) {
        // Confirmed
      }
    }
    
    function confirmDelete(id) {
      const message = 'Yakin ingin menghapus testimonial ini? Tindakan tidak dapat dibatalkan!';
      
      if (showConfirmToast(message, () => deleteTestimonial(id))) {
        // Confirmed
      }
    }
    
    // Show Confirm Toast
    function showConfirmToast(message, onConfirm) {
      const container = document.getElementById("toastContainer");
      
      const toast = document.createElement("div");
      toast.className = "toast warning";
      toast.style.maxWidth = "400px";
      
      toast.innerHTML = `
        <div class="toast-icon">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="toast-content">
          <div class="toast-title">Konfirmasi</div>
          <div class="toast-message">${message}</div>
          <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
            <button onclick="this.closest('.toast').remove()" style="flex: 1; padding: 0.5rem; background: rgba(255,255,255,0.1); border: none; border-radius: 5px; color: white; cursor: pointer;">Batal</button>
            <button onclick="(${onConfirm.toString()})(); this.closest('.toast').remove();" style="flex: 1; padding: 0.5rem; background: linear-gradient(135deg, #ff003c, #ff4d7a); border: none; border-radius: 5px; color: white; font-weight: 700; cursor: pointer;">Ya, Lanjutkan</button>
          </div>
        </div>
      `;
      
      container.appendChild(toast);
      return true;
    }
    
    async function toggleApproval(id, status) {
      showToast('info', 'Memproses...', 'Mohon tunggu');
      
      try {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('is_approved', status);
        
        const response = await fetch('../../API/testimonials/toggle_approval.php', {
          method: 'POST',
          body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
          showToast('success', 'Berhasil!', result.message);
          setTimeout(() => location.reload(), 1500);
        } else {
          showToast('error', 'Gagal', result.message);
        }
      } catch (error) {
        console.error('Error:', error);
        showToast('error', 'Error', 'Terjadi kesalahan. Silakan coba lagi.');
      }
    }
    
    async function deleteTestimonial(id) {
      showToast('info', 'Menghapus...', 'Mohon tunggu');
      
      try {
        const formData = new FormData();
        formData.append('id', id);
        
        const response = await fetch('../../API/testimonials/delete_testimonial.php', {
          method: 'POST',
          body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
          showToast('success', 'Terhapus!', result.message);
          setTimeout(() => location.reload(), 1500);
        } else {
          showToast('error', 'Gagal', result.message);
        }
      } catch (error) {
        console.error('Error:', error);
        showToast('error', 'Error', 'Terjadi kesalahan. Silakan coba lagi.');
      }
    }
    
    function showToast(type, title, message) {
      let container = document.getElementById("toastContainer");
      if (!container) {
        container = document.createElement("div");
        container.id = "toastContainer";
        container.className = "toast-container";
        document.body.appendChild(container);
      }

      // Remove all existing toasts to prevent stacking
      const existingToasts = container.querySelectorAll('.toast');
      existingToasts.forEach(toast => toast.remove());

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
      `;

      container.appendChild(toast);

      setTimeout(() => {
        toast.remove();
      }, 4000);
    }
  </script>
</body>
</html>