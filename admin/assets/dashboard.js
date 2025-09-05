
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


const headersMap = {
    announcement: ["Order", "ID", "Header", "Description", "Action"],
    events: ["ID", "Event Name", "Description", "Action"],
    merch: ["ID", "Item", "Price", "Stocks", "Stock (Small)", "Stock (Medium)", "Stock (Large)", "Action"]
};
var table = $("#myTable").DataTable();

$("#tableType").on("change", function () {
    let type = $(this).val();
    let headers = headersMap[type];

    // Destroy DataTable before changing headers
    table.destroy();

    // Rebuild table head
    let thead = $("#myTable thead tr");
    thead.empty();
    headers.forEach(h => {
        thead.append(`<th>${h}</th>`);
    });

    // Clear tbody as well (optional, depends on your data loading strategy)
    $("#myTable tbody").empty();

    // Reinitialize DataTable
    table = $("#myTable").DataTable({
        // if you want, define columns explicitly to match headers
        columns: headers.map(h => ({ title: h }))
    });


    loadData()
});


document.addEventListener('DOMContentLoaded', function () {
    loadData()
    // render("announcement")
    loadSched();
});


function loadSched() {
    $.ajax({
        url: "assets/ajax.php?action=getSched",
        type: "POST",
        dataType: 'json',
        success: function (response) {
            document.getElementById("sched-photo").src = "../photos/schedule/" + response.photo;
        },
        error: function (xhr, status, error) {
            console.error("AJAX error: " + status + " - " + error);
        }
    });
}

function changeSched() {
    const form = document.getElementById("sched-form");
    const formData = new FormData(form);

    $.ajax({
        url: "assets/ajax.php?action=changeSched",
        type: "POST",
        dataType: 'json',
        processData: false, // Required for FormData
        contentType: false,
        data: formData,
        success: function (response) {
            loadSched()
        },
        error: function (xhr, status, error) {
            console.error("AJAX error: " + status + " - " + error);
            console.log("Response text: " + xhr.responseText);
        }
    });
}

document.getElementById('addButton').addEventListener('click', () => {
    var category = document.getElementById("tableType").value;
    let modall = "";
    if (category == "announcement") {
        modall = "aa-modal";
    }
    else if (category == "merch") {
        modall = "am-modal";
    }
    else if (category == "events") {
        modall = "ae-modal";
    }
    const modalEl = document.getElementById(modall);
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
});

function loadData() {
    var category = document.getElementById("tableType").value;
    let ajax = "";
    if (category == "announcement") {
        ajax = "assets/ajax.php?action=getAnnouncements";
    }
    else if (category == "merch") {
        ajax = "assets/ajax.php?action=getMerch";
    }
    else if (category == "events") {
        ajax = "assets/ajax.php?action=getEvents";
    }
    $.ajax({
        url: ajax,
        type: "POST",
        dataType: 'json',
        success: function (response) {
            if (category == "announcement") {
                var data = response.announcements;
                // console.log(data)
                table.clear().draw();
                $.each(data, function (index, value) {
                    if (value.status == 0) {
                        table.row.add([
                            value.order,
                            value.id,
                            value.header,
                            value.desc,
                            `<button type="button" class="btn btn-success" data-bs-toggle="modal" onclick="viewAnnouncementData(${value.id})" data-bs-target="#ea-modal">Edit </button>
              <button type="button" class="btn btn-primary" onclick="changeAnnouncementOrder(${value.id}, 'up')">Up </button>
              <button type="button" class="btn btn-warning" onclick="changeAnnouncementOrder(${value.id}, 'down')">Down </button>
              <button type="button" class="btn btn-success" onclick="changeAnnouncementStatus(${value.id}, 1)">Enable </button>`

                        ]).draw(false);
                    }
                    else {
                        table.row.add([
                            value.order,
                            value.id,
                            value.header,
                            value.desc,
                            `<button type="button" class="btn btn-success" data-bs-toggle="modal" onclick="viewAnnouncementData(${value.id})" data-bs-target="#ea-modal">Edit </button>
              <button type="button" class="btn btn-primary" onclick="changeAnnouncementOrder(${value.id}, 'up')">Up </button>
              <button type="button" class="btn btn-warning" onclick="changeAnnouncementOrder(${value.id}, 'down')">Down </button>
              <button type="button" class="btn btn-danger" onclick="changeAnnouncementStatus(${value.id}, 0)">Disable </button>`

                        ]).draw(false);
                    }


                });
            }
            else if (category == "merch") {
                var data = response.merch;
                // console.log(data)
                table.clear().draw();
                $.each(data, function (index, value) {
                    const statusBtn = value.status === 0
                        ? `<button type="button" class="btn btn-success" onclick="changeMerchStatus(${value.id}, 1)">Enable</button>`
                        : `<button type="button" class="btn btn-danger" onclick="changeMerchStatus(${value.id}, 0)">Disable</button>`;
                    const qty = value.hasSize === 0 ? value.qty : (parseFloat(value.qtyS) + parseFloat(value.qtyM) + parseFloat(value.qtyL))

                    table.row.add([
                        value.id,
                        value.name,
                        value.price,
                        qty,
                        value.qtyS,
                        value.qtyM,
                        value.qtyL,
                        `
                  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#em-modal"
                    onclick="viewMerchData(${value.id})">
                    Edit
                  </button>
                  ${statusBtn}
                `
                    ]).draw(false);
                });
            }
            else if (category == "events") {
                var data = response.event;
                // console.log(data)
                table.clear().draw();
                $.each(data, function (index, value) {
                    const statusBtn = value.status === 0
                        ? `<button type="button" class="btn btn-success" onclick="changeEventStatus(${value.id}, 1)">Enable</button>`
                        : `<button type="button" class="btn btn-danger" onclick="changeEventStatus(${value.id}, 0)">Disable</button>`;

                    table.row.add([
                        value.id,
                        value.name,
                        value.desc,
                        `
                  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ee-modal"
                    onclick="viewEventData(${value.id})">
                    Edit
                  </button>
                  <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#es-modal"
                    onclick="viewEventSched(${value.id})">
                    View Schedule
                  </button>
                  ${statusBtn}
                `
                    ]).draw(false);
                });
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX error: " + status + " - " + error);
            console.log("Response text: " + xhr.responseText);
        }
    });

}
