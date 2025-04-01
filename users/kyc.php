<?php


require_once '../session/session_manager.php';
start_secure_session();


if (!isset($_SESSION['user_id'])) {
    header("Location: ../authentication/login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KYC Verification</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .required:after {
            content: " *";
            color: red;
        }
        select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.75rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
        }
        .input-valid {
            border-color: #10b981 !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2310b981'%3e%3cpath d='M20.285 2l-11.285 11.567-5.286-5.011-3.714 3.716 9 8.728 15-15.285z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1em;
            padding-right: 2.5rem;
        }
        .input-invalid {
            border-color: #ef4444 !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23ef4444'%3e%3cpath d='M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-1.5 5h3v10h-3v-10zm1.5 15.25c-1.105 0-2-.895-2-2s.895-2 2-2 2 .895 2 2-.895 2-2 2z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1em;
            padding-right: 2.5rem;
        }
        .validation-message {
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }
        .error-message {
            color: #ef4444;
        }
        .success-message {
            color: #10b981;
        }
    </style>
</head>
<body class="bg-gray-50">

    <?php include ('navbar.php'); ?>

    <?php include ('sidebar.php'); ?>

    <!-- Main content - adjusted with padding to account for sidebar and navbar -->
    <div class="sm:ml-20 pt-20">
        <div class="px-4 py-8">
            <div class="max-w-4xl mx-auto">
                <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white px-8 py-6">
                        <h3 class="text-2xl font-bold">PropertyWise KYC Verification</h3>
                    </div>
                    
                    <div class="p-8 space-y-8">
                        <!-- Display error messages if any -->
                        <?php
                    
                        if (isset($_SESSION['error_messages']) && !empty($_SESSION['error_messages'])) {
                            echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-sm">';
                            echo '<ul class="list-disc pl-5">';
                            foreach ($_SESSION['error_messages'] as $error) {
                                echo "<li>$error</li>";
                            }
                            echo '</ul>';
                            echo '</div>';
                            unset($_SESSION['error_messages']);
                        }
                        
                        if (isset($_SESSION['success_message'])) {
                            echo '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg shadow-sm">';
                            echo $_SESSION['success_message'];
                            echo '</div>';
                            unset($_SESSION['success_message']);
                        }
                        ?>
                        
                        <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 rounded-lg shadow-sm">
                            <p class="m-0 font-medium">Please complete all required fields marked with an asterisk (*) and upload a clear copy of your ID document.</p>
                        </div>
                        
                        <form id="kycForm" enctype="multipart/form-data" method="post" action="process_kyc.php" class="space-y-8">
                            <!-- Personal Information Section -->
                            <div class="space-y-6">
                                <h4 class="text-xl font-bold text-blue-700 border-l-4 border-blue-600 pl-4 mb-4 pb-2">Personal Information</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label for="firstName" class="block text-sm font-semibold text-gray-800 mb-2 required">First Name</label>
                                        <input type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3 transition-all duration-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 hover:shadow-md placeholder-gray-400" id="firstName" name="firstName" required>
                                    </div>
                                    <div>
                                        <label for="middleName" class="block text-sm font-semibold text-gray-800 mb-2">Middle Name</label>
                                        <input type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3 transition-all duration-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 hover:shadow-md placeholder-gray-400" id="middleName" name="middleName">
                                    </div>
                                    <div>
                                        <label for="lastName" class="block text-sm font-semibold text-gray-800 mb-2 required">Last Name</label>
                                        <input type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3 transition-all duration-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 hover:shadow-md placeholder-gray-400" id="lastName" name="lastName" required>
                                    </div>
                                    
                                    <div>
                                        <label for="dob" class="block text-sm font-semibold text-gray-800 mb-2 required">Date of Birth</label>
                                        <input type="date" class="w-full border border-gray-300 rounded-lg px-4 py-3 transition-all duration-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 hover:shadow-md placeholder-gray-400" id="dob" name="dob" required>
                                    </div>
                                    <div>
                                        <label for="gender" class="block text-sm font-semibold text-gray-800 mb-2 required">Gender</label>
                                        <select class="w-full border border-gray-300 rounded-lg px-4 py-3 transition-all duration-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 hover:shadow-md appearance-none pr-10" id="gender" name="gender" required>
                                            <option value="">Select Gender</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="nationality" class="block text-sm font-semibold text-gray-800 mb-2 required">Nationality</label>
                                        <select class="w-full border border-gray-300 rounded-lg px-4 py-3 transition-all duration-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 hover:shadow-md appearance-none pr-10" id="nationality" name="nationality" required>
                                            <option value="Filipino" selected>Filipino</option>
                                            <option value="other">Others</option>
                                        </select>
                                    </div>
                                    <div id="otherNationalityContainer" class="hidden">
                                        <label for="otherNationality" class="block text-sm font-semibold text-gray-800 mb-2 required">Specify Nationality</label>
                                        <input type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3 transition-all duration-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 hover:shadow-md placeholder-gray-400" id="otherNationality" name="otherNationality">
                                    </div>
                                    
                                    <div>
                                        <label for="civilStatus" class="block text-sm font-semibold text-gray-800 mb-2 required">Civil Status</label>
                                        <select class="w-full border border-gray-300 rounded-lg px-4 py-3 transition-all duration-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 hover:shadow-md appearance-none pr-10" id="civilStatus" name="civilStatus" required>
                                            <option value="">Select Civil Status</option>
                                            <option value="single">Single</option>
                                            <option value="married">Married</option>
                                            <option value="widowed">Widowed</option>
                                            <option value="separated">Separated</option>
                                            <option value="divorced">Divorced</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="email" class="block text-sm font-semibold text-gray-800 mb-2 required">Email Address</label>
                                        <input type="email" class="w-full border border-gray-300 rounded-lg px-4 py-3 transition-all duration-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 hover:shadow-md placeholder-gray-400" id="email" name="email" required>
                                        <div id="emailValidationMessage" class="validation-message hidden"></div>
                                    </div>
                                    
                                    <div class="md:col-span-2">
                                        <label for="mobileNumber" class="block text-sm font-semibold text-gray-800 mb-2 required">Mobile Number</label>
                                        <div class="flex">
                                            <span class="inline-flex items-center px-4 rounded-l-lg border border-gray-300 bg-gray-50 text-gray-600 font-medium shadow-sm">+63</span>
                                            <input type="text" class="w-full border border-gray-300 rounded-r-lg px-4 py-3 transition-all duration-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 hover:shadow-md placeholder-gray-400" id="mobileNumber" name="mobileNumber" placeholder="09XX XXX XXXX" maxlength="11" required>
                                        </div>
                                        <div id="phoneValidationMessage" class="validation-message hidden"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Address Section -->
                            <div class="space-y-6">
                                <h4 class="text-xl font-bold text-blue-700 border-l-4 border-blue-600 pl-4 mb-4 pb-2">Current Address</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="streetAddress" class="block text-sm font-semibold text-gray-800 mb-2 required">Street Address</label>
                                        <input type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3 transition-all duration-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 hover:shadow-md placeholder-gray-400" id="streetAddress" name="streetAddress" required>
                                    </div>
                                    <div>
                                        <label for="barangay" class="block text-sm font-semibold text-gray-800 mb-2 required">Barangay</label>
                                        <select class="w-full border border-gray-300 rounded-lg px-4 py-3 transition-all duration-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 hover:shadow-md appearance-none pr-10" id="barangay" name="barangay" required disabled>
                                            <option value="">Select Barangay</option>
                                            <!-- Will be populated via API based on city -->
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label for="city" class="block text-sm font-semibold text-gray-800 mb-2 required">City/Municipality</label>
                                        <select class="w-full border border-gray-300 rounded-lg px-4 py-3 transition-all duration-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 hover:shadow-md appearance-none pr-10" id="city" name="city" required disabled>
                                            <option value="">Select City/Municipality</option>
                                            <!-- Will be populated via API based on province -->
                                        </select>
                                    </div>
                                    <div>
                                        <label for="province" class="block text-sm font-semibold text-gray-800 mb-2 required">Province</label>
                                        <select class="w-full border border-gray-300 rounded-lg px-4 py-3 transition-all duration-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 hover:shadow-md appearance-none pr-10" id="province" name="province" required>
                                            <option value="">Select Province</option>
                                            <!-- Will be populated via API -->
                                        </select>
                                    </div>
                                    <div>
                                        <label for="zipCode" class="block text-sm font-semibold text-gray-800 mb-2 required">ZIP Code</label>
                                        <input type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3 transition-all duration-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 hover:shadow-md placeholder-gray-400" id="zipCode" name="zipCode" maxlength="4" required>
                                        <div id="zipCodeValidationMessage" class="validation-message hidden"></div>
                                    </div>
                                </div>
                                <div class="hidden">
                                    <input type="hidden" id="barangay_name" name="barangay_name">
                                    <input type="hidden" id="city_name" name="city_name">
                                    <input type="hidden" id="province_name" name="province_name">
                                </div>
                            </div>
                            
                            <!-- ID Verification Section -->
                            <div class="space-y-6">
                                <h4 class="text-xl font-bold text-blue-700 border-l-4 border-blue-600 pl-4 mb-4 pb-2">ID Verification</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="idType" class="block text-sm font-semibold text-gray-800 mb-2 required">ID Type</label>
                                        <select class="w-full border border-gray-300 rounded-lg px-4 py-3 transition-all duration-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 hover:shadow-md appearance-none pr-10" id="idType" name="idType" required>
                                            <option value="">Select ID Type</option>
                                            <option value="passport">Passport</option>
                                            <option value="drivers_license">Driver's License</option>
                                            <option value="umid">UMID</option>
                                            <option value="sss">SSS ID</option>
                                            <option value="prc">PRC ID</option>
                                            <option value="postal">Postal ID</option>
                                            <option value="voters">Voter's ID</option>
                                            <option value="philsys">PhilSys ID (National ID)</option>
                                            <option value="other">Other Government-issued ID</option>
                                        </select>
                                    </div>
                                    <div id="otherIdContainer" class="hidden">
                                        <label for="otherIdType" class="block text-sm font-semibold text-gray-800 mb-2 required">Specify ID Type</label>
                                        <input type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3 transition-all duration-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 hover:shadow-md placeholder-gray-400" id="otherIdType" name="otherIdType">
                                    </div>
                                    
                                    <div>
                                        <label for="idNumber" class="block text-sm font-semibold text-gray-800 mb-2 required">ID Number</label>
                                        <input type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3 transition-all duration-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 hover:shadow-md placeholder-gray-400" id="idNumber" name="idNumber" required>
                                        <div id="idNumberValidationMessage" class="validation-message hidden"></div>
                                    </div>
                                    
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-semibold text-gray-800 mb-2 required">Upload ID (Front)</label>
                                        <label class="w-full flex items-center justify-center px-4 py-6 rounded-lg border-2 border-dashed border-gray-300 bg-white text-gray-600 hover:text-blue-600 hover:border-blue-600 hover:bg-blue-50 transition-all duration-200 cursor-pointer shadow-sm hover:shadow-md">
                                            <span class="mr-2" id="frontFileName">Choose File</span>
                                            <input type="file" class="sr-only" id="idUpload" name="idUpload" accept="image/*,.pdf" required>
                                        </label>
                                        <p class="mt-2 text-sm text-gray-500">Upload a clear photo or scan of your ID. Maximum file size: 5MB. Accepted formats: JPG, PNG, PDF.</p>
                                    </div>
                                    
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-semibold text-gray-800 mb-2 required">Upload ID (Back)</label>
                                        <label class="w-full flex items-center justify-center px-4 py-6 rounded-lg border-2 border-dashed border-gray-300 bg-white text-gray-600 hover:text-blue-600 hover:border-blue-600 hover:bg-blue-50 transition-all duration-200 cursor-pointer shadow-sm hover:shadow-md">
                                            <span class="mr-2" id="backFileName">Choose File</span>
                                            <input type="file" class="sr-only" id="idUploadBack" name="idUploadBack" accept="image/*,.pdf" required>
                                        </label>
                                        <p class="mt-2 text-sm text-gray-500">Upload a clear photo or scan of the back of your ID. Maximum file size: 5MB. Accepted formats: JPG, PNG, PDF.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Source of Funds Section -->
                            <div class="space-y-6">
                                <h4 class="text-xl font-bold text-blue-700 border-l-4 border-blue-600 pl-4 mb-4 pb-2">Source of Funds</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="fundsSource" class="block text-sm font-semibold text-gray-800 mb-2 required">Primary Source of Funds</label>
                                        <select class="w-full border border-gray-300 rounded-lg px-4 py-3 transition-all duration-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 hover:shadow-md appearance-none pr-10" id="fundsSource" name="fundsSource" required>
                                            <option value="">Select Source of Funds</option>
                                            <option value="salary">Employment Salary</option>
                                            <option value="business">Business Income</option>
                                            <option value="investments">Investments</option>
                                            <option value="pension">Pension/Retirement</option>
                                            <option value="remittance">Remittance</option>
                                            <option value="inheritance">Inheritance</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div id="otherFundsContainer" class="hidden">
                                        <label for="otherFundsSource" class="block text-sm font-semibold text-gray-800 mb-2 required">Specify Source</label>
                                        <input type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3 transition-all duration-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 hover:shadow-md placeholder-gray-400" id="otherFundsSource" name="otherFundsSource">
                                    </div>
                                    
                                    <div>
                                        <label for="occupation" class="block text-sm font-semibold text-gray-800 mb-2 required">Occupation/Nature of Work</label>
                                        <input type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3 transition-all duration-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 hover:shadow-md placeholder-gray-400" id="occupation" name="occupation" required>
                                    </div>
                                    <div>
                                        <label for="employer" class="block text-sm font-semibold text-gray-800 mb-2">Employer/Business Name</label>
                                        <input type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3 transition-all duration-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 hover:shadow-md placeholder-gray-400" id="employer" name="employer">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Terms and Submit -->
                            <div class="space-y-6">
                                <div class="flex items-start space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors duration-200 border border-transparent hover:border-gray-200">
                                    <div class="flex items-center h-5">
                                        <input id="termsCheck" name="termsCheck" type="checkbox" class="h-5 w-5 mt-0.5 text-blue-600 border-2 border-gray-300 rounded focus:ring-blue-500 focus:ring-2" required>
                                    </div>
                                    <div class="text-sm">
                                        <label for="termsCheck" class="font-medium text-gray-800">
                                            I certify that all information provided is accurate and complete. I understand that providing false information may lead to rejection of my application and possible legal consequences.
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="flex items-start space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors duration-200 border border-transparent hover:border-gray-200">
                                    <div class="flex items-center h-5">
                                        <input id="privacyCheck" name="privacyCheck" type="checkbox" class="h-5 w-5 mt-0.5 text-blue-600 border-2 border-gray-300 rounded focus:ring-blue-500 focus:ring-2" required>
                                    </div>
                                    <div class="text-sm">
                                        <label for="privacyCheck" class="font-medium text-gray-800">
                                            I agree to the <button type="button" class="text-blue-600 underline font-semibold" id="privacyPolicyBtn">Privacy Policy</button> and consent to the collection and processing of my personal information for KYC verification purposes.
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="flex justify-end gap-4 pt-4">
                                    <button type="reset" class="px-6 py-2.5 border-2 border-gray-300 rounded-lg font-semibold text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 shadow-sm hover:shadow-md">
                                        Reset Form
                                    </button>
                                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 rounded-lg font-semibold text-white shadow-md transition-all duration-200 transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                        Submit KYC Information
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Privacy Policy Modal (Hidden by default) -->
    <div id="privacyModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-xl overflow-hidden shadow-2xl transform transition-all sm:max-w-lg sm:w-full max-h-[90vh] overflow-y-auto">
            <div class="bg-white px-6 pt-6 pb-4">
                <div class="flex justify-between items-start">
                    <h3 class="text-xl font-bold text-gray-900">Privacy Policy</h3>
                    <button type="button" class="modal-close text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mt-4">
                    <h5 class="text-lg font-semibold text-gray-900">KYC Information Collection and Processing Policy</h5>
                    <p class="mt-2 text-sm text-gray-600">This Privacy Policy explains how we collect, use, and safeguard your information when you provide your KYC (Know Your Customer) information to us.</p>
                    
                    <h6 class="mt-4 text-base font-semibold text-gray-900">Collection of Your Information</h6>
                    <p class="mt-1 text-sm text-gray-600">We collect personal information that you voluntarily provide to us when you fill out our KYC form, including but not limited to:</p>
                    <ul class="mt-2 text-sm text-gray-600 list-disc pl-5 space-y-1">
                        <li>Personal identifiable information (name, date of birth, gender, civil status)</li>
                        <li>Contact information (email address, phone number, physical address)</li>
                        <li>Government-issued identification details and copies</li>
                        <li>Financial information (source of funds, occupation)</li>
                    </ul>
                    
                    <h6 class="mt-4 text-base font-semibold text-gray-900">Use of Your Information</h6>
                    <p class="mt-1 text-sm text-gray-600">We may use the information we collect from you for:</p>
                    <ul class="mt-2 text-sm text-gray-600 list-disc pl-5 space-y-1">
                        <li>Verifying your identity</li>
                        <li>Processing your transactions</li>
                        <li>Complying with legal and regulatory requirements</li>
                        <li>Preventing fraud and illegal activities</li>
                    </ul>
                    
                    <h6 class="mt-4 text-base font-semibold text-gray-900">Security of Your Information</h6>
                    <p class="mt-1 text-sm text-gray-600">We use administrative, technical, and physical security measures to help protect your personal information. While we have taken reasonable steps to secure the personal information you provide to us, please be aware that no security measures are perfect.</p>
                    
                    <h6 class="mt-4 text-base font-semibold text-gray-900">Data Retention</h6>
                    <p class="mt-1 text-sm text-gray-600">We will retain your KYC information for as long as necessary to fulfill the purposes for which we collected it, including for the purposes of satisfying any legal requirements.</p>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end">
                <button type="button" class="modal-close px-6 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg font-semibold text-white transition-colors duration-200">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        // All previous JavaScript remains the same
        document.getElementById('idType').addEventListener('change', function() {
            const otherIdContainer = document.getElementById('otherIdContainer');
            const otherIdInput = document.getElementById('otherIdType');
            
            if (this.value === 'other') {
                otherIdContainer.classList.remove('hidden');
                otherIdInput.setAttribute('required', '');
            } else {
                otherIdContainer.classList.add('hidden');
                otherIdInput.removeAttribute('required');
            }
        });
        
        document.getElementById('fundsSource').addEventListener('change', function() {
            const otherFundsContainer = document.getElementById('otherFundsContainer');
            const otherFundsInput = document.getElementById('otherFundsSource');
            
            if (this.value === 'other') {
                otherFundsContainer.classList.remove('hidden');
                otherFundsInput.setAttribute('required', '');
            } else {
                otherFundsContainer.classList.add('hidden');
                otherFundsInput.removeAttribute('required');
            }
        });
        
        document.getElementById('nationality').addEventListener('change', function() {
            const otherNationalityContainer = document.getElementById('otherNationalityContainer');
            const otherNationalityInput = document.getElementById('otherNationality');
            
            if (this.value === 'other') {
                otherNationalityContainer.classList.remove('hidden');
                otherNationalityInput.setAttribute('required', '');
            } else {
                otherNationalityContainer.classList.add('hidden');
                otherNationalityInput.removeAttribute('required');
            }
        });
        
        document.getElementById('mobileNumber').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        document.getElementById('kycForm').addEventListener('submit', function(event) {
            const fileInput = document.getElementById('idUpload');
            const backFileInput = document.getElementById('idUploadBack');
            const maxFileSize = 5 * 1024 * 1024; // 5MB
            
            if (fileInput.files.length > 0 && fileInput.files[0].size > maxFileSize) {
                alert('Front ID file size exceeds the maximum limit of 5MB.');
                event.preventDefault();
                return false;
            }
            
            if (backFileInput.files.length > 0 && backFileInput.files[0].size > maxFileSize) {
                alert('Back ID file size exceeds the maximum limit of 5MB.');
                event.preventDefault();
                return false;
            }

            const province = document.getElementById('province_name').value;
            const city = document.getElementById('city_name').value;
            const barangay = document.getElementById('barangay_name').value;

            if (!province || !city || !barangay) {
                event.preventDefault();
                alert('Please select complete address information (Province, City, and Barangay)');
                return false;
            }
        });
        
        document.getElementById('idUpload').addEventListener('change', function() {
            const fileName = this.files[0] ? this.files[0].name : 'Choose File';
            document.getElementById('frontFileName').textContent = fileName;
        });
        
        document.getElementById('idUploadBack').addEventListener('change', function() {
            const fileName = this.files[0] ? this.files[0].name : 'Choose File';
            document.getElementById('backFileName').textContent = fileName;
        });
        
        const modal = document.getElementById('privacyModal');
        const openModalBtn = document.getElementById('privacyPolicyBtn');
        const closeModalBtns = document.querySelectorAll('.modal-close');
        
        openModalBtn.addEventListener('click', function() {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });
        
        closeModalBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            });
        });
        
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        });

        // Real-time validation functions
        document.addEventListener('DOMContentLoaded', function() {
            // Phone number validation
            const mobileNumberInput = document.getElementById('mobileNumber');
            const phoneValidationMessage = document.getElementById('phoneValidationMessage');
            
            mobileNumberInput.addEventListener('input', function() {
                // Allow only numbers and limit to 11 digits
                this.value = this.value.replace(/[^0-9]/g, '').substring(0, 11);
                
                // Validate Philippine mobile number format (09XX XXX XXXX)
                const phoneRegex = /^09\d{9}$/;
                
                if (this.value.length === 0) {
                    // Empty field
                    this.classList.remove('input-valid', 'input-invalid');
                    phoneValidationMessage.classList.add('hidden');
                } else if (phoneRegex.test(this.value)) {
                    // Valid phone number
                    this.classList.add('input-valid');
                    this.classList.remove('input-invalid');
                    phoneValidationMessage.textContent = "Valid phone number";
                    phoneValidationMessage.classList.remove('hidden', 'error-message');
                    phoneValidationMessage.classList.add('success-message');
                } else {
                    // Invalid phone number
                    this.classList.add('input-invalid');
                    this.classList.remove('input-valid');
                    phoneValidationMessage.textContent = "Please enter a valid 11-digit mobile number starting with 09";
                    phoneValidationMessage.classList.remove('hidden', 'success-message');
                    phoneValidationMessage.classList.add('error-message');
                }
            });
            
            // Email validation
            const emailInput = document.getElementById('email');
            const emailValidationMessage = document.getElementById('emailValidationMessage');
            
            emailInput.addEventListener('input', function() {
                // Email regex pattern
                const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                
                if (this.value.length === 0) {
                    // Empty field
                    this.classList.remove('input-valid', 'input-invalid');
                    emailValidationMessage.classList.add('hidden');
                } else if (emailRegex.test(this.value)) {
                    // Valid email
                    this.classList.add('input-valid');
                    this.classList.remove('input-invalid');
                    emailValidationMessage.textContent = "Valid email address";
                    emailValidationMessage.classList.remove('hidden', 'error-message');
                    emailValidationMessage.classList.add('success-message');
                } else {
                    // Invalid email
                    this.classList.add('input-invalid');
                    this.classList.remove('input-valid');
                    emailValidationMessage.textContent = "Please enter a valid email address";
                    emailValidationMessage.classList.remove('hidden', 'success-message');
                    emailValidationMessage.classList.add('error-message');
                }
            });
            
            // ID number validation based on ID type
            const idTypeSelect = document.getElementById('idType');
            const idNumberInput = document.getElementById('idNumber');
            const idNumberValidationMessage = document.getElementById('idNumberValidationMessage');
            
            idTypeSelect.addEventListener('change', function() {
                // Reset validation when ID type changes
                idNumberInput.value = '';
                idNumberInput.classList.remove('input-valid', 'input-invalid');
                idNumberValidationMessage.classList.add('hidden');
                
                // Set placeholder based on ID type
                switch(this.value) {
                    case 'sss':
                        idNumberInput.placeholder = "XX-XXXXXXX-X";
                        break;
                    case 'drivers_license':
                        idNumberInput.placeholder = "XXX-XX-XXXXXX";
                        break;
                    case 'passport':
                        idNumberInput.placeholder = "PXXXXXXXX";
                        break;
                    case 'philsys':
                        idNumberInput.placeholder = "XXXX-XXXX-XXXX-XXXX";
                        break;
                    default:
                        idNumberInput.placeholder = "";
                }
            });
            
            idNumberInput.addEventListener('input', function() {
                const idType = idTypeSelect.value;
                let isValid = false;
                let message = "Please enter a valid ID number";
                
                // Validation logic based on ID type
                switch(idType) {
                    case 'sss':
                        // SSS format: XX-XXXXXXX-X
                        isValid = /^\d{2}-\d{7}-\d{1}$/.test(this.value) || /^\d{10,12}$/.test(this.value);
                        if (!isValid && this.value.length > 0) {
                            message = "SSS should be in XX-XXXXXXX-X format or 10-12 digits";
                        }
                        break;
                    case 'drivers_license':
                        // Driver's License format: XXX-XX-XXXXXX
                        isValid = /^[A-Z0-9]{1,3}-\d{2}-\d{6,}$/.test(this.value) || /^[A-Z0-9\d]{8,}$/.test(this.value);
                        if (!isValid && this.value.length > 0) {
                            message = "Driver's License should be properly formatted";
                        }
                        break;
                    case 'passport':
                        // Passport format: PXXXXXXXX (P + 8 characters)
                        isValid = /^[A-Z]{1,2}[0-9]{6,7}$/.test(this.value);
                        if (!isValid && this.value.length > 0) {
                            message = "Passport should start with a letter followed by numbers";
                        }
                        break;
                    case 'philsys':
                        // PhilSys format: XXXX-XXXX-XXXX-XXXX
                        isValid = /^\d{4}-\d{4}-\d{4}-\d{4}$/.test(this.value) || /^\d{16}$/.test(this.value);
                        if (!isValid && this.value.length > 0) {
                            message = "PhilSys should be 16 digits, optionally with hyphens";
                        }
                        break;
                    default:
                        // For other ID types, just ensure it's not empty
                        isValid = this.value.length >= 5;
                        if (!isValid && this.value.length > 0) {
                            message = "ID number should be at least 5 characters";
                        }
                }
                
                if (this.value.length === 0) {
                    // Empty field
                    this.classList.remove('input-valid', 'input-invalid');
                    idNumberValidationMessage.classList.add('hidden');
                } else if (isValid) {
                    // Valid ID number
                    this.classList.add('input-valid');
                    this.classList.remove('input-invalid');
                    idNumberValidationMessage.textContent = "Valid ID number";
                    idNumberValidationMessage.classList.remove('hidden', 'error-message');
                    idNumberValidationMessage.classList.add('success-message');
                } else {
                    // Invalid ID number
                    this.classList.add('input-invalid');
                    this.classList.remove('input-valid');
                    idNumberValidationMessage.textContent = message;
                    idNumberValidationMessage.classList.remove('hidden', 'success-message');
                    idNumberValidationMessage.classList.add('error-message');
                }
            });
            
            // Philippine address API integration
            const provinceSelect = document.getElementById('province');
            const citySelect = document.getElementById('city');
            const barangaySelect = document.getElementById('barangay');
            
            // Add NCR (Metro Manila) manually with the correct region code
            function addNCRToDropdown() {
                const ncrOption = document.createElement('option');
                // The correct NCR region code according to PSGC API
                ncrOption.value = "130000000";  // This is the correct region code for NCR
                ncrOption.textContent = "Metro Manila (NCR)";
                provinceSelect.appendChild(ncrOption);
            }
            
            // First fetch regions to get NCR code
            fetch('https://psgc.gitlab.io/api/regions/')
                .then(response => response.json())
                .then(data => {
                    // Find NCR in the regions data
                    const ncrRegion = data.find(region => region.name === "National Capital Region" || region.name === "NCR");
                    if (ncrRegion) {
                        const ncrOption = document.createElement('option');
                        ncrOption.value = ncrRegion.code;
                        ncrOption.textContent = "Metro Manila (NCR)";
                        provinceSelect.appendChild(ncrOption);
                        console.log("Added NCR with code:", ncrRegion.code);
                    } else {
                        // Fallback to hardcoded NCR option
                        addNCRToDropdown();
                    }
                    
                    // Then fetch provinces
                    return fetch('https://psgc.gitlab.io/api/provinces/');
                })
                .then(response => response.json())
                .then(data => {
                    // Sort provinces alphabetically
                    data.sort((a, b) => a.name.localeCompare(b.name));
                    
                    // Add provinces to dropdown
                    data.forEach(province => {
                        const option = document.createElement('option');
                        option.value = province.code;
                        option.textContent = province.name;
                        provinceSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error fetching regions/provinces:', error);
                    // Fallback if API fails - show a message
                    provinceSelect.innerHTML = '<option value="">Failed to load provinces</option>';
                    // Still add NCR as an option
                    addNCRToDropdown();
                });
                
            // When province is selected, fetch cities/municipalities
            provinceSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    document.getElementById('province_name').value = selectedOption.textContent.trim();
                }
                
                // Reset city and barangay
                citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
                barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
                barangaySelect.disabled = true;
                
                if (this.value) {
                    citySelect.disabled = false;
                    
                    // Check if this is NCR or another province by looking at the code prefix
                    // NCR region code starts with "13" (for the 130000000 code)
                    const isNCR = this.value.startsWith("13");
                    
                    // Build the appropriate API URL based on whether this is NCR or a province
                    let apiUrl = isNCR 
                        ? `https://psgc.gitlab.io/api/regions/${this.value}/cities-municipalities/`
                        : `https://psgc.gitlab.io/api/provinces/${this.value}/cities-municipalities/`;
                    
                    console.log("Fetching cities from URL:", apiUrl);
                    
                    // Fetch cities/municipalities
                    fetch(apiUrl)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! Status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            // Sort cities alphabetically
                            data.sort((a, b) => a.name.localeCompare(b.name));
                            
                            if (data.length === 0) {
                                citySelect.innerHTML = '<option value="">No cities found</option>';
                            } else {
                                // Add cities to dropdown
                                data.forEach(city => {
                                    const option = document.createElement('option');
                                    option.value = city.code;
                                    option.textContent = city.name;
                                    citySelect.appendChild(option);
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching cities/municipalities:', error);
                            citySelect.innerHTML = '<option value="">Failed to load cities</option>';
                        });
                } else {
                    citySelect.disabled = true;
                }
            });
            
            // When city is selected, fetch barangays - improved error handling
            citySelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    document.getElementById('city_name').value = selectedOption.textContent.trim();
                }
                
                // Reset barangay
                barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
                
                if (this.value) {
                    barangaySelect.disabled = false;
                    
                    const apiUrl = `https://psgc.gitlab.io/api/cities-municipalities/${this.value}/barangays/`;
                    console.log("Fetching barangays from URL:", apiUrl);
                    
                    // Fetch barangays based on selected city/municipality
                    fetch(apiUrl)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! Status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            // Sort barangays alphabetically
                            data.sort((a, b) => a.name.localeCompare(b.name));
                            
                            if (data.length === 0) {
                                barangaySelect.innerHTML = '<option value="">No barangays found</option>';
                            } else {
                                // Add barangays to dropdown
                                data.forEach(barangay => {
                                    const option = document.createElement('option');
                                    option.value = barangay.code;
                                    option.textContent = barangay.name;
                                    barangaySelect.appendChild(option);
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching barangays:', error);
                            barangaySelect.innerHTML = '<option value="">Failed to load barangays</option>';
                        });
                } else {
                    barangaySelect.disabled = true;
                }
            });
            
            barangaySelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    document.getElementById('barangay_name').value = selectedOption.textContent.trim();
                }
            });

            // Zipcode validation
            const zipCodeInput = document.getElementById('zipCode');
            const zipCodeValidationMessage = document.getElementById('zipCodeValidationMessage');
            
            zipCodeInput.addEventListener('input', function() {
                // Allow only numbers and limit to 4 digits
                this.value = this.value.replace(/[^0-9]/g, '').substring(0, 4);
                
                // Philippine ZIP codes are 4-digit numbers
                const zipCodeRegex = /^\d{4}$/;
                
                if (this.value.length === 0) {
                    // Empty field
                    this.classList.remove('input-valid', 'input-invalid');
                    zipCodeValidationMessage.classList.add('hidden');
                } else if (zipCodeRegex.test(this.value)) {
                    // Valid ZIP code
                    this.classList.add('input-valid');
                    this.classList.remove('input-invalid');
                    zipCodeValidationMessage.textContent = "Valid ZIP code";
                    zipCodeValidationMessage.classList.remove('hidden', 'error-message');
                    zipCodeValidationMessage.classList.add('success-message');
                } else {
                    // Invalid ZIP code
                    this.classList.add('input-invalid');
                    this.classList.remove('input-valid');
                    zipCodeValidationMessage.textContent = "Please enter a valid 4-digit ZIP code";
                    zipCodeValidationMessage.classList.remove('hidden', 'success-message');
                    zipCodeValidationMessage.classList.add('error-message');
                }
            });
        });
    </script>
</body>
</html>