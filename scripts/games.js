(function () {
    "use strict";

    var root = document.querySelector("[data-games-lobby]");
    if (!root) {
        return;
    }

    var apiUrl = root.dataset.api;
    var csrf = root.dataset.csrf;
    var message = root.querySelector("[data-lobby-message]");

    function showMessage(text, isError) {
        message.textContent = text;
        message.hidden = !text;
        message.classList.toggle("error", Boolean(isError));
    }

    document.querySelectorAll("[data-open-dialog]").forEach(function (button) {
        button.addEventListener("click", function () {
            var dialog = document.getElementById(button.dataset.openDialog);
            if (dialog && typeof dialog.showModal === "function") {
                dialog.showModal();
                var input = dialog.querySelector("input");
                if (input) {
                    window.setTimeout(function () { input.focus(); }, 60);
                }
            }
        });
    });

    document.querySelectorAll("[data-close-dialog]").forEach(function (button) {
        button.addEventListener("click", function () {
            var dialog = button.closest("dialog");
            if (dialog) {
                dialog.close();
            }
        });
    });

    document.querySelectorAll(".gameDialog").forEach(function (dialog) {
        dialog.addEventListener("click", function (event) {
            if (event.target === dialog) {
                dialog.close();
            }
        });
    });

    var codeInput = document.querySelector(".inviteCodeInput");
    if (codeInput) {
        codeInput.addEventListener("input", function () {
            codeInput.value = codeInput.value
                .replace(/[^a-z0-9]/gi, "")
                .toUpperCase()
                .slice(0, 6);
        });
    }

    document.querySelectorAll("[data-game-form]").forEach(function (form) {
        form.addEventListener("submit", function (event) {
            event.preventDefault();
            if (!form.reportValidity()) {
                return;
            }
            var submit = form.querySelector("[type='submit']");
            var originalText = submit.textContent;
            var data = {};
            new FormData(form).forEach(function (value, key) {
                data[key] = value;
            });
            data.action = form.dataset.gameForm === "create"
                ? "create_game"
                : "join_game";
            data.csrf_token = csrf;

            submit.disabled = true;
            submit.textContent = "Preparando la mesa…";
            showMessage("", false);

            fetch(apiUrl, {
                method: "POST",
                credentials: "same-origin",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-Token": csrf
                },
                body: JSON.stringify(data)
            })
                .then(function (response) {
                    return response.json().catch(function () {
                        return { ok: false, error: "La respuesta del servidor no es válida." };
                    }).then(function (payload) {
                        if (!response.ok || !payload.ok) {
                            throw new Error(payload.error || "No se pudo completar la acción.");
                        }
                        return payload;
                    });
                })
                .then(function (payload) {
                    window.location.href = payload.redirect;
                })
                .catch(function (error) {
                    showMessage(error.message, true);
                    var dialog = form.closest("dialog");
                    if (dialog) {
                        dialog.close();
                    }
                })
                .finally(function () {
                    submit.disabled = false;
                    submit.textContent = originalText;
                });
        });
    });
}());
