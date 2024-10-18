async function reset() {
  const userId = document.getElementById("userId").value;
  const password = document.getElementById("password").value;
  const body = {
    userId: userId,
    password: password,
  };
  console.log(body);
  await fetch("http://localhost/MoX-Project/src/api/resetPassword", {
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
      if (data === "Password Changed!") {
        window.location.replace("http://localhost/MoX-Project/src/view/login.html");
      } else {
        alert(data);
      }
      return data;
    })
    .catch((error) => {
      console.error("Error:", error.message);
    });
}
