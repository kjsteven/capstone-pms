<?php
session_start();

require_once '../session/db.php';
require_once '../session/audit_trail.php';
require_once '../notification/notif_handler.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "You must be logged in to submit KYC verification.";
    header("Location: ../authentication/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input fields
    $firstName = isset($_POST['firstName']) ? trim($_POST['firstName']) : '';
    $middleName = isset($_POST['middleName']) ? trim($_POST['middleName']) : null;
    $lastName = isset($_POST['lastName']) ? trim($_POST['lastName']) : '';
    $dob = isset($_POST['dob']) ? trim($_POST['dob']) : '';
    $gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
    $nationality = isset($_POST['nationality']) ? trim($_POST['nationality']) : '';
    $otherNationality = ($nationality === 'other' && isset($_POST['otherNationality'])) ? trim($_POST['otherNationality']) : null;
    $civilStatus = isset($_POST['civilStatus']) ? trim($_POST['civilStatus']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $mobileNumber = isset($_POST['mobileNumber']) ? trim($_POST['mobileNumber']) : '';
    $streetAddress = isset($_POST['streetAddress']) ? trim($_POST['streetAddress']) : '';
    $barangayCode = isset($_POST['barangay']) ? trim($_POST['barangay']) : '';
    $cityCode = isset($_POST['city']) ? trim($_POST['city']) : '';
    $provinceCode = isset($_POST['province']) ? trim($_POST['province']) : '';
    $barangayName = isset($_POST['barangay_name']) ? trim($_POST['barangay_name']) : '';
    $cityName = isset($_POST['city_name']) ? trim($_POST['city_name']) : '';
    $provinceName = isset($_POST['province_name']) ? trim($_POST['province_name']) : '';
    $zipCode = isset($_POST['zipCode']) ? trim($_POST['zipCode']) : '';
    $idType = isset($_POST['idType']) ? trim($_POST['idType']) : '';
    $otherIdType = ($idType === 'other' && isset($_POST['otherIdType'])) ? trim($_POST['otherIdType']) : null;
    $idNumber = isset($_POST['idNumber']) ? trim($_POST['idNumber']) : '';
    $fundsSource = isset($_POST['fundsSource']) ? trim($_POST['fundsSource']) : '';
    $otherFundsSource = ($fundsSource === 'other' && isset($_POST['otherFundsSource'])) ? trim($_POST['otherFundsSource']) : null;
    $occupation = isset($_POST['occupation']) ? trim($_POST['occupation']) : '';
    $employer = isset($_POST['employer']) ? trim($_POST['employer']) : null;
    
    // Validation
    $errors = [];
    
    // Required fields validation
    $required_fields = [
        'firstName' => 'First name',
        'lastName' => 'Last name',
        'dob' => 'Date of birth',
        'gender' => 'Gender',
        'nationality' => 'Nationality',
        'civilStatus' => 'Civil status',
        'email' => 'Email',
        'mobileNumber' => 'Mobile number',
        'streetAddress' => 'Street address',
        'zipCode' => 'ZIP code',
        'idType' => 'ID type',
        'idNumber' => 'ID number',
        'fundsSource' => 'Source of funds',
        'occupation' => 'Occupation'
    ];

    // Special validation for address fields - check both code and name
    if (empty($barangayCode) && empty($barangayName)) {
        $errors[] = "Barangay is required.";
    }

    if (empty($cityCode) && empty($cityName)) {
        $errors[] = "City is required.";
    }

    if (empty($provinceCode) && empty($provinceName)) {
        $errors[] = "Province is required.";
    }

    foreach ($required_fields as $field => $label) {
        if (empty(${$field})) {
            $errors[] = "$label is required.";
        }
    }
    
    // Email validation
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    
    // Mobile number validation (Philippine format)
    if (!empty($mobileNumber) && !preg_match('/^09\d{9}$/', $mobileNumber)) {
        $errors[] = "Invalid mobile number format. Please use format: 09XXXXXXXXX";
    }
    
    // ZIP code validation
    if (!empty($zipCode) && !preg_match('/^\d{4}$/', $zipCode)) {
        $errors[] = "Invalid ZIP code. Please enter a 4-digit number.";
    }
    
    // Additional validations for conditional fields
    if ($nationality === 'other' && empty($otherNationality)) {
        $errors[] = "Please specify your nationality.";
    }
    
    if ($idType === 'other' && empty($otherIdType)) {
        $errors[] = "Please specify your ID type.";
    }
    
    if ($fundsSource === 'other' && empty($otherFundsSource)) {
        $errors[] = "Please specify your source of funds.";
    }
    
    // File upload validation and handling
    $targetDir = "../uploads/kyc/";
    
    // Create directory if it doesn't exist
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    // ID Front
    $idFrontPath = "";
    if (isset($_FILES["idUpload"]) && $_FILES["idUpload"]["error"] == 0) {
        $allowedTypes = ["image/jpeg", "image/jpg", "image/png", "application/pdf"];
        $maxFileSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES["idUpload"]["type"], $allowedTypes)) {
            $errors[] = "Invalid file type for ID front. Allowed types: JPG, PNG, PDF.";
        } elseif ($_FILES["idUpload"]["size"] > $maxFileSize) {
            $errors[] = "ID front file size exceeds the 5MB limit.";
        } else {
            $fileName = "user_" . $user_id . "_front_" . time() . "_" . basename($_FILES["idUpload"]["name"]);
            $idFrontPath = $targetDir . $fileName;
            
            // Prevent directory traversal attempt
            if (strpos($fileName, '..') !== false) {
                $errors[] = "Invalid file name.";
            }
        }
    } else {
        $errors[] = "Front ID image is required.";
    }
    
    // ID Back
    $idBackPath = "";
    if (isset($_FILES["idUploadBack"]) && $_FILES["idUploadBack"]["error"] == 0) {
        $allowedTypes = ["image/jpeg", "image/jpg", "image/png", "application/pdf"];
        $maxFileSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES["idUploadBack"]["type"], $allowedTypes)) {
            $errors[] = "Invalid file type for ID back. Allowed types: JPG, PNG, PDF.";
        } elseif ($_FILES["idUploadBack"]["size"] > $maxFileSize) {
            $errors[] = "ID back file size exceeds the 5MB limit.";
        } else {
            $fileName = "user_" . $user_id . "_back_" . time() . "_" . basename($_FILES["idUploadBack"]["name"]);
            $idBackPath = $targetDir . $fileName;
            
            // Prevent directory traversal attempt
            if (strpos($fileName, '..') !== false) {
                $errors[] = "Invalid file name.";
            }
        }
    } else {
        $errors[] = "Back ID image is required.";
    }
    
    // Check if user already submitted KYC
    $checkStmt = $conn->prepare("SELECT kyc_id, verification_status FROM kyc_verification WHERE user_id = ?");
    $checkStmt->bind_param("i", $user_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $kycData = $checkResult->fetch_assoc();
        $kycStatus = isset($kycData['verification_status']) ? $kycData['verification_status'] : 'pending';  // Changed from 'submitted' to 'pending'
        
        if ($kycStatus != 'rejected') {
            $statusMessage = ($kycStatus == 'approved') ? 
                'Your KYC verification has already been approved.' : 
                'You already have a pending KYC verification. Please wait for review.';
            
            $errors[] = $statusMessage;
        }
    }
    $checkStmt->close();
    
    // If there are no errors, proceed with the database insertion
    if (empty($errors)) {
        // Move uploaded files to target directory
        if (!move_uploaded_file($_FILES["idUpload"]["tmp_name"], $idFrontPath)) {
            $errors[] = "Failed to upload front ID image.";
        }
        
        if (!move_uploaded_file($_FILES["idUploadBack"]["tmp_name"], $idBackPath)) {
            $errors[] = "Failed to upload back ID image.";
        }
        
        if (empty($errors)) {
            // Store relative paths in the database
            $idFrontPath = str_replace("../", "", $idFrontPath);
            $idBackPath = str_replace("../", "", $idBackPath);
            
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Prepare the SQL statement
                $stmt = $conn->prepare("
                    INSERT INTO kyc_verification (
                        user_id, first_name, middle_name, last_name, date_of_birth, gender, 
                        nationality, other_nationality, civil_status, email, mobile_number, 
                        street_address, barangay, city, province, zip_code, 
                        id_type, other_id_type, id_number, id_front_path, id_back_path, 
                        funds_source, other_funds_source, occupation, employer
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    )
                ");
                
                $stmt->bind_param(
                    "issssssssssssssssssssssss",
                    $user_id, $firstName, $middleName, $lastName, $dob, $gender,
                    $nationality, $otherNationality, $civilStatus, $email, $mobileNumber,
                    $streetAddress, $barangayName, $cityName, $provinceName, $zipCode,
                    $idType, $otherIdType, $idNumber, $idFrontPath, $idBackPath,
                    $fundsSource, $otherFundsSource, $occupation, $employer
                );
                
                if ($stmt->execute()) {
                    // Try to update user status if the column exists
                    try {
                        $updateUserStmt = $conn->prepare("UPDATE users SET kyc_status = 'pending' WHERE user_id = ?");  // Changed from 'submitted' to 'pending'
                        $updateUserStmt->bind_param("i", $user_id);
                        $updateUserStmt->execute();
                        $updateUserStmt->close();
                    } catch (Exception $e) {
                        // Log the error but don't fail the entire process
                        error_log("Unable to update kyc_status in users table: " . $e->getMessage());
                        // This is non-critical, so we continue processing
                    }
                    
                    // Log activity
                    logActivity($user_id, 'KYC Submission', 'User submitted KYC verification');
                    
                    // Create notification for user
                    $userNotification = "Your KYC verification request has been submitted successfully. We will review your information shortly.";
                    createNotification($user_id, $userNotification, 'kyc');

                    // Create notification for admin (assuming admin user_id is 1)
                    $adminNotification = "New KYC verification request from " . htmlspecialchars($_POST['firstName'] . ' ' . $_POST['lastName']);
                    createNotification(1, $adminNotification, 'kyc_admin');

                    // Commit transaction
                    $conn->commit();
                    
                    // Success message
                    $_SESSION['success_message'] = "KYC verification submitted successfully! We will review your information and update you soon.";
                    header("Location: kyc.php");
                    exit;
                } else {
                    throw new Exception("Error executing statement: " . $stmt->error);
                }
            } catch (Exception $e) {
                // Rollback transaction
                $conn->rollback();
                
                // Delete uploaded files if they exist
                if (file_exists("../" . $idFrontPath)) {
                    unlink("../" . $idFrontPath);
                }
                if (file_exists("../" . $idBackPath)) {
                    unlink("../" . $idBackPath);
                }
                
                $errors[] = "Database error: " . $e->getMessage();
            } finally {
                $stmt->close();
            }
        }
    }
    
    // If there were errors, redirect back to the KYC form with error messages
    if (!empty($errors)) {
        $_SESSION['error_messages'] = $errors;
        header("Location: kyc.php");
        exit;
    }
} else {
    // If not a POST request, redirect to the KYC form
    header("Location: kyc.php");
    exit;
}
?>
