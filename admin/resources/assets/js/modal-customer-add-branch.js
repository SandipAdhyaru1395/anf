/**
 * Add New Branch
 */

'use strict';

// Select2 (jquery)
$(function () {
  const select2 = $('.select2');

  // Select2 Country
  if (select2.length) {
    select2.each(function () {
      var $this = $(this);
      $this.select2({
        placeholder: 'Select value',
        dropdownParent: $this.parent()
      });
    });
  }
});

// Add New Branch form validation
document.addEventListener('DOMContentLoaded', function () {
  (function () {

    FormValidation.formValidation(document.getElementById('addCustomerBranchForm'), {
      fields: {
        name: {
          validators: {
            notEmpty: {
              message: 'Branch name is required'
            },
          }
        },
        address_line1: {
          validators: {
            notEmpty: {
              message: 'Address line 1 is required'
            },
          }
        },
        city: {
          validators: {
            notEmpty: {
              message: 'City is required'
            },
          }
        },
        zip_code: {
          validators: {
            notEmpty: {
              message: 'ZIP Code is required'
            },
          }
        },
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
