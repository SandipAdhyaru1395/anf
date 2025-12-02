/**
 * Payment Add Modal Script
 */

'use strict';

(function () {
  // Initialize Quill editor for payment note
  let paymentNoteEditor = null;
  let paymentDatePicker = null;
  let datatableInstance = null; // Will be set from order-list.js if needed
  let currentUnpaidAmount = 0; // Store unpaid amount for validation

  // Function to initialize payment modal components
  function initializePaymentModal() {
    // Initialize Quill editor for payment note
    const paymentNoteEditorEl = document.getElementById('payment_note_editor');
    if (paymentNoteEditorEl && !paymentNoteEditor) {
      paymentNoteEditor = new Quill(paymentNoteEditorEl, {
        modules: {
          toolbar: paymentNoteEditorEl.closest('.form-control').querySelector('.comment-toolbar')
        },
        placeholder: 'Add note...',
        theme: 'snow'
      });
      
      // Sync Quill content to hidden input
      paymentNoteEditor.on('text-change', function() {
        let content = paymentNoteEditor.root.innerHTML;
        if (content === '<p><br></p>') {
          content = ''; // Treat as empty
        }
        const noteInput = document.getElementById('payment_note');
        if (noteInput) {
          noteInput.value = content;
        }
      });
    }

  }

  // Handle Add Payment button click
  document.addEventListener('click', function (e) {
    const addPaymentBtn = e.target.closest('.add-payment-btn');
    if (!addPaymentBtn) return;
    
    e.preventDefault();
    e.stopPropagation();
    
    // Initialize modal components if not already done
    initializePaymentModal();
    
    const orderId = addPaymentBtn.getAttribute('data-id');
    const orderNumber = addPaymentBtn.getAttribute('data-order-number');
    const unpaidAmount = parseFloat(addPaymentBtn.getAttribute('data-unpaid')) || 0;
    
    // Store unpaid amount for validation
    currentUnpaidAmount = unpaidAmount;
    
    // Set order ID
    const orderIdInput = document.getElementById('payment_order_id');
    if (orderIdInput) {
      orderIdInput.value = orderId;
    }
    
    // Reset form
    const addPaymentForm = document.getElementById('addPaymentForm');
    if (addPaymentForm) {
      addPaymentForm.reset();
    }
    
    if (paymentNoteEditor) {
      paymentNoteEditor.setContents([]);
    }
    
    // Set default values
    if (paymentDatePicker) {
      paymentDatePicker.setDate(new Date());
    }
    
    const amountInput = document.getElementById('payment_amount');
    if (amountInput) {
      amountInput.value = unpaidAmount.toFixed(2);
    }
    
    // Revalidate amount field if FormValidation is already initialized
    if (paymentFormValidation) {
      paymentFormValidation.revalidateField('amount');
    }
    
    // Open modal
    const modalEl = document.getElementById('addPaymentModal');
    if (modalEl) {
      const modal = new bootstrap.Modal(modalEl);
      modal.show();
    }
  });

  // Initialize FormValidation for payment form
  let paymentFormValidation = null;
  
  function initializePaymentFormValidation() {
    const addPaymentForm = document.getElementById('addPaymentForm');
    if (addPaymentForm && typeof FormValidation !== 'undefined' && !paymentFormValidation) {
      // Prevent default form submission
      addPaymentForm.addEventListener('submit', function(e) {
        e.preventDefault();
      });
      
      paymentFormValidation = FormValidation.formValidation(addPaymentForm, {
        // fields: {
        //   date: {
        //     validators: {
        //       notEmpty: {
        //         message: 'Date is required'
        //       },
        //       callback: {
        //         message: 'Date must be in format dd/mm/yyyy hh:mm or dd/mm/yyyy',
        //         callback: function(input) {
        //           const value = (input.value || '').trim();
        //           if (!value) return false;
                  
        //           // Check for d/m/Y H:i or d/m/Y format
        //           const datePattern = /^(\d{1,2})\/(\d{1,2})\/(\d{4})(\s+(\d{1,2}):(\d{2}))?$/;
        //           if (!datePattern.test(value)) return false;
                  
        //           // Try to parse with flatpickr
        //           try {
        //             let parsed;
        //             if (value.includes(':')) {
        //               // Has time component
        //               parsed = flatpickr.parseDate(value, 'd/m/Y H:i');
        //             } else {
        //               // Date only
        //               parsed = flatpickr.parseDate(value, 'd/m/Y');
        //             }
        //             return parsed instanceof Date && !isNaN(parsed.getTime());
        //           } catch (e) {
        //             return false;
        //           }
        //         }
        //       }
        //     }
        //   },
        //   amount: {
        //     validators: {
        //       notEmpty: {
        //         message: 'Amount is required'
        //       },
        //       numeric: {
        //         message: 'Amount must be a valid number'
        //       },
        //       greaterThan: {
        //         min: 1,
        //         message: 'Amount must be at least 1'
        //       },
        //       callback: {
        //         message: 'Amount cannot exceed unpaid amount',
        //         callback: function(input) {
        //           const value = (input.value || '').trim();
        //           if (!value) return false;
        //           const amount = parseFloat(value.replace(/,/g, ''));
        //           if (isNaN(amount) || amount < 1) return false;
                  
        //           // Check if amount exceeds unpaid amount (only if unpaid amount is set and > 0)
        //           if (currentUnpaidAmount > 0 && amount > currentUnpaidAmount) {
        //             return {
        //               valid: false,
        //               message: 'Amount cannot be greater than payable amount (' + currentUnpaidAmount.toFixed(2) + ')'
        //             };
        //           }
        //           return true;
        //         }
        //       }
        //     }
        //   },
        //   payment_method: {
        //     validators: {
        //       notEmpty: {
        //         message: 'Paying by is required'
        //       },
        //       choice: {
        //         min: 1,
        //         max: 1,
        //         message: 'Paying by must be Cash, Bank, or Outstanding'
        //       },
        //       callback: {
        //         message: 'Paying by must be Cash, Bank, or Outstanding',
        //         callback: function(input) {
        //           const value = input.value;
        //           const validMethods = ['Cash', 'Bank', 'Outstanding'];
        //           return validMethods.includes(value);
        //         }
        //       }
        //     }
        //   }
        // },
        plugins: {
          trigger: new FormValidation.plugins.Trigger(),
          bootstrap5: new FormValidation.plugins.Bootstrap5({
            eleValidClass: '',
            rowSelector: '.form-control-validation'
          }),
          submitButton: new FormValidation.plugins.SubmitButton(),
          autoFocus: new FormValidation.plugins.AutoFocus()
        },
        init: instance => {
          instance.on('plugins.message.placed', e => {
            if (e.element.parentElement.classList.contains('input-group')) {
              e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
            }
          });
        }
      });
      
      // Handle form submission
      paymentFormValidation.on('core.form.valid', function() {
        // Update note from Quill editor
        if (paymentNoteEditor) {
          const noteInput = document.getElementById('payment_note');
          if (noteInput) {
            let content = paymentNoteEditor.root.innerHTML;
            if (content === '<p><br></p>') {
              content = '';
            }
            noteInput.value = content;
          }
        }
        
        const formData = new FormData(addPaymentForm);
        
        // Show loading
        const submitBtn = addPaymentForm.querySelector('button[type="submit"]');
        const originalText = submitBtn ? submitBtn.textContent : 'Add Payment';
        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.textContent = 'Processing...';
        }
        
        // Get baseUrl from global scope or use default
        const baseUrl = window.baseUrl || '';
        
        fetch(baseUrl + 'order/payment/add', {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        .then(response => {
          return response.json().then(data => {
            return { status: response.status, data: data };
          });
        })
        .then(({ status, data }) => {
          if (data.success) {
            Swal.fire({
              icon: 'success',
              title: 'Success!',
              text: data.message || 'Payment added successfully!',
              customClass: {
                confirmButton: 'btn btn-success waves-effect waves-light'
              },
              buttonsStyling: false
            }).then(() => {
              // Close modal
              const modalEl = document.getElementById('addPaymentModal');
              if (modalEl) {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
              }
              
              // Reload datatable if callback is set or if datatable instance is available
              if (window.reloadOrderDatatable && typeof window.reloadOrderDatatable === 'function') {
                window.reloadOrderDatatable();
              } else if (datatableInstance) {
                datatableInstance.ajax.reload(null, false);
              } else if (window.dt_products) {
                window.dt_products.ajax.reload(null, false);
              }
              
              // Reload order statistics to update payment status counts
              if (window.loadOrderStatistics && typeof window.loadOrderStatistics === 'function') {
                const currencySymbol = window.currencySymbol || '';
                window.loadOrderStatistics(currencySymbol);
              }
            });
          } else {
            // Show SweetAlert for general error
            Swal.fire({
              icon: 'error',
              title: 'Validation Error!',
              text: data.message || 'Failed to add payment. Please check the form fields.',
              customClass: {
                confirmButton: 'btn btn-danger waves-effect waves-light'
              },
              buttonsStyling: false
            });
            
            // Display field-specific errors if validation errors exist
            if (status === 422 && data.errors && paymentFormValidation) {
              // Map server field names to form field names
              const fieldMapping = {
                'date': 'date',
                'amount': 'amount',
                'payment_method': 'payment_method'
              };
              
              // Set errors for each field
              Object.keys(data.errors).forEach(fieldName => {
                const formFieldName = fieldMapping[fieldName] || fieldName;
                const errorMessages = Array.isArray(data.errors[fieldName]) 
                  ? data.errors[fieldName] 
                  : [data.errors[fieldName]];
                const errorMessage = errorMessages[0]; // Get first error message
                
                // Set field state to invalid with server error message
                // Using FormValidation's setFieldState method
                try {
                  paymentFormValidation.setFieldState(
                    formFieldName,
                    'Invalid',
                    {
                      message: errorMessage
                    }
                  );
                } catch (e) {
                  // Fallback: manually show error message if setFieldState fails
                  console.error('Error setting field state:', e);
                  const fieldElement = addPaymentForm.querySelector('[name="' + formFieldName + '"]');
                  if (fieldElement) {
                    fieldElement.classList.add('is-invalid');
                    // Create or update error message element
                    let errorElement = fieldElement.parentElement.querySelector('.invalid-feedback');
                    if (!errorElement) {
                      errorElement = document.createElement('div');
                      errorElement.className = 'invalid-feedback';
                      fieldElement.parentElement.appendChild(errorElement);
                    }
                    errorElement.textContent = errorMessage;
                  }
                }
              });
            }
          }
        })
        .catch(error => {
          console.error('Error:', error);
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'An error occurred while adding payment. Please try again.',
            customClass: {
              confirmButton: 'btn btn-danger waves-effect waves-light'
            },
            buttonsStyling: false
          });
        })
        .finally(() => {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
          }
        });
      });
    }
  }

  // Initialize on DOM ready
  document.addEventListener('DOMContentLoaded', function() {
    initializePaymentModal();
    
    // Set up flatpickr and form validation initialization when modal is shown
    const modalEl = document.getElementById('addPaymentModal');
    if (modalEl) {
      modalEl.addEventListener('shown.bs.modal', function() {
        const paymentDateEl = document.getElementById('payment_date');
        if (paymentDateEl) {
          // Destroy existing instance if any
          if (paymentDatePicker && paymentDatePicker.destroy) {
            paymentDatePicker.destroy();
            paymentDatePicker = null;
          }
          // Initialize flatpickr when modal is shown
          paymentDatePicker = flatpickr(paymentDateEl, {
            enableTime: true,
            dateFormat: 'd/m/Y H:i',
            time_24hr: true,
            allowInput: true,
            clickOpens: true,
            defaultDate: new Date(),
            onOpen: function(selectedDates, dateStr, instance) {
              // Ensure calendar z-index is above modal
              const calendar = instance.calendarContainer;
              if (calendar) {
                calendar.style.zIndex = '9999';
              }
            }
          });
        }
        
        // Initialize FormValidation when modal is shown
        initializePaymentFormValidation();
      });
      
      // Reset form validation and clear all error messages when modal is hidden
      modalEl.addEventListener('hidden.bs.modal', function() {
        // Reset FormValidation
        if (paymentFormValidation) {
          paymentFormValidation.resetForm();
        }
        
        // Manually clear all error messages and validation classes
        const form = document.getElementById('addPaymentForm');
        if (form) {
          // Remove all invalid feedback elements (FormValidation places these in form-control-validation containers)
          const invalidFeedbacks = form.querySelectorAll('.invalid-feedback');
          invalidFeedbacks.forEach(function(feedback) {
            feedback.remove();
          });
          
          // Remove is-invalid classes from all form controls
          const invalidInputs = form.querySelectorAll('.is-invalid');
          invalidInputs.forEach(function(input) {
            input.classList.remove('is-invalid');
          });
          
          // Remove is-valid classes as well
          const validInputs = form.querySelectorAll('.is-valid');
          validInputs.forEach(function(input) {
            input.classList.remove('is-valid');
          });
          
          // Clear any error message containers that FormValidation might create
          const formControlValidation = form.querySelectorAll('.form-control-validation');
          formControlValidation.forEach(function(container) {
            // Remove any error message divs
            const errorDivs = container.querySelectorAll('div[role="alert"], .fv-plugins-message-container');
            errorDivs.forEach(function(div) {
              div.remove();
            });
          });
        }
      });
    }
  });

  // Export function to set datatable instance (for use in order-list.js)
  window.setPaymentDatatableInstance = function(dtInstance) {
    datatableInstance = dtInstance;
  };

})();

