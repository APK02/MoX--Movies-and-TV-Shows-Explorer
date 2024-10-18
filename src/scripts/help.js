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

  const form = document.getElementById("help-form");
  if (form) {
    form.addEventListener("submit", function (event) {
      event.preventDefault();
      sendHelpRequest();
    });
  }
});

async function sendHelpRequest() {
  const topic = document.getElementById("help-topic").value;
  const username = document.getElementById("name").value;
  const email = document.getElementById("email").value;
  const message = document.getElementById("message").value;

  const body = {
    "help-topic": topic,
    name: username,
    email: email,
    message: message,
  };

  console.log(body);

  fetch("http://localhost/MoX-Project/src/api/controllers/HelpController.php", {
    method: "POST",
    body: JSON.stringify(body),
    headers: {
      "Content-Type": "application/json; charset=UTF-8",
    },
  })
    .then((response) => {
      if (response.ok) {
        return response.json();
      } else {
        throw new Error("Request failed");
      }
    })
    .then((data) => {
      showToastMessage(data.message);
      if (data.status === "success") {
        document.getElementById("help-form").reset();
      }
    })
    .catch((error) => {
      console.error("Error:", error.message);
      showToastMessage("An error occurred while sending your message.");
    });
}
