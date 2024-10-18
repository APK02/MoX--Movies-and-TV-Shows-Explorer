let connectBtn = document.getElementById("connectBtn");
let createBtn = document.getElementById("createBtn");

if (cookie.indexOf("token") !== -1) {
  connectBtn.setAttribute("onclick", "window.location.replace('http://localhost/MoX-Project/src/view/dashboard.html')");
  createBtn.setAttribute("onclick", "window.location.replace('http://localhost/MoX-Project/src/view/dashboard.html')");
} else {
  connectBtn.setAttribute("onclick", "window.location.replace('http://localhost/MoX-Project/src/view/login.html')");
  createBtn.setAttribute("onclick", "window.location.replace('http://localhost/MoX-Project/src/view/register.html')");
}
