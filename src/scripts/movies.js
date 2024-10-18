let spinnerElement;

document.addEventListener("DOMContentLoaded", () => {
  spinnerElement = document.getElementById("spinner");
  getFavouriteMovies();
});

async function getFavouriteMovies() {
  console.log("getFavouriteMovies");
  console.log(spinnerElement);
  spinnerElement.style.display = "block";
  await fetch("http://localhost/MoX-Project/src/api/dash", {
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
    .then(({ movies, images, scores }) => {
      localStorage.setItem("movies", JSON.stringify(movies));
      localStorage.setItem("images", JSON.stringify(images));
      localStorage.setItem("scores", JSON.stringify(scores));
      spinnerElement.style.display = "none";
      sortMovies("title", "desc");
    })
    .catch((error) => {
      console.error("Error:", error.message);
    })
    .finally(() => {
      console.log("done");
      console.log(spinnerElement);
    });
}

async function handleFilterSubmit() {
  let content = document.querySelector(".dashboard__center-content");
  content.innerHTML = '<div id="spinner" class="spinner" style="display: block;"></div>';
  const filters = {
    streamingService: document.getElementById("streaming-service-select").value,
    genre: document.getElementById("genre-select").value,
    year: document.getElementById("year-select").value,
    type: document.getElementById("type-select").value,
    duration: document.getElementById("duration-select").value,
  };

  console.log(filters);

  spinnerElement.style.display = "block";
  await fetch("http://localhost/MoX-Project/src/api/dash", {
    method: "PUT",
    body: JSON.stringify(filters),
    headers: {
      "Content-Type": "application/json; charset=UTF-8",
    },
  })
    .then((response) => {
      if (response.ok) {
        return response.json();
      } else if (response.status === 404) {
        throw new Error("No movies found");
      } else {
        throw new Error("Request failed");
      }
    })
    .then(({ movies, images, scores }) => {
      localStorage.setItem("movies", JSON.stringify(movies));
      localStorage.setItem("images", JSON.stringify(images));
      localStorage.setItem("scores", JSON.stringify(scores));
      spinnerElement.style.display = "none";
      sortMovies("title", "desc");
    })
    .catch((error) => {
      if (error.message === "No movies found") {
        document.querySelector(".dashboard__center-content").innerHTML = "<h1>No movies found</h1>";
      } else {
        console.error("Error:", error.message);
      }
    });
}

document.addEventListener("DOMContentLoaded", () => {
  document.getElementById("submit-button").addEventListener("click", handleFilterSubmit);

  document.getElementById("search-form").addEventListener("submit", function (event) {
    event.preventDefault();
    searchMovies();
  });

  let sort = "title";
  let order = "desc";
  var sortElement = document.getElementById("sort");
  var orderElement = document.getElementById("order");

  sortElement.addEventListener("change", function () {
    sort = this.value;
    sortMovies(sort, order);
  });

  orderElement.addEventListener("change", function () {
    order = this.value;
    sortMovies(sort, order);
  });
});

function sortMovies(sort, order) {
  let content = document.querySelector(".dashboard__center-content");
  content.innerHTML = '<div id="spinner" class="spinner" style="display: block;"></div>';
  console.log(sort, order);
  spinnerElement.style.display = "block";
  let movies = JSON.parse(localStorage.getItem("movies"));
  let images = JSON.parse(localStorage.getItem("images"));
  let scores = JSON.parse(localStorage.getItem("scores"));

  let moviesWithDetails = movies.map((movie, index) => {
    return {
      ...movie,
      image: images[index],
      score: scores[index],
    };
  });

  if (sort === "title" && order === "asc") {
    moviesWithDetails.sort((a, b) => a.title.localeCompare(b.title));
  } else if (sort === "title" && order === "desc") {
    moviesWithDetails.sort((a, b) => b.title.localeCompare(a.title));
  } else if (sort === "score" && order === "asc") {
    moviesWithDetails.sort((a, b) => a.score - b.score);
  } else if (sort === "score" && order === "desc") {
    moviesWithDetails.sort((a, b) => b.score - a.score);
  }
  spinnerElement.style.display = "none";
  visibilityMovies(moviesWithDetails);
}

function visibilityMovies(movies) {
  let content = document.querySelector(".dashboard__center-content");
  content.innerHTML = '<div id="spinner" class="spinner" style="display: none;"></div>';

  movies.forEach((movie) => {
    let movieDiv = document.createElement("div");
    movieDiv.className = "dashboard__center-content__movie";

    let img = document.createElement("img");
    if (movie.image == "none") {
      img.src = "../assets/cover.png";
    } else {
      img.src = movie.image;
    }
    img.alt = "movie";
    img.className = "dashboard__center-content__movie__image";
    movieDiv.appendChild(img);

    let title = document.createElement("h2");
    title.className = "dashboard__center-content__movie-title";
    title.textContent = movie["title"];
    movieDiv.appendChild(title);

    let date = document.createElement("p");
    date.className = "dashboard__center-content__movie-date";
    date.textContent = movie["release_year"];
    movieDiv.appendChild(date);

    content.appendChild(movieDiv);
  });
}

async function searchMovies() {
  let content = document.querySelector(".dashboard__center-content");
  content.innerHTML = '<div id="spinner" class="spinner" style="display: block;"></div>';
  const search = document.getElementById("search-input").value;
  console.log(search);

  spinnerElement.style.display = "block";
  await fetch("http://localhost/MoX-Project/src/api/dash", {
    method: "POST",
    body: JSON.stringify({ search }),
    headers: {
      "Content-Type": "application/json; charset=UTF-8",
    },
  })
    .then((response) => {
      if (response.ok) {
        return response.json();
      } else if (response.status === 404) {
        throw new Error("No movies found");
      } else {
        throw new Error("Request failed");
      }
    })
    .then(({ movies, images, scores }) => {
      localStorage.setItem("movies", JSON.stringify(movies));
      localStorage.setItem("images", JSON.stringify(images));
      localStorage.setItem("scores", JSON.stringify(scores));
      spinnerElement.style.display = "none";
      sortMovies("title", "desc");
    })
    .catch((error) => {
      if (error.message === "No movies found") {
        document.querySelector(".dashboard__center-content").innerHTML = "<h1>No movies found</h1>";
      } else {
        console.error("Error:", error.message);
      }
    });
}
