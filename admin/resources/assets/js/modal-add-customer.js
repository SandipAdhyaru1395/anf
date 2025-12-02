/**
 * App eCommerce customer all
 */

'use strict';

(function () {

  const addCustomerForm = document.getElementById('addCustomerForm');

  // Add New customer Form Validation
  if (!addCustomerForm) return;
  const fv = FormValidation.formValidation(addCustomerForm, {
    fields: {
      companyName: {
        validators: {
          notEmpty: {
            message: 'Please enter company name '
          }
        }
      },
      email: {
        validators: {
          notEmpty: {
            message: 'Please enter your email'
          },
          emailAddress: {
            message: 'Please enter a valid email address'
          }
        }
      },
      mobile: {
        validators: {
          notEmpty: {
            message: 'Please enter mobile number'
          },
          numeric: {
            message: 'The value is not a valid number'
          },
          stringLength: {
            min: 10,
            max: 10,
            message: 'Mobile number must be 10 digits'
          }
        },
      },
      password: {
        validators: {
          notEmpty: {
            message: 'Please enter password'
          },
          stringLength: {
            min: 6,
            message: 'Password must be more than 6 characters'
          }
        }
      },
      confirmPassword: {
        validators: {
          notEmpty: {
            message: 'Please confirm password'
          },
          identical: {
            compare: function () {
              return addCustomerForm.querySelector('[name="password"]').value;
            },
            message: 'The password and its confirm are not the same'
          },
          stringLength: {
            min: 6,
            message: 'Password must be more than 6 characters'
          }
        }
      },
      status: {
        validators: {
          notEmpty: {
            message: 'Please select status'
          }
        }
      },
      addressLine1: {
        validators: {
          notEmpty: {
            message: 'Please enter address line 1'
          }
        }
      },
      city: {
        validators: {
          notEmpty: {
            message: 'Please enter city'
          }
        }
      },
      zip_code: {
        validators: {
          notEmpty: {
            message: 'Please enter postcode'
          }
        }
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        // Use this for enabling/changing valid/invalid class
        eleValidClass: '',
        rowSelector: function (field, ele) {
          // field is the field name & ele is the field element
          return '.form-control-validation';
        }
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      // Submit the form when all fields are valid
      defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    },
    init: instance => {
        instance.on('plugins.message.placed', function (e) {
          if (e.element.parentElement.classList.contains('input-group')) {
            e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
          }
        });
      }
  });
})();
