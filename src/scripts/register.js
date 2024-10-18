const emailExists = "Email already in use";
const usernameExists = "Username already in use";
const success = "Account created successfully";

function submitForm(event) {
  event.preventDefault();
}

async function createAccount(body) {
  console.log(body);
  return await fetch("http://localhost/MoX-Project/src/api/register", {
    method: "POST",
    body: JSON.stringify(body),
    headers: {
      "Content-Type": "application/json; charset=UTF-8",
    },
  }).then((response) => response.json());
}

async function handleRegister() {
  const form = document.getElementById("registerForm");
  form.addEventListener("submit", submitForm);

  const emailValue = document.getElementById("email").value;
  const usernameValue = document.getElementById("username").value;
  const passwordValue = document.getElementById("password").value;
  const conPasswordValue = document.getElementById("confirm-password").value;

  var validRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
  if (emailValue !== "") {
    if (!emailValue.match(validRegex)) {
      showToastMessage("Insert a valid email!");
      return;
    }
  }
  if (passwordValue !== conPasswordValue) {
    showToastMessage("Passwords do not match");
  } else if (passwordValue.length < 8) {
    showToastMessage("Passwords is too short");
  } else {
    const body = {
      email: emailValue,
      username: usernameValue,
      password: passwordValue,
    };

    const reqResponse = await createAccount(body);
    if (reqResponse["message"] === success) {
      // access userId as reqResponse['userId']
      window.location.replace("http://localhost/MoX-Project/src/view/preferences.html");
    } else if (reqResponse["message"] === emailExists) {
      showToastMessage(emailExists);
    } else {
      showToastMessage(usernameExists);
    }
  }
}
