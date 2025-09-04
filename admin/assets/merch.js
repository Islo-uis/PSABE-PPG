document.querySelectorAll('input[name="sizeornot"]').forEach(radio => {
    radio.addEventListener('change', (event) => {
        const selected = event.target;
        if (selected.id === "yessize") {
            document.getElementById("nsize").style.display = "none";
            document.getElementById("size").style.display = "flex";
        }
        else if (selected.id === "nosize") {
            document.getElementById("nsize").style.display = "flex";
            document.getElementById("size").style.display = "none";
        }
        document.getElementById("am-qty").value = "";
        document.getElementById("am-sqty").value = "";
        document.getElementById("am-mqty").value = "";
        document.getElementById("am-lqty").value = "";
    });
});


document.querySelectorAll('input[name="em-sizeornot"]').forEach(radio => {
    radio.addEventListener('change', (event) => {
        const selected = event.target;
        if (selected.id === "em-yessize") {
            document.getElementById("em-nsize").style.display = "none";
            document.getElementById("em-size").style.display = "flex";

        }
        else if (selected.id === "em-nosize") {
            document.getElementById("em-nsize").style.display = "flex";
            document.getElementById("em-size").style.display = "none";

        }
        if (document.getElementById("em-hasSize").value == 1) {
            document.getElementById("em-qty").value = "";
        }
        else {
            document.getElementById("em-sqty").value = "";
            document.getElementById("em-mqty").value = "";
            document.getElementById("em-lqty").value = "";
        }
    });
});

document.getElementById('am-photo').onchange = e => {
    const f = e.target.files[0];
    if (!f) return;
    const r = new FileReader();
    r.onload = () => document.getElementById('am-card-preview').src = r.result;
    r.readAsDataURL(f);
};

document.getElementById('em-photo').onchange = e => {
    const f = e.target.files[0];
    if (!f) return;
    const r = new FileReader();
    r.onload = () => document.getElementById('em-card-preview').src = r.result;
    r.readAsDataURL(f);
};

function addMerch() {
    const form = document.getElementById("am-form");
    const formData = new FormData(form);

    $.ajax({
        url: "assets/ajax.php?action=addMerch",
        type: "POST",
        dataType: 'json',
        processData: false, // Required for FormData
        contentType: false,
        data: formData,
        success: function (response) {
            $("#am-modal").modal('hide');
            loadData();
        },
        error: function (xhr, status, error) {
            console.error("AJAX error: " + status + " - " + error);
            console.log("Response text: " + xhr.responseText);
        }
    });
}

function viewMerchData(id) {
    $.ajax({
        url: "assets/ajax.php?action=getMerchData",
        type: "POST",
        data: {
            id: id
        },
        dataType: 'json',
        success: function (response) {

            document.getElementById("em-name").value = response.name;
            document.getElementById("em-hasSize").value = response.hasSize;
            document.getElementById("em-id").value = id;
            document.getElementById("em-price").value = response.price;

            if (response.hasSize == 1) {
                document.getElementById("em-yessize").checked = true;
                document.getElementById('em-size').style.display = "flex";
                document.getElementById('em-nsize').style.display = "none";
                document.getElementById("em-sqty").value = response.qtyS;
                document.getElementById("em-mqty").value = response.qtyM;
                document.getElementById("em-lqty").value = response.qtyL;
            }
            else {
                document.getElementById("em-nosize").checked = true;
                document.getElementById('em-nsize').style.display = "flex";
                document.getElementById('em-size').style.display = "none";
                document.getElementById('em-qty').value = response.qty;
            }
            document.getElementById("em-card-preview").src = "../photos/merch/" + response.photo;

        },
        error: function (xhr, status, error) {
            console.error("AJAX error: " + status + " - " + error);
            console.log("Response text: " + xhr.responseText);
        }
    });
}

function editMerch() {

    const form = document.getElementById("em-form");
    const formData = new FormData(form);

    $.ajax({
        url: "assets/ajax.php?action=editMerch",
        type: "POST",
        dataType: 'json',
        processData: false, // Required for FormData
        contentType: false,
        data: formData,
        success: function (response) {
            $("#em-modal").modal('hide');
            loadData();
        },
        error: function (xhr, status, error) {
            console.error("AJAX error: " + status + " - " + error);
            console.log("Response text: " + xhr.responseText);
        }
    });
}

function changeMerchStatus(id, status) {
    $.ajax({
        url: "assets/ajax.php?action=changeMerchStatus",
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


$("#am-exit").click(function (e) {
    $("#am-modal").modal('hide');
});
$("#am-close").click(function (e) {
    $("#am-modal").modal('hide');
});