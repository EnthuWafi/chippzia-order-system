function initialize(url) {
    const table = $("#datatable").DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/ms.json",
        },
        ajax: `${url}/user`,
        fixedColumns: {
            end: 1,
        },
        columns: [
            {
                searchable: false,
                orderable: false,
                targets: 0,
            },
            { data: "username" },
            { data: "email" },
            {
                data: "group",
                render: function (data, type, row) {
                    if (data.includes("admin")) {
                        return `<span class="badge bg-primary">Pentadbir</span>`;
                    } else {
                        return `<span class="badge bg-secondary">Pengguna</span>`;
                    }
                },
                className: "dt-center",
                width: "10%",
            },
            {
                data: "created_at.date",
                render: function (data, type, row) {
                    return formatDate(data);
                },
            },
            {
                data: "updated_at.date",
                render: function (data, type, row) {
                    return formatDate(data);
                },
            },
            {
                defaultContent: `
                <div class="d-flex align-items-center gap-2">
                <button class="btn btn-primary btn-sm" name="update" title="Kemaskini"><i class="fa-solid fa-pen-to-square"></i></button>
                <button class="btn btn-danger btn-sm" name="delete" title="Padam"><i class="fa-solid fa-trash"></i></button>
                </div>`,
                width: "10%",
                searchable: false,
                sortable: false,
            },
        ],
        processing: true,
        buttons: [
            {
                extend: "colvis",
                columns: ":not(.noVis)",
                text: "Sorok / tunjuk ruangan",
            },
        ],
        columnDefs: [{ targets: "_all", defaultContent: "-" }],
        order: [[4, "desc"]],
        stateSave: true,
    });

    function addUserPromise() {
        const userButton = $("#userAdd");
        userButton.on("click", function () {
            return getPromise(`${url}/user/new`)
                .then((response) => {
                    addUser(response);
                })
                .catch((error) => {
                    console.error("Error adding user:", error);
                });
        });
    }

    function addUser(response) {
        const table = $("#datatable").DataTable();
        const token = $("#token");
        const form = assembleForm(response);

        //bootbox
        bootbox.dialog({
            title: "Tambah pengguna",
            message: form,
            size: "large",
            buttons: {
                cancel: {
                    label: "Tutup",
                    className: "btn-secondary",
                },
                ok: {
                    label: "Simpan",
                    className: "btn-primary",
                    callback: function () {
                        var form = new FormData(document.getElementById("form"));

                        var data = {};
                        for (const pair of form.entries()) {
                            data[pair[0]] = pair[1];
                        }

                        if (token == undefined) {
                            bootbox.alert("Token tidak ditemukan");
                            return;
                        }
                        const csrfToken = token.attr("name");
                        const csrfHash = token.attr("value");

                        $.ajax({
                            url: `${url}/user`,
                            type: "POST",
                            data: {
                                [csrfToken]: csrfHash,
                                ...data,
                            },
                            success: function (json) {
                                ajaxSuccessAlertandFunction(json, function () {
                                    table.ajax.reload();
                                });
                            },
                            error: function (error) {
                                ajaxErrorAlert(error);
                            },
                        });
                    },
                },
            },
        });
    }

    function editUserPromise(row) {
        const id = row.data().id;
        return getPromise(`${url}/user/${id}/edit`)
            .then((response) => {
                editUser(response, row);
            })
            .catch((error) => {
                console.error("Error updating user:", error);
            });
    }

    function editUser(response, row) {
        const id = row.data().id;
        const token = $("#token");
        const form = assembleForm(response);

        //bootbox
        bootbox.dialog({
            title: "Kemaskini pengguna",
            message: form,
            size: "large",
            buttons: {
                cancel: {
                    label: "Tutup",
                    className: "btn-secondary",
                },
                ok: {
                    label: "Simpan",
                    className: "btn-primary",
                    callback: function () {
                        var form = new FormData(document.getElementById("form"));

                        var data = {};
                        for (const pair of form.entries()) {
                            data[pair[0]] = pair[1];
                        }

                        if (token == undefined) {
                            bootbox.alert("Token tidak ditemukan");
                            return;
                        }
                        const csrfToken = token.attr("name");
                        const csrfHash = token.attr("value");

                        $.ajax({
                            url: `${url}/user/${id}`,
                            type: "PUT",
                            data: {
                                [csrfToken]: csrfHash,
                                ...data,
                            },
                            success: function (json) {
                                ajaxSuccessAlertandFunction(json, function () {
                                    row.data(json.data).draw();
                                });
                            },
                            error: function (error) {
                                ajaxErrorAlert(error);
                            },
                        });
                    },
                },
            },
        });
    }

    //event handling for delete
    function deleteUser(row) {
        const token = $("#token");
        const id = row.data().id;

        const csrfToken = token.attr("name");
        const csrfHash = token.attr("value");
        $.ajax({
            url: `${url}/user/${id}`,
            method: "DELETE",
            data: {
                [csrfToken]: csrfHash,
            },
            success: function (response) {
                ajaxSuccessAlertandFunction(response, () => {
                    row.remove().draw();
                });
            },
            error: function (error) {
                ajaxErrorAlert(error);
            },
        });
    }

    function addButtonClickEvents() {
        $("#datatable tbody").on("click", "button", function () {
            //get action
            var button = $(this);
            var actionType = button.prop("name");

            var actionTranslate = button.prop("title");

            // Set label and text
            var modalTitle = `Tindakan ${actionTranslate}`;
            var modalText = `Adakah anda pasti untuk meneruskan dengan <b>${actionTranslate}</b> barisan data?`;

            bootbox.confirm({
                size: "large",
                title: modalTitle,
                message: modalText,
                buttons: {
                    confirm: {
                        label: "Teruskan",
                        className: "btn-primary",
                    },
                    cancel: {
                        label: "Tidak",
                        className: "btn-secondary",
                    },
                },
                callback: function (result) {
                    var row = $("#datatable").DataTable().row($(button).parents("tr")); // Get row

                    if (result) {
                        if (actionType === "update") {
                            editUserPromise(row);
                        } else if (actionType === "delete") {
                            deleteUser(row);
                        }
                    }
                },
            });
        });
    }

    addButtonClickEvents();

    autoIncrement();

    addUserPromise();
}
