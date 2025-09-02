
document.getElementById('aa-photo').onchange = e => {
    const f = e.target.files[0];
    if (!f) return;
    const r = new FileReader();
    r.onload = () => document.getElementById('aa-card-preview').src = r.result;
    r.readAsDataURL(f);
};

document.getElementById('ea-photo').onchange = e => {
    const f = e.target.files[0];
    if (!f) return;
    const r = new FileReader();
    r.onload = () => document.getElementById('ea-card-preview').src = r.result;
    r.readAsDataURL(f);
};

function addAnnouncement() {
    const form = document.getElementById("aa-form");
    const formData = new FormData(form);

    $.ajax({
        url: "assets/ajax.php?action=addAnnouncement",
        type: "POST",
        dataType: 'json',
        processData: false, // Required for FormData
        contentType: false,
        data: formData,
        success: function (response) {
            window.location.href = "dashboard.html"
        },
        error: function (xhr, status, error) {
            console.error("AJAX error: " + status + " - " + error);
            console.log("Response text: " + xhr.responseText);
        }
    });
}

function viewAnnouncementData(id) {
    $.ajax({
        url: "assets/ajax.php?action=getAnnouncementData",
        type: "POST",
        data: {
            id: id
        },
        dataType: 'json',
        success: function (response) {
            document.getElementById("ea-header").value = response.header;
            document.getElementById("ea-desc").value = response.desc;
            document.getElementById("ea-id").value = id;
            document.getElementById("ea-card-preview").src = "../photos/announcement/" + response.photo;

        },
        error: function (xhr, status, error) {
            console.error("AJAX error: " + status + " - " + error);
        }
    });
}

function editAnnouncement() {
    let id = document.getElementById("ea-id").value;

    const form = document.getElementById("ea-form");
    const formData = new FormData(form);

    $.ajax({
        url: "assets/ajax.php?action=editAnnouncement",
        type: "POST",
        dataType: 'json',
        processData: false, // Required for FormData
        contentType: false,
        data: formData,
        success: function (response) {
            window.location.href = "dashboard.html"
        },
        error: function (xhr, status, error) {
            console.error("AJAX error: " + status + " - " + error);
            console.log("Response text: " + xhr.responseText);
        }
    });
}


$("#aa-exit").click(function (e) {
    $("#aa-modal").modal('hide');
});
$("#aa-close").click(function (e) {
    $("#aa-modal").modal('hide');
});
$("#ea-exit").click(function (e) {
    $("#ea-modal").modal('hide');
});
$("#ea-close").click(function (e) {
    $("#ea-modal").modal('hide');
});



function changeAnnouncementOrder(id, status) {
    console.log(id);
    console.log(status)
    $.ajax({
        url: "assets/ajax.php?action=changeAnnouncementOrder",
        type: "POST",
        data: {
            id: id,
            status: status
        },
        dataType: 'json',
        success: function (response) {
            loadData();
        },
        error: function (xhr, status, error) {
            console.error("AJAX error: " + status + " - " + error);
            console.log("Response text: " + xhr.responseText);
        }
    });
}
function changeAnnouncementStatus(id, status) {
    $.ajax({
        url: "assets/ajax.php?action=changeAnnouncementStatus",
        type: "POST",
        data: {
            id: id,
            status: status
        },
        dataType: 'json',
        success: function (response) {
            loadData();
        },
        error: function (xhr, status, error) {
            console.error("AJAX error: " + status + " - " + error);
            console.log("Response text: " + xhr.responseText);
        }
    });
}
