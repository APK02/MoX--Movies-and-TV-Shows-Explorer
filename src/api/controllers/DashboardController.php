<?php
declare(strict_types=1);

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use GuzzleHttp\Client;

require_once('../vendor/autoload.php');
include "../services/UserService.php";
include "../services/MovieService.php";

class DashboardController
{
    private $userService;
    private $movieService;
    private $user;
    private $jwtSecretKey = '';
    private $movies = [];
    private $images = [];
    private $movieScores = [];
    private $favouriteGenres = [];
    
    public function __construct() 
    {
        $this->userService = new UserService();
        $this->movieService = new MovieService();
        $this->user = $this->getUserProfile();
        $this->favouriteGenres = $this->user['favourite_genres'];
    }

    public function handleRequest()
    {
        try {
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $this->getMoviesByUserFavourites();
                    $this->images = $this->getImages();
                    $responseBody = [
                        'movies' => $this->movies,
                        'images' => $this->images,
                        'scores' => $this->movieScores
                    ];
                    $this->sendArrayResponse(200, $responseBody);
                    break;
                case 'PUT':
                    $this->getMoviesByFilters();
                    $this->images = $this->getImages();
                    $responseBody = [
                        'movies' => $this->movies,
                        'images' => $this->images,
                        'scores' => $this->movieScores
                    ];
                    if (empty($this->movies)) {
                        $this->sendArrayResponse(404, ["message" => "No movies found"]);
                    } else {
                        $this->sendArrayResponse(200, $responseBody);
                    }
                    break;
                case 'POST':
                    $this->getMoviesBySearch();
                    $this->images = $this->getImages();
                    $responseBody = [
                        'movies' => $this->movies,
                        'images' => $this->images,
                        'scores' => $this->movieScores
                    ];
                    if (empty($this->movies)) {
                        $this->sendArrayResponse(404, ["message" => "No movies found"]);
                    } else {
                        $this->sendArrayResponse(200, $responseBody);
                    }
                    break;
                default:
                    $this->sendArrayResponse(405, ["message" => "Method Not Allowed"]);
                    break;
            }
        } catch (Exception $e) {
            $this->sendArrayResponse(500, ["message" => "Internal Server Error", "error" => $e->getMessage()]);
        }
    }

    private function getMoviesBySearch() {
        $content = trim(file_get_contents("php://input"));
        $data = json_decode($content, true);
        $search = $data["search"];
        $this->movies = $this->movieService->getMoviesSearch($search);
    }

    private function getMoviesByFilters() {
        $content = trim(file_get_contents("php://input"));
        $data = json_decode($content, true);

        if (!$this->isValidData($data)) {
            $this->sendArrayResponse(400, ["message" => "Incomplete data"]);
            return;
        }

        if ($data["genre"] == "user_favorite") {
            $data["genre"] = $this->movieService->convertGenres($this->favouriteGenres);

        } else {
            if (!is_array($data["genre"])) {
                $data["genre"] = array($data["genre"]);
            }
            $data["genre"] = $this->movieService->convertGenres($data["genre"]);
        }
        $this->movies = $this->movieService->getMoviesFiltered($data);
    }

    private function isValidData($data): bool
    {
        return isset($data["streamingService"], $data["genre"], $data["year"], $data["type"], $data["duration"]);
    }

    private function getMoviesByUserFavourites()
    {
        $favourites = $this->user['favourite_genres'];
        $this->movies = $this->movieService->getMoviesByGenres($favourites);
        //$this->sendArrayResponse(200, $this->movies);
    }

    private function getTvShowImage($show)
    {
        $api_key = '';
        $client = new Client([
            'base_uri' => 'https://api.themoviedb.org/3/',
        ]);

        $response = $client->request('GET', 'search/tv', [
            'query' => [
                'api_key' => $api_key,
                'query' => $show->title,
            ]
        ]);

        $data = json_decode((string)$response->getBody(), true);
        if (!empty($data['results'])) {
            //for movie scores
            $score = $data['results'][0]['vote_average'];
            if ($score != null) {
                $this->movieScores[] = $score;
            } else {
                $this->movieScores[] = 0;
            }
            $path = $data['results'][0]['poster_path'];
            if ($path != null) {
                return $path;
            } else {
                return 'none';
            }
        } else {
            $this->movieScores[] = 0;
            return 'none';
        }
    }

    private function getMovieImage($movie)
    {
        $api_key = '';
        $client = new Client([
            'base_uri' => 'https://api.themoviedb.org/3/',
        ]);

        $response = $client->request('GET', 'search/movie', [
            'query' => [
                'api_key' => $api_key,
                'query' => $movie->title,
            ]
        ]);

        $data = json_decode((string)$response->getBody(), true);
        if (!empty($data['results'])) {
            //for movie scores
            $score = $data['results'][0]['vote_average'];
            if ($score != null) {
                $this->movieScores[] = $score;
            } else {
                $this->movieScores[] = 0;
            }
            $path = $data['results'][0]['poster_path'];
            if ($path != null) {
                return $path;
            } else {
                return 'none';
            }
        } else {
            $this->movieScores[] = 0;
            return 'none';
        }
    }

    private function getImages()
    {
        $api_key = '';
        $client = new Client([
            'base_uri' => 'https://api.themoviedb.org/3/',
        ]);
        
        $imagesPaths = [];
        foreach($this->movies as $movie) {
            if ($movie->type == 'TV Show') {
                $imagesPaths[] = $this->getTvShowImage($movie);
                continue;
            } else if ($movie->type == 'Movie') {
                $imagesPaths[] = $this->getMovieImage($movie);
                continue;
            }
        }

        $imagesUrls = [];
        foreach($imagesPaths as $path) {
            if ($path == 'none') {
                $imagesUrls[] = 'none';
                continue;
            }
            $imagesUrls[] = 'https://image.tmdb.org/t/p/w185/'.$path;
        }
        return $imagesUrls;
    }

    private function getUserProfile()
    {
        $token = $_COOKIE['token'] ?? null;
        if (!$token) {
            $this->sendArrayResponse(401, ["message" => "Unauthorized"]);
            return;
        }

        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecretKey, 'HS512'));
            $decodedArray = (array) $decoded;
            $userId = $decodedArray['userId'];
            $data = $this->userService->userRepository->findById($userId);
            $dataArray = $data->getArrayCopy(); // Convert BSONDocument to array
            return $dataArray;
        } catch (ExpiredException $e) {
            $this->sendArrayResponse(401, ["message" => "Token expired"]);
        } catch (SignatureInvalidException $e) {
            $this->sendArrayResponse(401, ["message" => "Invalid token"]);
        } catch (Exception $e) {
            $this->sendArrayResponse(500, ["message" => "Internal server error", "error" => $e->getMessage()]);
        }
    
    }

    private function sendArrayResponse(int $statusCode, array $data)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
    private function sendBoolResponse(int $statusCode, bool $data)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }


}

$controler = new DashboardController();
$controler->handleRequest();
?>