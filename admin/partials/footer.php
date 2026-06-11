  <!-- jQuery (Cloudflare CDN) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- marked.js -->
  <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
  <!-- highlight.js -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
  <!-- DOMPurify -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.1.3/purify.min.js"></script>
  <!-- Admin JS -->
  <script src="<?= APP_URL ?>/assets/js/admin.js"></script>
  <?php if (isset($loadEditor) && $loadEditor): ?>
  <script src="<?= APP_URL ?>/assets/js/editor.js"></script>
  <?php endif; ?>

</body>
</html>
