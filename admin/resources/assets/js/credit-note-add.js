/**
 * Credit Note Add Script
 * Used for order/credit-note-add.blade.php
 */

'use strict';

document.addEventListener('DOMContentLoaded', function() {
  if (typeof window.$ !== 'undefined' && $('#creditNoteForm').length) {
    const currencySymbol = window.currencySymbol || '';

    // Calculate totals (returned quantity and amount)
    function calculateTotal() {
      var totalReturned = 0;
      var totalAmount = 0;
      $('#products-table tbody tr:not(.total-row)').each(function() {
        var $row = $(this);
        var returnedQty = parseFloat($row.find('.returned-input').val()) || 0;
        var unitPrice = parseFloat($row.find('input[name*="[unit_price]"]').val()) || 0;
        var sub = returnedQty * unitPrice;
        $row.find('.subtotal-cell').text(currencySymbol + sub.toFixed(2));
        totalReturned += returnedQty;
        totalAmount += sub;
      });
      $('.total-returned').text(totalReturned.toFixed(0));
      $('.total-amount').text(currencySymbol + totalAmount.toFixed(2));
    }

    // Validate returned quantity against order quantity
    function validateReturnedQuantity(input) {
      var $input = $(input);
      var returnedQty = parseFloat($input.val()) || 0;
      var orderQty = parseFloat($input.data('order-qty')) || 0;
      var productId = $input.data('product-id');
      var $errorMsg = $('#error-' + productId);
      
      if (returnedQty < 0) {
        $errorMsg.text('Returned quantity cannot be negative').show();
        $input.addClass('is-invalid');
        return false;
      }
      
      if (returnedQty > orderQty) {
        $errorMsg.text('Returned quantity cannot exceed order quantity (' + orderQty + ')').show();
        $input.addClass('is-invalid');
        return false;
      }
      
      $errorMsg.hide();
      $input.removeClass('is-invalid');
      return true;
    }

    // Validate all returned quantities
    function validateAllReturnedQuantities() {
      var isValid = true;
      $('.returned-input').each(function() {
        if (!validateReturnedQuantity(this)) {
          isValid = false;
        }
      });
      return isValid;
    }

    // Update totals and validate on input change
    $(document).on('input blur', '.returned-input', function() {
      validateReturnedQuantity(this);
      calculateTotal();
    });

    // Calculate totals on initial load
    setTimeout(function() {
      calculateTotal();
    }, 100);

    // FormValidation for credit note form
    const creditNoteForm = document.getElementById('creditNoteForm');
    if (creditNoteForm) {
      const fv = FormValidation.formValidation(creditNoteForm, {
        fields: {
          'products[*][returned_quantity]': {
            validators: {
              notEmpty: {
                message: 'Returned quantity is required'
              },
              numeric: {
                message: 'Returned quantity must be a number'
              },
              callback: {
                message: 'Returned quantity cannot exceed order quantity',
                callback: function(value, validator, $field) {
                  var $input = $field[0];
                  var returnedQty = parseFloat(value) || 0;
                  var orderQty = parseFloat($input.getAttribute('data-order-qty')) || 0;
                  
                  if (returnedQty < 0) {
                    return {
                      valid: false,
                      message: 'Returned quantity cannot be negative'
                    };
                  }
                  
                  if (returnedQty > orderQty) {
                    return {
                      valid: false,
                      message: 'Returned quantity cannot exceed order quantity (' + orderQty + ')'
                    };
                  }
                  
                  return true;
                }
              }
            }
          }
        },
        plugins: {
          trigger: new FormValidation.plugins.Trigger(),
          bootstrap5: new FormValidation.plugins.Bootstrap5({
            eleValidClass: '',
            rowSelector: function(field, ele) {
              switch (field) {
                case 'products[*][returned_quantity]':
                  return '.returned-input';
                default:
                  return '.form-control';
              }
            }
          }),
          autoFocus: new FormValidation.plugins.AutoFocus()
        }
      }).on('core.form.valid', function() {
        // Additional validation before submit
        if (!validateAllReturnedQuantities()) {
          fv.disableSubmitButtons(true);
          return false;
        }
        
        // Check if at least one returned quantity is greater than 0
        var hasReturnedItems = false;
        $('.returned-input').each(function() {
          if (parseFloat($(this).val()) > 0) {
            hasReturnedItems = true;
            return false; // break
          }
        });
        
        if (!hasReturnedItems) {
          Swal.fire({
            icon: 'warning',
            title: 'No Items to Return',
            text: 'Please enter at least one returned quantity greater than 0.',
            customClass: {
              confirmButton: 'btn btn-primary waves-effect waves-light'
            }
          });
          fv.disableSubmitButtons(true);
          return false;
        }
        
        fv.disableSubmitButtons(false);
      });
    }
  }
});

