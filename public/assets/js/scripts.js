const inputs = document.querySelectorAll('input[type="text"], input[type="checkbox"], textarea');

// Fetch all the forms we want to apply custom Bootstrap validation styles to
let forms = document.querySelectorAll('.needs-validation');
const mailRegEx = /^([A-z\d.-]+)@([a-z\d-]+)\.([a-z]{2,8})(\.[a-z]{2,8})?$/;
const fullNameRegEx = /^([A-z]){3,25}\s([A-z]){3,25}$/

let password = document.getElementById("password-register");
let groupPassword = document.getElementById("group-password-register");
let passwordAlert = document.getElementById("password-alert");
let passwordHelp = document.getElementById("password-register-help");
let passwordConfirm = document.getElementById("password-register-confirm");

const passwordPatterns = {
    "upper-char-required": /[A-Z]/,
    "lower-char-required": /[a-z]/,
    "special-char-required": /[@$!%*?&]/,
    "number-required": /[0-9]/,
    "length-required": /\S{8,}/
}

const patterns = {
    "full-name-contact": fullNameRegEx,
    "mail-contact": mailRegEx,
    "comment-contact": /^[\s\S]+$/,
    "pseudo-register": /^[A-z\d]{3,25}$/,
    "full-name-register": fullNameRegEx,
    "mail-register": mailRegEx
};

//Check if the confirmation password match with the password
function checkPasswordConfirm(password, passwordConfirm) {
    if (passwordConfirm.value === password.value && passwordConfirm.value !== "") {
        passwordConfirm.className = 'form-control valid';
        passwordConfirm.parentElement.classList.add('mb-3');
    } else if (passwordConfirm.value !== password && passwordConfirm.value !== "") {
        passwordConfirm.className = 'form-control invalid';
        passwordConfirm.parentElement.classList.remove('mb-3');
    } else {
        passwordConfirm.className = 'form-control';
        passwordConfirm.parentElement.classList.add('mb-3');
    }
}

/**Set css classes (invalid = red, valid = green)
 When the field is empty, the css classes are removed
 If the form has just been validated, errors are displayed*/
function validate(field, regex, afterSubmit = false) {
    if (field.value === "" && !afterSubmit) {
        field.className = 'form-control';
        field.parentElement.className = 'form-floating mb-3';
    } else if (regex !== "" && regex.test(field.value)) {
        field.className = 'form-control valid';
        field.parentElement.className = 'form-floating mb-3';
    } else {
        field.className = 'form-control invalid';
        field.parentElement.classList.remove('mb-3');
    }
}

/* Bootstrap navbar */
window.addEventListener('DOMContentLoaded', () => {
    let scrollPos = 0;
    const mainNav = document.getElementById('mainNav');
    const headerHeight = mainNav.clientHeight;
    window.addEventListener('scroll', function () {
        const currentTop = document.body.getBoundingClientRect().top * -1;
        if (currentTop < scrollPos) {
            // Scrolling Up
            if (currentTop > 0 && mainNav.classList.contains('is-fixed')) {
                mainNav.classList.add('is-visible');
            } else {
                mainNav.classList.remove('is-visible', 'is-fixed');
            }
        } else {
            // Scrolling Down
            mainNav.classList.remove(['is-visible']);
            if (currentTop > headerHeight && !mainNav.classList.contains('is-fixed')) {
                mainNav.classList.add('is-fixed');
            }
        }
        scrollPos = currentTop;
    });
});


//When the user fills in the field, check if the expected pattern matches.
inputs.forEach((input) => {
    input.addEventListener('keyup', (e) => {
        if (patterns[e.target.attributes.id.value] !== undefined) {
            //If a pattern is defined, check the validity
            validate(e.target, patterns[e.target.attributes.id.value]);
        }
    });
});

if (password !== null) {
    password.addEventListener("keyup", (e) => {
        if (e.target.value !== "") {
            passwordAlert.classList.remove('mt-3')
        }

        //Number of valid patterns
        let nbValidPatterns = 0;
        let nbPatterns = 0;

        //Regex testing on the password
        for (let patternId in passwordPatterns) {
            nbPatterns++;
            if (passwordPatterns[patternId].test(e.target.value)) {
                //Valid
                document.getElementById(patternId).classList.replace("invalid", "valid");
                nbValidPatterns++;
            } else {
                //Invalid
                document.getElementById(patternId).classList.replace("valid", "invalid");
            }
        }

        if (nbValidPatterns === nbPatterns) {
            passwordHelp.innerHTML = "Your password is secure.";
            passwordHelp.classList.replace("invalid", "valid");
            e.target.classList.add("valid");
            e.target.classList.remove("invalid");
        } else if (nbValidPatterns !== nbPatterns && e.target.value !== "") {
            passwordHelp.innerHTML = "Your password does not meet all the required criteria.";
            passwordHelp.classList.replace("valid", "invalid");
            e.target.classList.add("invalid");
            e.target.classList.remove("valid");
        } else {
            password.classList.remove("valid", "invalid");
            passwordAlert.classList.add('mt-3')
        }


        checkPasswordConfirm(e.target, passwordConfirm);
    })

    password.addEventListener("focus", () => {
        passwordAlert.classList.remove("hidden");
        passwordAlert.classList.add("visible");
        if (password.classList.contains("valid") || password.classList.contains("invalid")) {
            passwordAlert.classList.remove("mt-3");
            groupPassword.classList.add("mb-3");
        } else {
            if (!password.classList.contains("mt-3")) {
                passwordAlert.classList.add("mt-3");
            }
        }
    });

    password.addEventListener('blur', () => {
        passwordAlert.classList.remove("visible");
        passwordAlert.classList.add("hidden");
        if (password.classList.contains("valid") || password.classList.contains("invalid")) {
            groupPassword.classList.remove("mb-3");
            passwordAlert.classList.remove("mt-3");
        } else {
            groupPassword.classList.add("mb-3");
            passwordAlert.classList.remove("mt-3")
        }
    })

    passwordConfirm.addEventListener('keyup', (e) => {
        checkPasswordConfirm(password, e.target);
    })
}

// Loop over them and prevent submission
Array.prototype.slice.call(forms)
    .forEach(function (form) {
        form.addEventListener('submit', function (event) {
            /**We Check if all the fields are valid or match with regex patterns(valid or invalid css class)
             and it adds the right css class*/
            if (!form.checkValidity() || document.querySelectorAll('.invalid').length > 0) {
                inputs.forEach((input) => {
                    if (input.value === "" || (input.type === "checkbox" && !input.checked)) {
                        if (input.type === "checkbox") {
                            input.className = "form-check-input invalid me-2";
                        } else {
                            input.className = "form-control invalid";
                            input.parentElement.className = 'form-floating';
                        }
                    }
                });

                //Add css bootstrap class to the comment => display the div block if the field is not valid
                event.preventDefault();
                event.stopPropagation();
            }

        }, false);

        window.onload = function () {
            let data = Object.fromEntries(new FormData(form).entries());
            let filledFields = 0;
            //Submitted values(only available when the form is not valid)
            for (let fieldName in data) {
                if (data[fieldName] !== "") {
                    filledFields++;
                }
            }

            //Display the errors if there are any
            if (filledFields > 0) {
                for (let fieldName in data) {
                    let field = document.getElementsByName(fieldName);
                    //A regex pattern exists for the field
                    if (patterns[field[0].id]) {
                        validate(field[0], patterns[field[0].id], true);
                    } else if (field[0] === password) {
                        //The password is always empty on the page reload --> no regex needed
                        validate(field[0], "", true);
                    }
                }
                //form.classList.add('was-validated');
            } else {
                let myModal = document.getElementById('exampleModal');
                //Check if the modal element exists
                if (myModal) {
                    let myBtModal = new bootstrap.Modal(myModal);
                    myBtModal.toggle();
                }
            }
        }
    });






