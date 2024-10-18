<?php

require_once __DIR__ . '/../vendor/autoload.php';

class ResetPasswordRepository
{
    public $client;
    public $database;

    public function __construct()
    {
        $this->client = new MongoDB\Client;
        $this->database = $this->client->MoXDB;
    }

    public function getResetPasswordContainer()
    {
        return $this->database->reset_password;
    }

    public function saveToken($userId, $token, $expiry)
    {
        $collection = $this->getResetPasswordContainer();
        $document = [
            "user_id" => (string)$userId,
            "token" => $token,
            "expiry" => $expiry
        ];
        return $collection->insertOne($document);
    }

    public function deleteToken($userId): bool
    {
        $collection = $this->getResetPasswordContainer();
        $filterOption = ["user_id" => $userId];
        $result = $collection->deleteMany($filterOption);
        if ($result->getDeletedCount() != 0) {
            return true;
        }
        return false;
    }

    public function findByUserId($userId): ? object{
        $collection = $this->getResetPasswordContainer();
        $filterOption = ['user_id' => $userId];
        return $collection->findOne($filterOption);
    }
}