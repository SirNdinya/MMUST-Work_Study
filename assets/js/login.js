$(document).ready(function () {
    // Step 1: Fetch username based on reg_number
    $("#reg_number").on("blur", function () {
        let reg_number = $(this).val().trim();

        $.ajax({
            url: "login_process.php",
            type: "POST",
            data: {
                validate_reg_number: true,
                reg_number: reg_number
            },
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    $("#username").val(response.username);
                } else {
                    $("#username").val(""); // Clear if not found
                    openModal(response.message);
                }
            },
            error: function () {
                openModal("Could not validate registration number.");
            }
        });
    });

    // Step 2: Verify password on form submission
    $("#login-form").on("submit", function (event) {
        event.preventDefault();

        let reg_number = $("#reg_number").val().trim();
        let password = $("#password").val().trim();

        if (reg_number !== "" && password !== "") {
            $.ajax({
                url: "login_process.php",
                type: "POST",
                data: {
                    validate_password: true,
                    reg_number: reg_number,
                    password: password
                },
                dataType: "json",
                success: function (response) {
                    if (response.status === "success") {
                        openModal(response.message);
                        setTimeout(function () {
                            window.location.href = response.redirect;
                        }, 1000);
                    } else {
                        openModal(response.message);
                    }
                },
                error: function () {
                    openModal("Login request failed.");
                }
            });
        } else {
            openModal("Please fill in all fields.");
        }
    });
});