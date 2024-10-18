function submitForm(event) {
  event.preventDefault();
}

async function auth(body) {
  return await fetch("http://localhost/MoX-Project/src/api/auth", {
    method: "POST",
    body: JSON.stringify(body),
    headers: {
      "Content-Type": "application/json; charset=UTF-8",
    },
  }).then((response) => response.json());
}

const usernameNotFound = "Username not found";
const wrongPassword = "Wrong password";

async function handleLogin() {
  const form = document.getElementById("login-form");
  form.addEventListener("submit", submitForm);

  const username = document.getElementById("username").value;
  const password = document.getElementById("password").value;

  const body = {
    username: username,
    password: password,
  };

  const loginResponse = await auth(body);
  if (loginResponse["message"] === usernameNotFound) {
    showToastMessage(usernameNotFound);
  } else if (loginResponse["message"] === wrongPassword) {
    showToastMessage(wrongPassword);
  } else {
    localStorage.setItem("type", "special");
    window.location.replace("http://localhost/MoX-Project/src/view/dashboard.html");
  }
}
