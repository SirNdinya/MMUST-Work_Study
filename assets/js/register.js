
$(document).ready(function() {
    $("#overlay, #closeModal").click(modalFunctions.closeModal);
    $("#reg_number").on("blur", function() {
        let reg_number = $(this).val().trim();
    
        $.ajax({
            url: "register_process.php",
            type: "POST",
            data: { validate_reg_number: true, reg_number: reg_number },
            dataType: "json",
            success: function(response) {
                if (response.status === "success") {
                    $("#full_name").val(response.full_name);
                } 
            },
        });
    });

    // Registration Form submission
    $(".registration-form").submit(function(event) {
        event.preventDefault();

        let formData = {
            reg_number: $("#reg_number").val().trim(),
            username: $("#username").val().trim(),
            password: $("#password").val().trim(),
            confirm_password: $("#confirm_password").val().trim(),
        };

        // Proceed with form submission
        $.ajax({
            url: "register_process.php",
            type: "POST",
            data: formData,
            dataType: "json",
            success: function(response) {
                if (response.status === "success") {
                
                   alert(response.message);
                   window.location.href = "../login/login.php"; // or whatever your login page is

                } else {
                    modalFunctions.openModal(response.message);
                }
            }
        });
    });
});
