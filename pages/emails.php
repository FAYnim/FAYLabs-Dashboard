<?php
// ============================================================
// Emails — UI-only Inbox
// ============================================================

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/auth.php';
require_once ROOT_PATH . '/includes/csrf.php';
require_once ROOT_PATH . '/includes/helpers.php';

csrfStart();
requireAdmin();

$pageTitle  = 'Emails';
$activePage = 'emails';
$csrfToken  = csrfGenerate();

try {
    $pdo  = Database::getConnection();
    $stmt = $pdo->query(
        "SELECT id, sender_name, sender_email, recipient_email, subject, body, direction, is_read, sent_at
         FROM emails ORDER BY sent_at DESC"
    );
    $emails = array_map(function ($email) {
        $sender = $email['sender_name'] ?: $email['sender_email'];

        return [
            'id'              => $email['id'],
            'sender'          => $sender,
            'email'           => $email['sender_email'],
            'recipient_email' => $email['recipient_email'],
            'subject'         => $email['subject'],
            'preview'         => mb_strimwidth(trim(preg_replace('/\s+/', ' ', $email['body'])), 0, 100, '...'),
            'body'            => $email['body'],
            'direction'       => $email['direction'],
            'date'            => date('M j, H:i', strtotime($email['sent_at'])),
            'unread'          => !$email['is_read'],
        ];
    }, $stmt->fetchAll());
} catch (Exception $e) {
    $emails  = [];
    $dbError = 'Failed to load emails.';
}

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
      <p>Manage incoming FAYLabs emails.</p>
    </div>

    <?php if (isset($dbError)): ?>
    <div class="alert alert-danger">
      <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
      <?= e($dbError) ?>
    </div>
    <?php endif; ?>

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
                  data-direction="<?= e($email['direction']) ?>"
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
            <button type="button" class="btn btn-danger btn-sm" data-email-delete>
              <i class="bi bi-trash" aria-hidden="true"></i>
              Delete
            </button>
            <button type="button" class="btn btn-success btn-sm" data-email-send <?= ($selectedEmail['direction'] ?? '') === 'outgoing' ? '' : 'hidden' ?>>
              <i class="bi bi-send" aria-hidden="true"></i>
              Send Email
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
        <input type="email" id="compose-to" name="to" class="form-control" placeholder="client@example.com" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="compose-subject">Subject</label>
        <input type="text" id="compose-subject" name="subject" class="form-control" placeholder="Email subject" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="compose-message">Message</label>
        <textarea id="compose-message" name="message" class="form-control compose-message" rows="8" placeholder="Write your message..." required></textarea>
      </div>

      <p class="form-hint">Email will be saved as outgoing message.</p>

      <div class="modal-actions">
        <button type="button" class="btn btn-secondary" data-compose-close>Cancel</button>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-send" aria-hidden="true"></i>
          Save Email
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
    const deleteBtn     = document.querySelector('[data-email-delete]');
    const sendBtn       = document.querySelector('[data-email-send]');
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
      updateSendButton();

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

    function updateSendButton() {
      if (!sendBtn || !activeItem) return;
      sendBtn.hidden = activeItem.dataset.direction !== 'outgoing';
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
      toggleReadBtn.addEventListener('click', async function () {
        if (!activeItem) return;

        const previousUnread = activeItem.dataset.unread === '1';
        const nextUnread = !previousUnread;

        function setUnreadState(isUnread) {
          activeItem.dataset.unread = isUnread ? '1' : '0';
          activeItem.classList.toggle('unread', isUnread);

          const dot = activeItem.querySelector('.email-unread-dot');
          if (isUnread && !dot) {
            const subjectRow = activeItem.querySelector('.email-subject-row');
            if (subjectRow) {
              const newDot = document.createElement('span');
              newDot.className = 'email-unread-dot';
              newDot.setAttribute('aria-label', 'Unread');
              subjectRow.prepend(newDot);
            }
          }
          if (!isUnread && dot) dot.remove();
          updateReadButton();
        }

        setUnreadState(nextUnread);
        toggleReadBtn.disabled = true;

        try {
          const formData = new FormData();
          formData.append('id', activeItem.dataset.emailId || '0');
          formData.append('is_read', nextUnread ? '0' : '1');
          formData.append('csrf_token', FAY_CONFIG.csrfToken);

          const response = await fetch(FAY_CONFIG.apiBase + '/emails/toggle-read.php', {
            method: 'POST',
            body: formData,
          });
          const result = await response.json();

          if (!response.ok || !result.success) {
            throw new Error(result.message || 'Failed to update email.');
          }

          showToast(result.message || (nextUnread ? 'Email marked as unread.' : 'Email marked as read.'), 'success');
        } catch (error) {
          setUnreadState(previousUnread);
          showToast(error.message || 'Failed to update email.', 'danger');
        } finally {
          toggleReadBtn.disabled = false;
        }
      });
    }

    // ── Delete email ──────────────────────────────────────────
    if (deleteBtn) {
      deleteBtn.addEventListener('click', async function () {
        if (!activeItem || !confirm('Delete this email?')) return;

        deleteBtn.disabled = true;

        try {
          const formData = new FormData();
          formData.append('id', activeItem.dataset.emailId || '0');
          formData.append('csrf_token', FAY_CONFIG.csrfToken);

          const response = await fetch(FAY_CONFIG.apiBase + '/emails/delete.php', {
            method: 'POST',
            body: formData,
          });
          const result = await response.json();

          if (!response.ok || !result.success) {
            throw new Error(result.message || 'Failed to delete email.');
          }

          showToast(result.message || 'Email deleted successfully.', 'success');
          window.location.reload();
        } catch (error) {
          showToast(error.message || 'Failed to delete email.', 'danger');
        } finally {
          deleteBtn.disabled = false;
        }
      });
    }

    // ── Send outgoing email ───────────────────────────────────
    if (sendBtn) {
      sendBtn.addEventListener('click', async function () {
        if (!activeItem || activeItem.dataset.direction !== 'outgoing') return;
        if (!confirm('Send this email?')) return;

        sendBtn.disabled = true;

        try {
          const formData = new FormData();
          formData.append('id', activeItem.dataset.emailId || '0');
          formData.append('csrf_token', FAY_CONFIG.csrfToken);

          const response = await fetch(FAY_CONFIG.apiBase + '/emails/send.php', {
            method: 'POST',
            body: formData,
          });
          const result = await response.json();

          if (!response.ok || !result.success) {
            throw new Error(result.message || 'Failed to send email.');
          }

          showToast(result.message || 'Email sent successfully.', 'success');
        } catch (error) {
          showToast(error.message || 'Failed to send email.', 'danger');
        } finally {
          sendBtn.disabled = false;
        }
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

    // ── Compose submit ──────────────────────────────────────
    if (composeForm) {
      composeForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const submitBtn = composeForm.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        try {
          const formData = new FormData(composeForm);
          formData.append('csrf_token', FAY_CONFIG.csrfToken);

          const response = await fetch(FAY_CONFIG.apiBase + '/emails/create.php', {
            method: 'POST',
            body: formData,
          });
          const result = await response.json();

          if (!response.ok || !result.success) {
            throw new Error(result.message || 'Failed to save email.');
          }

          showToast(result.message || 'Email saved successfully.', 'success');
          setModalOpen(false);
          composeForm.reset();
          window.location.reload();
        } catch (error) {
          showToast(error.message || 'Failed to save email.', 'danger');
        } finally {
          if (submitBtn) submitBtn.disabled = false;
        }
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

    // Initial button state
    updateReadButton();
    updateSendButton();
  });
</script>

<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
