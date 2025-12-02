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
    const modalEditUserTaxID = document.querySelector('.modal-edit-tax-id');
    const modalEditUserPhone = document.querySelector('.phone-number-mask');

    // Prefix
    if (modalEditUserTaxID) {
      const prefixOption = {
        prefix: 'TIN',
        blocks: [3, 3, 3, 4],
        delimiter: ' '
      };
      registerCursorTracker({
        input: modalEditUserTaxID,
        delimiter: ' '
      });
      modalEditUserTaxID.value = formatGeneral('', prefixOption);
      modalEditUserTaxID.addEventListener('input', event => {
        modalEditUserTaxID.value = formatGeneral(event.target.value, prefixOption);
      });
    }

    // Phone Number Input Mask
    if (modalEditUserPhone) {
      modalEditUserPhone.addEventListener('input', event => {
        const cleanValue = event.target.value.replace(/\D/g, '');
        modalEditUserPhone.value = formatGeneral(cleanValue, {
          blocks: [3, 3, 4],
          delimiters: [' ', ' ']
        });
      });
      registerCursorTracker({
        input: modalEditUserPhone,
        delimiter: ' '
      });
    }


    // Edit user form validation
    const ajaxEditUserFormEl = document.getElementById('ajaxEditUserForm');
    if (!ajaxEditUserFormEl) return;
    FormValidation.formValidation(ajaxEditUserFormEl, {
      fields: {
        modalEditUserName: {
          validators: {
            notEmpty: {
              message: 'Please enter name'
            }
          }
        },
        modalEditUserEmail: {
          validators: {
            notEmpty: {
              message: 'Please enter email'
            }
          }
        },
        modalEditUserStatus: {
          validators: {
            notEmpty: {
              message: 'Please select status'
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
      }
    });

  })();
});
