/**
 * Edit User
 */

'use strict';

// Select2 (jquery)
$(function () {
  const select2 = $('.select2');

  // Select2 Country
  if (select2.length) {
    select2.each(function () {
      var $this = $(this);
      $this.wrap('<div class="position-relative"></div>').select2({
        placeholder: 'Select value',
        dropdownParent: $this.parent()
      });
    });
  }
});

document.addEventListener('DOMContentLoaded', function (e) {
  (function () {
    // variables
    // const modalAddUserTaxID = document.querySelector('.modal-edit-tax-id');
    const modalAddUserPhone = document.querySelector('.phone-number-mask');

    // Prefix
    // if (modalAddUserTaxID) {
    //   const prefixOption = {
    //     prefix: 'TIN',
    //     blocks: [3, 3, 3, 4],
    //     delimiter: ' '
    //   };
    //   registerCursorTracker({
    //     input: modalAddUserTaxID,
    //     delimiter: ' '
    //   });
    //   modalAddUserTaxID.value = formatGeneral('', prefixOption);
    //   modalAddUserTaxID.addEventListener('input', event => {
    //     modalAddUserTaxID.value = formatGeneral(event.target.value, prefixOption);
    //   });
    // }

    // Phone Number Input Mask
    if (modalAddUserPhone) {
      modalAddUserPhone.addEventListener('input', event => {
        const cleanValue = event.target.value.replace(/\D/g, '');
        modalAddUserPhone.value = formatGeneral(cleanValue, {
          blocks: [3, 3, 4],
          delimiters: [' ', ' ']
        });
      });
      registerCursorTracker({
        input: modalAddUserPhone,
        delimiter: ' '
      });
    }


    // Edit user form validation
    const addUserFormEl = document.getElementById('addUserForm');
    if (!addUserFormEl) return;
    FormValidation.formValidation(addUserFormEl, {
      fields: {
        modalAddUserName: {
          validators: {
            notEmpty: {
              message: 'Please enter name'
            }
          }
        },
        modalAddUserEmail: {
          validators: {
            notEmpty: {
              message: 'Please enter email'
            }
          }
        },
        modalAddUserStatus: {
          validators: {
            notEmpty: {
              message: 'Please select status'
            }
          }
        },
        newPassword: {
          validators: {
            notEmpty: {
              message: 'Please enter new password'
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
              message: 'Please confirm new password'
            },
            identical: {
              compare: function () {
                return document.getElementById('addUserForm').querySelector('[name="newPassword"]').value;
              },
              message: 'The password and its confirm are not the same'
            },
            stringLength: {
              min: 6,
              message: 'Password must be more than 6 characters'
            }
          }
        }
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          // Use this for enabling/changing valid/invalid class
          // eleInvalidClass: '',
          eleValidClass: '',
          rowSelector: '.form-control-validation'
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
});
