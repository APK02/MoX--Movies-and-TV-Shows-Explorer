async function send() {
  const email = document.getElementById("email").value;
  const body = {
    email: email,
  };
  fetch("http://localhost/MoX-Project/src/api/resetPassword", {
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
      showToastMessage(data);
      return data;
    })
    .catch((error) => {
      console.error("Error:", error.message);
    });
}
