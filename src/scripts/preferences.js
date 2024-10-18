const maximum = 3;
let clicked = 0;
let value;
let pressed = new Map([
  ["fantasy", false],
  ["thriller", false],
  ["action", false],
  ["comedy", false],
  ["documentary", false],
  ["animation", false],
  ["science_fiction", false],
  ["romance", false],
  ["drama", false],
  ["mistery", false],
  ["horror", false],
]);

function buttonOnClick(button) {
  value = button.value;
  if (pressed.get(value) === false) {
    if (clicked < maximum) {
      clicked += 1;
      pressed.set(value, true);
      button.style.backgroundColor = "lightgray";
    } else {
      showToastMessage("You can select up to 3 favourite genres only.");
    }
  } else {
    clicked -= 1;
    pressed.set(value, false);
    button.style.backgroundColor = "gray";
  }
}

async function addFavsToDb() {
  let favArray = [];

  for (let [key, value] of pressed) {
    if (value === true) {
      favArray.push(key);
    }
  }

  const body = {
    fav0: favArray[0],
    fav1: favArray[1],
    fav2: favArray[2],
  };

  const response = await fetch("http://localhost/MoX-Project/src/api/register", {
    method: "PUT",
    body: JSON.stringify(body),
    headers: {
      "Content-Type": "application/json; charset=UTF-8",
    },
  });

  const result = await response.json();
  return result;
}

async function changeFavsToDb() {
  let favArray = [];

  for (let [key, value] of pressed) {
    if (value === true) {
      favArray.push(key);
    }
  }

  const body = {
    fav0: favArray[0],
    fav1: favArray[1],
    fav2: favArray[2],
  };

  try {
    const response = await fetch("http://localhost/MoX-Project/src/api/preferences", {
      method: "PUT",
      body: JSON.stringify(body),
      headers: {
        "Content-Type": "application/json; charset=UTF-8",
      },
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const result = await response.json();
    return { status: true };
  } catch (error) {
    console.log("Error:", error);
    showToastMessage("Failed to change preferences. Please try again.");
    return { status: false };
  }
}

function submitFavs(event) {
  event.preventDefault();
}

async function handleSubmit() {
  let button = document.getElementById("submit-button");
  button.addEventListener("submit", submitFavs);

  if (clicked === maximum) {
    let cookie = document.cookie;

    if (cookie.indexOf("userId=") !== -1) {
      let result = await addFavsToDb();
      if (result["status"]) {
        window.location.replace("http://localhost/MoX-Project/src/view/login.html");
      } else {
        showToastMessage("Something went wrong!");
      }
    } else if (cookie.indexOf("token=") !== -1) {
      let result = await changeFavsToDb();
      console.log(result);
      window.location.replace("http://localhost/MoX-Project/src/view/profile.html");
    } else {
      showToastMessage("You need to be logged in to change preferences!");
    }
  } else {
    showToastMessage("You need to select exactly 3 favourite genres!");
  }
}
