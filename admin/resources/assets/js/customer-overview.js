/**
 * Page Detail overview
 */

'use strict';


// Datatable (js)
document.addEventListener('DOMContentLoaded', function (e) {
  const dt_customer_order = document.querySelector('.datatables-customer-order'),
    edit_order_url = baseUrl + 'order/edit/',
    currencySymbol = window.currencySymbol || '',
    statusObj = {
      'New': { title: 'New', class: 'bg-label-primary' },
      'Fulfilled': { title: 'Fulfilled', class: 'bg-success' },
      'Cancelled': { title: 'Cancelled', class: 'bg-label-secondary' },
      'Returned': { title: 'Returned', class: 'bg-danger' }
    };

  // orders datatable
  if (dt_customer_order) {
    const customerId = dt_customer_order.getAttribute('data-customer-id');
    var dt_order = new DataTable(dt_customer_order, {
      ajax: baseUrl + 'customer/' + customerId + '/orders/ajax',
      columns: [
        { data: 'number' },
        { data: 'order_date' },
        { data: 'total' },
        { data: 'invoice' },
        { data: 'status' }
      ],
      columnDefs: [
        {
          targets: 1,
          render: function (data, type, full) {
            if (!data) return '-';
            const date = new Date(data);
            const formattedDate = date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
            return '<span class="text-nowrap">' + formattedDate + '</span>';
          }
        },
        {
          targets: 2,
          className: 'text-nowrap',
          render: function (data) {
            const amount = data ? data : '0.00';
            return '<span class="fw-medium">' + currencySymbol + amount + '</span>';
          }
        },
        {
          targets: 4,
          render: function (data) {
            const status = data || 'New';
            const info = statusObj[status] || { title: status, class: 'bg-label-secondary' };
            return `<span class="badge px-2 ${info.class} text-capitalized">${info.title}</span>`;
          }
        }
      ],
      order: [[0, 'desc']],
      layout: {
        topStart: null,
        topEnd: null,
        bottomStart: {
          rowClass: 'row mx-3 justify-content-between',
          features: ['info']
        },
        bottomEnd: 'paging'
      },
      pageLength: 10,
      language: {
        paginate: {
          next: '<i class="icon-base ti tabler-chevron-right scaleX-n1-rtl icon-18px"></i>',
          previous: '<i class="icon-base ti tabler-chevron-left scaleX-n1-rtl icon-18px"></i>',
          first: '<i class="icon-base ti tabler-chevrons-left scaleX-n1-rtl icon-18px"></i>',
          last: '<i class="icon-base ti tabler-chevrons-right scaleX-n1-rtl icon-18px"></i>'
        }
      }
    });

    // Row click -> open order edit page
    dt_customer_order.querySelector('tbody').addEventListener('click', function (e) {
      if (e.target.closest('a, button, input, select, textarea, label, .dropdown-menu')) return;
      var tr = e.target.closest('tr');
      if (!tr) return;
      var row = dt_order.row(tr);
      var data = row && row.data ? row.data() : null;
      if (!data || !data.id) return;
      window.location.href = edit_order_url + data.id;
    });
  }

  //? The 'delete-record' class is necessary for the functionality of the following code.
  document.addEventListener('click', function (e) {
    const trigger = e.target.closest('.delete-record');
    if (!trigger) return;
    e.preventDefault();
    e.stopPropagation();

    // Prefer id from data attribute; fallback to row data
    let orderId = trigger.getAttribute('data-id');
    if (!orderId) {
      const tr = trigger.closest('tr');
      if (tr) {
        const row = dt_order.row(tr);
        const data = row && row.data && row.data();
        orderId = data && data.id;
      }
    }
    if (!orderId) return;

    Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert order!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, Delete order!',
      customClass: {
        confirmButton: 'btn btn-primary me-2 waves-effect waves-light',
        cancelButton: 'btn btn-label-secondary waves-effect waves-light'
      },
      buttonsStyling: false
    }).then(function (result) {
      if (result.value) {
        window.location.href = baseUrl + 'order/delete/' + orderId;
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
  });

  // Filter form control to default size
  // ? setTimeout used for customer-detail-overview table initialization
  setTimeout(() => {
    const elementsToModify = [
      { selector: '.dt-buttons .btn', classToRemove: 'btn-secondary' },
      { selector: '.dt-search .form-control', classToRemove: 'form-control-sm' },
      { selector: '.dt-length .form-select', classToRemove: 'form-select-sm' },
      { selector: '.dt-layout-table', classToRemove: 'row mt-2' },
      { selector: '.dt-layout-start', classToAdd: 'mb-xxl-0 mb-4' },
      { selector: '.dt-layout-full', classToRemove: 'col-md col-12', classToAdd: 'table-responsive' }
    ];

    // Delete record
    elementsToModify.forEach(({ selector, classToRemove, classToAdd }) => {
      document.querySelectorAll(selector).forEach(element => {
        if (classToRemove) {
          classToRemove.split(' ').forEach(className => element.classList.remove(className));
        }
        if (classToAdd) {
          classToAdd.split(' ').forEach(className => element.classList.add(className));
        }
      });
    });
  }, 100);
});

// Validation & Phone mask

