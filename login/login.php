<?php require_once __DIR__ . '/../includes/header.php'; ?>
<main class="login-main">
    <!-- Left Column -->
    <section class="login-left">
        <h2>Welcome to the Work-Study Program</h2>
        <p>Login to access your account</p>
    </section>

    <!-- Right Column -->
    <section class="login-right">
        <div class="info">
            <h2>Login to Apply!!</h2>
        </div>


        <form id="login-form" class="login-form" method="POST">
            <div class="form-group">
                <label for="reg_number">Registration Number</label>
                <input type="text" id="reg_number" name="reg_number" placeholder="Enter your registration number" required>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Username will be auto-filled" readonly required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                <div id="password_error" class="error-message"></div>
            </div>

            <div class="form-group">
                <button type="submit" id="login-btn">Login</button> 
            </div>
        </form>
    </section>

    <!-- Modal and Overlay -->
    <div id="overlay" class="overlay"></div>

    <div id="errorModal" class="modal">
        <div class="modal-content">
            <p id="modalMessage"></p>
            <button id="closeModal" class="modal-ok">OK</button>
        </div>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<script>
    function openModal(message) {
        $("#modalMessage").text(message);
        $("#errorModal").fadeIn();
        $("#overlay").fadeIn();
    }

    $("#closeModal").on("click", function () {
        $("#errorModal").fadeOut();
        $("#overlay").fadeOut();
    });
</script>

