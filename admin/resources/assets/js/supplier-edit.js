/**
 * Supplier Edit Script
 */
'use strict';

(function () {
  //For form validation
  const editSupplierForm = document.getElementById('editSupplierForm');

  if (editSupplierForm) {
    const fv = FormValidation.formValidation(editSupplierForm, {
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
                  email: editSupplierForm.querySelector('[name="email"]').value,
                  id: editSupplierForm.querySelector('[name="id"]').value
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
                  phone: editSupplierForm.querySelector('[name="phone"]').value,
                  id: editSupplierForm.querySelector('[name="id"]').value
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
      editSupplierForm.submit();
    });
  }
})();

