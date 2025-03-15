<?php

require './session/db.php';


// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");

// Security headers
header("X-Content-Type-Options: nosniff"); // Prevent MIME-type sniffing
header("X-Frame-Options: DENY"); // Prevent clickjacking
header("Content-Security-Policy: default-src 'self'; script-src 'self'; object-src 'none'; frame-ancestors 'none'; base-uri 'self';"); // Prevent XSS & Base Tag Injection
header("Referrer-Policy: strict-origin-when-cross-origin"); // More secure referrer policy

// Only add this if your site runs on HTTPS
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("Strict-Transport-Security: max-age=31536000; preload"); 


// Fetch all properties from the database
$properties_query = "SELECT * FROM property WHERE status = 'Available'";
$properties_result = mysqli_query($conn, $properties_query);
$properties = mysqli_fetch_all($properties_result, MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PropertyWise</title>
    <link rel="icon" href="images/logo.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif; /* Apply Poppins font */
        }
        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }
        /* Optional transition for mobile menu */
        .mobile-menu {
            display: none;
        }
        .mobile-menu.active {
            display: block;
        }
        section {
        scroll-margin-top: 80px; /* Adjust this value based on your header height */
        }
    
        /* Hero Section Animation */
        .hero-content {
            animation: fadeInUp 1s ease-out;
        }

        /* Custom Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                transform: translateX(-100px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        /* Image float animation for 3D building */
        @keyframes float {
            0% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
            100% {
                transform: translateY(0px);
            }
        }
        
        .floating-image {
            animation: float 6s ease-in-out infinite;
        }
         
    </style>
</head>
<body class="bg-gray-100">

    <!-- Header Section -->
    <header class="bg-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
          
            <!-- Logo -->
            <a href="." class="text-2xl font-bold text-blue-600">
                <i class="fas fa-key text-inherit"></i> <!-- This ensures the icon inherits the text color -->
                <span>PropertyWise</span>
            </a>


            <!-- Hamburger Icon for Mobile -->
            <div class="md:hidden">
                <button id="menu-btn" class="text-gray-700 focus:outline-none">
                    <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

            <nav class="space-x-6 hidden md:block !hidden md:!block">
                <a href="#features" class="text-gray-700 hover:text-blue-600">Features</a>
                <a href="#services" class="text-gray-700 hover:text-blue-600">Properties</a>
                <a href="#how-it-works" class="text-gray-700 hover:text-blue-600">How It Works</a>
                <a href="#contact" class="text-gray-700 hover:text-blue-600">Contact Us</a>
            </nav>

            <a href="./authentication/signup.php" class="bg-blue-600 text-white px-4 py-2 mt-4 block text-center hidden md:block !hidden mb-2 rounded-lg shadow-md hover:bg-blue-500 md:!block">
                Register now
            </a>

        </div>

        <!-- Mobile Menu (hidden by default) -->
        <div class="mobile-menu md:hidden px-4 py-4 bg-blue-50">
            <a href="#features" class="block text-gray-700 py-2 hover:text-blue-600">Features</a>
            <a href="#services" class="block text-gray-700 py-2 hover:text-blue-600">Properties</a>
            <a href="#how-it-works" class="block text-gray-700 py-2 hover:text-blue-600">How It Works</a>
            <a href="#contact" class="block text-gray-700 py-2 hover:text-blue-600">Contact Us</a>
            <a href="./authentication/signup.php" class="bg-blue-600 text-white px-4 py-2 mt-4 block text-center rounded-lg shadow-md hover:bg-blue-500">
                Register now
            </a>
        </div>
    </header>


    <!-- Hero Section (Two-column layout) -->
    <section class="py-16 bg-gradient-to-r from-blue-50 to-gray-50">
        <div class="container mx-auto px-4">
            <div class="flex flex-col-reverse md:flex-row items-center">
                <!-- Left Column (Text Content) -->
                <div class="md:w-1/2 mt-8 md:mt-0 text-center md:text-left" data-aos="fade-right" data-aos-delay="100">
                    <h1 class="text-3xl md:text-5xl font-bold text-blue-600 mb-4">Simplify Your Living Experience with PropertyWise</h1>
                    <p class="text-lg md:text-xl text-gray-600 mb-8">Enjoy a seamless experience for managing your rental, from maintenance requests to secure payments, all in one place.</p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
                        <a href="#how-it-works" class="bg-blue-600 text-white px-8 py-3 rounded-lg text-lg shadow-lg hover:bg-blue-500 transition duration-300 transform hover:scale-105">
                            Discover How It Works
                        </a>
                        <a href="#services" class="border-2 border-blue-600 text-blue-600 px-8 py-3 rounded-lg text-lg shadow-md hover:bg-blue-600 hover:text-white transition duration-300">
                            View Properties
                        </a>
                    </div>
                </div>
                
                <!-- Right Column (3D Building Image) -->
                <div class="md:w-1/2 flex justify-center" data-aos="fade-left" data-aos-delay="200">
                    <div class="relative">
                        <!-- Main building image -->
                        <img 
                            src="images/3d-building.png" 
                            alt="Modern 3D Building" 
                            class="w-full max-w-lg floating-image"
                            onerror="this.onerror=null; this.src='images/3Dbuilding.png';"
                        />
                        
                        <!-- Optional decorative elements (can be removed if not needed) -->
                        <div class="absolute -bottom-4 w-full h-8 bg-gradient-to-t from-gray-100 to-transparent"></div>
                        
                        <!-- Circle decorations -->
                        <div class="absolute top-1/4 -left-12 w-24 h-24 bg-blue-200 rounded-full opacity-30"></div>
                        <div class="absolute bottom-1/3 -right-8 w-16 h-16 bg-blue-300 rounded-full opacity-20"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<!-- Features Section -->
<section id="features" class="py-16 bg-gray-50">
    <div class="container mx-auto px-6">
        <div class="text-center mb-12" data-aos="fade-up">
            <h2 class="text-3xl md:text-4xl font-bold text-blue-600">Key Features</h2>
            <p class="text-gray-600 mt-4">Explore the essential features that simplify your rental experience and enhance your comfort.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Feature 1: Profile Management -->
            <div class="bg-white p-6 rounded-lg shadow-lg text-blue-600 transition duration-300 ease-in-out transform hover:bg-blue-500 hover:text-white" data-aos="fade-up" data-aos-delay="100">
                <h3 class="text-xl font-bold mb-2 ">Profile Management</h3>
                <p class="text-gray-600 transition duration-300 ease-in-out hover:text-white">Easily manage your personal information, contact details, and account settings from one platform.</p>
            </div>

            <!-- Feature 2: View Unit Information -->
            <div class="bg-white p-6 rounded-lg shadow-lg text-blue-600 transition duration-300 ease-in-out transform hover:bg-blue-500 hover:text-white" data-aos="fade-up" data-aos-delay="200">
                <h3 class="text-xl font-bold mb-2">View Unit Information</h3>
                <p class="text-gray-600 transition duration-300 ease-in-out hover:text-white">Access important details about your unit, including rental dates and rental rates, at any time.</p>
            </div>

            <!-- Feature 3: Maintenance Requests -->
            <div class="bg-white p-6 rounded-lg shadow-lg text-blue-600 transition duration-300 ease-in-out transform hover:bg-blue-500 hover:text-white" data-aos="fade-up" data-aos-delay="300">
                <h3 class="text-xl font-bold mb-2">Maintenance Requests</h3>
                <p class="text-gray-600 transition duration-300 ease-in-out hover:text-white">Submit and track your maintenance requests effortlessly to ensure your home is always comfortable.</p>
            </div>

            <!-- Feature 4: Online Payments -->
            <div class="bg-white p-6 rounded-lg shadow-lg text-blue-600 transition duration-300 ease-in-out transform hover:bg-blue-500 hover:text-white" data-aos="fade-up" data-aos-delay="400">
                <h3 class="text-xl font-bold mb-2">Online Payments</h3>
                <p class="text-gray-600 transition duration-300 ease-in-out hover:text-white">Pay your rent and other fees securely online, making your payment process hassle-free.</p>
            </div>

            <!-- Feature 5: Notifications -->
            <div class="bg-white p-6 rounded-lg shadow-lg text-blue-600 transition duration-300 ease-in-out transform hover:bg-blue-500 hover:text-white" data-aos="fade-up" data-aos-delay="500">
                <h3 class="text-xl font-bold mb-2">Notifications</h3>
                <p class="text-gray-600 transition duration-300 ease-in-out hover:text-white">Receive timely updates on maintenance, payment reminders, and important announcements via email or SMS.</p>
            </div>

            <!-- Feature 6: Booking -->
            <div class="bg-white p-6 rounded-lg shadow-lg text-blue-600 transition duration-300 ease-in-out transform hover:bg-blue-500 hover:text-white" data-aos="fade-up" data-aos-delay="600">
                <h3 class="text-xl font-bold mb-2">Booking</h3>
                <p class="text-gray-600 transition duration-300 ease-in-out hover:text-white">Schedule appointments or reserve facilities easily through an intuitive booking system.</p>
            </div>
        </div>
    </div>
</section>

 <!-- List of Properties Section -->
<section id="services" class="py-16 bg-gray-50">
    <div class="container mx-auto text-center mb-12" data-aos="fade-up">
        <h2 class="text-3xl md:text-4xl font-bold text-blue-600">Our Properties</h2>
        <p class="text-gray-600 mt-4">Explore a variety of properties curated just for you.</p>
    </div>

    <!-- Property Cards Container -->
    <div class="relative flex items-center justify-center" data-aos="fade-up" data-aos-delay="200">
        <!-- Left Arrow -->
        <button id="prevButton" 
                class=" left-2 z-10 bg-blue-600 text-white p-3 md:p-4 rounded-full shadow-lg hover:bg-blue-700 focus:outline-none transform -translate-y-1/2 top-1/2">
            <i class="fas fa-chevron-left"></i>
        </button>

        <div id="propertyCardWrapper" 
             class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 px-4 md:px-10 lg:px-20 overflow-hidden">
            <!-- PHP will populate this section -->
            <?php if (!empty($properties)): ?>
                <?php 
                $displayedProperties = array_slice($properties, 0, 3);
                foreach ($displayedProperties as $property): 
                ?>
                <div class="flex-shrink-0 w-full md:w-80 bg-white shadow-lg hover:shadow-2xl rounded-lg overflow-hidden transition-transform duration-300 transform hover:scale-105 mx-auto">
                    <figure class="relative h-48 w-full overflow-hidden">
                        <?php
                        $image_path = !empty($property['images']) 
                            ? './admin/' . $property['images']
                            : './images/bg2.jpg';
                        ?>
                        <img class="w-full h-full object-cover" src="<?php echo htmlspecialchars($image_path); ?>" alt="Property Image" />
                    </figure>
                    <div class="p-4">
                        <h2 class="text-lg font-semibold mb-2"><?php echo htmlspecialchars($property['unit_type']); ?></h2>
                        <p class="text-sm"><span class="font-medium">Unit No:</span> <?php echo htmlspecialchars($property['unit_no']); ?></p>
                        <p class="text-sm"><span class="font-medium">Monthly Rent:</span> ₱<?php echo number_format($property['monthly_rent'], 2); ?></p>
                        <p class="text-sm"><span class="font-medium">Area:</span> <?php echo htmlspecialchars($property['square_meter']); ?> sqm</p>
                        <div class="mt-4 text-center">
                            <button class="reserve-button bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg transition duration-300">Reserve Now</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="w-full text-center py-8 col-span-3">
                    <p class="text-gray-500">No available properties at the moment.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Arrow -->
        <button id="nextButton" 
                class=" right-2 z-10 bg-blue-600 text-white p-3 md:p-4 rounded-full shadow-lg hover:bg-blue-700 focus:outline-none transform -translate-y-1/2 top-1/2">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>

    <!-- Pagination Dots -->
    <div class="flex justify-center mt-6">
        <div id="paginationDots" class="flex space-x-2">
            <button class="w-3 h-3 bg-blue-600 rounded-full active-dot"></button>
            <button class="w-3 h-3 bg-gray-300 hover:bg-blue-600 rounded-full"></button>
        </div>
    </div>
</section>


     <!-- Modal -->
     <div id="reserveModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-lg p-5 w-full max-w-xs sm:max-w-sm md:max-w-md transition-all">
            <h2 class="text-lg font-semibold mb-3">Are you interested in renting?</h2>
            <p class="text-sm text-gray-600 mb-5">Register now to proceed with your reservation.</p>
            <div class="flex flex-col sm:flex-row sm:justify-end gap-3">
                <button id="closeModal" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 w-full sm:w-auto order-2 sm:order-1">Cancel</button>
                <button id="signupButton" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 w-full sm:w-auto order-1 sm:order-2 mb-2 sm:mb-0">Register Now</button>
            </div>
        </div>
    </div>

 <!-- How It Works Section -->
<section id="how-it-works" class="py-16 bg-blue-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12" data-aos="fade-up">
            <h2 class="text-3xl md:text-4xl font-bold text-blue-600">How It Works</h2>
            <p class="text-gray-600 mt-4">Managing your rental experience has never been easier! Here's how it works:</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Step 1 -->
            <div class="text-center p-6" data-aos="fade-right" data-aos-delay="100">
                <div class="text-blue-600 mb-4">
                    <span class="text-4xl font-bold">1</span>
                </div>
                <h3 class="text-xl font-bold text-gray-600">Create an Account</h3>
                <p class="text-gray-600 mt-2">Sign up easily and start managing your rental needs in minutes.</p>
            </div>

            <!-- Step 2 -->
            <div class="text-center p-6" data-aos="fade-up" data-aos-delay="200">
                <div class="text-blue-600 mb-4">
                    <span class="text-4xl font-bold">2</span>
                </div>
                <h3 class="text-xl font-bold text-gray-600">Submit Maintenance Requests</h3>
                <p class="text-gray-600 mt-2">Quickly report issues and track the status of your requests.</p>
            </div>

            <!-- Step 3 -->
            <div class="text-center p-6" data-aos="fade-left" data-aos-delay="300">
                <div class="text-blue-600 mb-4">
                    <span class="text-4xl font-bold">3</span>
                </div>
                <h3 class="text-xl font-bold text-gray-600">Make Payments Online</h3>
                <p class="text-gray-600 mt-2">Easily pay rent and fees through our secure platform.</p>
            </div>
        </div>
    </div>
</section>

   <!-- Contact Section -->
   <section id="contact" class="py-16 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12" data-aos="fade-down">
                <h2 class="text-3xl md:text-4xl font-bold text-blue-600">Contact Us</h2>
                <p class="text-gray-600 mt-4">We're here to help you with any questions or support your needs.</p>
            </div>
            <form class="max-w-lg mx-auto" data-aos="zoom-in" data-aos-delay="200">
                <div class="grid grid-cols-1 gap-4">
                    <input type="text" class="border border-gray-300 p-4 rounded-lg focus:outline-none focus:border-blue-500" placeholder="Your Name" required>
                    <input type="email" class="border border-gray-300 p-4 rounded-lg focus:outline-none focus:border-blue-500" placeholder="Your Email" required>
                    <textarea class="border border-gray-300 p-4 rounded-lg focus:outline-none focus:border-blue-500" rows="4" placeholder="Your Message" required></textarea>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-500">Send Message</button>
                </div>
            </form>
        </div>
    </section>

<!-- Get Started Section -->
<section id="get-started" class="py-16 bg-blue-50">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-blue-600" data-aos="fade-up">Ready to Enhance Your Rental Experience?</h2>
        <p class="text-gray-600 mt-4" data-aos="fade-up" data-aos-delay="100">Join our community of tenants and experience hassle-free property management.</p>

        <a href="./authentication/signup.php" class="bg-blue-600 text-white px-8 py-3 rounded-lg text-lg shadow-md hover:bg-blue-500 mt-4 inline-block" data-aos="fade-up" data-aos-delay="200">Register Now</a>
    </div>
</section>


<!-- Footer Section -->
<footer class="bg-gray-50 mx-auto w-full max-w-container px-4 sm:px-6 lg:px-8">
    <div class="border-t border-slate-900/5 py-10">
        <p class="mt-5 text-center text-sm leading-6 text-slate-500">&copy; 2025 PropertyWise. All Rights Reserved.</p>
        <div class="mt-8 flex items-center justify-center space-x-4 text-sm font-semibold leading-6 text-slate-700">
            <a href="./asset/privacy_policy.php" class="text-blue-600 hover:underline">Privacy Policy</a>
            <div class="h-4 w-px bg-slate-500/20"></div>
            <a href="./asset/terms_condition.php" class="text-blue-600 hover:underline">Terms of Service</a>
            <div class="h-4 w-px bg-slate-500/20"></div>
            <a href="./asset/team.php" class="text-blue-600 hover:underline">Meet Our Team</a>
        </div>
    </div>
</footer>

    


        <script>

                document.addEventListener("DOMContentLoaded", () => {
                const properties = <?php echo json_encode($properties); ?>; // PHP array to JavaScript
                const propertyContainer = document.getElementById("propertyCardWrapper");
                const prevButton = document.getElementById("prevButton");
                const nextButton = document.getElementById("nextButton");
                const paginationDots = document.getElementById("paginationDots");
                const modal = document.getElementById("reserveModal");

                let currentIndex = 0;
                let cardsPerPage = 3;

                // Determine cards per page based on screen size
                const updateCardsPerPage = () => {
                    if (window.innerWidth < 768) {
                        cardsPerPage = 1; // Small screens
                    } else if (window.innerWidth < 1024) {
                        cardsPerPage = 2; // Medium screens
                    } else {
                        cardsPerPage = 3; // Large screens
                    }
                    renderCards(currentIndex);
                };

                // Render cards
                const renderCards = (startIndex) => {
                    propertyContainer.innerHTML = ""; // Clear current cards
                    const endIndex = startIndex + cardsPerPage;
                    const visibleProperties = properties.slice(startIndex, endIndex);

                    visibleProperties.forEach(property => {
                        const imagePath = property.images 
                            ? `./admin/${property.images}`
                            : `./images/bg2.jpg`;
                        const card = `
                            <div class="w-full bg-white shadow-lg hover:shadow-2xl rounded-lg overflow-hidden transition-transform duration-300 transform hover:scale-105">
                                <figure>
                                    <img class="w-full h-48 object-cover" src="${imagePath}" alt="Property Image" />
                                </figure>
                                <div class="p-4">
                                    <h2 class="text-lg font-semibold mb-2">${property.unit_type}</h2>
                                    <p class="text-sm"><span class="font-medium">Unit No:</span> ${property.unit_no}</p>
                                    <p class="text-sm"><span class="font-medium">Monthly Rent:</span> ₱${parseFloat(property.monthly_rent).toFixed(2)}</p>
                                    <p class="text-sm"><span class="font-medium">Area:</span> ${property.square_meter} sqm</p>
                                    <div class="mt-4 text-center">
                                        <button class="reserve-button bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg transition duration-300">Reserve Now</button>
                                    </div>
                                </div>
                            </div>
                        `;
                        propertyContainer.innerHTML += card;
                    });

                    // Update pagination dots
                    updatePaginationDots(Math.ceil(properties.length / cardsPerPage), Math.floor(startIndex / cardsPerPage));
                };

                // Update pagination dots
                const updatePaginationDots = (totalDots, activeIndex) => {
                    paginationDots.innerHTML = ""; // Clear current dots
                    for (let i = 0; i < totalDots; i++) {
                        const dot = document.createElement("button");
                        dot.className = `w-3 h-3 rounded-full ${i === activeIndex ? "bg-blue-600" : "bg-gray-300 hover:bg-blue-600"}`;
                        dot.addEventListener("click", () => {
                            currentIndex = i * cardsPerPage;
                            renderCards(currentIndex);
                        });
                        paginationDots.appendChild(dot);
                    }
                };

                // Event listeners for arrows
                prevButton.addEventListener("click", () => {
                    if (currentIndex - cardsPerPage >= 0) {
                        currentIndex -= cardsPerPage;
                        renderCards(currentIndex);
                    }
                });

                nextButton.addEventListener("click", () => {
                    if (currentIndex + cardsPerPage < properties.length) {
                        currentIndex += cardsPerPage;
                        renderCards(currentIndex);
                    }
                });

                // Handle screen resizing
                window.addEventListener("resize", updateCardsPerPage);

                // Event delegation for "Reserve Now" buttons
                document.addEventListener("click", (event) => {
                    if (event.target.classList.contains("reserve-button")) {
                        console.log("Reserve button clicked!");
                        modal.classList.remove("hidden");  // Show the modal
                    }
                });

                // Close modal event listener
                const closeModal = document.getElementById("closeModal");
                if (closeModal) {
                    closeModal.addEventListener("click", () => {
                        modal.classList.add("hidden"); // Hide the modal
                    });
                }

                // Signup button event listener
                const signupButton = document.getElementById("signupButton");
                if (signupButton) {
                    signupButton.addEventListener("click", () => {
                        window.location.href = './authentication/signup.php';
                    });
                }

                // Initial setup
                updateCardsPerPage();
            });

    </script>

    <script>
        const menuBtn = document.getElementById('menu-btn');
        const mobileMenu = document.querySelector('.mobile-menu');

        menuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('active');
        });

        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: false,  // Changed from true to false to allow repeat animations
            offset: 100
        });
    </script>

</body>
</html>
