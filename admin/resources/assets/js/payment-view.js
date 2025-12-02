/**
 * Payment View Modal Script
 * Handles viewing and deleting payments for orders
 */

'use strict';

(function () {
  // Get currency symbol from global scope
  const currencySymbol = window.currencySymbol || '';
  const baseUrl = window.baseUrl || '';

  // Function to render payments table
  function renderPaymentsTable(payments, currencySymbol) {
    const tableBody = document.getElementById('viewPaymentsTableBody');
    if (!tableBody) return;
    
    if (!payments || payments.length === 0) {
      tableBody.innerHTML = '<tr><td colspan="5" class="text-center">No payments found</td></tr>';
      return;
    }
    
    let html = '';
    payments.forEach(function(payment) {
      const date = new Date(payment.date);
      const day = String(date.getDate()).padStart(2, '0');
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const year = date.getFullYear();
      const hours = String(date.getHours()).padStart(2, '0');
      const minutes = String(date.getMinutes()).padStart(2, '0');
      const formattedDate = `${day}/${month}/${year} ${hours}:${minutes}`;
      
      const amount = parseFloat(payment.amount || 0);
      const orderId = payment.order_id || '';
      
      const note = payment.note || '';
      const hasNote = note && note.trim() !== '' && note !== '<p><br></p>';
      // Store note in a way that preserves HTML - use base64 encoding for safety
      const noteEncoded = hasNote ? btoa(unescape(encodeURIComponent(note))) : '';
      
      html += `
        <tr>
          <td>${formattedDate}</td>
          <td>${payment.reference_no || 'N/A'}</td>
          <td>${currencySymbol}${amount.toFixed(2)}</td>
          <td>${payment.payment_method || 'N/A'}</td>
          <td>
            <div class="d-flex gap-2">
              ${hasNote ? `<button type="button" class="btn btn-icon btn-label-primary view-note-btn" data-note="${noteEncoded}" title="View Note">
                <i class="icon-base ti tabler-eye"></i>
              </button>` : ''}
              <button type="button" class="btn btn-icon btn-danger delete-payment-btn" data-payment-id="${payment.id}" data-order-id="${orderId}" title="Delete Payment">
                <i class="icon-base ti tabler-trash text-white"></i>
              </button>
            </div>
          </td>
        </tr>
      `;
    });
    
    tableBody.innerHTML = html;
  }

  // Function to get datatable instance (will be set from order-list.js)
  let datatableInstance = null;
  window.setPaymentViewDatatableInstance = function(dtInstance) {
    datatableInstance = dtInstance;
  };

  // Handle modal backdrop cleanup
  const modalEl = document.getElementById('viewPaymentsModal');
  if (modalEl) {
    modalEl.addEventListener('hidden.bs.modal', function () {
      // Remove any lingering backdrop elements
      const backdrops = document.querySelectorAll('.modal-backdrop');
      backdrops.forEach(function(backdrop) {
        backdrop.remove();
      });
      // Remove modal-open class from body
      document.body.classList.remove('modal-open');
      document.body.style.overflow = '';
      document.body.style.paddingRight = '';
    });
  }

  // Handle View Payments button click
  document.addEventListener('click', function (e) {
    const viewPaymentsBtn = e.target.closest('.view-payments-btn');
    if (!viewPaymentsBtn) return;
    
    e.preventDefault();
    e.stopPropagation();
    
    const orderId = viewPaymentsBtn.getAttribute('data-id');
    const orderNumber = viewPaymentsBtn.getAttribute('data-order-number');
    
    if (!orderId) return;
    
    // Update modal title
    const modalTitle = document.getElementById('viewPaymentsModalTitle');
    if (modalTitle) {
      modalTitle.textContent = `VIEW PAYMENTS (#${orderNumber || 'N/A'})`;
    }
    
    // Show loading state
    const tableBody = document.getElementById('viewPaymentsTableBody');
    if (tableBody) {
      tableBody.innerHTML = '<tr><td colspan="5" class="text-center">Loading payments...</td></tr>';
    }
    
    // Open modal
    if (modalEl) {
      const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      modal.show();
    }
    
    // Fetch payments
    fetch(baseUrl + 'order/payments/' + orderId, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success && data.payments) {
        renderPaymentsTable(data.payments, currencySymbol);
      } else {
        if (tableBody) {
          tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Failed to load payments</td></tr>';
        }
      }
    })
    .catch(error => {
      console.error('Error loading payments:', error);
      if (tableBody) {
        tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error loading payments</td></tr>';
      }
    });
  });
  
  // Handle view note button click
  document.addEventListener('click', function (e) {
    const viewNoteBtn = e.target.closest('.view-note-btn');
    if (!viewNoteBtn) return;
    
    e.preventDefault();
    e.stopPropagation();
    
    const noteEncoded = viewNoteBtn.getAttribute('data-note') || '';
    
    if (!noteEncoded) {
      Swal.fire({
        icon: 'info',
        title: 'No Note',
        text: 'This payment does not have a note.',
        customClass: {
          confirmButton: 'btn btn-primary waves-effect waves-light'
        },
        buttonsStyling: false
      });
      return;
    }
    
    // Decode the note from base64
    let note = '';
    try {
      note = decodeURIComponent(escape(atob(noteEncoded)));
    } catch (e) {
      console.error('Error decoding note:', e);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Failed to load note.',
        customClass: {
          confirmButton: 'btn btn-danger waves-effect waves-light'
        },
        buttonsStyling: false
      });
      return;
    }
    
    if (!note || note.trim() === '' || note === '<p><br></p>') {
      Swal.fire({
        icon: 'info',
        title: 'No Note',
        text: 'This payment does not have a note.',
        customClass: {
          confirmButton: 'btn btn-primary waves-effect waves-light'
        },
        buttonsStyling: false
      });
      return;
    }
    
    // Display note in SweetAlert with HTML content
    Swal.fire({
      title: 'Payment Note',
      html: `<div style="text-align: left; max-height: 400px; overflow-y: auto; padding: 1rem;">${note}</div>`,
      width: '600px',
      customClass: {
        confirmButton: 'btn btn-primary waves-effect waves-light',
        popup: 'text-start'
      },
      buttonsStyling: false,
      showCloseButton: true
    });
  });
  
  // Handle delete payment
  document.addEventListener('click', function (e) {
    const deletePaymentBtn = e.target.closest('.delete-payment-btn');
    if (!deletePaymentBtn) return;
    
    e.preventDefault();
    e.stopPropagation();
    
    const paymentId = deletePaymentBtn.getAttribute('data-payment-id');
    const orderId = deletePaymentBtn.getAttribute('data-order-id');
    
    if (!paymentId || !orderId) return;
    
    Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!',
      customClass: {
        confirmButton: 'btn btn-primary me-2 waves-effect waves-light',
        cancelButton: 'btn btn-label-secondary waves-effect waves-light'
      },
      buttonsStyling: false
    }).then(function (result) {
      if (result.value) {
        // Delete payment
        fetch(baseUrl + 'order/payment/delete/' + paymentId, {
          method: 'DELETE',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            Swal.fire({
              icon: 'success',
              title: 'Deleted!',
              text: 'Payment has been deleted.',
              customClass: {
                confirmButton: 'btn btn-success waves-effect waves-light'
              },
              buttonsStyling: false
            }).then(() => {
              // Reload payments
              const viewPaymentsBtn = document.querySelector(`.view-payments-btn[data-id="${orderId}"]`);
              if (viewPaymentsBtn) {
                viewPaymentsBtn.click();
              }
              // Reload datatable if instance is available
              if (datatableInstance) {
                datatableInstance.ajax.reload(null, false);
              } else if (window.dt_products) {
                window.dt_products.ajax.reload(null, false);
              } else if (window.reloadOrderDatatable && typeof window.reloadOrderDatatable === 'function') {
                window.reloadOrderDatatable();
              }
              
              // Reload order statistics to update payment status counts
              if (window.loadOrderStatistics && typeof window.loadOrderStatistics === 'function') {
                const currencySymbol = window.currencySymbol || '';
                window.loadOrderStatistics(currencySymbol);
              }
              
              // Ensure backdrop is cleaned up after SweetAlert
              setTimeout(function() {
                const backdrops = document.querySelectorAll('.modal-backdrop');
                if (backdrops.length > 0) {
                  backdrops.forEach(function(backdrop) {
                    backdrop.remove();
                  });
                  document.body.classList.remove('modal-open');
                  document.body.style.overflow = '';
                  document.body.style.paddingRight = '';
                }
              }, 100);
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: data.message || 'Failed to delete payment.',
              customClass: {
                confirmButton: 'btn btn-danger waves-effect waves-light'
              },
              buttonsStyling: false
            });
          }
        })
        .catch(error => {
          console.error('Error deleting payment:', error);
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'An error occurred while deleting payment.',
            customClass: {
              confirmButton: 'btn btn-danger waves-effect waves-light'
            },
            buttonsStyling: false
          });
        });
      }
    });
  });
})();

