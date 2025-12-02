/**
 * purchase-list
 */

'use strict';

// Datatable (js)
document.addEventListener('DOMContentLoaded', function (e) {
  let borderColor, bodyBg, headingColor, currencySymbol;
  borderColor = config.colors.borderColor;
  bodyBg = config.colors.bodyBg;
  headingColor = config.colors.headingColor;
  currencySymbol = window.currencySymbol || '';

  // Variable declaration for table
  const dt_purchase_table = document.querySelector('.datatables-purchases'),
    purchaseAdd = baseUrl + 'purchase/add',
    purchaseEdit = baseUrl + 'purchase/edit';

  // Purchases datatable

  if (dt_purchase_table) {
    var dt_purchases = new DataTable(dt_purchase_table, {
      processing: true,
      stateSave: true,
      serverSide: true,
      ajax: baseUrl + 'purchase/list/ajax',
      columns: [
        // columns according to JSON
        { data: 'id' },
        { data: 'id', orderable: false, render: DataTable.render.select() },
        { data: 'date_formatted' },
        { data: 'reference_no_display' },
        { data: 'supplier_name' },
        { data: 'total_amount_display' },
        { data: 'user_name' },
        { data: 'note_display' },
        { data: 'actions', orderable: false, searchable: false }
      ],
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          searchable: false,
          responsivePriority: 2,
          targets: 0,
          render: function (data, type, full, meta) {
            return '';
          }
        },
        {
          // For Checkboxes
          targets: 1,
          searchable: false,
          responsivePriority: 3,
          checkboxes: true,
          render: function () {
            return '<input type="checkbox" class="dt-checkboxes form-check-input">';
          },
          checkboxes: {
            selectAllRender: '<input type="checkbox" class="form-check-input">'
          }
        },
        {
          // Date
          targets: 2,
          responsivePriority: 1,
          render: function (data, type, full, meta) {
            const date = full['date_formatted'];
            return '<span class="text-nowrap">' + (date ? date : '-') + '</span>';
          }
        },
        {
          // Reference No
          targets: 3,
          render: function (data, type, full, meta) {
            const referenceNo = full['reference_no_display'];
            // reference_no_display already includes "PO" prefix from server
            return '<span>' + (referenceNo && referenceNo !== 'N/A' ? referenceNo : '-') + '</span>';
          }
        },
        {
          // Supplier
          targets: 4,
          render: function (data, type, full, meta) {
            const supplierName = full['supplier_name'];
            return '<span>' + (supplierName ? supplierName : '-') + '</span>';
          }
        },
        {
          // Total Amount
          targets: 5,
          render: function (data, type, full, meta) {
            const totalAmount = full['total_amount_display'];
            return '<span class="text-nowrap">' + (totalAmount ? currencySymbol + totalAmount : currencySymbol + '0.00') + '</span>';
          }
        },
        {
          // User Name
          targets: 6,
          render: function (data, type, full, meta) {
            const userName = full['user_name'];
            return '<span>' + (userName ? userName : '-') + '</span>';
          }
        },
        {
          // Note
          targets: 7,
          render: function (data, type, full, meta) {
            const note = full['note_display'];
            return '<span class="d-inline-block text-truncate" style="max-width: 200px;">' + (note ? note : '-') + '</span>';
          }
        },
        {
          targets: -1,
          title: 'Actions',
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            // Use actions from server if available, otherwise render client-side
            if (data && type === 'display') {
              return data;
            }
            return `
              <div class="d-inline-block text-nowrap">
                <a href="${purchaseEdit}/${full['id']}" class="rounded-pill waves-effect btn-icon"><button class="btn btn-text-secondary "><i class="icon-base ti tabler-edit icon-22px"></i></button></a>
                <a href="javascript:;" onclick="deletePurchase(${full['id']})" class="rounded-pill waves-effect btn-icon"><button class="btn"><i class="icon-base ti tabler-trash icon-22px"></i></button></a>
              </div>
            `;
          }
        }
      ],
      select: {
        style: 'multi',
        selector: 'td:nth-child(2)'
      },
      order: [0, 'desc'],
      displayLength: 7,
      lengthMenu: [[7, 10, 25, 50, 100, -1], [7, 10, 25, 50, 100, "All"]],
      layout: {
        topStart: {
          rowClass: 'card-header d-flex border-top rounded-0 flex-wrap py-0 flex-column flex-md-row align-items-start',
          features: [
            {
              search: {
                className: 'me-5 ms-n4 pe-5 mb-n6 mb-md-0',
                placeholder: 'Search Purchase',
                text: '_INPUT_'
              }
            }
          ]
        },
        topEnd: {
          rowClass: 'row m-3 my-0 justify-content-between',
          features: [
            {
              pageLength: {
                menu: [[7, 10, 25, 50, 100, -1], [7, 10, 25, 50, 100, "All"]],
                text: '_MENU_'
              },
              buttons: [
                {
                  extend: 'collection',
                  className: 'btn btn-label-secondary dropdown-toggle me-4',
                  text: '<span class="d-flex align-items-center gap-1"><i class="icon-base ti tabler-upload icon-xs"></i> <span class="d-none d-sm-inline-block">Export</span></span>',
                  buttons: [
                    {
                      extend: 'print',
                      text: `<span class="d-flex align-items-center"><i class="icon-base ti tabler-printer me-1"></i>Print</span>`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [2, 3, 4, 5, 6, 7],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Check if inner is HTML content
                            if (inner.indexOf('<') > -1) {
                              const parser = new DOMParser();
                              const doc = parser.parseFromString(inner, 'text/html');
                              const text = doc.body.textContent || doc.body.innerText;
                              return text.trim();
                            }

                            return inner;
                          }
                        }
                      },
                      customize: function (win) {
                        win.document.body.style.color = config.colors.headingColor;
                        win.document.body.style.borderColor = config.colors.borderColor;
                        win.document.body.style.backgroundColor = config.colors.bodyBg;
                        const table = win.document.body.querySelector('table');
                        table.classList.add('compact');
                        table.style.color = 'inherit';
                        table.style.borderColor = 'inherit';
                        table.style.backgroundColor = 'inherit';
                      }
                    },
                    {
                      extend: 'csv',
                      text: `<span class="d-flex align-items-center"><i class="icon-base ti tabler-file me-1"></i>Csv</span>`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [2, 3, 4, 5, 6, 7],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Parse HTML content
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(inner, 'text/html');
                            const text = doc.body.textContent || doc.body.innerText;
                            return text.trim();
                          }
                        }
                      }
                    },
                    {
                      extend: 'excel',
                      text: `<span class="d-flex align-items-center"><i class="icon-base ti tabler-upload me-1"></i>Excel</span>`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [2, 3, 4, 5, 6, 7],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Parse HTML content
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(inner, 'text/html');
                            const text = doc.body.textContent || doc.body.innerText;
                            return text.trim();
                          }
                        }
                      }
                    },
                    {
                      extend: 'pdf',
                      text: `<span class="d-flex align-items-center"><i class="icon-base ti tabler-file-text me-1"></i>Pdf</span>`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [2, 3, 4, 5, 6, 7],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Parse HTML content
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(inner, 'text/html');
                            const text = doc.body.textContent || doc.body.innerText;
                            return text.trim();
                          }
                        }
                      }
                    },
                    {
                      extend: 'copy',
                      text: `<i class="icon-base ti tabler-copy me-1"></i>Copy`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [2, 3, 4, 5, 6, 7],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Parse HTML content
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(inner, 'text/html');
                            const text = doc.body.textContent || doc.body.innerText;
                            return text.trim();
                          }
                        }
                      }
                    }
                  ]
                },
                {
                  text: '<i class="icon-base ti tabler-plus me-0 me-sm-1 icon-16px"></i><span class="d-none d-sm-inline-block">Add Purchase</span>',
                  className: 'add-new btn btn-primary',
                  action: function () {
                    window.location.href = purchaseAdd;
                  }
                }
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
              return 'Details of ' + (data['reference_no_display'] || 'Purchase');
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
      },
      initComplete: function () {
        const api = this.api();

        // Row click to open details modal (ignore checkbox and actions columns)
        dt_purchase_table.querySelector('tbody').addEventListener('click', function (e) {
          const cell = e.target.closest('td');
          if (!cell) return;
          const cellIndex = cell.cellIndex;
          // Ignore control (0), checkbox (1), actions (last)
          const lastIndex = dt_purchases.columns().count() - 1;
          if (cellIndex === 0 || cellIndex === 1 || cellIndex === lastIndex) return;

          const rowEl = e.target.closest('tr');
          if (!rowEl) return;
          const row = dt_purchases.row(rowEl);
          if (!row || !row.data()) return;
          const id = row.data().id;
          if (!id) return;

          // Fetch details via AJAX and show modal
          const url = baseUrl + 'purchase/show/ajax/' + id;
          fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (res) { return res.json(); })
            .then(function (payload) {
              if (!payload || !payload.html) return;
              const modalEl = document.getElementById('purchase-view-modal');
              const contentEl = document.getElementById('purchase-view-modal-content');
              if (!modalEl || !contentEl) return;
              contentEl.innerHTML = payload.html;
              const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
              modal.show();
            })
            .catch(function () { /* ignore */ });
        });
      }
    });
  }

  // Filter form control to default size
  // ? setTimeout used for purchase-list table initialization
  setTimeout(() => {
    const elementsToModify = [
      { selector: '.dt-buttons .btn', classToRemove: 'btn-secondary' },
      { selector: '.dt-buttons.btn-group', classToAdd: 'mb-md-0 mb-6' },
      { selector: '.dt-search .form-control', classToRemove: 'form-control-sm', classToAdd: 'ms-0' },
      { selector: '.dt-search', classToAdd: 'mb-0 mb-md-6' },
      { selector: '.dt-length .form-select', classToRemove: 'form-select-sm' },
      { selector: '.dt-layout-end', classToAdd: 'gap-md-2 gap-0 mt-0' },
      { selector: '.dt-layout-start', classToAdd: 'mt-0' },
      { selector: '.dt-layout-table', classToRemove: 'row mt-2' },
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

