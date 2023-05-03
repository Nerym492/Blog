const inputs = document.querySelectorAll("input[type=\"text\"], input[type=\"checkbox\"], textarea, #password-log-in");
const postContainerPostPage = document.getElementById("posts-container");
const postContainerAdminPage = document.getElementById("admin-posts-container");
const commentsContainerAdminPage = document.getElementById(
  "admin-comments-container"
);

// Fetch all the forms we want to apply custom Bootstrap validation styles to
const forms = document.querySelectorAll(".needs-validation");
const mailRegEx = /^([A-Za-z\d.-]+)@([a-z\d-]+)\.([a-z]{2,8})(\.[a-z]{2,8})?$/;
const fullNameRegEx = /^([A-Za-z]){3,25}\s([A-Za-z]){3,25}$/;
const notEmptyRegEx = /^[\s\S]+$/;

const password = document.getElementById("password-register");
const groupPassword = document.getElementById("group-password-register");
const passwordAlert = document.getElementById("password-alert");
const passwordHelp = document.getElementById("password-register-help");
const passwordConfirm = document.getElementById("password-register-confirm");

const passwordPatterns = {
  "upper-char-required": /[A-Z]/,
  "lower-char-required": /[a-z]/,
  "special-char-required": /[@$!%*?&]/,
  "number-required": /[0-9]/,
  "length-required": /\S{8,}/
};

const patterns = {
  "full-name-contact": fullNameRegEx,
  "mail-contact": mailRegEx,
  "comment-contact": notEmptyRegEx,
  "pseudo-register": /^[A-z\d]{3,25}$/,
  "full-name-register": fullNameRegEx,
  "mail-register": mailRegEx,
  "mail-log-in": notEmptyRegEx,
  "password-log-in": notEmptyRegEx,
  "message-post": notEmptyRegEx,
  "title-post": notEmptyRegEx,
  "excerpt-post": notEmptyRegEx,
  "content-post": notEmptyRegEx
};

// These listeners only works for the admin page
/**
 * These listeners only works for the admin page
 * @param {HTMLElement} container Example : div that contains the posts list
 * @param {string} listType "post", "comment", ...
 */
function setDeleteLineListeners (container, listType) {
  if (container !== null && listType !== "") {
    const containerId = container.id;
    const linkClass = ".delete-" + listType + "-link";
    const spanItemSelected = document.getElementById("span-item-selected");
    // Example "#admin-posts-container .delete-post-link"
    document.querySelectorAll("#" + containerId + " " + linkClass).forEach((link) => {
      link.addEventListener("click", () => {
        // Setting the hidden span in the confirm button in the modal #modal-confirm
        spanItemSelected.innerHTML = link.firstElementChild.innerHTML;
        if (spanItemSelected.innerHTML !== "") {
          const btnConfirmDelete = document.getElementById("btn-confirm-delete");
          btnConfirmDelete.addEventListener("click",
            () => deleteItem(spanItemSelected, listType), { once: true });
        }
      });
    });
  }
}

/**
 * Deleting the post in the database with an XMLHttpRequest
 * @param {HTMLElement} spanBtnConfirm Confirmation button in the modal when we click on the trash can icon
 * @param {string} listType Type of the object we want to delete(example : post, comment)
 */
function deleteItem (spanBtnConfirm, listType) {
  const xmlHttp = new XMLHttpRequest();
  xmlHttp.onreadystatechange = function () {
    if (this.readyState === 4 && this.status === 200) {
      // Setting the "listType"(post, comment) list container with the new content.
      const containerToReload = document.getElementById("admin-" + listType + "s-container");
      containerToReload.innerHTML = this.responseText;
      setReloadContainerListeners(containerToReload, listType, true);
    }
  };
  // Send a request to delete the post.
  xmlHttp.open("GET", "delete/" + spanBtnConfirm.innerHTML + "", true);
  xmlHttp.send();
}

// Check if the confirmation password match with the password.
function checkPasswordConfirm (password, passwordConfirm) {
  if (passwordConfirm.value === password.value && passwordConfirm.value !== "") {
    passwordConfirm.className = "form-control valid";
    passwordConfirm.parentElement.classList.add("mb-3");
  } else if (passwordConfirm.value !== password && passwordConfirm.value !== "") {
    passwordConfirm.className = "form-control invalid";
    passwordConfirm.parentElement.classList.remove("mb-3");
  } else {
    passwordConfirm.className = "form-control";
    passwordConfirm.parentElement.classList.add("mb-3");
  }
}

/** Set css classes (invalid = red, valid = green)
 When the field is empty, the css classes are removed
 If the form has just been validated, errors are displayed */
function validate (field, regex, afterSubmit = false) {
  if (field.value === "" && !afterSubmit) {
    field.className = "form-control";
    field.parentElement.className = "form-floating mb-3";
  } else if (regex !== "" && regex.test(field.value)) {
    field.className = "form-control valid";
    field.parentElement.className = "form-floating mb-3";
  } else {
    field.className = "form-control invalid";
    field.parentElement.classList.remove("mb-3");
  }
}

/**
 * Reload the container entered in parameter with an XMLHttpRequest.
 * This function is usable in any page.
 * @param {HTMLElement} containerToReload
 * @param {string} listType "post" or "comment"
 * @param {boolean} deleteListeners True if there are buttons to delete in lines in the containers
 */
function setReloadContainerListeners (containerToReload, listType, deleteListeners) {
  if (containerToReload !== null) {
    // selector example ".page-link.post-link"
    if (deleteListeners) {
      setDeleteLineListeners(containerToReload, listType);
    }
    document.querySelectorAll(".page-link" + "." + listType + "-link").forEach((pageLink) => {
      pageLink.addEventListener("click", (event) => {
        let nextPage = event.target.innerHTML;
        if (event.target.innerHTML === "Previous" || event.target.innerHTML === "Next") {
          nextPage = event.target.nextElementSibling.innerHTML;
        }
        const xmlHttp = new XMLHttpRequest();
        xmlHttp.onreadystatechange = function () {
          if (this.readyState === 4 && this.status === 200) {
            const oldPage = document.querySelector(".page-item." + listType + "-item.active").firstElementChild.innerHTML;
            containerToReload.innerHTML = this.responseText;
            // Add a slide effect on the new page
            addSlideEffect(listType, oldPage, nextPage);
            setReloadContainerListeners(containerToReload, listType, deleteListeners);
            if (listType === "comment") {
              setValidationListeners();
            }
          }
        };
        xmlHttp.open("GET", listType + "s-page-" + nextPage, true);
        xmlHttp.send();
      });
    });
  }
}

function setValidationListeners () {
  document.querySelectorAll(".validate-comment-link").forEach((validateLink) => {
    validateLink.addEventListener("click", () => {
      const xmlHttp = new XMLHttpRequest();
      xmlHttp.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
          commentsContainerAdminPage.innerHTML = this.responseText;
          setReloadContainerListeners(commentsContainerAdminPage, "comment", true);
          setValidationListeners();
        }
      };
      if (validateLink.classList.contains("comment-link-cancel")) {
        xmlHttp.open("GET", "validate/" + validateLink.firstElementChild.innerHTML + "/cancel", true);
      } else {
        xmlHttp.open("GET", "validate/" + validateLink.firstElementChild.innerHTML, true);
      }
      xmlHttp.send();
    });
  });
}

function addSlideEffect (listType, startPage, endPage) {
  if (endPage > startPage) {
    document.querySelector("." + listType + "s-list").classList.add("slide-in-right");
    document.querySelector("." + listType + "s-list").classList.remove("slide-in-left");
  } else if (endPage < startPage) {
    document.querySelector("." + listType + "s-list").classList.remove("slide-in-right");
    document.querySelector("." + listType + "s-list").classList.add("slide-in-left");
  } else {
    document.querySelector("." + listType + "s-list").classList.remove("slide-in-left");
    document.querySelector("." + listType + "s-list").classList.remove("slide-in-right");
  }
}

/* Bootstrap navbar */
window.addEventListener("DOMContentLoaded", () => {
  let scrollPos = 0;
  const mainNav = document.getElementById("mainNav");
  const headerHeight = mainNav.clientHeight;
  window.addEventListener("scroll", function () {
    const currentTop = document.body.getBoundingClientRect().top * -1;
    if (currentTop < scrollPos) {
      // Scrolling Up
      if (currentTop > 0 && mainNav.classList.contains("is-fixed")) {
        mainNav.classList.add("is-visible");
      } else {
        mainNav.classList.remove("is-visible", "is-fixed");
      }
    } else {
      // Scrolling Down
      mainNav.classList.remove(["is-visible"]);
      if (currentTop > headerHeight && !mainNav.classList.contains("is-fixed")) {
        mainNav.classList.add("is-fixed");
      }
    }
    scrollPos = currentTop;
  });
});

// When the user fills in the field, check if the expected pattern matches.
inputs.forEach((input) => {
  input.addEventListener("keyup", (e) => {
    if (patterns[e.target.attributes.id.value] !== undefined) {
      // If a pattern is defined, check the validity
      validate(e.target, patterns[e.target.attributes.id.value]);
    }
  });
});

if (password !== null) {
  password.addEventListener("keyup", (e) => {
    if (e.target.value !== "") {
      passwordAlert.classList.remove("mt-3");
    }

    // Number of valid patterns
    let nbValidPatterns = 0;
    let nbPatterns = 0;

    // Regex testing on the password
    for (const patternId in passwordPatterns) {
      nbPatterns++;
      if (passwordPatterns[patternId].test(e.target.value)) {
        // Valid
        document.getElementById(patternId).classList.replace("invalid", "valid");
        nbValidPatterns++;
      } else {
        // Invalid
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
      passwordAlert.classList.add("mt-3");
    }

    checkPasswordConfirm(e.target, passwordConfirm);
  });

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

  password.addEventListener("blur", () => {
    passwordAlert.classList.remove("visible");
    passwordAlert.classList.add("hidden");
    if (password.classList.contains("valid") || password.classList.contains("invalid")) {
      groupPassword.classList.remove("mb-3");
      passwordAlert.classList.remove("mt-3");
    } else {
      groupPassword.classList.add("mb-3");
      passwordAlert.classList.remove("mt-3");
    }
  });

  passwordConfirm.addEventListener("keyup", (e) => {
    checkPasswordConfirm(password, e.target);
  });
}

// Loop over them and prevent submission
Array.prototype.slice.call(forms)
  .forEach(function (form) {
    form.addEventListener("submit", function (event) {
      /** We Check if all the fields are valid or match with regex patterns(valid or invalid css class)
       and it adds the right css class */
      if (!form.checkValidity() || document.querySelectorAll(".invalid").length > 0) {
        inputs.forEach((input) => {
          if (input.value === "" || (input.type === "checkbox" && !input.checked)) {
            if (input.type === "checkbox") {
              input.className = "form-check-input invalid me-2";
            } else {
              input.className = "form-control invalid";
              input.parentElement.className = "form-floating";
            }
          }
        });

        // Add css bootstrap class to the comment => display the div block if the field is not valid
        event.preventDefault();
        event.stopPropagation();
      }
    }, false);

    window.onload = function () {
      const data = Object.fromEntries(new FormData(form).entries());
      let filledFields = 0;
      // Submitted values(only available when the form is not valid)
      for (const fieldName in data) {
        if (data[fieldName] !== "" && fieldName !== "formToken") {
          filledFields++;
        }
      }

      // Display the errors if there are any
      if (filledFields > 0) {
        for (const fieldName in data) {
          const field = document.getElementsByName(fieldName);
          // A regex pattern exists for the field
          if (patterns[field[0].id]) {
            validate(field[0], patterns[field[0].id], true);
          } else if (field[0] === password) {
            // The password is always empty on the page reload --> no regex needed
            validate(field[0], "", true);
          }
        }
        // form.classList.add('was-validated');
      } else {
        const myModal = document.getElementById("formStatusModal");
        // Check if the modal element exists
        if (myModal) {
          const myBtModal = new bootstrap.Modal(myModal);
          myBtModal.toggle();
        }
      }
    };
  });

/* Adding events listeners on pagination items to only reload the posts with Ajax
  The function calls itself to reload the events listeners after the elements have been reloaded by Ajax */
setReloadContainerListeners(postContainerAdminPage, "post", true);
setReloadContainerListeners(postContainerPostPage, "post", false);
setReloadContainerListeners(commentsContainerAdminPage, "comment", true);
setValidationListeners();
