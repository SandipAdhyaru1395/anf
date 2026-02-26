'use strict';

(function () {
  const pricingForm = document.getElementById('editProductPricingForm');

  if (!pricingForm) {
    return;
  }

  const fv = FormValidation.formValidation(pricingForm, {
    fields: {
      productPrice: {
        validators: {
          notEmpty: {
            message: 'Please enter unit price'
          },
          numeric: {
            message: 'Unit price must be a number'
          }
        }
      },
      rrp: {
        validators: {
          numeric: {
            message: 'RRP must be a number'
          }
        }
      },
      priceListUnitPrice: {
        selector: 'input[name^="price_list"][name$="[unit_price]"]',
        validators: {
          numeric: {
            message: 'Unit price must be a number'
          }
        }
      },
      priceListRrp: {
        selector: 'input[name^="price_list"][name$="[rrp]"]',
        validators: {
          numeric: {
            message: 'RRP must be a number'
          }
        }
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        eleValidClass: 'is-valid',
        rowSelector: '.form-control-validation'
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    }
  });

  fv.on('core.form.valid', function () {
    pricingForm.submit();
  });
})();

