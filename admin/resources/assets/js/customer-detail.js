/**
 * App eCommerce Customer Detail - delete customer Script
 */
'use strict';


$(function () {
  const select2 = $('.select2');

  // Select2 Country
  if (select2.length) {
    select2.each(function () {
      var $this = $(this);
      
      // if ($this.attr('id') == 'customer_group_id' || $this.attr('id') == 'price_list_id') {
          $this.wrap('<div class="position-relative"></div>').select2({
            placeholder: 'Select value',
            dropdownParent: $this.parent(),
            allowClear: true
          });
        // }else{
        //   $this.wrap('<div class="position-relative"></div>').select2({
        //     placeholder: 'Select value',
        //     dropdownParent: $this.parent()
        //   });
        // }
    });
  }
});


(function () {
  const deleteCustomer = document.querySelector('.delete-customer');

  // Suspend User javascript
  if (deleteCustomer) {
    deleteCustomer.onclick = function () {
      const customerId = deleteCustomer.getAttribute('data-id');
      if (!customerId) return;
      const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert customer!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete customer!',
        customClass: {
          confirmButton: 'btn btn-primary me-2 waves-effect waves-light',
          cancelButton: 'btn btn-label-secondary waves-effect waves-light'
        },
        buttonsStyling: false
      }).then(function (result) {
        if (result.value) {
          fetch(baseUrl + 'customer/' + customerId, {
            method: 'DELETE',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Content-Type': 'application/json',
              ...(token ? { 'X-CSRF-TOKEN': token } : {})
            }
          })
            .then(res => res.json())
            .then(json => {
              if (json && json.success) {
                Swal.fire({
                  icon: 'success',
                  title: 'Deleted!',
                  text: 'Customer has been removed.',
                  customClass: {
                    confirmButton: 'btn btn-success waves-effect waves-light'
                  }
                }).then(() => {
                  window.location.href = baseUrl + 'customer';
                });
              } else {
                Swal.fire({
                  icon: 'error',
                  title: 'Failed!',
                  text: (json && json.message) || 'Failed to delete customer.',
                  customClass: {
                    confirmButton: 'btn btn-danger waves-effect waves-light'
                  }
                });
              }
            })
            .catch(() => {
              Swal.fire({
                icon: 'error',
                title: 'Failed!',
                text: 'Failed to delete customer.',
                customClass: {
                  confirmButton: 'btn btn-danger waves-effect waves-light'
                }
              });
            });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
          Swal.fire({
            title: 'Cancelled',
            text: 'Cancelled Delete :)',
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-success waves-effect waves-light'
            }
          });
        }
      });
    };
  }

  
})();
