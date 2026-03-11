/**
 * App eCommerce customer all
 */

'use strict';

// Datatable (js)
document.addEventListener('DOMContentLoaded', function (e) {
  let borderColor, bodyBg, headingColor, currencySymbol;
  currencySymbol = window.currencySymbol;
  borderColor = config.colors.borderColor;
  bodyBg = config.colors.bodyBg;
  headingColor = config.colors.headingColor;

  // Variable declaration for table
  const dt_customer_table = document.querySelector('.datatables-customers'),
    customerView = baseUrl + 'app/ecommerce/customer/details/overview';

  $(function () {
    const select2 = $('.select2');
    
    // Select2 Country
    if (select2.length) {
      select2.each(function () {
        var $this = $(this);

        if ($this.attr('id') == 'customer_group_id') {
          $this.wrap('<div class="position-relative"></div>').select2({
            placeholder: 'Select value',
            dropdownParent: $this.parent(),
            allowClear: true
          });
        }else{
          $this.wrap('<div class="position-relative"></div>').select2({
            placeholder: 'Select value',
            dropdownParent: $this.parent()
          });
        }
      });
    }

  });

  // customers datatable
  if (dt_customer_table) {
    var dt_customer = new DataTable(dt_customer_table, {
      ajax: {
        url: baseUrl + 'customer/list/ajax',
        data: function (d) {
          var filterShow = document.getElementById('filter-show');
          d.status_filter = filterShow ? filterShow.value : '';
        }
      },
      processing: true,
      stateSave: true,
      serverSide: true,
      // ordering: false,
      columns: [
        { data: 'name' },
        { data: 'main_contact' },
        { data: 'group' },
        { data: 'last_seen' },
        { data: 'last_order' },
        { data: 'min_spend' },
        { data: 'status' }
      ],
      columnDefs: [
        {
          targets: 0,
          render: function (data, type, full) {
            if (type === 'display' && data) {
              var url = baseUrl + 'customer/' + full.id + '/overview';
              return '<a href="' + url + '" class="text-body fw-medium">' + (data || '-') + '</a>';
            }
            return data || '-';
          }
        },
        {
          targets: [1, 2, 3, 4, 5],
          render: function (data) {
            return data !== null && data !== undefined && data !== '' ? data : '-';
          }
        }
      ],
      layout: {
        topStart: null,
        topEnd: {
          features: [
            {
              buttons: [
                { extend: 'print', exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6], format: { body: function (inner) { if (!inner || inner.length <= 0) return inner; if (inner.indexOf('<') > -1) { var d = document.createElement('div'); d.innerHTML = inner; return (d.textContent || d.innerText || '').trim(); } return inner; } } } },
                { extend: 'csv', exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6], format: { body: function (inner) { if (!inner || inner.length <= 0) return inner; if (inner.indexOf('<') > -1) { var d = document.createElement('div'); d.innerHTML = inner; return (d.textContent || d.innerText || '').trim(); } return inner; } } } },
                { extend: 'excel', exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6], format: { body: function (inner) { if (!inner || inner.length <= 0) return inner; if (inner.indexOf('<') > -1) { var d = document.createElement('div'); d.innerHTML = inner; return (d.textContent || d.innerText || '').trim(); } return inner; } } } },
                { extend: 'pdf', exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6], format: { body: function (inner) { if (!inner || inner.length <= 0) return inner; if (inner.indexOf('<') > -1) { var d = document.createElement('div'); d.innerHTML = inner; return (d.textContent || d.innerText || '').trim(); } return inner; } } } },
                { extend: 'copy', exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6], format: { body: function (inner) { if (!inner || inner.length <= 0) return inner; if (inner.indexOf('<') > -1) { var d = document.createElement('div'); d.innerHTML = inner; return (d.textContent || d.innerText || '').trim(); } return inner; } } } }
              ]
            }
          ]
        },
        bottomStart: {
          rowClass: 'row mx-3 justify-content-between',
          features: ['info']
        },
        bottomEnd: 'paging'
      },
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
              return 'Details of ' + (data['name'] || data['main_contact'] || 'Customer');
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

    // Row click -> customer overview/edit
    dt_customer_table.querySelector('tbody').addEventListener('click', function (e) {
      // Don't hijack clicks on interactive elements
      if (e.target.closest('a, button, input, select, textarea, label, .dropdown-menu')) return;

      var tr = e.target.closest('tr');
      if (!tr) return;

      var row = dt_customer.row(tr);
      var data = row && row.data ? row.data() : null;
      if (!data || !data.id) return;

      window.location.href = baseUrl + 'customer/' + data.id + '/overview';
    });

    // Wire custom filter bar: Show dropdown and Go button
    var filterShow = document.getElementById('filter-show');
    var searchInput = document.getElementById('customer-search-input');
    var searchGo = document.getElementById('customer-search-go');
    if (filterShow) {
      filterShow.addEventListener('change', function () {
        dt_customer.ajax.reload();
      });
    }
    if (searchGo && searchInput) {
      searchGo.addEventListener('click', function () {
        dt_customer.search(searchInput.value).draw();
      });
      searchInput.addEventListener('keypress', function (e) {
        if (e.which === 13) {
          dt_customer.search(searchInput.value).draw();
        }
      });
    }
    // Move DataTable export buttons into hidden placeholder so header dropdown can trigger them
    var placeholder = document.getElementById('customer-export-buttons-placeholder');
    if (placeholder) {
      setTimeout(function () {
        var card = dt_customer_table && dt_customer_table.closest ? dt_customer_table.closest('.card') : null;
        var btnContainer = card ? card.querySelector('.dt-buttons') : document.querySelector('.dt-buttons');
        if (btnContainer) placeholder.appendChild(btnContainer);
      }, 0);
    }
    // Header Export dropdown: trigger the corresponding DataTable export
    document.addEventListener('click', function (e) {
      var action = e.target.closest('.customer-export-action');
      if (!action || !action.getAttribute('data-export')) return;
      e.preventDefault();
      var type = action.getAttribute('data-export');
      var selector = '.buttons-' + type;
      var btn = dt_customer.button(selector);
      if (btn && btn.length) btn.trigger();
    });
  }

  // Filter form control to default size
  // ? setTimeout used for customer-all table initialization
  setTimeout(() => {
    const elementsToModify = [
      { selector: '.dt-buttons', classToAdd: 'gap-4' },
      { selector: '.dt-buttons.btn-group', classToAdd: 'mb-6 mb-md-0' },
      { selector: '.dt-buttons .btn-group', classToAdd: 'me-md-0 me-6' },
      { selector: '.dt-buttons .btn-group .btn', classToRemove: 'btn-secondary', classToAdd: 'btn-label-secondary' },
      { selector: '.dt-buttons .btn-group ~ .btn', classToRemove: 'btn-secondary' },
      { selector: '.dt-search .form-control', classToRemove: 'form-control-sm', classToAdd: 'ms-0' },
      { selector: '.dt-length .form-select', classToRemove: 'form-select-sm' },
      { selector: '.dt-length', classToAdd: 'mt-0 mt-md-6' },
      { selector: '.dt-layout-table', classToRemove: 'row mt-2' },
      { selector: '.dt-layout-start', classToAdd: 'mt-0' },
      { selector: '.dt-layout-end', classToAdd: 'gap-md-2 gap-0 mt-0' },
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

  // Delete customer confirmation (same UX as orders)
  document.addEventListener('click', function (e) {
    const trigger = e.target.closest('.delete-record');
    if (!trigger) return;
    e.preventDefault();
    e.stopPropagation();

    const tr = trigger.closest('tr');
    let id = trigger.getAttribute('data-id');
    if (!id && tr && dt_customer_table) {
      const dt = DataTable.dom.dataTable(dt_customer_table);
      const row = dt && dt.row && dt.row(tr);
      const data = row && row.data && row.data();
      id = data && data.id;
    }
    if (!id) return;

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
        // TODO: wire actual delete endpoint when available
        fetch(baseUrl + 'customer/' + id, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
          },
        })
          .then(response => response.json())
          .then(data => {
            // optionally redirect or reload
            window.location.reload();
          })
          .catch(error => {
            console.error('Error:', error);
          });
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'Cancelled Delete :)',
          icon: 'error',
          customClass: { confirmButton: 'btn btn-success waves-effect waves-light' }
        });
      }
    });
  });
});

