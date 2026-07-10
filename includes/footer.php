  <footer class="footer">
    <div class="container footer-grid">
      <div><strong>NovaTech Gadgets</strong><p>Premium gadgets for study, work, lifestyle, and entertainment.</p></div>
      <div><strong>Shop</strong><p>Smartphones<br>Laptops<br>Accessories</p></div>
      <div><strong>Trust</strong><p>OTP payment<br>Warranty records<br>Admin approval</p></div>
      <div><strong>Project</strong><p>PHP + MySQL<br>XAMPP coursework system</p></div>
    </div>
  </footer>
  <?php if (empty($hide_chatbot)): ?><?php include dirname(__DIR__) . '/chatbot.php'; ?><?php endif; ?>
  <script src="<?= $base ?? '' ?>js/app.js?v=20260707-responsive-system"></script>
  <?php if (!empty($landing_page)): ?><script src="<?= $base ?? '' ?>js/landing-events.js?v=20260701-event-banners"></script><?php endif; ?>
  <script src="<?= $base ?? '' ?>js/auth.js?v=20260627"></script>
  <script src="<?= $base ?? '' ?>js/chatbot.js?v=20260701-chatbot-position"></script>
</body>
</html>
