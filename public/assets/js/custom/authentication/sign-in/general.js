"use strict";
var KTSigninGeneral = function () {
    var t, e, i, s;
    return {
        init: function () {
            t = document.querySelector("#kt_sign_in_form");
            e = document.querySelector("#kt_sign_in_submit");
            s = document.querySelector("#kt_sign_in_errors");

            if (!t) return;

            // Initialize form validation
            i = FormValidation.formValidation(t, {
                fields: {
                    email: {
                        validators: {
                            notEmpty: {
                                message: "Email address is required"
                            },
                            emailAddress: {
                                message: "The value is not a valid email address"
                            }
                        }
                    },
                    password: {
                        validators: {
                            notEmpty: {
                                message: "The password is required"
                            }
                        }
                    }
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: ".fv-row",
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            });

            // Handle form submission
            t.addEventListener('submit', function (event) {
                event.preventDefault();
                handleSubmit();
            });

            e.addEventListener("click", function (event) {
                event.preventDefault();
                handleSubmit();
            });

            async function handleSubmit() {
                // Validate form first
                const validation = await i.validate();

                if (validation === 'Valid') {
                    // Hide any previous errors
                    s.classList.add('d-none');

                    // Set loading state
                    e.setAttribute("data-kt-indicator", "on");
                    e.disabled = true;

                    try {
                        // Get form data
                        const formData = new FormData(t);

                        // Send AJAX request
                        const response = await fetch("login", {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: formData
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Show success message
                            Swal.fire({
                                text: data.message || "You have successfully logged in!",
                                icon: "success",
                                buttonsStyling: false,
                                confirmButtonText: "Continue",
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // Redirect to dashboard
                                    window.location.href = data.redirect;
                                }
                            });
                        } else {
                            // Show error message
                            if (data.errors) {
                                let errorHtml = '';
                                for (const field in data.errors) {
                                    if (data.errors.hasOwnProperty(field)) {
                                        errorHtml += `<div>${data.errors[field].join('<br>')}</div>`;
                                    }
                                }
                                document.getElementById('kt_sign_in_error_message').innerHTML = errorHtml;
                                s.classList.remove('d-none');
                            } else {
                                Swal.fire({
                                    text: data.message || "Sorry, looks like there are some errors detected, please try again.",
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, got it!",
                                    customClass: {
                                        confirmButton: "btn btn-primary"
                                    }
                                });
                            }
                        }
                    } catch (error) {
                        console.error('Login error:', error);
                        Swal.fire({
                            text: "An error occurred. Please try again.",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, got it!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        });
                    } finally {
                        // Remove loading state
                        e.removeAttribute("data-kt-indicator");
                        e.disabled = false;
                    }
                }
            }
        }
    };
}();

KTUtil.onDOMContentLoaded(function () {
    KTSigninGeneral.init();
});
