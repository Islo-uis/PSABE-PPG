const MAX_SIZE = 2 * 1024 * 1024;

document.getElementById("receipt-input").addEventListener("change", function (event) {
    const file = event.target.files[0]; // Get the selected file
    const preview = document.getElementById("gcash-preview");
    const photoinput = document.getElementById("receiptinput");

    // Check if a file is selected and it's an image
    if (file && (file.type === "image/jpeg" || file.type === "image/png")) {

        if (file && file.size > MAX_SIZE) {
            this.setCustomValidity("File must be 2 MB or smaller.");
        } else {
            this.setCustomValidity("");

        }
        // Create a URL for the image and set it as the preview source
        const reader = new FileReader();

        reader.onload = function (e) {
            preview.src = e.target.result; // Set image preview source
            preview.classList.remove("hidden"); // Show the preview

            photoinput.style.display = "none"; // Hide the upload prompt
        };

        reader.readAsDataURL(file); // Read the image as a data URL

    } else {
        preview.classList.add("hidden"); // Hide preview if not an image
        alert("Please upload a valid image file.");
        photoinput.style.display = "flex"; // Hide the upload prompt

    }
});
document.getElementById("profile-input").addEventListener("change", function (event) {
    const file = event.target.files[0]; // Get the selected file
    const preview = document.getElementById("profile-preview");
    const photoinput = document.getElementById("profileinput");

    // Check if a file is selected and it's an image
    if (file && (file.type === "image/jpeg" || file.type === "image/png")) {

        if (file && file.size > MAX_SIZE) {
            this.setCustomValidity("File must be 2 MB or smaller.");
        } else {
            this.setCustomValidity("");

        }
        // Create a URL for the image and set it as the preview source
        const reader = new FileReader();

        reader.onload = function (e) {
            preview.src = e.target.result; // Set image preview source
            preview.classList.remove("hidden"); // Show the preview

            photoinput.style.display = "none"; // Hide the upload prompt
        };

        reader.readAsDataURL(file); // Read the image as a data URL
    } else {
        preview.classList.add("hidden"); // Hide preview if not an image
        alert("Please upload a valid image file.");
        photoinput.style.display = "flex"; // Hide the upload prompt

    }
});

const form = document.getElementById("pr-form");
const emailAd = document.getElementById('email');
emailAd.addEventListener("input", function () {
    if (!emailAd.value.endsWith(".edu.ph")) {
        emailAd.setCustomValidity("Invalid Email Address! Use Your School Email!")
    }
    else {
        emailAd.setCustomValidity("")
    }

})

form.addEventListener("submit", function (event) {
    event.preventDefault(); // stop actual submission
    // ✅ built-in validation already ran at this point


    if (!emailAd.value.endsWith(".edu.ph")) {
        emailAd.setCustomValidity("Invalid Email Address! Use Your School Email!")
    }
    else {
        emailAd.setCustomValidity("")
    }

    if (form.checkValidity()) {
        // run your JS instead of submitting
        const formData = new FormData(form);
        $.ajax({
            url: "assets/ajax.php?action=submitRegistration",
            type: "POST",
            dataType: 'json',
            processData: false, // Required for FormData
            contentType: false,
            data: formData,
            success: function (response) {
                Swal.fire({
                    title: "grats bay registered naka!",
                    text: "",
                    icon: "success"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "../index.html"
                    }
                });
            },
            error: function (xhr, status, error) {
                console.error("AJAX error: " + status + " - " + error);
                console.log("Response text: " + xhr.responseText);
            }
        });
    } else {
        form.reportValidity();
    }
});