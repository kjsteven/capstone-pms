<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PMS Landing Page</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif; /* Apply Poppins font */
        }
        /* Optional transition for mobile menu */
        .mobile-menu {
            display: none;
        }
        .mobile-menu.active {
            display: block;
        }
    </style>
</head>
<body class="bg-gray-100">

    <!-- Header Section -->
    <header class="bg-white shadow-lg">
        <div class="container mx-auto px-4 py-6 flex justify-between items-center">
            <!-- Logo -->
            <div class="text-2xl font-bold text-blue-600">
                <a href="#">One Soler PMS</a>
            </div>

            <!-- Hamburger Icon for Mobile -->
            <div class="md:hidden">
                <button id="menu-btn" class="text-gray-700 focus:outline-none">
                    <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

            <!-- Navigation (Hidden on Mobile, Visible on Medium and Larger Screens) -->
            <nav class="space-x-6 hidden md:block">
                <a href="#features" class="text-gray-700 hover:text-blue-600">Features</a>
                <a href="#how-it-works" class="text-gray-700 hover:text-blue-600">How It Works</a>
                <a href="#services" class="text-gray-700 hover:text-blue-600">Services</a>
                <a href="#contact" class="text-gray-700 hover:text-blue-600">Contact</a>
            </nav>

            <!-- CTA Button -->
            <a href="#get-started" class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow-md hidden md:inline-block hover:bg-blue-500">
                Get Started
            </a>
        </div>

        <!-- Mobile Menu (hidden by default) -->
        <div class="mobile-menu md:hidden px-4 py-4 bg-blue-50">
            <a href="#features" class="block text-gray-700 py-2 hover:text-blue-600">Features</a>
            <a href="#how-it-works" class="block text-gray-700 py-2 hover:text-blue-600">How It Works</a>
            <a href="#services" class="block text-gray-700 py-2 hover:text-blue-600">Services</a>
            <a href="#contact" class="block text-gray-700 py-2 hover:text-blue-600">Contact</a>
            <a href="#get-started" class="bg-blue-600 text-white px-4 py-2 mt-4 block text-center rounded-lg shadow-md hover:bg-blue-500">
                Get Started
            </a>
        </div>
    </header>


    <!-- Heroe Section -->
    <section class="bg-no-repeat bg-cover py-16 pb-24" style="background-image: url('images/bg2.jpg'); background-color: rgba(51, 51, 51, 0.9);">
    <div class="container mx-auto px-4 flex flex-col md:flex-row items-center">
        <div class="md:w-1/2 mb-6 md:mb-0 text-center md:text-left">
            <h1 class="text-3xl md:text-5xl font-bold text-white mb-4">
                Simplify Your Living Experience with One Soler PMS
            </h1>
            <p class="text-lg md:text-xl text-white mb-8">
                Enjoy a seamless experience for managing your rental, from maintenance requests to secure payments, all in one place.
            </p>
            <a href="#features" class="bg-blue-600 text-white px-8 py-3 rounded-lg text-lg shadow-md hover:bg-blue-500">
                Discover How It Works
            </a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-16 bg-gray-100">
    <div class="container mx-auto px-6">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-violet-800">Key Features</h2>
            <p class="text-gray-600 mt-4">Explore the essential features that simplify your rental experience and enhance your comfort.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Feature 1: Profile Management -->
            <div class="bg-white p-6 rounded-lg shadow-lg text-violet-800 transition duration-300 ease-in-out transform hover:bg-blue-500 hover:text-white">
                <h3 class="text-xl font-bold mb-2 ">Profile Management</h3>
                <p class="text-gray-600 transition duration-300 ease-in-out hover:text-white">Easily manage your personal information, contact details, and account settings from one platform.</p>
            </div>

            <!-- Feature 2: View Unit Information -->
            <div class="bg-white p-6 rounded-lg shadow-lg text-violet-800 transition duration-300 ease-in-out transform hover:bg-blue-500 hover:text-white">
                <h3 class="text-xl font-bold mb-2">View Unit Information</h3>
                <p class="text-gray-600 transition duration-300 ease-in-out hover:text-white">Access important details about your unit, including rental dates and rental rates, at any time.</p>
            </div>

            <!-- Feature 3: Maintenance Requests -->
            <div class="bg-white p-6 rounded-lg shadow-lg text-violet-800 transition duration-300 ease-in-out transform hover:bg-blue-500 hover:text-white">
                <h3 class="text-xl font-bold mb-2">Maintenance Requests</h3>
                <p class="text-gray-600 transition duration-300 ease-in-out hover:text-white">Submit and track your maintenance requests effortlessly to ensure your home is always comfortable.</p>
            </div>

            <!-- Feature 4: Online Payments -->
            <div class="bg-white p-6 rounded-lg shadow-lg text-violet-800 transition duration-300 ease-in-out transform hover:bg-blue-500 hover:text-white">
                <h3 class="text-xl font-bold mb-2">Online Payments</h3>
                <p class="text-gray-600 transition duration-300 ease-in-out hover:text-white">Pay your rent and other fees securely online, making your payment process hassle-free.</p>
            </div>

            <!-- Feature 5: Notifications -->
            <div class="bg-white p-6 rounded-lg shadow-lg text-violet-800 transition duration-300 ease-in-out transform hover:bg-blue-500 hover:text-white">
                <h3 class="text-xl font-bold mb-2">Notifications</h3>
                <p class="text-gray-600 transition duration-300 ease-in-out hover:text-white">Receive timely updates on maintenance, payment reminders, and important announcements via email or SMS.</p>
            </div>

            <!-- Feature 6: Booking -->
            <div class="bg-white p-6 rounded-lg shadow-lg text-violet-800 transition duration-300 ease-in-out transform hover:bg-blue-500 hover:text-white">
                <h3 class="text-xl font-bold mb-2">Booking</h3>
                <p class="text-gray-600 transition duration-300 ease-in-out hover:text-white">Schedule appointments or reserve facilities easily through an intuitive booking system.</p>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section id="services" class="py-16 bg-gray-100">
    <div class="container mx-auto px-6 text-center mb-12">
        <h2 class="text-3xl md:text-4xl font-bold text-violet-800">Our Services</h2>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8 px-6 md:px-10 lg:px-48 mx-auto">

        <!-- Service 1: Warehouse Space -->
        <div class="bg-white p-6 rounded-lg shadow-lg flex flex-col items-center text-violet-800 transition duration-300 ease-in-out transform hover:bg-blue-500 hover:text-white mb-6">
            <i class="fas fa-box-open fa-3x mb-4"></i> <!-- Warehouse Icon -->
            <h3 class="text-lg font-bold">Warehouse Space</h3>
        </div>

        <!-- Service 2: Office Space -->
        <div class="bg-white p-6 rounded-lg shadow-lg flex flex-col items-center text-violet-800 transition duration-300 ease-in-out transform hover:bg-blue-500 hover:text-white mb-6">
            <i class="fas fa-briefcase fa-3x mb-4"></i> <!-- Office Icon -->
            <h3 class="text-lg font-bold">Office Space</h3>
        </div>

        <!-- Service 3: Commercial Space -->
        <div class="bg-white p-6 rounded-lg shadow-lg flex flex-col items-center text-violet-800 transition duration-300 ease-in-out transform hover:bg-blue-500 hover:text-white mb-6">
            <i class="fas fa-store fa-3x mb-4"></i> <!-- Commercial Icon -->
            <h3 class="text-lg font-bold">Commercial Space</h3>
        </div>
    </div>
</section>


    <!-- How It Works Section -->
<section id="how-it-works" class="py-16 bg-blue-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-violet-800">How It Works</h2>
            <p class="text-gray-600 mt-4">Managing your rental experience has never been easier! Here's how it works:</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Step 1 -->
            <div class="text-center p-6">
                <div class="text-blue-600 mb-4">
                    <span class="text-4xl font-bold">1</span>
                </div>
                <h3 class="text-xl font-bold text-violet-800">Create an Account</h3>
                <p class="text-gray-600 mt-2">Sign up easily and start managing your rental needs in minutes.</p>
            </div>

            <!-- Step 2 -->
            <div class="text-center p-6">
                <div class="text-blue-600 mb-4">
                    <span class="text-4xl font-bold">2</span>
                </div>
                <h3 class="text-xl font-bold text-violet-800">Submit Maintenance Requests</h3>
                <p class="text-gray-600 mt-2">Quickly report issues and track the status of your requests.</p>
            </div>

            <!-- Step 3 -->
            <div class="text-center p-6">
                <div class="text-blue-600 mb-4">
                    <span class="text-4xl font-bold">3</span>
                </div>
                <h3 class="text-xl font-bold text-violet-800">Make Payments Online</h3>
                <p class="text-gray-600 mt-2">Easily pay rent and fees through our secure platform.</p>
            </div>
        </div>
    </div>
</section>


   <!-- Get in Touch Section -->
<section id="contact" class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-violet-800">Contact Us</h2>
            <p class="text-gray-600 mt-4">Have questions? We're here to help! Get in touch with us.</p>
        </div>

        <div class="max-w-xl mx-auto">
            <form action="#" method="POST" class="bg-blue-50 p-8 rounded-lg shadow-md">
                <div class="mb-4">
                    <label for="name" class="block text-gray-600 mb-2">Name</label>
                    <input type="text" id="name" name="name" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring focus:ring-blue-200">
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-gray-600 mb-2">Email</label>
                    <input type="email" id="email" name="email" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring focus:ring-blue-200">
                </div>

                <div class="mb-4">
                    <label for="message" class="block text-gray-600 mb-2">Message</label>
                    <textarea id="message" name="message" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring focus:ring-blue-200"></textarea>
                </div>

                <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg shadow-md hover:bg-blue-500">
                    Send Message
                </button>
            </form>
        </div>
    </div>
</section>

<!-- Get Started Section -->
<section id="get-started" class="py-16 bg-blue-50">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-violet-800">Ready to Enhance Your Rental Experience?</h2>
        <p class="text-gray-600 mt-4">Join our community of tenants and experience hassle-free property management.</p>

        <a href="./authentication/login.php" class="bg-blue-600 text-white px-8 py-3 rounded-lg text-lg shadow-md hover:bg-blue-500 mt-4 inline-block">Sign Up Now</a>
    </div>
</section>



    <!-- Footer Section -->
    <footer class="bg-white py-6">
        <div class="container mx-auto text-center">
            <p class="text-gray-600">&copy; 2024 One Soler PMS. All Rights Reserved.</p>
        </div>
    </footer>

    <script>
        const menuBtn = document.getElementById('menu-btn');
        const mobileMenu = document.querySelector('.mobile-menu');

        menuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('active');
        });
    </script>

</body>
</html>
