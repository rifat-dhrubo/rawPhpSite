<?php

class Users extends Controller
{

    public function __construct()
    {
        $this->userModel = $this->model('User');
    }

    //Check for posts
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize post data
            $sanitize_data = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            // Init data
            $data = [
                "name" => trim($sanitize_data['name']),
                "email" => trim($sanitize_data['email']),
                "password" => trim($sanitize_data['password']),
                "confirm_password" => trim($sanitize_data['confirm_password']),
                'name_err' => '',
                "email_err" => "",
                "password_err" => "",
                "confirm_password_err" => "",
            ];

            //validate email
            if (empty($data["email"])) {
                $data["email_err"] = 'Please enter email';
            } else {
                // Check Email
                if ($this->userModel->findUserByEmail($data["email"])) {
                    $data["email_err"] = "Email is already taken";
                }
            }

            //validate name
            if (empty($data["name"])) {
                $data["name_err"] = 'Please enter name';
            }

            //validate password
            if (empty($data["password"])) {
                $data["password_err"] = 'Please enter password';
            } else if (strlen($data["password"]) < 6) {
                $data["password_err"] = 'Password must be at least 6 characters';
            }

            //validate confirm password
            //validate password
            if (empty($data["confirm_password"])) {
                $data["confirm_password_err"] = 'Please confirm password';
            } else {
                if ($data["password"] !== $data["confirm_password"]) {
                    $data["confirm_password_err"] = 'Password does not match';
                }
            }

            // Make sure errors are empty
            if (
                empty($data["name_err"]) &&
                empty($data["email_err"]) &&
                empty($data["password_err"]) &&
                empty($data["confirm_password_err"])
            ) {
                // Validated

                // Hash Password
                $data["password"] = password_hash($data["password"], PASSWORD_DEFAULT);

                // Register User
                if ($this->userModel->register($data)) {
                    flash('register_success', 'You are registered and can login');
                    redirect('users/login');
                } else {
                    die("something went wrong");
                }
            } else {
                // Load views with error
                $this->view('users/register', $data);
            }
        } else {
            // Init data
            $data = [
                "name" => "",
                "email" => "",
                "password" => "",
                "confirm_password" => "",
                'name_err' => '',
                "email_err" => "",
                "password_err" => "",
                "confirm_password_err" => "",
            ];

            // Load View
            $this->view("users/register", $data);
        }
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process form
            // Sanitize post data
            $sanitize_data = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            // Init data
            $data = [
                "email" => trim($sanitize_data['email']),
                "password" => trim($sanitize_data['password']),
                "email_err" => "",
                "password_err" => "",
            ];

            // Validate Email
            if (empty($data["email"])) {
                $data["email_err"] = 'Please enter email';
            }

            // Validate Password
            if (empty($data["password"])) {
                $data["password_err"] = 'Please enter password';
            }

            // Check for user/email
            if ($this->userModel->findUserByEmail($data["email"])) {
                // User found
            } else {
                // User not found
                $data["email_err"] = 'No users found';
            }

            // Make sure errors are empty
            if (
                empty($data["email_err"]) &&
                empty($data["password_err"])
            ) {
                // Validated
                // Check and set logged in users
                $loggedInUser = $this->userModel->login($data["email"], $data["password"]);

                if ($loggedInUser) {
                    // Create session
                    $this->createUserSession($loggedInUser);
                } else {
                    $data["password_err"] = "Password Incorrect";

                    $this->view('users/login', $data);
                }
            } else {
                // Load views with error
                $this->view('users/login', $data);
            }
        } else {
            // Init data
            $data = [
                "email" => "",
                "password" => "",
                "email_err" => "",
                "password_err" => "",
            ];

            // Load View
            $this->view("users/login", $data);
        }
    }

    public function createUserSession($user)
    {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_name'] = $user->name;

        redirect('posts');
    }

    public function logout()
    {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_name']);

        session_destroy();

        redirect('users/login');
    }
}
