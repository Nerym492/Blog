/*!
* Start Bootstrap - Clean Blog v6.0.8 (https://startbootstrap.com/theme/clean-blog)
* Copyright 2013-2022 Start Bootstrap
* Licensed under MIT (https://github.com/StartBootstrap/startbootstrap-clean-blog/blob/master/LICENSE)
*/
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
        console.log(123);
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



const inputs = document.querySelectorAll('input');

// Fetch all the forms we want to apply custom Bootstrap validation styles to
var forms = document.querySelectorAll('.needs-validation');

const patterns = {
  "full-name-contact": /^([A-z]){3,25}\s{1}([A-z]){3,25}$/,
  "mail-contact": /^([a-z\d\.-]+)@([a-z\d-]+)\.([a-z]{2,8})(\.[a-z]{2,8})?$/,
};

//When the user fills in the field, check if the expected pattern matches.
inputs.forEach((input) => {
  input.addEventListener('keyup', (e) => {
    validate(e.target, patterns[e.target.attributes.id.value]);
  });
});

//Set css classes (invalid = red, valid = green)
//When the field is empty, the css classes above are removed
function validate(field, regex) {
  if (field.value == "") {
    field.className = 'form-control';
    field.parentElement.classList.remove('mb-2');
  } else if (regex.test(field.value)) {
    field.className = 'form-control valid';
    field.parentElement.classList.remove('mb-2');
  } else {
    field.className = 'form-control invalid';
    field.parentElement.className = 'form-floating mb-2';
  }
}

// Loop over them and prevent submission
Array.prototype.slice.call(forms)
  .forEach(function (form) {
    form.addEventListener('submit', function (event) {
      //We Check if all the fields are valid or match with regex patterns
      if (!form.checkValidity() || document.querySelectorAll('.invalid').length > 0) {
        /**Adding bootstrap class to show validation status.
        No need to add this on others classes because they 
        are already checked by regex*/
        document.getElementById("group-comment-contact").classList.add('was-validated');
        event.preventDefault();
        event.stopPropagation();
      }

    }, false)
  })
