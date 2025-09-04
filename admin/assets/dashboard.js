
// Initialize and run periodically
document.addEventListener('DOMContentLoaded', function () {
    checkIfLoggedOut()
    setInterval(checkIfLoggedOut, 5000)
});

function checkIfLoggedOut() {
    $.ajax({
        url: "assets/session.php?action=checkIfLoggedOut",
        type: "POST",
        dataType: 'json',
        success: function (response) {
            console.log(response)
            if (response) {
                window.location.href = "login.html";
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX error: " + status + " - " + error);
        }
    });
}

