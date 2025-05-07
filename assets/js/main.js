// Replace your current modal functions with:
window.modalFunctions = {
    openModal: function(message) {
        $("#modalMessage").text(message);
        $("#overlay, #errorModal").fadeIn();
    },
    closeModal: function() {
        $("#overlay, #errorModal").fadeOut();
    }
};

$(document).ready(function() {
    $("#overlay, #closeModal").click(modalFunctions.closeModal);
});