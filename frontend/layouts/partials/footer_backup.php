<footer class="bg-dark text-white mt-auto py-4 border-top">
  <div class="container">
    <div class="row text-start">
      <div class="col-md-4 mb-3">
        <h5>Vá» chÃºng tÃ´i</h5>
        <p>VÄƒn PhÃ²ng Pháº©m Online chuyÃªn cung cáº¥p cÃ¡c sáº£n pháº©m há»c táº­p, vÄƒn phÃ²ng uy tÃ­n, cháº¥t lÆ°á»£ng.</p>
      </div>
      <div class="col-md-4 mb-3">
        <h5>LiÃªn há»‡</h5>
        <p>ðŸ“ 123 ÄÆ°á»ng ABC, Quáº­n 1, TP.HCM</p>
        <p>ðŸ“ž 0123 456 789</p>
        <p>âœ‰ contact@vpponline.vn</p>
      </div>
      <div class="col-md-4 mb-3">
        <h5>LiÃªn káº¿t nhanh</h5>
        <ul class="list-unstyled">
          <li><a href="/index.php" class="text-decoration-none text-white">ðŸ  Trang chá»§</a></li>
          <li><a href="/frontend/pages/products_list.php" class="text-decoration-none text-white">ðŸ›’ Sáº£n pháº©m</a></li>
          <li><a href="/frontend/pages/about_us.php" class="text-decoration-none text-white">â„¹ï¸ Giá»›i thiá»‡u</a></li>
          <li><a href="/frontend/pages/contact.php" class="text-decoration-none text-white">ï¿½ LiÃªn há»‡</a></li>
        </ul>
      </div>
    </div>
    <div class="text-center border-top pt-3 mt-3 small text-muted">
      Â© <?= date("Y"); ?> VÄƒn PhÃ²ng Pháº©m Online - All rights reserved.
    </div>
  </div>
  <!-- Scroll to top button -->
  <div id="scrollBtn">â†‘</div>

  <!-- Social Media Float buttons -->
  <div class="social-links">
    <a href="https://facebook.com" target="_blank"><i class="fa fa-facebook"></i></a>
    <a href="https://youtube.com" target="_blank"><i class="fa fa-youtube"></i></a>
    <a href="https://zalo.me" target="_blank" style="font-size:16px;font-weight:bold;">Z</a>
  </div>
  <script>
  const scrollBtn = document.getElementById("scrollBtn");

  window.addEventListener("scroll", () => {
    if (window.scrollY > 300) {
      scrollBtn.classList.add("show");
    } else {
      scrollBtn.classList.remove("show");
    }
  });

  scrollBtn.onclick = () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
  };
  </script>
  <script>
// Theme toggle
const themeToggle = document.getElementById("themeToggle");
const themeIcon = document.getElementById("themeIcon");
const currentTheme = localStorage.getItem("theme");

// Apply saved theme
if (currentTheme === "dark") {
  document.body.classList.add("dark-mode");
  themeIcon.classList.remove("fa-moon-o");
  themeIcon.classList.add("fa-sun-o");
}

// Toggle click
themeToggle.addEventListener("click", () => {
  document.body.classList.toggle("dark-mode");

  if (document.body.classList.contains("dark-mode")) {
    themeIcon.classList.remove("fa-moon-o");
    themeIcon.classList.add("fa-sun-o");
    localStorage.setItem("theme","dark");
  } else {
    themeIcon.classList.remove("fa-sun-o");
    themeIcon.classList.add("fa-moon-o");
    localStorage.setItem("theme","light");
  }
});
</script>

</footer>

