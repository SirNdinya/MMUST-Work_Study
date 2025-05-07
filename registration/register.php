<?php  ?>
<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work-Study Program</title>
    <link rel="stylesheet" href="http://localhost/work_study/assets/css/styles.css">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script defer src="http://localhost/work_study/assets/js/main.js"></script>
    <script defer src = "http://localhost/work_study/assets/js/register.js"></script>

</head>
<div id="overlay" class="overlay"></div>
<div id="errorModal" class="modal">
    <div class="modal-content">
        <p id="modalMessage"></p>
        <button id="closeModal" class="modal-ok">OK</button>
    </div>
</div>
<body>
    <header class="registration-header">
        <div class="header-container">
            <div class="header-logo">
                <img src="http://localhost/work_study/assets/images/logo.png" alt="Masinde Muliro University Logo" class="logo-img">
                <h3>Work-Study Program</h3>
            </div>
            <nav class="login-nav">
                <ul>
                    <li><a href="http://localhost/work_study/login/login.php">Login</a></li>
                </ul>
            </nav>
        </div>
        
    </header>


    <main class="registration-main">
        <!-- Left Column -->
        <section class="registration-left " >
            <h2>Welcome to the Work-Study Program</h2>
            <p>Join us to gain valuable experience while studying at Masinde Muliro University.</p>
        </section>

        <!-- Right Column -->
        <section class="registration-right">
            <div class="info">
            <h2 >Register Now</h2>
            <p>Fill out the form below to apply for the program.</p>
            </div>

            <!-- Registration Form -->
              <form class="registration-form" action="register_process.php" method="POST" onsubmit="return validateForm()">
                <div class="form-group">
                    <label for="reg_number">Registration Number</label>
                    <input type="text" id="reg_number" name="reg_number" placeholder="Enter your registration number" required>
                </div>

                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" placeholder="Full name will be auto-filled" readonly required>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Choose a username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Create a password" required>
                    <div id="password_error" class="error-message"></div> <!-- Error message for password -->
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                    <div id="confirm_password_error" class="error-message"></div> <!-- Error message for confirm password -->
                </div>

                <div class="form-group">
                    <button type="submit" id="register_btn" >Register</button>
                </div>
             </form>
        </section>
        

<?php ?>