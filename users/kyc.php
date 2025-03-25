<?php

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KYC Verification - Philippines</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom styles that extend Tailwind */
        .required:after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto py-8 px-4">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="bg-blue-600 text-white px-6 py-4">
                    <h3 class="text-xl font-bold">Know Your Customer (KYC) Verification</h3>
                </div>
                <div class="p-6">
                    <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 mb-6 rounded">
                        <p class="m-0">Please complete all required fields marked with an asterisk (*) and upload a clear copy of your ID document.</p>
                    </div>
                    
                    <form id="kycForm" enctype="multipart/form-data" method="post" action="process_kyc.php">
                        <!-- Personal Information Section -->
                        <div class="mb-8">
                            <h4 class="text-lg font-semibold text-blue-600 border-b-2 border-blue-500 pb-2 mb-4">Personal Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="firstName" class="block text-sm font-medium text-gray-700 mb-1 required">First Name</label>
                                    <input type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="firstName" name="firstName" required>
                                </div>
                                <div>
                                    <label for="middleName" class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                                    <input type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="middleName" name="middleName">
                                </div>
                                <div>
                                    <label for="lastName" class="block text-sm font-medium text-gray-700 mb-1 required">Last Name</label>
                                    <input type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="lastName" name="lastName" required>
                                </div>
                                
                                <div>
                                    <label for="dob" class="block text-sm font-medium text-gray-700 mb-1 required">Date of Birth</label>
                                    <input type="date" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="dob" name="dob" required>
                                </div>
                                <div>
                                    <label for="gender" class="block text-sm font-medium text-gray-700 mb-1 required">Gender</label>
                                    <select class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="gender" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="nationality" class="block text-sm font-medium text-gray-700 mb-1 required">Nationality</label>
                                    <select class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="nationality" name="nationality" required>
                                        <option value="Filipino" selected>Filipino</option>
                                        <option value="other">Others</option>
                                    </select>
                                </div>
                                <div id="otherNationalityContainer" class="hidden">
                                    <label for="otherNationality" class="block text-sm font-medium text-gray-700 mb-1 required">Specify Nationality</label>
                                    <input type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="otherNationality" name="otherNationality">
                                </div>
                                
                                <div>
                                    <label for="civilStatus" class="block text-sm font-medium text-gray-700 mb-1 required">Civil Status</label>
                                    <select class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="civilStatus" name="civilStatus" required>
                                        <option value="">Select Civil Status</option>
                                        <option value="single">Single</option>
                                        <option value="married">Married</option>
                                        <option value="widowed">Widowed</option>
                                        <option value="separated">Separated</option>
                                        <option value="divorced">Divorced</option>
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1 required">Email Address</label>
                                    <input type="email" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="email" name="email" required>
                                </div>
                                
                                <div class="md:col-span-2">
                                    <label for="mobileNumber" class="block text-sm font-medium text-gray-700 mb-1 required">Mobile Number</label>
                                    <div class="flex">
                                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500">+63</span>
                                        <input type="text" class="flex-1 min-w-0 block w-full rounded-none rounded-r-md border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="mobileNumber" name="mobileNumber" placeholder="9XX XXX XXXX" maxlength="10" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Address Section -->
                        <div class="mb-8">
                            <h4 class="text-lg font-semibold text-blue-600 border-b-2 border-blue-500 pb-2 mb-4">Current Address</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="streetAddress" class="block text-sm font-medium text-gray-700 mb-1 required">Street Address</label>
                                    <input type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="streetAddress" name="streetAddress" required>
                                </div>
                                <div>
                                    <label for="barangay" class="block text-sm font-medium text-gray-700 mb-1 required">Barangay</label>
                                    <input type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="barangay" name="barangay" required>
                                </div>
                                
                                <div>
                                    <label for="city" class="block text-sm font-medium text-gray-700 mb-1 required">City/Municipality</label>
                                    <input type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="city" name="city" required>
                                </div>
                                <div>
                                    <label for="province" class="block text-sm font-medium text-gray-700 mb-1 required">Province</label>
                                    <input type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="province" name="province" required>
                                </div>
                                <div>
                                    <label for="zipCode" class="block text-sm font-medium text-gray-700 mb-1 required">ZIP Code</label>
                                    <input type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="zipCode" name="zipCode" maxlength="4" required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- ID Verification Section -->
                        <div class="mb-8">
                            <h4 class="text-lg font-semibold text-blue-600 border-b-2 border-blue-500 pb-2 mb-4">ID Verification</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="idType" class="block text-sm font-medium text-gray-700 mb-1 required">ID Type</label>
                                    <select class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="idType" name="idType" required>
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
                                    <label for="otherIdType" class="block text-sm font-medium text-gray-700 mb-1 required">Specify ID Type</label>
                                    <input type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="otherIdType" name="otherIdType">
                                </div>
                                
                                <div>
                                    <label for="idNumber" class="block text-sm font-medium text-gray-700 mb-1 required">ID Number</label>
                                    <input type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="idNumber" name="idNumber" required>
                                </div>
                                
                                <div class="md:col-span-2">
                                    <label for="idUpload" class="block text-sm font-medium text-gray-700 mb-1 required">Upload ID (Front)</label>
                                    <div class="mt-1 flex items-center">
                                        <label class="w-full flex items-center px-3 py-2 rounded-md border border-gray-300 shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none cursor-pointer">
                                            <span>Choose File</span>
                                            <input type="file" class="sr-only" id="idUpload" name="idUpload" accept="image/*,.pdf" required>
                                        </label>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">Upload a clear photo or scan of your ID. Maximum file size: 5MB. Accepted formats: JPG, PNG, PDF.</p>
                                </div>
                                
                                <div class="md:col-span-2">
                                    <label for="idUploadBack" class="block text-sm font-medium text-gray-700 mb-1 required">Upload ID (Back)</label>
                                    <div class="mt-1 flex items-center">
                                        <label class="w-full flex items-center px-3 py-2 rounded-md border border-gray-300 shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none cursor-pointer">
                                            <span>Choose File</span>
                                            <input type="file" class="sr-only" id="idUploadBack" name="idUploadBack" accept="image/*,.pdf" required>
                                        </label>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">Upload a clear photo or scan of the back of your ID. Maximum file size: 5MB. Accepted formats: JPG, PNG, PDF.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Source of Funds Section -->
                        <div class="mb-8">
                            <h4 class="text-lg font-semibold text-blue-600 border-b-2 border-blue-500 pb-2 mb-4">Source of Funds</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="fundsSource" class="block text-sm font-medium text-gray-700 mb-1 required">Primary Source of Funds</label>
                                    <select class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="fundsSource" name="fundsSource" required>
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
                                    <label for="otherFundsSource" class="block text-sm font-medium text-gray-700 mb-1 required">Specify Source</label>
                                    <input type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="otherFundsSource" name="otherFundsSource">
                                </div>
                                
                                <div>
                                    <label for="occupation" class="block text-sm font-medium text-gray-700 mb-1 required">Occupation/Nature of Work</label>
                                    <input type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="occupation" name="occupation" required>
                                </div>
                                <div>
                                    <label for="employer" class="block text-sm font-medium text-gray-700 mb-1">Employer/Business Name</label>
                                    <input type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="employer" name="employer">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Terms and Submit -->
                        <div class="mb-8">
                            <div class="mb-4">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="termsCheck" name="termsCheck" type="checkbox" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded" required>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="termsCheck" class="font-medium text-gray-700">
                                            I certify that all information provided is accurate and complete. I understand that providing false information may lead to rejection of my application and possible legal consequences.
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-6">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="privacyCheck" name="privacyCheck" type="checkbox" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded" required>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="privacyCheck" class="font-medium text-gray-700">
                                            I agree to the <button type="button" class="text-blue-600 underline" id="privacyPolicyBtn">Privacy Policy</button> and consent to the collection and processing of my personal information for KYC verification purposes.
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex justify-end space-x-3">
                                <button type="reset" class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Reset Form</button>
                                <button type="submit" class="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Submit KYC Information</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Privacy Policy Modal (Hidden by default) -->
    <div id="privacyModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex justify-between items-start">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Privacy Policy</h3>
                    <button type="button" class="modal-close text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mt-3">
                    <h5 class="text-base font-medium text-gray-900">KYC Information Collection and Processing Policy</h5>
                    <p class="mt-2 text-sm text-gray-500">This Privacy Policy explains how we collect, use, and safeguard your information when you provide your KYC (Know Your Customer) information to us.</p>
                    
                    <h6 class="mt-4 text-sm font-medium text-gray-900">Collection of Your Information</h6>
                    <p class="mt-1 text-sm text-gray-500">We collect personal information that you voluntarily provide to us when you fill out our KYC form, including but not limited to:</p>
                    <ul class="mt-1 text-sm text-gray-500 list-disc pl-5">
                        <li>Personal identifiable information (name, date of birth, gender, civil status)</li>
                        <li>Contact information (email address, phone number, physical address)</li>
                        <li>Government-issued identification details and copies</li>
                        <li>Financial information (source of funds, occupation)</li>
                    </ul>
                    
                    <h6 class="mt-4 text-sm font-medium text-gray-900">Use of Your Information</h6>
                    <p class="mt-1 text-sm text-gray-500">We may use the information we collect from you for:</p>
                    <ul class="mt-1 text-sm text-gray-500 list-disc pl-5">
                        <li>Verifying your identity</li>
                        <li>Processing your transactions</li>
                        <li>Complying with legal and regulatory requirements</li>
                        <li>Preventing fraud and illegal activities</li>
                    </ul>
                    
                    <h6 class="mt-4 text-sm font-medium text-gray-900">Security of Your Information</h6>
                    <p class="mt-1 text-sm text-gray-500">We use administrative, technical, and physical security measures to help protect your personal information. While we have taken reasonable steps to secure the personal information you provide to us, please be aware that no security measures are perfect.</p>
                    
                    <h6 class="mt-4 text-sm font-medium text-gray-900">Data Retention</h6>
                    <p class="mt-1 text-sm text-gray-500">We will retain your KYC information for as long as necessary to fulfill the purposes for which we collected it, including for the purposes of satisfying any legal requirements.</p>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="modal-close w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-gray-600 text-base font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Custom JavaScript -->
    <script>
        // Show/hide "Other" ID type input field
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
        
        // Show/hide "Other" source of funds input field
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
        
        // Show/hide "Other" nationality input field
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
        
        // Mobile number validation - Philippines format
        document.getElementById('mobileNumber').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        // Form validation
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
        });
        
        // File upload display
        document.getElementById('idUpload').addEventListener('change', function() {
            const fileName = this.files[0] ? this.files[0].name : 'No file chosen';
            this.closest('label').querySelector('span').textContent = fileName;
        });
        
        document.getElementById('idUploadBack').addEventListener('change', function() {
            const fileName = this.files[0] ? this.files[0].name : 'No file chosen';
            this.closest('label').querySelector('span').textContent = fileName;
        });
        
        // Modal functionality
        const modal = document.getElementById('privacyModal');
        const openModalBtn = document.getElementById('privacyPolicyBtn');
        const closeModalBtns = document.querySelectorAll('.modal-close');
        
        openModalBtn.addEventListener('click', function() {
            modal.classList.remove('hidden');
        });
        
        closeModalBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                modal.classList.add('hidden');
            });
        });
        
        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
