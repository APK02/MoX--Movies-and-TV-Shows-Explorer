<?php

require('../vendor/autoload.php');

class UserRepository
{
    public $client;
    public $database;

    public function __construct()
    {
        $this->client = new MongoDB\Client;
        $this->database = $this->client->MoXDB;
    }

    public function createUserContainer(): void
    {
        $this->database->createCollection('users');
    }

    public function getUserContainer()
    {
        return $this->database->users;
    }

    public function saveUser($email, $username, $password): string
    {
        $collection = $this->getUserContainer();
        $document = [
            "email" => $email,
            "username" => $username,
            "password" => password_hash($password, PASSWORD_BCRYPT),
            "favourite_genres" => array()
        ];
        $result = $collection->insertOne($document);
        return $result->getInsertedId();
    }

    public function saveUserFavourites($array, $userId): bool
    {
        $collection = $this->getUserContainer();

        $filterOption = ["_id" => new \MongoDB\BSON\ObjectID($userId)];
        $updateOption = ["favourite_genres" => $array];

        $result = $collection->updateOne(
            $filterOption,
            ['$set' => $updateOption]
        );

        if ($result->getModifiedCount() != 1) {
            return false;
        }
        return true;
    }

    public function findById($id): ?object
    {
        $query = ["_id" => new MongoDB\BSON\ObjectId($id)];
        $collection = $this->getUserContainer();
        return $collection->findOne($query);
    }

    public function findByEmail($email): ?object
    {
        $query = ["email" => $email];
        $collection = $this->getUserContainer();
        return $collection->findOne($query);
    }

    public function findByUsername($username): ?object
    {
        $query = ["username" => $username];
        $collection = $this->getUserContainer();
        return $collection->findOne($query);
    }

    public function changeUsername($username, $userId): bool
    {
        $collection = $this->getUserContainer();

        $filterOption = ["_id" => new \MongoDB\BSON\ObjectID($userId)];
        $updateOption = ["username" => $username];

        $result = $collection->updateOne(
            $filterOption,
            ['$set' => $updateOption]
        );
        if ($result->getModifiedCount() != 1) {
            return false;
        }
        return true;
    }
    public function changeEmail($email, $userId): bool
    {
        $collection = $this->getUserContainer();

        $filterOption = ["_id" => new \MongoDB\BSON\ObjectID($userId)];
        $updateOption = ["email" => $email];

        $result = $collection->updateOne(
            $filterOption,
            ['$set' => $updateOption]
        );
        if ($result->getModifiedCount() != 1) {
            return false;
        }
        return true;
    }
    public function changePassword($password, $userId): bool{
        $collection = $this->getUserContainer();
        $filterOption = ["_id" => new \MongoDB\BSON\ObjectID($userId)];
        $updateOption = ["password" => password_hash($password, PASSWORD_BCRYPT)];

        $result = $collection->updateOne(
            $filterOption,
            ['$set' => $updateOption]
        );
        if ($result->getModifiedCount() != 1) {
            return false;
        }
        return true;
    }

    public function deleteUser($userId): bool{
        $collection = $this->getUserContainer();
        $filterOption = ["_id" => new \MongoDB\BSON\ObjectID($userId)];
        $result = $collection->deleteOne($filterOption);
        if ($result->getDeletedCount() != 1) {
            return false;
        }
        return true;
    }
}