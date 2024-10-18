<?php

require('../vendor/autoload.php');

class MovieRepository
{
    public $client;
    public $database;

    public function __construct()
    {
        $this->client = new MongoDB\Client;
        $this->database = $this->client->MoXDB;
    }

    public function getMovieContainer()
    {
        return $this->database->movies;
    }

    public function findById($id): ?object
    {
        $query = ["_id" => new MongoDB\BSON\ObjectId($id)];
        $collection = $this->getMovieContainer();
        $result = $collection->findOne($query);
        return $result;
    }

    public function findByName($name): ?object
    {
        $query = ["name" => $name];
        $collection = $this->getMovieContainer();
        $result = $collection->findOne($query);
        return $result;
    }

    public function findLikeTitle($title): array 
    {
        $collection = $this->getMovieContainer();
        $results = [];
        $query = ["title" => ['$regex' => new MongoDB\BSON\Regex('^' . preg_quote($title), 'i')]];
        $total = $collection->count($query);
        $skip = rand(0, max(0, $total - 10));
        $options = ['limit' => 10, 'skip' => $skip];
        $cursor = $collection->find($query, $options);
        foreach ($cursor as $document) {
            $results[] = $document->bsonSerialize();
        }
        return $results;
    }

    public function findByFilters($genres, $year, $streamingService, $type, $duration): array
    {
        $collection = $this->getMovieContainer();
        $results = [];
        $query = [];
        if ($genres) {
            $query["genres"] = ['$in' => $genres];
        }
        if ($year) {
            $query["release_year"] = ['$in' => $year];
        }
        if ($streamingService && is_array($streamingService)) {
            $query["streaming_platform"] = ['$in' => $streamingService];
        } else if ($streamingService) {
            $query["streaming_platform"] = $streamingService;
        }
        if ($type && is_array($type)) {
            $query["type"] = ['$in' => $type];
        } else if ($type) {
            $query["type"] = $type;
        }
        if ($duration) {
            if ($duration == "150+") {
                $query["duration"] = ['$gte' => 150];
            } else if ($duration == "91,149") {
                $query["duration"] = ['$gte' => 91, '$lte' => 149];
            } else if ($duration == "90-") {
                $query["duration"] = ['$lte' => 90];
            }
        }
        $total = $collection->count($query);
        $skip = rand(0, max(0, $total - 10));
        $options = ['limit' => 10, 'skip' => $skip];
        $cursor = $collection->find($query, $options);
        foreach ($cursor as $document) {
            $results[] = $document->bsonSerialize();
        }
        return $results;
    }

    public function findByGenres($genres): array
    {
        $collection = $this->getMovieContainer();
        $results = [];
        //Netflix
        $query = ["genres" => ['$in' => $genres], "streaming_platform" => "Netflix"];
        $total = $collection->count($query);
        $skip = rand(0, max(0, $total - 5));
        $options = ['limit' => 5, 'skip' => $skip];
        $cursor = $collection->find($query, $options);
        foreach ($cursor as $document) {
            $results[] = $document->bsonSerialize();
        }

        //Disney
        $query = ["genres" => ['$in' => $genres], "streaming_platform" => "Disney"];
        $total = $collection->count($query);
        $skip = rand(0, max(0, $total - 5));
        $options = ['limit' => 5, 'skip' => $skip];
        $cursor = $collection->find($query, $options);
        foreach ($cursor as $document) {
            $results[] = $document->bsonSerialize();
        }
        return $results;
    }
}
?>