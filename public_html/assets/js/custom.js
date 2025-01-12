//MAIN FUNCTIONS

function formatDate(data) {
    var date = new Date(data);
    const options = {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
    };
    return date.toLocaleDateString("ms", options);
}

function assembleForm(response, url) {
    let formHTML = `<form id='form' action='${url}' method='POST'>`;

    Object.keys(response.form).forEach((fieldName) => {
        const field = response.form[fieldName];

        // Generate HTML for the field
        let fieldHTML = `<div class="row mb-3">`;

        fieldHTML += `<label for="${fieldName}" class="col-sm-2 col-form-label">${field.label}</label>`;
        fieldHTML += `<div class="col-sm-10">`;

        if (field.type === "select") {
            fieldHTML += `<select id="${fieldName}" name="${fieldName}" class="form-select">`;

            field.options.forEach((option) => {
                    fieldHTML += `<option value="${option.value}">${option.label}</option>`;
            });

            fieldHTML += "</select>";

            if (field.value) {
                fieldHTML += `<script>$('select[name="${fieldName}"]').val("${field.value}");</script>`;
            }
        } else {
            fieldHTML += `<input id="${fieldName}" class="form-control" type="${field.type}" name="${fieldName}" value="${field.value}" placeholder="${field.placeholder}">`;
        }

        // Add line break for better spacing
        fieldHTML += "</div>";
        fieldHTML += "</div>";

        // Add field HTML to the appropriate section
        formHTML += fieldHTML;
    });

    formHTML += "</form>";
    return formHTML;
}

function getPromise(apiURL) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: apiURL,
            type: "GET",
            success: function (response) {
                resolve(response);
            },
            error: function (xhr, status, error) {
                reject(error);
            },
        });
    });
}

function extractBaseURL(url, layer = 1) {
    let baseURL = url.split("/").slice(0, -layer).join("/");
    return baseURL;
}
