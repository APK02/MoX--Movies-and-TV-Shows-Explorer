document.addEventListener("DOMContentLoaded", function () {
  var menuButton = document.querySelector(".menu-button");
  var menuModal = document.getElementById("menuModal");
  var menuModalContent = document.querySelector(".menu-modal__content");

  menuButton.addEventListener("click", function () {
    menuButton.classList.toggle("is-active");
    menuModal.classList.toggle("is-visible");
  });

  menuModal.addEventListener("click", function (event) {
    if (event.target === menuModal) {
      menuButton.classList.remove("is-active");
      menuModal.classList.remove("is-visible");
    }
  });

  menuModalContent.addEventListener("click", function (event) {
    event.stopPropagation();
  });

  window.addEventListener("resize", function () {
    var currentWidth = window.innerWidth;
    if (currentWidth >= 1015) {
      menuButton.classList.remove("is-active");
      menuModal.classList.remove("is-visible");
    }
  });
});