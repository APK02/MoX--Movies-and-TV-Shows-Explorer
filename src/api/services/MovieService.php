<?php
require '../repositories/MovieRepository.php';
require '../vendor/autoload.php';


class MovieService
{
    private $movieRepository;

    public function __construct()
    {
        $this->movieRepository = new MovieRepository();
    }

    public function getMovieById($id)
    {
        return $this->movieRepository->findById($id);
    }

    public function getMovieByName($name)
    {
        return $this->movieRepository->findByName($name);
    }

    public function getMoviesSearch($search)
    {
        return $this->movieRepository->findLikeTitle($search);
    }

    public function getMoviesFiltered($filters) 
    {   
        $genres = $filters['genre'];
        $yearsArray = [];
        $streamingService;
        $type;
        $duration;

        if ($filters['year'] == 0) {
            $year = null;
        } else {
            $string = $filters['year'];
            $years = explode(",", $string);
            $startYear = intval($years[0]);
            $endYear = intval($years[1]);

            for ($i = $startYear; $i <= $endYear; $i++) {
                $yearsArray[] = $i;
            }
        }

        if ($filters['streamingService'] == "both") {
            $streamingService = ["Netflix", "Disney"];
        } else {
            $streamingService = $filters['streamingService'];
        }

        if ($filters['type'] == "both") {
            $type = ["Movie", "TV Show"];
        } else {
            $type = $filters['type'];
        }

        if ($filters['duration'] == 0) {
            $duration = null;
        } else {
            $duration = $filters['duration'];
        }
        return $this->movieRepository->findByFilters($genres, $yearsArray, $streamingService, $type, $duration);
    }

    public function getMoviesByGenres($genres)
    {
        $favourites = $this->convertGenres($genres);
        return $this->movieRepository->findByGenres($favourites);
    }

    public function convertGenres($genres) {
        $favourites = []; 
        foreach ($genres as $fav) {
            switch ($fav) {
                case "fantasy":
                    $favourites[] = "Sci-Fi & Fantasy"; //netflix
                    $favourites[] = "Fantasy"; //disney
                    break;
                case "comedy":
                    $favourites[] = "TV Comedies";
                    $favourites[] = "Comedies";
                    $favourites[] = "Comedy"; //disney
                    break;
                case "drama":
                    $favourites[] = "TV Dramas";
                    $favourites[] = "Dramas"; 
                    $favourites[] = "Drama"; //disney
                    break;
                case "action":
                    $favourites[] = "Action & Adventure";
                    $favourites[] = "Action-Adventure"; //disney
                    break;
                case "horror":
                    $favourites[] = "Horror Movies";
                    $favourites[] = "TV Horror";
                    break;
                case "thriller":
                    $favourites[] = "Thrillers";
                    break;
                case "romance":
                    $favourites[] = "Romantic Movies";
                    $favourites[] = "Romantic TV Shows";
                    break;
                case "science_fiction":
                    $favourites[] = "Sci-Fi & Fantasy";
                    $favoutites[] = "Science Fiction"; //disney
                    break;
                case "mystery":
                    $favourites[] = "Mysteries";
                    $favourites[] = "TV Mysteries";
                    break;
                case "documentary":
                    $favourites[] = "Documentaries";
                    $favourites[] = "Docuseries";
                    $favourites[] = "Documentary"; //disney
                    break;
                case "animation":
                    $favourites[] = "Animation";
                    break;
                default:
                    break;
            }
        }
        return $favourites;
    }
}

?>