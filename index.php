<?php
// This is a basic PHP file.
// In a real application, you would start a session here:
// session_start();
// And potentially check if a user is logged in:
// $is_logged_in = isset($_SESSION['user_id']);
// For this design, we simulate being NOT logged in to always show the modal on food card click.
$is_logged_in = false; // Set to false for design purposes
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Golden Fork - Order Food Online</title>
    <!-- Google Font - Poppins (Body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
     <!-- Google Font - Merriweather (Headings) -->
     <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700;900&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        /* Add smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        :root {
            /* Refined Color Palette */
            --golden-fork-primary-dark: #1A2C3B; /* Dark Slate Blue */
            --golden-fork-secondary-dark: #273A4E; /* Slightly Lighter Slate Blue */
            --golden-fork-accent-gold: #FFC107; /* Bootstrap Warning Gold */
            --golden-fork-accent-teal: #00BCD4; /* Cyan/Teal */
            --golden-fork-light: #EFEFEF; /* Very Light Gray */
            --golden-fork-text-dark: #333;
            --golden-fork-text-light: #fff;
            --golden-fork-translucent-dark: rgba(26, 44, 59, 0.95); /* Translucent Slate Blue */
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif; /* Poppins for body text */
            background: url('pic6.AVIF') no-repeat center center fixed;
            background-size: cover;
            color: var(--golden-fork-text-light);
            padding-top: 120px; /* Adjust based on fixed header height */
            position: relative;
            min-height: 100vh;
            overflow-x: hidden;
            line-height: 1.6; /* Improved readability */
        }

        h1, h2, h3, h4, h5, h6 {
             font-family: 'Merriweather', serif; /* Merriweather for headings */
             font-weight: 900;
             color: var(--golden-fork-accent-gold); /* Headings are gold */
             text-shadow: 1px 1px 4px rgba(0,0,0,0.5); /* Consistent shadow */
        }
         h2.section-title {
             color: var(--golden-fork-accent-gold);
         }


        /* Announcement Bar Styling */
        .announcement-bar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: linear-gradient(to right, var(--golden-fork-accent-gold), var(--golden-fork-accent-teal)); /* Gold to Teal Gradient */
            color: var(--golden-fork-text-dark);
            padding: 8px 0;
            text-align: center;
            z-index: 1050;
            overflow: hidden;
            white-space: nowrap;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.95rem;
        }

        .announcement-text {
            display: inline-block;
            padding-left: 100%;
            animation: scroll-left 40s linear infinite; /* Slower animation */
        }

        @keyframes scroll-left {
            0% { transform: translateX(0); }
            100% { transform: translateX(-150%); }
        }

        /* Navbar Styling */
        .navbar {
            top: 42px;
            z-index: 1040;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            background-color: var(--golden-fork-primary-dark) !important; /* Dark Slate Blue */
        }

        .navbar-brand {
             font-family: 'Merriweather', serif; /* Use Merriweather for brand */
            font-weight: 900;
            font-size: 2.2rem; /* Larger */
            color: var(--golden-fork-accent-gold) !important;
            text-shadow: 1px 1px 4px rgba(0,0,0,0.4);
        }

         .navbar-nav .nav-link {
            font-weight: 500;
            transition: color 0.3s ease, transform 0.2s ease;
            color: rgba(var(--golden-fork-text-light), 0.95) !important;
             padding: 0.5rem 1rem;
         }

        .navbar-nav .nav-link:hover {
             color: var(--golden-fork-accent-teal) !important;
             transform: translateY(-2px);
        }

        /* Custom CSS for Hover Dropdown */
        .dropdown:hover > .dropdown-menu {
            display: block;
            margin-top: 0;
        }

        .dropdown-menu {
            background-color: rgba(var(--golden-fork-secondary-dark), 0.98);
            border: none;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.4);
            animation: fadeIn 0.3s ease-out;
        }

        .dropdown-item {
            color: rgba(var(--golden-fork-text-light), 0.9);
            font-weight: 400;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .dropdown-item:hover {
            background-color: rgba(var(--golden-fork-accent-teal), 0.2);
            color: var(--golden-fork-accent-teal);
        }

        .dropdown-divider {
            border-top-color: rgba(var(--golden-fork-text-light), 0.15);
        }

        /* Hero Section (Redesigned) */
        .hero-section {
            padding: 100px 0; /* More padding */
            min-height: 80vh; /* Takes up more vertical space */
            display: flex;
            align-items: center; /* Vertically center content */
            text-align: center;
            background-color: var(--golden-fork-translucent-dark); /* Overlay */
             box-shadow: 0 5px 15px rgba(0,0,0,0.3);
             position: relative; /* Needed for potential pseudo-elements or patterns */
        }

         /* Optional: Add a subtle pattern overlay to the hero */
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: radial-gradient(rgba(var(--golden-fork-accent-gold), 0.1) 1px, transparent 1px); /* Subtle dot pattern */
            background-size: 20px 20px;
            z-index: 0;
            opacity: 0.5;
        }

         .hero-content {
             position: relative; /* Position above pattern */
             z-index: 1;
             max-width: 900px;
             margin: 0 auto;
             padding: 0 15px;
         }

         .hero-section h1 {
             color: var(--golden-fork-accent-gold);
             font-size: 3.8rem; /* Larger heading */
             margin-bottom: 25px;
              font-weight: 900;
         }

         .hero-section p {
             color: rgba(var(--golden-fork-text-light), 0.95);
             font-size: 1.4rem;
             margin-bottom: 40px;
             font-weight: 300; /* Lighter weight */
         }


         /* Search Bar Section (New Position) */
        .search-bar-section {
             padding: 40px 0;
             background-color: var(--golden-fork-light);
             box-shadow: 0 5px 10px rgba(0,0,0,0.1);
             color: var(--golden-fork-text-dark);
             text-align: center;
        }

         .search-bar-section h2 {
             color: var(--golden-fork-primary-dark); /* Navy Title */
             margin-bottom: 30px;
             font-size: 2rem;
         }

        .search-bar-container {
            background-color: #fff; /* White background */
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            max-width: 700px;
            margin: 0 auto; /* Center */
            border: 1px solid rgba(var(--golden-fork-primary-dark), 0.1);
        }

        .search-bar-container .form-control {
            border: none;
            box-shadow: none !important;
             font-size: 1.1rem;
             color: var(--golden-fork-text-dark);
        }

         .search-bar-section .btn-warning { /* Styling Bootstrap's .btn-warning */
             background-color: var(--golden-fork-accent-teal);
             border-color: var(--golden-fork-accent-teal);
             color: var(--golden-fork-text-light);
             font-weight: 700;
             transition: background-color 0.3s ease, border-color 0.3s ease;
         }

         .search-bar-section .btn-warning:hover {
             background-color: darken(var(--golden-fork-accent-teal), 10%);
             border-color: darken(var(--golden-fork-accent-teal), 10%);
         }


        /* Featured Categories Section */
        .featured-categories-section {
            padding: 60px 0;
             background-color: var(--golden-fork-secondary-dark); /* Darker Blue-Gray background */
             color: var(--golden-fork-text-light); /* White text */
             box-shadow: 0 5px 10px rgba(0,0,0,0.2);
        }

        .featured-categories-section .section-title {
             color: var(--golden-fork-accent-gold);
        }

        .category-card {
             text-align: center;
             margin-bottom: 30px;
             text-decoration: none;
             color: rgba(var(--golden-fork-text-light), 0.9); /* Slightly transparent white */
             transition: transform 0.3s ease, color 0.3s ease;
             padding: 15px 0; /* Add padding */
             border-radius: 8px;
             background-color: rgba(var(--golden-fork-text-light), 0.05); /* Subtle background */
             border: 1px solid rgba(var(--golden-fork-text-light), 0.1);
        }

        .category-card:hover {
             transform: translateY(-8px); /* More lift */
             color: var(--golden-fork-accent-teal);
             background-color: rgba(var(--golden-fork-text-light), 0.1); /* More visible background on hover */
             box-shadow: 0 5px 10px rgba(0,0,0,0.2);
        }

        .category-card i {
             font-size: 3.8rem; /* Larger icons */
             color: var(--golden-fork-accent-gold);
             margin-bottom: 15px;
             transition: color 0.3s ease;
        }

         .category-card:hover i {
             color: var(--golden-fork-accent-teal);
         }


        .category-card h4 {
             font-size: 1.4rem;
             font-weight: 600;
        }


        /* Food Listings Section */
        .food-listings-section {
            padding: 60px 0;
             background-color: var(--golden-fork-primary-dark); /* Primary Dark background */
            box-shadow: 0 -5px 10px rgba(0,0,0,0.3);
        }

        .food-listings-section .section-title {
             color: var(--golden-fork-accent-gold);
        }


        .food-cards-container {
            display: flex;
            overflow-x: auto;
            padding-bottom: 20px;
            scrollbar-width: thin;
            scrollbar-color: var(--golden-fork-accent-teal) rgba(var(--golden-fork-text-light),0.1); /* Teal scrollbar thumb */
            padding-left: 15px;
            padding-right: 15px;
            justify-content: flex-start;
        }

        .food-cards-container::-webkit-scrollbar-thumb {
            background-color: var(--golden-fork-accent-teal);
        }


        .food-card-link {
             text-decoration: none;
             color: inherit;
             display: block;
             flex: 0 0 auto;
             width: 350px; /* Wider cards */
             margin-right: 35px; /* More space */
             transition: transform 0.4s ease-out, box-shadow 0.4s ease-out;
             border-radius: 15px;
             overflow: hidden;
             box-shadow: none;
             border: none;
             position: relative; /* Needed for pseudo-elements or overlays */
        }

        .food-card-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(var(--golden-fork-accent-teal), 0); /* Start transparent */
            transition: background-color 0.3s ease; /* Smooth transition for overlay */
             z-index: 1; /* Above card image/body */
        }

        .food-card-link:hover::before {
            background-color: rgba(var(--golden-fork-accent-teal), 0.1); /* Subtle teal overlay on hover */
        }


        .food-card-link:hover {
             transform: translateY(-15px); /* More pronounced lift */
             box-shadow: none; /* Shadow is on the card */
        }


        .food-card {
            background-color: var(--golden-fork-light);
            color: var(--golden-fork-text-dark);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 15px rgba(0,0,0,0.2); /* More prominent initial shadow */
             height: 100%;
             display: flex;
             flex-direction: column;
             border: 1px solid rgba(var(--golden-fork-secondary-dark), 0.1); /* Subtle border */
             transition: box-shadow 0.4s ease-out;
             position: relative; /* Needed for stacking context with link overlay */
             z-index: 0; /* Ensure card is below link overlay */
        }

         /* Add shadow to the card when its parent link is hovered */
        .food-card-link:hover .food-card {
             box-shadow: 0 20px 40px rgba(0,0,0,0.7); /* Darker, larger shadow on card hover */
        }


        .food-card img {
            width: 100%;
            height: 240px; /* Taller image */
            object-fit: cover;
             transition: transform 0.4s ease-out; /* Add image zoom transition */
        }

         .food-card-link:hover .food-card img {
             transform: scale(1.05); /* Subtle zoom on image hover */
         }


        .food-card-body {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
             position: relative; /* Position above link overlay */
             z-index: 2; /* Ensure body content is above the link overlay */
        }

        .food-card-title {
            font-size: 1.6rem; /* Larger title */
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--golden-fork-primary-dark);
        }

        .food-card-meta {
            font-size: 1rem;
            color: #555;
            margin-bottom: 15px;
        }

        .food-card-price {
            font-size: 1.4rem; /* Larger price */
            font-weight: 700;
            color: var(--golden-fork-accent-teal);
        }

        .food-card .badge {
             font-size: 0.95rem; /* Larger badge text */
             padding: 7px 14px; /* More padding */
             background-color: var(--golden-fork-accent-gold) !important;
             color: var(--golden-fork-text-dark) !important;
             font-weight: 700;
             margin-bottom: 10px !important; /* Ensure space below badge */
        }

         /* Spice Level Indicator Styling */
         .spice-level {
             color: var(--golden-fork-accent-red);
             font-size: 1.1rem; /* Slightly larger icons */
             margin-top: 8px; /* More space above */
         }

         .spice-level i {
             margin-right: 2px; /* Space between chili icons */
         }


        /* How it Works Section */
        .how-it-works-section {
            padding: 80px 0;
             background-color: var(--golden-fork-primary-dark);
            color: var(--golden-fork-text-light);
            text-align: center;
            box-shadow: 0 -5px 10px rgba(0,0,0,0.3);
             margin-top: 0;
        }

        .how-it-works-section .section-title {
             color: var(--golden-fork-accent-gold);
             margin-bottom: 60px;
        }

        .step {
            padding: 0 20px;
        }

        .step i {
            font-size: 4rem;
            color: var(--golden-fork-accent-teal);
            margin-bottom: 25px;
             transition: color 0.3s ease;
        }
         .step:hover i {
             color: var(--golden-fork-accent-gold);
         }


        .step h3 {
            font-size: 1.7rem;
            font-weight: 700;
            margin-bottom: 15px;
             color: var(--golden-fork-text-light);
        }

        .step p {
            font-size: 1.05rem;
            color: rgba(var(--golden-fork-text-light), 0.8);
        }


         /* Footer Styling */
         .footer {
            width: 100%;
            background-color: rgba(var(--golden-fork-primary-dark), 0.98);
            color: #adb5bd;
            text-align: center;
            padding: 25px 0;
            font-size: 0.9rem;
            z-index: 1000;
            margin-top: 0;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
         }

         .footer .container {
             padding: 0 15px;
         }

         /* Icon spacing */
         .navbar-brand i {
             margin-left: 8px;
             font-size: 1.7rem;
         }

         .nav-link i {
              margin-right: 6px;
         }

        /* Live Chat Widget Placeholder */
         .chat-widget-placeholder {
             position: fixed;
             bottom: 20px;
             right: 20px;
             z-index: 1030; /* Below navbar, above footer */
         }

         .chat-widget-placeholder .btn {
             width: 60px;
             height: 60px;
             border-radius: 50%;
             font-size: 1.8rem;
             background-color: var(--golden-fork-accent-teal);
             border-color: var(--golden-fork-accent-teal);
             color: var(--golden-fork-text-light);
             box-shadow: 0 4px 10px rgba(0,0,0,0.3);
             transition: all 0.3s ease;
             display: flex;
             justify-content: center;
             align-items: center;
         }

         .chat-widget-placeholder .btn:hover {
             background-color: darken(var(--golden-fork-accent-teal), 10%);
             border-color: darken(var(--golden-fork-accent-teal), 10%);
             transform: scale(1.1) rotate(5deg); /* Subtle pulse and rotate */
             box-shadow: 0 6px 15px rgba(0,0,0,0.4);
         }


        /* Modal Styling */
        .login-register-modal .modal-content {
            background-color: rgba(var(--golden-fork-primary-dark), 0.99);
            color: var(--golden-fork-text-light);
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.4);
        }

        .login-register-modal .modal-header {
            border-bottom-color: rgba(var(--golden-fork-text-light), 0.15);
        }

         .login-register-modal .modal-footer {
            border-top-color: rgba(var(--golden-fork-text-light), 0.15);
         }

         .login-register-modal .btn-primary { /* Login button */
             background-color: var(--golden-fork-accent-gold);
             border-color: var(--golden-fork-accent-gold);
             color: var(--golden-fork-text-dark);
             font-weight: 700;
             transition: all 0.3s ease;
         }
          .login-register-modal .btn-primary:hover {
             background-color: darken(var(--golden-fork-accent-gold), 10%);
             border-color: darken(var(--golden-fork-accent-gold), 10%);
          }

         .login-register-modal .btn-success { /* Register button */
             background-color: var(--golden-fork-accent-teal);
             border-color: var(--golden-fork-accent-teal);
             color: var(--golden-fork-text-light);
             font-weight: 700;
              transition: all 0.3s ease;
         }
          .login-register-modal .btn-success:hover {
             background-color: darken(var(--golden-fork-accent-teal), 10%);
             border-color: darken(var(--golden-fork-accent-teal), 10%);
          }


        .text-shadow {
             text-shadow: 2px 2px 6px rgba(0,0,0,0.6);
        }

         .text-shadow-sm {
             text-shadow: 1px 1px 4px rgba(0,0,0,0.4);
         }

         


    </style>
</head>
<body>

    <!-- Announcement Bar -->
    <div class="announcement-bar">
        <span class="announcement-text">✨ Limited Time Offer! Enjoy Free Delivery on orders over $50! Order Now! ✨ Get amazing deals on your favorite meals! 🍕🍔🍣 Fresh ingredients, delivered fast! 👨‍🍳🚚</span>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary-dark fixed-top">
      <div class="container-fluid">
        <a class="navbar-brand" href="#"><span style="color: var(--golden-fork-accent-teal);">Golden</span> Fork <i class="fas fa-utensils"></i></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item">
              <a class="nav-link active" aria-current="page" href="#"><i class="fas fa-home"></i> Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#how-it-works-section"><i class="fas fa-info-circle"></i> How it Works</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="our_services.php"><i class="fas fa-concierge-bell"></i> Our Services</a>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="customer_menu.php" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-book-open"></i> Menu & Price
              </a>
              <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                <li><a class="dropdown-item" href="customer_menu.php">Appetizers</a></li>
                <li><a class="dropdown-item" href="customer_menu.php">Main Courses</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="customer_menu.php">Today's Specials</a></li>
              </ul>
            </li>
             <li class="nav-item">
              <a class="nav-link" href="contact.php"><i class="fas fa-envelope"></i> Contact</a>
            </li>
             <li class="nav-item">
              <a class="nav-link btn btn-outline-light btn-sm ms-2 px-3" href="login.php"><i class="fas fa-sign-in-alt me-1"></i> Login</a>
            </li>
             <li class="nav-item">
              <a class="nav-link btn btn-warning btn-sm ms-2 px-3" href="register.php"><i class="fas fa-user-plus me-1"></i> Sign Up</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <!-- Hero Section (Visually Striking Showcase) -->
    <section class="hero-section">
        <div class="container position-relative z-1"> <!-- z-index to be above pattern -->
            <div class="row justify-content-center">
                 <div class="col-md-10">
                    <div class="hero-content text-shadow">
                        <h1>Your Culinary Journey Starts Here</h1>
                        <p>Discover delicious food from the best restaurants around you, delivered hot and fast!</p>
                        <!-- Maybe add a prominent "Order Now" button here -->
                         <a href="#search-bar-section" class="btn btn-accent-gold btn-lg mt-3 px-5"><i class="fas fa-search me-2"></i> Find Your Food</a>
                    </div>
                 </div>
            </div>
        </div>
    </section>

<!-- Food Listings Section -->
    <section class="food-listings-section container-fluid">
        <h2 class="section-title">Featured Dishes</h2>
        <div class="food-cards-container">

            <!-- --- Manually Added Food Cards Start --- -->

            <a href="food_detail.php?id=1" class="food-card-link">
                <div class="food-card" data-food-id="1">
                    <!-- Replace src with the actual path to your image -->
                    <img src="pasta.jpg" class="card-img-top" alt="Pasta Mania">
                    <div class="food-card-body">
                        <span class="badge bg-warning text-dark mb-2">Tk 125 off Tk. 600+</span>
                        <h5 class="food-card-title">Pasta Mania</h5>
                        <p class="food-card-meta">Pasta &bull; 4.9 (1000+)</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="food-card-price">Tk 340</span>
                            <span class="food-card-meta"><i class="far fa-clock me-1"></i> 25-40 min</span>
                        </div>
                         <div class="spice-level">
                             <i class="fas fa-pepper-hot"></i>
                         </div>
                    </div>
                </div>
            </a>

            <a href="food_detail.php?id=2" class="food-card-link">
                <div class="food-card" data-food-id="2">
                     <!-- Replace src with the actual path to your image -->
                    <img src="burger.jpg" class="card-img-top" alt="Burger Xpress Deal">
                    <div class="food-card-body">
                        <span class="badge bg-warning text-dark mb-2">BOGO</span>
                        <h5 class="food-card-title">Burger Xpress Deal</h5>
                        <p class="food-card-meta">Burgers &bull; 4.8 (20000+)</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="food-card-price">Tk 560</span>
                            <span class="food-card-meta"><i class="far fa-clock me-1"></i> 20-35 min</span>
                        </div>
                         <div class="spice-level">
                             <i class="fas fa-pepper-hot"></i><i class="fas fa-pepper-hot"></i>
                         </div>
                    </div>
                </div>
            </a>

             <a href="food_detail.php?id=3" class="food-card-link">
                <div class="food-card" data-food-id="3">
                     <!-- Replace src with the actual path to your image -->
                    <img src="madchef.jpg" class="card-img-top" alt="Madchef - Banani">
                    <div class="food-card-body">
                        <span class="badge bg-warning text-dark mb-2">NEW</span>
                        <h5 class="food-card-title">Madchef - Banani</h5>
                        <p class="food-card-meta">Flavours That Flow &bull; 4.9 (10000+)</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="food-card-price">Tk 610</span>
                            <span class="food-card-meta"><i class="far fa-clock me-1"></i> 25-40 min</span>
                        </div>
                         <div class="spice-level">
                             <i class="fas fa-pepper-hot"></i><i class="fas fa-pepper-hot"></i><i class="fas fa-pepper-hot"></i>
                         </div>
                    </div>
                </div>
            </a>

             <a href="food_detail.php?id=4" class="food-card-link">
                <div class="food-card" data-food-id="4">
                     <!-- Replace src with the actual path to your image -->
                    <img src="abacus.webp" class="card-img-top" alt="Abacus Restaurant">
                    <div class="food-card-body">
                        <span class="badge bg-warning text-dark mb-2">Ad</span>
                        <h5 class="food-card-title">Abacus Restaurant</h5>
                        <p class="food-card-meta">Curry &bull; 4.6 (500+)</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="food-card-price">Tk 920</span>
                            <span class="food-card-meta"><i class="far fa-clock me-1"></i> 40-60 min</span>
                        </div>
                         <div class="spice-level">
                             <i class="fas fa-pepper-hot"></i><i class="fas fa-pepper-hot"></i><i class="fas fa-pepper-hot"></i><i class="fas fa-pepper-hot"></i>
                         </div>
                    </div>
                </div>
            </a>

             <a href="food_detail.php?id=5" class="food-card-link">
                <div class="food-card" data-food-id="5">
                     <!-- Replace src with the actual path to your image -->
                    <img src="chatime.jpg" class="card-img-top" alt="Chatime - Banani">
                    <div class="food-card-body">
                        <span class="badge bg-warning text-dark mb-2">Tk 125 off Tk. 550+</span>
                        <h5 class="food-card-title">Chatime - Banani</h5>
                        <p class="food-card-meta">Beverage &bull; 4.9 (550+)</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="food-card-price">Tk 560</span>
                            <span class="food-card-meta"><i class="far fa-clock me-1"></i> 20-35 min</span>
                        </div>
                         <div class="spice-level">
                             <!-- Not spicy -->
                         </div>
                    </div>
                </div>
            </a>

            <a href="food_detail.php?id=6" class="food-card-link">
                <div class="food-card" data-food-id="6">
                     <!-- Replace src with the actual path to your image -->
                    <img src="pizza.jpg" class="card-img-top" alt="Pizza Treat">
                    <div class="food-card-body">
                        <span class="badge bg-warning text-dark mb-2">Discount</span>
                        <h5 class="food-card-title">Pizza Treat</h5>
                        <p class="food-card-meta">Pizza &bull; 4.7 (800+)</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="food-card-price">Tk 700</span>
                            <span class="food-card-meta"><i class="far fa-clock me-1"></i> 30-45 min</span>
                        </div>
                         <div class="spice-level">
                             <i class="fas fa-pepper-hot"></i><i class="fas fa-pepper-hot"></i>
                         </div>
                    </div>
                </div>
            </a>

            <a href="food_detail.php?id=7" class="food-card-link">
                <div class="food-card" data-food-id="7">
                     <!-- Replace src with the actual path to your image -->
                    <img src="sushi.jpg" class="card-img-top" alt="Sushi Roll Combo">
                    <div class="food-card-body">
                        <span class="badge bg-warning text-dark mb-2">Popular</span>
                        <h5 class="food-card-title">Sushi Roll Combo</h5>
                        <p class="food-card-meta">Sushi &bull; 4.9 (1200+)</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="food-card-price">Tk 1100</span>
                            <span class="food-card-meta"><i class="far fa-clock me-1"></i> 35-50 min</span>
                        </div>
                         <div class="spice-level">
                             <!-- Not spicy -->
                         </div>
                    </div>
                </div>
            </a>

            </div>
    </section>


    <!-- Featured Categories Section -->
    <section class="featured-categories-section">
        <div class="container">
            <h2 class="section-title text-dark">Explore By Category</h2>
            <div class="row">
                <div class="col-6 col-sm-4 col-md-2">
                    <a href="#" class="category-card">
                         <i class="fas fa-pizza-slice"></i>
                         <h4>Pizza</h4>
                    </a>
                </div>
                 <div class="col-6 col-sm-4 col-md-2">
                    <a href="#" class="category-card">
                         <i class="fas fa-hamburger"></i>
                         <h4>Burgers</h4>
                    </a>
                </div>
                 <div class="col-6 col-sm-4 col-md-2">
                    <a href="#" class="category-card">
                         <i class="fas fa-bowl-food"></i>
                         <h4>Curries</h4>
                    </a>
                </div>
                 <div class="col-6 col-sm-4 col-md-2">
                    <a href="#" class="category-card">
                         <i class="fas fa-fish-cooked"></i>
                         <h4>Seafood</h4>
                    </a>
                </div>
                 <div class="col-6 col-sm-4 col-md-2">
                    <a href="#" class="category-card">
                         <i class="fas fa-seedling"></i>
                         <h4>Vegetarian</h4>
                    </a>
                </div>
                 <div class="col-6 col-sm-4 col-md-2">
                    <a href="#" class="category-card">
                         <i class="fas fa-ice-cream"></i>
                         <h4>Desserts</h4>
                    </a>
                </div>
            </div>
        </div>
    </section>

        <!-- Search Bar Section (Moved and Redesigned) -->
    <section class="search-bar-section" id="search-bar-section">
        <div class="container">
             <h2>Where Should We Deliver?</h2>
            <div class="search-bar-container d-flex flex-column flex-md-row align-items-center">
                <div class="input-group flex-grow-1 me-md-2 mb-3 mb-md-0">
                    <span class="input-group-text bg-white border-0"><i class="fas fa-map-marker-alt text-muted"></i></span>
                    <input type="text" class="form-control form-control-lg" placeholder="Enter your address">
                    <button class="btn btn-outline-secondary border-0" type="button"><i class="fas fa-location-arrow text-muted"></i> Locate me</button>
                </div>
                <button class="btn btn-warning btn-lg px-5" type="button"><i class="fas fa-search"></i> Find food</button>
            </div>
             <p class="text-center mt-3 text-muted"><small>Service available in select areas.</small></p>
        </div>
    </section>

    

     <!-- Testimonials Section (New Feature) -->
     <section class="testimonials-section">
         <div class="container">
             <h2 class="section-title text-center mb-5">What Our Customers Say</h2>
             <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
                 <div class="carousel-inner">
                     <div class="carousel-item active">
                         <div class="d-flex flex-column align-items-center text-center">
                             <i class="fas fa-quote-left fa-2x mb-3 text-muted"></i>
                             <p class="lead w-75 mx-auto">"Golden Fork changed the way I order food! Fast delivery, delicious options, and super easy to use. Highly recommended!"</p>
                             <div class="d-flex align-items-center mt-3">
                                 <!-- Add placeholder for customer image if desired -->
                                 <div class="rounded-circle bg-light text-dark d-flex justify-content-center align-items-center me-3" style="width: 60px; height: 60px;"><i class="fas fa-user fa-lg"></i></div>
                                 <div>
                                     <h5 class="mb-0" style="color: var(--golden-fork-accent-teal);">Jane Doe</h5>
                                     <p class="text-muted mb-0"><small>Happy Customer</small></p>
                                 </div>
                             </div>
                         </div>
                     </div>
                     <div class="carousel-item">
                         <div class="d-flex flex-column align-items-center text-center ">
                             <i class="fas fa-quote-left fa-2x mb-3 text-muted"></i>
                             <p class="lead w-75 mx-auto">"The variety of food is amazing, and the delivery is always on time. The app is very intuitive. My go-to for food delivery now!"</p>
                             <div class="d-flex align-items-center mt-3">
                                  <div class="rounded-circle bg-light text-dark d-flex justify-content-center align-items-center me-3" style="width: 60px; height: 60px;"><i class="fas fa-user fa-lg"></i></div>
                                 <div>
                                     <h5 class="mb-0" style="color: var(--golden-fork-accent-teal);">John Smith</h5>
                                     <p class="text-muted mb-0"><small>Food Lover</small></p>
                                 </div>
                             </div>
                         </div>
                     </div>
                      <div class="carousel-item">
                         <div class="d-flex flex-column align-items-center text-center">
                             <i class="fas fa-quote-left fa-2x mb-3 text-muted"></i>
                             <p class="lead w-75 mx-auto">"Finding vegetarian options is easy with Golden Fork! The filters are very helpful, and the food quality is consistently excellent."</p>
                             <div class="d-flex align-items-center mt-3">
                                  <div class="rounded-circle bg-light text-dark d-flex justify-content-center align-items-center me-3" style="width: 60px; height: 60px;"><i class="fas fa-user fa-lg"></i></div>
                                 <div>
                                     <h5 class="mb-0" style="color: var(--golden-fork-accent-teal);">Sarah Lee</h5>
                                     <p class="text-muted mb-0"><small>Vegetarian User</small></p>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>
                 <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
                     <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                     <span class="visually-hidden">Previous</span>
                 </button>
                 <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
                     <span class="carousel-control-next-icon" aria-hidden="true"></span>
                     <span class="visually-hidden">Next</span>
                 </button>
             </div>
         </div>
     </section>


    <!-- How it Works Section -->
    <section class="how-it-works-section" id="how-it-works-section">
        <div class="container">
            <h2 class="section-title">How Golden Fork Works</h2>
            <div class="row">
                <div class="col-md-4 step">
                    <i class="fas fa-map-marker-alt"></i>
                    <h3>Enter Your Location</h3>
                    <p>Tell us where you are so we can find restaurants near you.</p>
                </div>
                <div class="col-md-4 step">
                    <i class="fas fa-utensils"></i>
                    <h3>Choose Your Food</h3>
                    <p>Browse menus from a wide variety of restaurants and dishes.</p>
                </div>
                <div class="col-md-4 step">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Place Your Order</h3>
                    <p>Add items to your cart and complete your order securely.</p>
                </div>
                <div class="col-md-4 offset-md-2 step">
                    <i class="fas fa-motorcycle"></i>
                    <h3>Wait for Delivery</h3>
                    <p>Track your order in real-time as it comes to you.</p>
                </div>
                 <div class="col-md-4 step">
                    <i class="fas fa-smile-beam"></i>
                    <h3>Enjoy Your Meal!</h3>
                    <p>Receive your delicious food and enjoy every bite.</p>
                </div>
            </div>
        </div>
    </section>


    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            &copy; 2025 Golden Fork Restaurant. All rights reserved.
        </div>
    </footer>

     <!-- Live Chat Widget Placeholder -->
    <div class="chat-widget-placeholder">
        <button class="btn btn-circle"><i class="fas fa-comment-dots"></i></button>
    </div>


    <!-- Login/Register Modal -->
    <div class="modal fade login-register-modal" id="loginRegisterModal" tabindex="-1" aria-labelledby="loginRegisterModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="loginRegisterModalLabel">Login or Register to Order</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center">
            <p>You need to be logged in or registered to add items to your cart and place an order.</p>
            <i class="fas fa-user-lock fa-3x mb-3 text-warning"></i>
          </div>
          <div class="modal-footer justify-content-center">
            <a href="login.php" class="btn btn-primary"><i class="fas fa-sign-in-alt me-2"></i> Login</a>
            <a href="register.php" class="btn btn-success"><i class="fas fa-user-plus me-2"></i> Register</a>
          </div>
        </div>
      </div>
    </div>


    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // PHP variable to check login status (simulated here for design)
        const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;

        document.addEventListener('DOMContentLoaded', function () {
            // Select the links that wrap the food cards
            const foodCardLinks = document.querySelectorAll('.food-card-link');
            const loginRegisterModal = new bootstrap.Modal(document.getElementById('loginRegisterModal'));

            foodCardLinks.forEach(link => {
                link.addEventListener('click', function(event) {
                    if (!isLoggedIn) {
                        event.preventDefault(); // Prevent the browser from navigating to the link's href
                        loginRegisterModal.show(); // Show the modal
                    } else {
                        // If logged in (isLoggedIn is true), the default link behavior will happen,
                        // navigating the user to the href (e.g., food_detail.php).
                        // If you wanted clicking the card to add to cart instead of navigating,
                        // you would ALSO call event.preventDefault() here and add your add-to-cart logic.
                        console.log('User is logged in, proceeding to item detail or adding to cart.');
                    }
                });
            });

             // Custom JavaScript for hover dropdown (optional, CSS is used above)
        });
    </script>
</body>
</html>