
document.getElementById('ae-photo').onchange = e => {
    const f = e.target.files[0];
    if (!f) return;
    const r = new FileReader();
    r.onload = () => document.getElementById('ae-card-preview').src = r.result;
    r.readAsDataURL(f);
};

document.getElementById('ee-photo').onchange = e => {
    const f = e.target.files[0];
    if (!f) return;
    const r = new FileReader();
    r.onload = () => document.getElementById('ee-card-preview').src = r.result;
    r.readAsDataURL(f);
};

function addEvent() {
    const form = document.getElementById("ae-form");
    const formData = new FormData(form);

    $.ajax({
        url: "assets/ajax.php?action=addEvent",
        type: "POST",
        dataType: 'json',
        processData: false, // Required for FormData
        contentType: false,
        data: formData,
        success: function (response) {
            $("#ae-modal").modal('hide');
            loadData()
        },
        error: function (xhr, status, error) {
            console.error("AJAX error: " + status + " - " + error);
            console.log("Response text: " + xhr.responseText);
        }
    });
}

function viewEventData(id) {
    $.ajax({
        url: "assets/ajax.php?action=getEventData",
        type: "POST",
        data: {
            id: id
        },
        dataType: 'json',
        success: function (response) {
            document.getElementById("ee-title").value = response.name;
            document.getElementById("ee-desc").value = response.desc;
            document.getElementById("ee-id").value = id;
            document.getElementById("ee-card-preview").src = "../photos/events/" + response.photo;

        },
        error: function (xhr, status, error) {
            console.error("AJAX error: " + status + " - " + error);
        }
    });
}

function editEvent() {

    const form = document.getElementById("ee-form");
    const formData = new FormData(form);

    $.ajax({
        url: "assets/ajax.php?action=editEvent",
        type: "POST",
        dataType: 'json',
        processData: false, // Required for FormData
        contentType: false,
        data: formData,
        success: function (response) {
            $("#ee-modal").modal('hide');
            loadData()
        },
        error: function (xhr, status, error) {
            console.error("AJAX error: " + status + " - " + error);
            console.log("Response text: " + xhr.responseText);
        }
    });
}


$("#ae-exit").click(function (e) {
    $("#ae-modal").modal('hide');
});
$("#ae-close").click(function (e) {
    $("#ae-modal").modal('hide');
});
$("#ee-exit").click(function (e) {
    $("#ee-modal").modal('hide');
});
$("#ee-close").click(function (e) {
    $("#ee-modal").modal('hide');
});
$("#vs-close").click(function (e) {
    $("#vs-modal").modal('hide');
});
$("#vs-exit").click(function (e) {
    $("#vs-modal").modal('hide');
});




function changeEventStatus(id, status) {
    $.ajax({
        url: "assets/ajax.php?action=changeEventStatus",
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

function viewEventSched(id) {
    $.ajax({
        url: "assets/ajax.php?action=getEventSched",
        type: "POST",
        data: {
            id: id
        },
        dataType: 'json',
        success: function (response) {
            let sched = response.sched;
            let name = response.name;
            console.log(sched)
            document.getElementById("vs-title").textContent = "View Schedule for "+name;
            document.getElementById("ee-desc").value = response.desc;
            document.getElementById("ee-id").value = id;
            document.getElementById("ee-card-preview").src = "../photos/events/" + response.photo;

        },
        error: function (xhr, status, error) {
            console.error("AJAX error: " + status + " - " + error);
        }
    });
}
