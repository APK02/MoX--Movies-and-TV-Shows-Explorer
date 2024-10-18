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

async function getEmail() {
  await fetch("http://localhost/MoX-Project/src/api/profile", {
    method: "GET",
    headers: {
      "Content-Type": "application/json; charset=UTF-8",
    },
  })
    .then((response) => {
      if (response.ok) {
        console.log(response);
        return response.json();
      } else {
        throw new Error("Request failed");
      }
    })
    .then((data) => {
      console.log(data);
      let email = document.getElementById("email");
      email.setAttribute("placeholder", data["email"]);
      email.placeholder = data["email"];
      email.setAttribute("value", data["email"]);
      email.value = data["email"];
      return data["email"];
    })
    .catch((error) => {
      console.error("Error:", error.message);
    });
}

async function getUsername() {
  await fetch("http://localhost/MoX-Project/src/api/profile", {
    method: "GET",
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
      let username = document.getElementById("username");
      username.setAttribute("placeholder", data["username"]);
      username.placeholder = data["username"];
      username.setAttribute("value", data["username"]);
      username.value = data["username"];
      return data["username"];
    })
    .catch((error) => {
      console.error("Error:", error.message);
    });
}

async function emptyForm() {
  let username = document.getElementById("username");
  username.value = "";
  let email = document.getElementById("email");
  email.value = "";
}

async function save() {
  const email = document.getElementById("email").value;
  const username = document.getElementById("username").value;
  var validRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)+$/;
  if (email !== "") {
    if (!email.match(validRegex)) {
      showToastMessage("Insert a valid email!");
      return;
    }
  }

  const body = {
    email: email,
    username: username,
  };
  const saveResponse = await fetch("http://localhost/MoX-Project/src/api/profile", {
    method: "PUT",
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
      if (data["usernameUpdate"] === "Username already in use") showToastMessage("Username already in use!");
      if (data["usernameUpdate"] === true) showToastMessage("Username changed successfully!");
      else {
        if (username !== "") showToastMessage("Something went wrong with the username!:/");
      }
      if (data["emailUpdate"] === true) showToastMessage("Email changed successfully!");
      else {
        if (email !== "") showToastMessage("Something went wrong with the email!:/");
      }
      emptyForm();
      getEmail();
      getUsername();
      return data;
    })
    .catch((error) => {
      console.error("Error:", error.message);
    });
}

async function logout() {
  localStorage.clear();
  document.cookie = "token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
  window.location.replace("http://localhost/MoX-Project/src/view/index.html");
}

async function redirectToResetPassword() {
  localStorage.clear();
  document.cookie = "token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
  window.location.replace("http://localhost/MoX-Project/src/view/reset-password.html");
}

async function deleteAccount() {
  await fetch("http://localhost/MoX-Project/src/api/profile", {
    method: "DELETE",
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
      logout();
      return data;
    })
    .catch((error) => {
      console.error("Error:", error.message);
    });
}

async function getFavGenres() {
  await fetch("http://localhost/MoX-Project/src/api/profile", {
    method: "GET",
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
      let gsMap = new Map([
        ["fantasy", "Fantasy"],
        ["thriller", "Thriller"],
        ["action", "Action"],
        ["comedy", "Comedy"],
        ["documentary", "Documentary"],
        ["animation", "Animation"],
        ["science_fiction", "Science Fiction"],
        ["romance", "Romance"],
        ["drama", "Drama"],
        ["mistery", "Mistery"],
        ["horror", "Horror"],
      ]);

      let favourite_genres = document.getElementById("favourite_genres");
      const genres = data["favourite_genres"];
      let listItem;
      for (const [key, genre] of Object.entries(genres)) {
        listItem = document.createElement("p");
        listItem.innerHTML = gsMap.get(genre);
        favourite_genres.appendChild(listItem);
      }
      return data["favourite_genres"];
    })
    .catch((error) => {
      console.error("Error:", error.message);
    });
}
