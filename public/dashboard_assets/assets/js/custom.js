// iziToast Default Configuration
iziToast.settings({
    timeout: 3500,
    position: "topRight",
    pauseOnHover: false,
    transitionIn: "bounceInLeft",
    transitionOut: "fadeOutRight",
    transitionInMobile: "bounceInLeft",
    transitionOutMobile: "fadeOutRight",
});

function getRandomColor() {
    var letters = '0123456789ABCDEF';
    var color = '#';
    for (var i = 0; i < 6; i++) {
      color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
  }

function formatNumber(angka) {
    return angka.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

$("input.format-number").keyup(function () {
    $(this).val(formatNumber($(this).val()));
});

function returnNumberFormat(input) {
    $(input).val(formatNumber($(input).val()));
}

function showLoad() {
    $(".pre-loader").addClass("is-load");
    $(".pre-loader").fadeIn();
}

function closeLoad() {
    $(".pre-loader").fadeOut();
    setTimeout(() => {
        $(".pre-loader").removeClass("is-load");
    }, 400);
}

function generateDynamicRow(tableElement) {
    const firstRowClone = $(tableElement).find('tbody tr.dynamic-row').eq(0).clone();
    resetAllValueInOneRow($(firstRowClone));

    $(tableElement).find('tbody').append(firstRowClone);
}

function deleteDynamicRow(deleteTriggerButton, tableElement) {
    const remainingRow = $(tableElement).find('tbody').children('tr.dynamic-row').length
    if (remainingRow > 1) {
        $(deleteTriggerButton).parents("tr").remove();
    } else {
        resetAllValueInOneRow($(tableElement).find('tbody tr.dynamic-row').eq(0));
    }
}

function filterUniqueObjectInArray(array, uniqueKey) {
    return [...new Map(array.map(item => [item[uniqueKey], item])).values()];
}

function resetAllValueInOneRow(rowElement) {
    $(rowElement).find('input').val('');
    $(rowElement).find('textarea').val('');
    $(rowElement).find('select').val('');
}

// Image Upload Preview
function previewFile(input, previewElementId) {
    var file = $(input).get(0).files[0];

    if (file) {
        var reader = new FileReader();

        reader.onload = function () {
            $("#" + previewElementId).attr("src", reader.result);
        };

        reader.readAsDataURL(file);
    }
}

function customAjaxWithGetMethod(url, urlParameter = {}) {
    const stringUrlParameter = Object.keys(urlParameter).map((key) => `${key}=${urlParameter[key]}`).join('&');
    const finalURL = `${url}?${stringUrlParameter}`;

    return $.ajax({
        method: 'GET',
        url: finalURL,
        success: function (result) {
            const { message } = result
            console.log('AJAX API Call: ', message);
        },
        error: function (error) {
            console.error(error)
        }
    })
}

function togglePassword(){
    var e = document.querySelectorAll(".form-password-toggle i");
    null != e && e.forEach((function (e) {
        e.addEventListener("click", (function (t) {
            t.preventDefault();
            var n = e.closest(".form-password-toggle"), i = n.querySelector("i"),
                o = n.querySelector("input");
            "text" === o.getAttribute("type") ? (o.setAttribute("type", "password"), i.classList.replace("bx-show", "bx-hide")) : "password" === o.getAttribute("type") && (o.setAttribute("type", "text"), i.classList.replace("bx-hide", "bx-show"))
        }))
    }))
}

$(document).ready(function () {
    togglePassword();
    closeLoad();

    $("input.format-number").each(function () {
        $(this).val(formatNumber($(this).val()));
    });

    $(".simple-dataTable").DataTable({
        oLanguage: {
            sEmptyTable: "Tidak ada data yang tersedia pada tabel ini",
        },
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
        },
        pageLength: 25,
        scrollX: true,
    });

    $(".select2").select2({
        selectionCssClass: "form-select",
    });

    // Dropdown
    $(".table-responsive").on("show.bs.dropdown", (event) => {
        $(".table-responsive").css("overflow", "inherit");
    });

    $(".table-responsive").on("hide.bs.dropdown", (event) => {
        $(".table-responsive").css("overflow", "auto");
    });

    const _token = $("meta[name=csrf-token]").attr("content");
    const _inputCsrfToken = `<input type="hidden" name="_token" value="${_token}">`;

    // Dynamic Modal
    const defaultModal = `
    <div class="modal fade" id="defaultModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" id="dialog-box" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="defaultModalTitle">Modal Title</h5>
              <button
                type="button"
                class="btn-close"
                data-bs-dismiss="modal"
                aria-label="Close"
              ></button>
            </div>
          </div>
        </div>
    </div>`;

    // Show Confirm Modal
    const deleteConfirmHtml = `
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal modal-dialog-centered" id="dialog-box" role="document">
            <div class="modal-content">
                <div class="modal-body text-center d-flex flex-column align-items-center justify-content-center" style="gap: 15px;">
                    <i class='bx bx-message-alt-error text-danger mt-3' style="font-size: 90px !important;"></i>
                    <h2 class="mb-0">Apakah anda yakin?</h2>
                    <p class="mb-0">Data yang sudah dihapus tidak dapat dipulihkan kembali. Apakah anda ingin melanjutkan?</p>
                </div>
                <div class="modal-footer d-flex align-items-center justify-content-center">
                    <form id="delete-form" class="d-flex align-items-center justify-content-center" style="gap: 10px;" method="POST">
                        <input type="hidden" name="_method" value="DELETE">
                        ${_inputCsrfToken}
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Tidak
                        </button>
                        <button type="submit" class="btn btn-danger">Ya</button>
                    </form>
                </div>
            </div>
        </div>
    </div>`;

    // Show Confirm Modal
    const confirmHtml = `
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal modal-dialog-centered" id="dialog-box" role="document">
            <div class="modal-content">
                <div class="modal-body text-center d-flex flex-column align-items-center justify-content-center" style="gap: 15px;">
                    <i class='bx bx-message-alt-error text-danger mt-3' style="font-size: 90px !important;"></i>
                    <h2 class="mb-0">Apakah anda yakin?</h2>
                    <p class="mb-0">Data yang sudah diproses tidak dapat diperbarui. Apakah anda ingin melanjutkan?</p>
                </div>
                <div class="modal-footer d-flex align-items-center justify-content-center">
                    <form id="confirm-form" class="d-flex align-items-center justify-content-center" style="gap: 10px;" method="POST">
                        ${_inputCsrfToken}
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Tidak
                        </button>
                        <button type="submit" class="btn btn-danger">Ya</button>
                    </form>
                </div>
            </div>
        </div>
    </div>`;

    $("body").append(defaultModal);
    $("body").append(deleteConfirmHtml);
    $("body").append(confirmHtml);

    const myModal = document.getElementById("defaultModal");

    // Remove Modal Body and Footer when Closed
    myModal.addEventListener("hidden.bs.modal", (event) => {
        $("#defaultModal .modal-body, #defaultModal .modal-footer").remove();
        $("#defaultModal #dialog-box").removeAttr("class");
        $("#defaultModal #dialog-box").attr(
            "class",
            "modal-dialog modal-lg modal-dialog-centered"
        );
    });

    $(document)
        .off("click", "[data-type='modal']")
        .on("click", "[data-type='modal']", (event) => {
            event.preventDefault();

            const sizeModal =
                event.target.getAttribute("data-size-modal") ?? "lg";
            const modalType = ["sm", "lg", "xl"];
            const sizeClassModal =
                sizeModal == "" || !modalType.includes(sizeModal)
                    ? "modal-lg"
                    : `modal-${sizeModal}`;
            const modalDialog = myModal.querySelector("#dialog-box");

            // Change Modal Size
            modalDialog.classList.remove("modal-lg");
            modalDialog.classList.add(sizeClassModal);

            // Change Modal Title
            const modalTitle = event.target.getAttribute("data-modal-title") ?? "Modal Title";
            myModal.querySelector("#defaultModalTitle").innerText = modalTitle;

            // Render Dynamic Modal
            $.ajax({
                method: "GET",
                url: event.currentTarget.getAttribute("href"),
                success: function (data) {
                    $("#defaultModal .modal-content").append(data);
                },
                error: function (error) {
                    console.error("Error: ", error);
                },
            }).then(() => {
                $("#defaultModal").modal("show");
            });
        });

    const deleteConfirmModal = document.getElementById("deleteConfirmModal");

    $(document)
        .off("click", ".delete-confirm")
        .on("click", ".delete-confirm", (event) => {
            event.preventDefault();

            const setContentPromise = new Promise((resolve, reject) => {
                try {
                    const deleteForm =
                        deleteConfirmModal.querySelector("#delete-form");
                    deleteForm.setAttribute(
                        "action",
                        event.currentTarget.getAttribute("href")
                    );
                    resolve(deleteConfirmModal);
                } catch (error) {
                    reject(error);
                }
            });

            setContentPromise
                .then((modal) => {
                    $(modal).modal("show");
                })
                .catch((error) => {
                    console.error("Error: ", error);
                });
        });

    const confirmModal = document.getElementById("confirmModal");

    $(document)
        .off("click", ".post-confirm")
        .on("click", ".post-confirm", (event) => {
            event.preventDefault();

            const setContentPromise = new Promise((resolve, reject) => {
                try {
                    const confirmForm =
                        confirmModal.querySelector("#confirm-form");
                    confirmForm.setAttribute(
                        "action",
                        event.currentTarget.getAttribute("href")
                    );
                    resolve(confirmModal);
                } catch (error) {
                    reject(error);
                }
            });

            setContentPromise
                .then((modal) => {
                    $(modal).modal("show");
                })
                .catch((error) => {
                    console.error("Error: ", error);
                });
        });

    $(".modal [data-bs-dismiss='modal']")
        .off("click")
        .on("click", (event) => {
            event.preventDefault();
            $(event.target).parents(".modal").modal("hide");
        });

    // Remove Modal Body and Footer when Closed
    deleteConfirmModal.addEventListener("hidden.bs.modal", (event) => {
        const deleteForm = deleteConfirmModal.querySelector("#delete-form");
        deleteForm.removeAttribute("action");
    });
});
