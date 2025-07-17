document.getElementById('show-password').addEventListener('click', function () {
    var passwordInput = document.getElementById('passwordInput');
    var icon = document.getElementById('show-password');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.textContent = 'visibility_off';
    } else {
        passwordInput.type = 'password';
        icon.textContent = 'visibility';
    }
});

function login() {
    let user = document.getElementById("usernameInput").value.trim();
    let pass = document.getElementById("passwordInput").value.trim();
    if (user && pass) {
        $.ajax({
            type: "POST",
            url: "assets/ajax.php?action=getAccount",
            data: {
                user: user,
                pass: pass
            },
            dataType: 'json',
            success: function (response) {
                let real = response.real;
                if (real) {
                    document.getElementById("wrongPass").style.display = "hidden"

                } else {
                    document.getElementById("wrongPass").style.display = "block"
                }
            },
            error: function (xhr, status, error) {
                console.error("An error occurred: " + error);
                console.log("Response text: " + xhr.responseText);
            }
        });
    }

}