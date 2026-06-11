<!-- Site Footer -->
<footer class="site-footer">
  <div class="container">
    <p>© <?= date('Y') ?> <?= htmlspecialchars(APP_NAME) ?> · Built with PHP &amp; ☕</p>
  </div>
</footer>

<!-- jQuery (Cloudflare CDN) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<!-- highlight.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<!-- marked.js -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<!-- DOMPurify -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.1.3/purify.min.js"></script>

<?php if (!empty($loadProjectsJs)): ?>
<script>
  const FAY_PUBLIC = {
    apiBase:      '<?= APP_URL ?>/../api',
    projectsBase: '<?= APP_URL ?>/projects',
  };
</script>
<script src="<?= APP_URL ?>/assets/js/projects.js"></script>
<?php endif; ?>

<?php if (!empty($loadMarkdown)): ?>
<script>
  // Render markdown content on detail page
  document.addEventListener('DOMContentLoaded', function () {
    const rawContent = document.getElementById('raw-content');
    const target     = document.getElementById('rendered-content');

    if (!rawContent || !target) return;

    let html = '';
    if (typeof marked !== 'undefined') {
      marked.setOptions({ breaks: true, gfm: true });
      html = marked.parse(rawContent.textContent || '');
    }

    if (typeof DOMPurify !== 'undefined') {
      html = DOMPurify.sanitize(html);
    }

    target.innerHTML = html;

    if (typeof hljs !== 'undefined') {
      target.querySelectorAll('pre code').forEach(el => hljs.highlightElement(el));
    }
  });
</script>
<?php endif; ?>

</body>
</html>
