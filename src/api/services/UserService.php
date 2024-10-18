<?php
require '../repositories/UserRepository.php';

class UserService
{
    public $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function saveUser($email, $username, $password): array
    {
        try {
            $this->emailExists($email);
            $this->usernameExists($username);
            $userId = $this->userRepository->saveUser($email, $username, $password);
            $response = "Account created successfully";
        } catch (Exception $ex) {
            $response = $ex->getMessage();
            $userId = null;
        }
        return array($response, $userId);
    }

    public function setUserFavourites($array, $userId): bool
    {
        return $this->userRepository->saveUserFavourites($array, $userId);
    }

    /**
     * @throws Exception
     */
    private function emailExists($email): void
    {
        if ($this->userRepository->findByEmail($email) != null) {
            throw new Exception("Email already in use");
        }
    }

    /**
     * @throws Exception
     */
    private function usernameExists($username): void
    {
        if ($this->userRepository->findByUsername($username) != null) {
            throw new Exception("Username already in use");
        }
    }

    public function authUser($username, $password): string {
        $user = $this->userRepository->findByUsername($username);
        if($user == null)
            return "Username not found";
        else
        {
            if(password_verify($password, $user['password']))
            {
                return "Ok";
            } else
            {
                return "Wrong password";
            }
        }
    }
    public function getFavourites($id): array {
        $user = $this->userRepository->findById($id);
        if($user == null)
            return [-1];
        else
        {
            $response = array();
            $result = $user['favourite_genres'];
            foreach($result as $item)
            {
                $response[] = $item;
            }
            return $response;
        }
    }

    public function setNewPassword($id, $password): bool {
        $user = $this->userRepository->findById($id);
        if($user == null)
            return false;
        return $this->userRepository->changePassword($password, $id);
    }

    public function deleteAccount($id): bool{
        return $this->userRepository->deleteUser($id);
    }
}