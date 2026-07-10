document.addEventListener("DOMContentLoaded", () => {
  const carousel = document.querySelector("[data-event-carousel]");
  if (!carousel) return;

  const slides = Array.from(carousel.querySelectorAll("[data-event-slide]"));
  const dots = Array.from(carousel.querySelectorAll("[data-event-dot]"));
  const prev = carousel.querySelector("[data-event-prev]");
  const next = carousel.querySelector("[data-event-next]");
  const autoplayMs = 5000;
  let activeIndex = Number.parseInt(carousel.dataset.initialSlide || "0", 10);
  let timer = null;

  if (!slides.length) return;
  if (Number.isNaN(activeIndex) || activeIndex < 0 || activeIndex >= slides.length) activeIndex = 0;

  const showSlide = (index) => {
    activeIndex = (index + slides.length) % slides.length;
    slides.forEach((slide, slideIndex) => {
      slide.classList.toggle("active", slideIndex === activeIndex);
    });
    dots.forEach((dot, dotIndex) => {
      dot.classList.toggle("active", dotIndex === activeIndex);
      dot.setAttribute("aria-selected", dotIndex === activeIndex ? "true" : "false");
    });
  };

  const stopAutoplay = () => {
    if (!timer) return;
    window.clearInterval(timer);
    timer = null;
  };

  const startAutoplay = () => {
    stopAutoplay();
    if (slides.length <= 1) return;
    timer = window.setInterval(() => showSlide(activeIndex + 1), autoplayMs);
  };

  prev?.addEventListener("click", () => {
    showSlide(activeIndex - 1);
    startAutoplay();
  });

  next?.addEventListener("click", () => {
    showSlide(activeIndex + 1);
    startAutoplay();
  });

  dots.forEach((dot) => {
    dot.addEventListener("click", () => {
      showSlide(Number.parseInt(dot.dataset.eventDot || "0", 10));
      startAutoplay();
    });
  });

  carousel.addEventListener("mouseenter", stopAutoplay);
  carousel.addEventListener("mouseleave", startAutoplay);
  carousel.addEventListener("focusin", stopAutoplay);
  carousel.addEventListener("focusout", startAutoplay);

  carousel.addEventListener("keydown", (event) => {
    if (event.key === "ArrowLeft") {
      event.preventDefault();
      showSlide(activeIndex - 1);
      startAutoplay();
    }

    if (event.key === "ArrowRight") {
      event.preventDefault();
      showSlide(activeIndex + 1);
      startAutoplay();
    }
  });

  showSlide(activeIndex);
  startAutoplay();
});
