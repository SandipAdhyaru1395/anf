/**
 * Supplier Add Script
 */
'use strict';

(function () {
  //For form validation
  const addSupplierForm = document.getElementById('addSupplierForm');

  if (addSupplierForm) {
    const fv = FormValidation.formValidation(addSupplierForm, {
      fields: {
        company: {
          validators: {
            notEmpty: {
              message: 'Company is required'
            }
          }
        },
        full_name: {
          validators: {
            notEmpty: {
              message: 'Full Name is required'
            }
          }
        },
        email: {
          validators: {
            emailAddress: {
              message: 'Email must be a valid email address'
            },
            remote: {
              message: 'This email already exists',
              method: 'POST',
              url: baseUrl + 'supplier/check-email',
              data: function() {
                return {
                  _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                  email: addSupplierForm.querySelector('[name="email"]').value
                };
              },
              delay: 500
            }
          }
        },
        phone: {
          validators: {
            stringLength: {
              min: 10,
              max: 10,
              message: 'Phone must be exactly 10 digits'
            },
            regexp: {
              regexp: /^[0-9]+$/,
              message: 'Phone must contain only digits'
            },
            remote: {
              message: 'This phone already exists',
              method: 'POST',
              url: baseUrl + 'supplier/check-phone',
              data: function() {
                return {
                  _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                  phone: addSupplierForm.querySelector('[name="phone"]').value
                };
              },
              delay: 500
            }
          }
        }
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          eleValidClass: 'is-valid',
          rowSelector: function (field, ele) {
            return '.form-control-validation';
          }
        }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        autoFocus: new FormValidation.plugins.AutoFocus()
      }
    });

    fv.on('core.form.valid', function () {
      addSupplierForm.submit();
    });
  }
})();

