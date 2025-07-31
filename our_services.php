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
    <title>Our Services - Golden Fork</title> <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700;900&display=swap" rel="stylesheet">
     <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        /* Add smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        :root {
            /* New, Distinct Color Palette */
            --gf-primary-dark: #1A2B40; /* Darker Blue */
            --gf-secondary-dark: #263859; /* Muted Blue-Gray */
            --gf-accent-gold: #FFC300; /* Richer Gold */
            --gf-accent-teal: #00A896; /* Deep Teal */
            --gf-accent-burgundy: #900C3F; /* Burgundy Red */
            --gf-light: #EAECEE; /* Very Light Gray */
            --gf-text-dark: #222;
            --gf-text-light: #fff;
            --gf-translucent-dark: rgba(26, 43, 64, 0.95); /* Translucent Dark Blue */
            --gf-translucent-light-dark: rgba(26, 43, 64, 0.7); /* Lighter Translucent Dark */
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Roboto', sans-serif; /* New body font */
            background: url('pic6.AVIF') no-repeat center center fixed;
            background-size: cover;
            color: var(--gf-text-light);
            padding-top: 100px; /* Adjusted padding top for navbar */
            position: relative;
            min-height: 100vh;
            overflow-x: hidden;
            line-height: 1.6; /* Improved readability */
        }

        h1, h2, h3, h4, h5, h6 {
             font-family: 'Merriweather', serif; /* New heading font */
             font-weight: 900;
             color: var(--gf-accent-gold); /* Headings are gold by default */
             text-shadow: 1px 1px 3px rgba(0,0,0,0.3); /* Consistent shadow */
        }
         h2.section-title {
             color: var(--gf-accent-gold); /* Ensure section titles are gold */
             font-size: 2.8rem; /* Slightly larger section title */
         }


        /* Announcement Bar Styling */
        .announcement-bar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: linear-gradient(to right, var(--gf-accent-gold), var(--gf-accent-burgundy)); /* Gold to Burgundy Gradient */
            color: var(--gf-text-dark);
            padding: 8px 0;
            text-align: center;
            z-index: 1050;
            overflow: hidden;
            white-space: nowrap;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
            font-weight: 700;
            text-transform: uppercase;
        }

        .announcement-text {
            display: inline-block;
            padding-left: 100%;
            animation: scroll-left 45s linear infinite; /* Slower animation */
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
            background-color: var(--gf-primary-dark) !important; /* Dark Blue */
        }

        .navbar-brand {
             font-family: 'Playfair Display', serif; /* Use heading font for brand */
            font-weight: 900;
            font-size: 2.2rem; /* Larger brand */
            color: var(--gf-accent-gold) !important;
            text-shadow: 1px 1px 4px rgba(0,0,0,0.4);
        }

         .navbar-brand i {
              color: var(--gf-accent-teal); /* Teal icon in brand */
              margin-left: 8px;
              font-size: 1.8rem; /* Larger icon */
         }


         .navbar-nav .nav-link {
            font-weight: 500;
            transition: color 0.3s ease, transform 0.2s ease;
            color: rgba(var(--gf-text-light), 0.9) !important;
             padding: 0.5rem 1rem;
         }

        .navbar-nav .nav-link:hover {
             color: var(--gf-accent-teal) !important;
             transform: translateY(-2px);
        }

        /* Custom CSS for Hover Dropdown */
        .dropdown:hover > .dropdown-menu {
            display: block;
            margin-top: 0;
        }

        .dropdown-menu {
            background-color: rgba(var(--gf-secondary-dark), 0.98); /* Muted Blue-Gray */
            border: none;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.4);
            animation: fadeIn 0.3s ease-out;
        }

        .dropdown-item {
            color: rgba(var(--gf-text-light), 0.9);
            font-weight: 400;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .dropdown-item:hover {
            background-color: rgba(var(--gf-accent-teal), 0.2);
            color: var(--gf-accent-teal);
        }

        .dropdown-divider {
            border-top-color: rgba(var(--gf-text-light), 0.15);
        }

        /* Page Specific Sections */
        .page-header-section {
            padding: 80px 0;
            text-align: center;
            background-color: var(--gf-translucent-light-dark); /* Lighter Translucent Dark overlay */
             box-shadow: 0 5px 15px rgba(0,0,0,0.3);
             position: relative;
             z-index: 1;
        }

         .page-header-section h1 {
             color: var(--gf-accent-gold);
             font-size: 3.2rem;
             margin-bottom: 15px;
         }

         .page-header-section p {
             font-size: 1.3rem;
             font-weight: 400;
             color: rgba(var(--gf-text-light), 0.9);
         }


        .content-section {
            padding: 80px 0;
            background-color: var(--gf-primary-dark); /* Dark Blue */
            color: var(--gf-text-light);
            position: relative;
            z-index: 0;
        }

         .content-section.light-bg {
             background-color: var(--gf-light); /* Light Gray */
             color: var(--gf-text-dark);
             box-shadow: 0 5px 10px rgba(0,0,0,0.1);
         }

         .content-section .section-title {
             text-align: center;
             margin-bottom: 50px;
         }

         .content-section.light-bg .section-title {
             color: var(--gf-primary-dark); /* Navy title on light background */
         }


         /* Service Cards Styling */
         .service-card {
             background-color: var(--gf-secondary-dark);
             color: var(--gf-text-light);
             border-radius: 12px;
             padding: 30px;
             text-align: center;
             margin-bottom: 30px;
             box-shadow: 0 4px 10px rgba(0,0,0,0.2);
             transition: transform 0.3s ease, box-shadow 0.3s ease;
         }

          .content-section.light-bg .service-card {
             background-color: #fff;
             color: var(--gf-text-dark);
             border: 1px solid rgba(var(--gf-primary-dark), 0.1);
          }


          .service-card:hover {
             transform: translateY(-8px);
             box-shadow: 0 8px 15px rgba(0,0,0,0.3);
          }

          .service-card i {
             font-size: 4rem;
             color: var(--gf-accent-teal);
             margin-bottom: 20px;
             transition: color 0.3s ease;
          }

           .service-card:hover i {
             color: var(--gf-accent-gold);
           }

           .service-card h3 {
             font-size: 1.8rem;
             margin-bottom: 15px;
           }

            .content-section.light-bg .service-card h3 {
                color: var(--gf-primary-dark);
            }

           .service-card p {
             font-size: 1.05rem;
             color: rgba(var(--gf-text-light), 0.8);
           }

            .content-section.light-bg .service-card p {
                color: #555;
            }


        /* Diagonal Dividers */
        .page-header-section::after,
        .content-section::after {
            content: '';
            position: absolute;
            bottom: -50px; /* Adjust based on desired overlap */
            left: 0;
            width: 100%;
            height: 100px; /* Height of the diagonal area */
            background-color: var(--gf-primary-dark); /* Default to primary dark */
            transform: skewY(-3deg); /* Create the diagonal angle */
            transform-origin: top left;
            z-index: -1; /* Place behind the content */
             pointer-events: none;
        }
         /* Specific diagonal divider background colors */
         .page-header-section::after { background-color: var(--gf-primary-dark); } /* Matches the content-section below */
         .content-section.light-bg::after { background-color: var(--gf-primary-dark); } /* Matches the primary dark content-section */
         .content-section:not(.light-bg)::after { background-color: var(--gf-light); } /* Matches the light-bg content-section */


         /* Footer Styling (copied from index.php for consistency) */
         .footer {
            width: 100%;
            background-color: rgba(var(--gf-primary-dark), 0.98);
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

         /* Icon spacing (copied from index.php for consistency) */
         .navbar-brand i {
             margin-left: 8px;
             font-size: 1.7rem;
         }

         .nav-link i {
              margin-right: 6px;
         }

        /* Live Chat Widget Placeholder (copied from index.php for consistency) */
         .chat-widget-placeholder {
             position: fixed;
             bottom: 20px;
             right: 20px;
             z-index: 1050;
         }

         .chat-widget-placeholder .btn {
             width: 65px;
             height: 65px;
             border-radius: 50%;
             font-size: 2rem;
             background-color: var(--gf-accent-burgundy);
             border-color: var(--gf-accent-burgundy);
             color: var(--gf-text-light);
             box-shadow: 0 6px 12px rgba(0,0,0,0.4);
             transition: all 0.3s ease;
             display: flex;
             justify-content: center;
             align-items: center;
         }

         .chat-widget-placeholder .btn:hover {
             background-color: darken(var(--gf-accent-burgundy), 10%);
             border-color: darken(var(--gf-accent-burgundy), 10%);
             transform: scale(1.1);
         }


        /* Modal Styling (copied from index.php for consistency) */
        .login-register-modal .modal-content {
            background-color: rgba(var(--gf-primary-dark), 0.99);
            color: var(--gf-text-light);
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.4);
        }

        .login-register-modal .modal-header {
            border-bottom-color: rgba(var(--gf-text-light), 0.15);
        }

         .login-register-modal .modal-footer {
            border-top-color: rgba(var(--gf-text-light), 0.15);
         }

         .login-register-modal .btn-primary { /* Login button */
             background-color: var(--gf-accent-gold);
             border-color: var(--gf-accent-gold);
             color: var(--gf-text-dark);
             font-weight: 700;
             transition: all 0.3s ease;
         }
          .login-register-modal .btn-primary:hover {
             background-color: darken(var(--gf-accent-gold), 10%);
             border-color: darken(var(--gf-accent-gold), 10%);
          }

         .login-register-modal .btn-success { /* Register button */
             background-color: var(--gf-accent-teal);
             border-color: var(--gf-accent-teal);
             color: var(--gf-text-light);
             font-weight: 700;
              transition: all 0.3s ease;
         }
          .login-register-modal .btn-success:hover {
             background-color: darken(var(--gf-accent-teal), 10%);
             border-color: darken(var(--gf-accent-teal), 10%);
          }


        /* Text Shadows - Refined (copied from index.php for consistency) */
        .text-shadow-light {
             text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
        }
         .text-shadow-medium {
             text-shadow: 2px 2px 5px rgba(0,0,0,0.4);
         }
         .text-shadow-heavy {
             text-shadow: 3px 3px 8px rgba(0,0,0,0.6);
         }


    </style>
</head>
<body>

    <div class="announcement-bar">
        <span class="announcement-text">✨ Limited Time Offer! Enjoy Free Delivery on orders over $50! Order Now! ✨ Get amazing deals on your favorite meals! 🍕🍔🍣 Fresh ingredients, delivered fast! 👨‍🍳🚚 Exclusive online deals! ⏳</span>
    </div>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary-dark fixed-top">
      <div class="container-fluid">
        <a class="navbar-brand" href="index.php#hero-section"><span style="color: var(--gf-accent-teal);">Golden</span> Fork <i class="fas fa-utensils"></i></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item">
              <a class="nav-link" href="index.php#hero-section"><i class="fas fa-home"></i> Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link active" aria-current="page" href="#"><i class="fas fa-concierge-bell"></i> Our Services</a> </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-book-open"></i> Menu & More
              </a>
              <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                <li><a class="dropdown-item" href="menu.php">Full Menu</a></li> <li><a class="dropdown-item" href="index.php#food-listings-section">Popular Dishes (Home)</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#">Promotions</a></li>
              </ul>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="index.php#how-it-works-section"><i class="fas fa-question-circle"></i> How it Works (Home)</a>
            </li>
             <li class="nav-item">
              <a class="nav-link" href="contact.php"><i class="fas fa-envelope"></i> Contact</a> </li>
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

    <section class="page-header-section">
        <div class="container">
            <h1 class="text-shadow-medium">Discover Our Services</h1>
            <p class="text-shadow-light">Experience convenience and quality with Golden Fork's diverse offerings.</p>
        </div>
    </section>

    <section class="content-section">
        <div class="container">
            <h2 class="section-title text-center">What We Offer</h2>
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-4">
                    <div class="service-card">
                        <i class="fas fa-motorcycle"></i>
                        <h3>Fast Food Delivery</h3>
                        <p>Get your favorite dishes delivered hot and fresh right to your doorstep.</p>
                    </div>
                </div>
                 <div class="col-md-6 col-lg-4">
                    <div class="service-card">
                        <i class="fas fa-utensils"></i>
                        <h3>Premium Dine-in</h3>
                        <p>Enjoy an exquisite dining experience at our elegant restaurant location.</p>
                    </div>
                </div>
                 <div class="col-md-6 col-lg-4">
                    <div class="service-card">
                        <i class="fas fa-box-open"></i>
                        <h3>Convenient Pickup</h3>
                        <p>Order online or by phone and pick up your meal at your convenience.</p>
                    </div>
                </div>
                 <div class="col-md-6 col-lg-4">
                    <div class="service-card">
                        <i class="fas fa-suitcase-rolling"></i>
                        <h3>Corporate Catering</h3>
                        <p>Impress your clients and colleagues with our professional catering services.</p>
                    </div>
                </div>
                 <div class="col-md-6 col-lg-4">
                    <div class="service-card">
                        <i class="fas fa-concierge-bell"></i>
                        <h3>Event Planning</h3>
                        <p>Let us help make your special events memorable with custom menus.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

     <section class="content-section light-bg">
         <div class="container">
              <h2 class="section-title">Why Choose Us?</h2>
              <div class="row justify-content-center">
                  <div class="col-md-6 col-lg-4">
                      <div class="service-card">
                           <i class="fas fa-clipboard-check"></i>
                           <h3>Quality Ingredients</h3>
                           <p>We source only the freshest and finest ingredients for our dishes.</p>
                      </div>
                  </div>
                  <div class="col-md-6 col-lg-4">
                       <div class="service-card">
                           <i class="fas fa-hands-helping"></i>
                           <h3>Excellent Service</h3>
                           <p>Our dedicated team is here to provide you with the best service.</p>
                       </div>
                   </div>
                   <div class="col-md-6 col-lg-4">
                       <div class="service-card">
                           <i class="fas fa-smile-beam"></i>
                           <h3>Customer Satisfaction</h3>
                           <p>Your happiness is our priority in every order.</p>
                       </div>
                   </div>
              </div>
         </div>
     </section>


    <footer class="footer">
        <div class="container">
            &copy; 2025 Golden Fork Restaurant. All rights reserved.
        </div>
    </footer>

     <div class="chat-widget-placeholder">
        <button class="btn btn-circle"><i class="fas fa-comment-dots"></i></button>
    </div>


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


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // PHP variable to check login status (simulated here for design)
        const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;

        document.addEventListener('DOMContentLoaded', function () {
            // Select the links that wrap the food cards (empty on this page)
            // const foodCardLinks = document.querySelectorAll('.food-card-link');
            const loginRegisterModal = new bootstrap.Modal(document.getElementById('loginRegisterModal'));

            // Example: If a button on this page needs login, add event listener
            // document.querySelectorAll('.requires-login-btn').forEach(button => {
            //     button.addEventListener('click', function(event) {
            //         if (!isLoggedIn) {
            //             event.preventDefault();
            //             loginRegisterModal.show();
            //         }
            //     });
            // });
        });
    </script>
</body>
</html>