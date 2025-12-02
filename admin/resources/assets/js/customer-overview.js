/**
 * Page Detail overview
 */

'use strict';


// Datatable (js)
document.addEventListener('DOMContentLoaded', function (e) {
  const dt_customer_order = document.querySelector('.datatables-customer-order'),
    edit_order_url = baseUrl + 'order/edit',
    statusObj = {
      'Completed': { title: 'Completed', class: 'bg-success' }
    },
    paymentObj = {
      'Due': { title: 'Due', class: 'bg-warning' },
      'Paid': { title: 'Paid', class: 'bg-success' },
      'Partial': { title: 'Partial', class: 'bg-info' }
    };

  // orders datatable
  if (dt_customer_order) {
    let tableTitle = document.createElement('h5');
    tableTitle.classList.add('card-title', 'mb-0');
    tableTitle.innerHTML = 'Orders placed';
    const customerId = dt_customer_order.getAttribute('data-customer-id');
    var dt_order = new DataTable(dt_customer_order, {
      ajax: baseUrl + 'customer/' + customerId + '/orders/ajax',
      columns: [
        // columns according to JSON
        { data: 'id' },
        { data: 'order' },
        { data: 'date' },
        { data: 'payment_status' },
        { data: 'order_status' },
        { data: 'spent' },
        { data: 'id' }
      ],
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          searchable: false,
          orderable: false,
          responsivePriority: 2,
          targets: 0,
          render: function (data, type, full, meta) {
            return '';
          }
        },
        {
          // order order number
          targets: 1,
          responsivePriority: 4,
          render: function (data, type, full, meta) {
            const id = full['order'];
            return "<a href='" + edit_order_url + "?order_number=" + encodeURIComponent(full['order_number'] || '') + "'><span>#" + id + '</span></a>';
          }
        },
        {
          // date
          targets: 2,
          render: function (data, type, full, meta) {
            const date = new Date(full.date); // convert the date string to a Date object
            const formattedDate = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            return '<span class="text-nowrap">' + formattedDate + '</span > ';
          }
        },
        {
          // payment
          targets: 3,
          render: function (data, type, full, meta) {
            const payment = full['payment_status'];
            const info = paymentObj[payment] || { title: '', class: 'bg-label-secondary' };
            return `
              <span class="badge px-2 ${info.class} text-capitalized">
                ${info.title}
              </span>`;
          }
        },
        {
          // status
          targets: 4,
          render: function (data, type, full, meta) {
            const status = full['order_status'];
            const info = statusObj[status] || { title: '', class: 'bg-label-secondary' };
            return `
              <span class="badge px-2 ${info.class} text-capitalized">
                ${info.title}
              </span>`;
          }
        },
        {
          // spent
          targets: 5,
          render: function (data, type, full, meta) {
            const spent = full['spent'];
            return '<span >' + spent + '</span>';
          }
        },
        {
          targets: -1,
          title: 'Actions',
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            return `
              <div class="text-xxl-center">
                <button class="btn btn-text-secondary rounded-pill waves-effect btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                  <i class="icon-base ti tabler-dots-vertical"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end m-0">
                  <a href="${edit_order_url}/${full['id']}" class="dropdown-item">View</a>
                  <a href="javascript:void(0);" class="dropdown-item delete-record" data-id="${full['id']}">Delete</a>
                </div>
              </div>
            `;
          }
        }
      ],
      order: [[1, 'desc']],
      layout: {
        topStart: {
          rowClass: 'row card-header border-bottom mx-0 px-3 py-0',
          features: [tableTitle]
        },
        topEnd: {
          search: {
            placeholder: 'Search order',
            text: '_INPUT_'
          }
        },
        bottomStart: {
          rowClass: 'row mx-3 justify-content-between',
          features: ['info']
        },
        bottomEnd: 'paging'
      },
      pageLength: 6,
      language: {
        paginate: {
          next: '<i class="icon-base ti tabler-chevron-right scaleX-n1-rtl icon-18px"></i>',
          previous: '<i class="icon-base ti tabler-chevron-left scaleX-n1-rtl icon-18px"></i>',
          first: '<i class="icon-base ti tabler-chevrons-left scaleX-n1-rtl icon-18px"></i>',
          last: '<i class="icon-base ti tabler-chevrons-right scaleX-n1-rtl icon-18px"></i>'
        }
      },
      // For responsive popup
      responsive: {
        details: {
          display: DataTable.Responsive.display.modal({
            header: function (row) {
              const data = row.data();
              return 'Details of ' + data['order'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            const data = columns
              .map(function (col) {
                return col.title !== '' // Do not show row in modal popup if title is blank (for check box)
                  ? `<tr data-dt-row="${col.rowIndex}" data-dt-column="${col.columnIndex}">
                      <td>${col.title}:</td>
                      <td>${col.data}</td>
                    </tr>`
                  : '';
              })
              .join('');

            if (data) {
              const div = document.createElement('div');
              div.classList.add('table-responsive');
              const table = document.createElement('table');
              div.appendChild(table);
              table.classList.add('table');
              const tbody = document.createElement('tbody');
              tbody.innerHTML = data;
              table.appendChild(tbody);
              return div;
            }
            return false;
          }
        }
      }
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
        const row = dt_products.row(tr);
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

