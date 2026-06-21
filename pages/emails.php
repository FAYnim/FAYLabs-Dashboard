<?php
// ============================================================
// Emails — UI-only Inbox
// ============================================================

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/includes/auth.php';
require_once ROOT_PATH . '/includes/csrf.php';
require_once ROOT_PATH . '/includes/helpers.php';

csrfStart();
requireAdmin();

$pageTitle  = 'Emails';
$activePage = 'emails';
$csrfToken  = csrfGenerate();

$emails = [
    [
        'id'      => 1,
        'sender'  => 'Nadia Putri',
        'email'   => 'nadia.putri@example.com',
        'subject' => 'Inquiry untuk website company profile',
        'preview' => 'Halo FAYLabs, saya ingin bertanya tentang pembuatan website company profile untuk bisnis kami.',
        'body'    => "Halo FAYLabs,\n\nSaya ingin bertanya tentang pembuatan website company profile untuk bisnis kami. Kami membutuhkan halaman profil, layanan, portfolio, dan contact form.\n\nApakah bisa dibantu untuk estimasi timeline dan biaya?\n\nTerima kasih.",
        'date'    => 'Today, 09:42',
        'unread'  => true,
    ],
    [
        'id'      => 2,
        'sender'  => 'Raka Pratama',
        'email'   => 'raka.pratama@example.com',
        'subject' => 'Maintenance dashboard internal',
        'preview' => 'Kami punya dashboard internal yang perlu dirapikan dan ditambah beberapa fitur reporting.',
        'body'    => "Hi FAYLabs,\n\nKami punya dashboard internal yang perlu dirapikan dan ditambah beberapa fitur reporting. Saat ini aplikasinya sudah berjalan, tetapi UI dan alurnya masih membingungkan untuk tim operasional.\n\nBisa diskusi minggu ini?",
        'date'    => 'Yesterday, 16:18',
        'unread'  => true,
    ],
    [
        'id'      => 3,
        'sender'  => 'Maya Sari',
        'email'   => 'maya.sari@example.com',
        'subject' => 'Request redesign landing page',
        'preview' => 'Landing page produk kami butuh tampilan yang lebih modern dan conversion-focused.',
        'body'    => "Halo,\n\nLanding page produk kami butuh tampilan yang lebih modern dan conversion-focused. Targetnya meningkatkan jumlah demo request dari traffic iklan.\n\nSaya ingin tahu apakah FAYLabs menerima project redesign seperti ini.",
        'date'    => 'Jun 19',
        'unread'  => false,
    ],
    [
        'id'      => 4,
        'sender'  => 'Ardi Wijaya',
        'email'   => 'ardi.wijaya@example.com',
        'subject' => 'Integrasi Cloudinary untuk portfolio',
        'preview' => 'Saya melihat FAYLabs punya pengalaman Cloudinary. Kami ingin integrasi upload image.',
        'body'    => "Selamat siang,\n\nSaya melihat FAYLabs punya pengalaman Cloudinary. Kami ingin integrasi upload image untuk portfolio dan artikel di CMS kami.\n\nApakah bisa dibuatkan modul upload dan optimasi gambar?",
        'date'    => 'Jun 18',
        'unread'  => false,
    ],
    [
        'id'      => 5,
        'sender'  => 'Lina Kartika',
        'email'   => 'lina.kartika@example.com',
        'subject' => 'Kolaborasi project edukasi digital',
        'preview' => 'Kami sedang menyiapkan platform edukasi digital dan mencari partner development.',
        'body'    => "Halo FAYLabs,\n\nKami sedang menyiapkan platform edukasi digital dan mencari partner development untuk MVP. Fokus awalnya course listing, enrollment, dan dashboard siswa.\n\nJika tertarik, kami ingin menjadwalkan call singkat.",
        'date'    => 'Jun 17',
        'unread'  => false,
    ],
];

$selectedEmail = $emails[0] ?? null;

require_once ROOT_PATH . '/partials/head.php';
?>

<!-- Global JS Config -->
<script>
  const FAY_CONFIG = {
    apiBase:   '<?= BASE_PATH ?>/api',
    adminBase: '<?= BASE_PATH ?>',
    csrfToken: '<?= htmlspecialchars($csrfToken) ?>',
  };
</script>

<?php require_once ROOT_PATH . '/partials/sidebar.php'; ?>

<!-- Main Content -->
<div class="main-wrapper">
  <!-- Topbar -->
  <header class="topbar">
    <button id="sidebar-toggle" class="topbar-hamburger" aria-label="Toggle sidebar">
      <i class="bi bi-list" aria-hidden="true"></i>
    </button>
    <span class="topbar-title">Emails</span>
    <div class="topbar-actions">
      <button type="button" class="btn btn-primary" data-compose-open>
        <i class="bi bi-pencil-square" aria-hidden="true"></i>
        Compose
      </button>
    </div>
  </header>

  <!-- Page Content -->
  <main class="page-content">
    <div class="page-header">
      <h2>Emails</h2>
      <p>Manage incoming FAYLabs emails. Backend integration is not connected yet.</p>
    </div>

    <?php if (empty($emails)): ?>
    <!-- Empty State -->
    <div class="empty-state">
      <div class="empty-state-icon" aria-hidden="true">
        <i class="bi bi-envelope-open" aria-hidden="true"></i>
      </div>
      <h3>No emails yet.</h3>
      <p>Incoming FAYLabs emails will appear here once email integration is connected.</p>
      <button type="button" class="btn btn-primary" style="margin-top:16px;" data-compose-open>
        <i class="bi bi-pencil-square" aria-hidden="true"></i>
        Compose
      </button>
    </div>
    <?php else: ?>
    <!-- Split Inbox/Detail Layout -->
    <section class="email-layout" aria-label="Email inbox">

      <!-- Inbox List Panel -->
      <div class="email-list-panel">
        <div class="email-list-header">
          <div>
            <h3>Inbox</h3>
            <p><?= count($emails) ?> messages</p>
          </div>
          <span class="email-count-badge"><?= count(array_filter($emails, fn($e) => $e['unread'])) ?> unread</span>
        </div>

        <div class="email-list" role="list">
          <?php foreach ($emails as $index => $email): ?>
          <button type="button"
                  class="email-list-item <?= $index === 0 ? 'active' : '' ?> <?= $email['unread'] ? 'unread' : '' ?>"
                  data-email-id="<?= (int) $email['id'] ?>"
                  data-sender="<?= e($email['sender']) ?>"
                  data-email="<?= e($email['email']) ?>"
                  data-subject="<?= e($email['subject']) ?>"
                  data-body="<?= e($email['body']) ?>"
                  data-date="<?= e($email['date']) ?>"
                  data-unread="<?= $email['unread'] ? '1' : '0' ?>"
                  role="listitem">
            <span class="email-item-topline">
              <span class="email-sender"><?= e($email['sender']) ?></span>
              <span class="email-date"><?= e($email['date']) ?></span>
            </span>
            <span class="email-subject-row">
              <?php if ($email['unread']): ?>
              <span class="email-unread-dot" aria-label="Unread"></span>
              <?php endif; ?>
              <span class="email-subject"><?= e($email['subject']) ?></span>
            </span>
            <span class="email-preview"><?= e($email['preview']) ?></span>
          </button>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Email Detail Panel -->
      <article class="email-detail-panel" aria-live="polite">
        <div class="email-detail-toolbar">
          <button type="button" class="btn btn-secondary btn-sm email-back-btn">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
            Back
          </button>
          <div class="email-detail-actions">
            <button type="button" class="btn btn-secondary btn-sm" data-email-toggle-read>
              <i class="bi bi-envelope-open" aria-hidden="true"></i>
              Mark as read
            </button>
            <button type="button" class="btn btn-primary btn-sm" data-email-reply>
              <i class="bi bi-reply" aria-hidden="true"></i>
              Reply
            </button>
          </div>
        </div>

        <?php if ($selectedEmail): ?>
        <div class="email-detail-meta">
          <h3 data-email-detail-subject><?= e($selectedEmail['subject']) ?></h3>
          <div class="email-detail-sender">
            <span class="email-avatar" data-email-detail-avatar><?= e(strtoupper(substr($selectedEmail['sender'], 0, 1))) ?></span>
            <div>
              <strong data-email-detail-sender><?= e($selectedEmail['sender']) ?></strong>
              <p><span data-email-detail-email><?= e($selectedEmail['email']) ?></span> to FAYLabs</p>
            </div>
          </div>
          <span class="email-detail-date" data-email-detail-date><?= e($selectedEmail['date']) ?></span>
        </div>
        <div class="email-detail-body" data-email-detail-body><?= nl2br(e($selectedEmail['body'])) ?></div>
        <?php endif; ?>
      </article>

    </section>
    <?php endif; ?>

  </main>
</div>

<!-- Compose Modal -->
<div id="compose-modal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="compose-modal-title">
  <div class="modal-box compose-modal-box">
    <div class="compose-modal-header">
      <h2 class="modal-title" id="compose-modal-title">Compose Email</h2>
      <button type="button" class="compose-close-btn" data-compose-close aria-label="Close compose modal">
        <i class="bi bi-x-lg" aria-hidden="true"></i>
      </button>
    </div>

    <form id="compose-form" novalidate>
      <div class="form-group">
        <label class="form-label" for="compose-to">To</label>
        <input type="email" id="compose-to" name="to" class="form-control" placeholder="client@example.com">
      </div>

      <div class="form-group">
        <label class="form-label" for="compose-subject">Subject</label>
        <input type="text" id="compose-subject" name="subject" class="form-control" placeholder="Email subject">
      </div>

      <div class="form-group">
        <label class="form-label" for="compose-message">Message</label>
        <textarea id="compose-message" name="message" class="form-control compose-message" rows="8" placeholder="Write your message..."></textarea>
      </div>

      <p class="form-hint">Email sending is not connected yet. This form is UI-only.</p>

      <div class="modal-actions">
        <button type="button" class="btn btn-secondary" data-compose-close>Cancel</button>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-send" aria-hidden="true"></i>
          Send Placeholder
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Toast Container -->
<div id="toast-container" class="toast-container" aria-live="polite"></div>

<!-- Page Script -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const emailItems    = Array.from(document.querySelectorAll('.email-list-item'));
    const subject       = document.querySelector('[data-email-detail-subject]');
    const sender        = document.querySelector('[data-email-detail-sender]');
    const emailEl       = document.querySelector('[data-email-detail-email]');
    const avatar        = document.querySelector('[data-email-detail-avatar]');
    const date          = document.querySelector('[data-email-detail-date]');
    const body          = document.querySelector('[data-email-detail-body]');
    const toggleReadBtn = document.querySelector('[data-email-toggle-read]');
    const replyBtn      = document.querySelector('[data-email-reply]');
    const backBtn       = document.querySelector('.email-back-btn');
    const listPanel     = document.querySelector('.email-list-panel');
    const detailPanel   = document.querySelector('.email-detail-panel');
    const composeModal  = document.getElementById('compose-modal');
    const composeForm   = document.getElementById('compose-form');
    let   activeItem    = emailItems[0] || null;

    // ── Toast helper ─────────────────────────────────────────
    function showToast(message, type) {
      if (window.AdminToast) {
        window.AdminToast.show(message, type || 'success');
      } else {
        alert(message);
      }
    }

    // ── Render email detail ───────────────────────────────────
    function renderEmail(item) {
      if (!item) return;

      emailItems.forEach(function (i) { i.classList.remove('active'); });
      item.classList.add('active');
      activeItem = item;

      if (subject) subject.textContent = item.dataset.subject || '';
      if (sender)  sender.textContent  = item.dataset.sender  || '';
      if (emailEl) emailEl.textContent = item.dataset.email   || '';
      if (avatar)  avatar.textContent  = (item.dataset.sender || '?').trim().charAt(0).toUpperCase();
      if (date)    date.textContent    = item.dataset.date    || '';
      if (body)    body.innerHTML      = (item.dataset.body || '').replace(/\n/g, '<br>');

      updateReadButton();

      // On mobile: hide list, show detail
      if (window.innerWidth <= 900 && listPanel && detailPanel) {
        listPanel.style.display   = 'none';
        detailPanel.style.display = '';
      }
    }

    // ── Update read/unread button label ───────────────────────
    function updateReadButton() {
      if (!toggleReadBtn || !activeItem) return;
      const isUnread = activeItem.dataset.unread === '1';
      toggleReadBtn.innerHTML = isUnread
        ? '<i class="bi bi-envelope-open" aria-hidden="true"></i> Mark as read'
        : '<i class="bi bi-envelope" aria-hidden="true"></i> Mark as unread';
    }

    // ── Compose modal open/close ──────────────────────────────
    function setModalOpen(open) {
      if (!composeModal) return;
      composeModal.classList.toggle('active', open);
      if (open) {
        const toField = document.getElementById('compose-to');
        if (toField) toField.focus();
      }
    }

    // ── Inbox item click ──────────────────────────────────────
    emailItems.forEach(function (item) {
      item.addEventListener('click', function () { renderEmail(item); });
    });

    // ── Back button (mobile) ──────────────────────────────────
    if (backBtn && listPanel && detailPanel) {
      backBtn.addEventListener('click', function () {
        listPanel.style.display   = '';
        detailPanel.style.display = 'none';
      });
    }

    // ── Mark read/unread ──────────────────────────────────────
    if (toggleReadBtn) {
      toggleReadBtn.addEventListener('click', function () {
        if (!activeItem) return;

        const nextUnread = activeItem.dataset.unread !== '1';
        activeItem.dataset.unread = nextUnread ? '1' : '0';
        activeItem.classList.toggle('unread', nextUnread);

        const dot = activeItem.querySelector('.email-unread-dot');
        if (nextUnread && !dot) {
          const subjectRow = activeItem.querySelector('.email-subject-row');
          if (subjectRow) {
            const newDot = document.createElement('span');
            newDot.className = 'email-unread-dot';
            newDot.setAttribute('aria-label', 'Unread');
            subjectRow.prepend(newDot);
          }
        }
        if (!nextUnread && dot) dot.remove();

        updateReadButton();
        showToast(nextUnread ? 'Email marked as unread.' : 'Email marked as read.', 'success');
      });
    }

    // ── Reply pre-fills compose ───────────────────────────────
    if (replyBtn) {
      replyBtn.addEventListener('click', function () {
        if (!activeItem) return;
        setModalOpen(true);
        const toField      = document.getElementById('compose-to');
        const subjectField = document.getElementById('compose-subject');
        if (toField)      toField.value      = activeItem.dataset.email   || '';
        if (subjectField) subjectField.value = 'Re: ' + (activeItem.dataset.subject || '');
      });
    }

    // ── Compose open ──────────────────────────────────────────
    document.querySelectorAll('[data-compose-open]').forEach(function (btn) {
      btn.addEventListener('click', function () { setModalOpen(true); });
    });

    // ── Compose close ─────────────────────────────────────────
    document.querySelectorAll('[data-compose-close]').forEach(function (btn) {
      btn.addEventListener('click', function () { setModalOpen(false); });
    });

    // ── Close on backdrop click ───────────────────────────────
    if (composeModal) {
      composeModal.addEventListener('click', function (e) {
        if (e.target === composeModal) setModalOpen(false);
      });
    }

    // ── Close on Escape key ───────────────────────────────────
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && composeModal && composeModal.classList.contains('active')) {
        setModalOpen(false);
      }
    });

    // ── Compose submit (placeholder) ─────────────────────────
    if (composeForm) {
      composeForm.addEventListener('submit', function (e) {
        e.preventDefault();
        showToast('Email sending is not connected yet.', 'warning');
        setModalOpen(false);
        composeForm.reset();
      });
    }

    // ── Mobile: hide detail panel initially ───────────────────
    function handleResize() {
      if (window.innerWidth <= 900 && listPanel && detailPanel) {
        if (!detailPanel.style.display || detailPanel.style.display === 'none') {
          listPanel.style.display   = '';
          detailPanel.style.display = 'none';
        }
      } else {
        if (listPanel) listPanel.style.display   = '';
        if (detailPanel) detailPanel.style.display = '';
      }
    }

    handleResize();
    window.addEventListener('resize', handleResize);

    // Initial read button state
    updateReadButton();
  });
</script>

<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
