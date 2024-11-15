<?php

require_once '../session/session_manager.php';
require '../session/db.php';
require '../vendor/autoload.php'; 
require '../config/config.php';

start_secure_session();


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header('Location: ../authentication/login.php'); // Adjust the path as necessary
    exit();
}

// Staff Account
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $name = $data['name'];
    $email = $data['email'];
    $specialty = $data['specialty'];
    $phone = $data['phone'];

    // Generate a temporary password for the staff member
    $password = bin2hex(random_bytes(8)); // Random password for staff

    // Insert staff info into the database (adjust to your schema)
    $query = "INSERT INTO staff (name, email, specialty, phone_number, password)
              VALUES ('$name', '$email', '$specialty', '$phone', '$password')";
    mysqli_query($conn, $query);

    // Send email with the staff login details
    $subject = "Your Staff Account Information";
    $message = "Hello $name,\n\nYour staff account has been created.\n\nEmail: $email\nPassword: $password\n\nRegards,\nTeam";
    mail($email, $subject, $message);

    echo json_encode(["status" => "success"]);
}

?>