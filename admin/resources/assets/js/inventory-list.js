'use strict';

document.addEventListener('DOMContentLoaded', function () {
  let borderColor, bodyBg, headingColor;
  borderColor = config.colors.borderColor;
  bodyBg = config.colors.bodyBg;
  headingColor = config.colors.headingColor;

  const dt_inventory_table = document.querySelector('.datatables-inventory');

  if (dt_inventory_table) {
    const dt_inventory = new DataTable(dt_inventory_table, {
      processing: true,
      stateSave: true,
      serverSide: true,
      ajax: {
        url: baseUrl + 'inventory/list/ajax',
        data: function (d) {
          const categoryFilter = document.querySelector('#inventory_category');
          d.category_id = categoryFilter && categoryFilter.value ? categoryFilter.value : '';
        }
      },
      columns: [
        { data: 'id', orderable: false, searchable: false },
        { data: 'product_name' },
        { data: 'sku' },
        { data: 'on_hand' },
        { data: 'ordered' },
        { data: 'available' },
        { data: null, orderable: false, searchable: false }
      ],
      columnDefs: [
        {
          className: 'control',
          searchable: false,
          responsivePriority: 2,
          targets: 0,
          render: function () {
            return '';
          }
        },
        {
          targets: 1,
          responsivePriority: 1,
          width: '35%',
          render: function (data, type, full) {
            const name = full['product_name'] || '-';
            return `<span class="d-inline-block" style="max-width: 260px; white-space: normal; word-break: break-word;">${name}</span>`;
          }
        },
        {
          targets: 3,
          className: 'text-end',
          render: function (data, type, full) {
            return full['on_hand'] ?? 0;
          }
        },
        {
          targets: 4,
          className: 'text-end',
          render: function (data, type, full) {
            return full['ordered'] ?? 0;
          }
        },
        {
          targets: 5,
          className: 'text-end',
          render: function (data, type, full) {
            return full['available'] ?? 0;
          }
        },
        {
          targets: 6,
          className: 'text-end',
          render: function (data, type, full) {
            const onHand = parseInt(full['on_hand'] ?? 0, 10);
            const available = parseInt(full['available'] ?? 0, 10);
            const percent = onHand > 0 ? Math.min(100, (available / Math.max(1, onHand)) * 100) : 0;

            return `
              <div class="d-inline-flex align-items-center" style="min-width: 170px;">
                <div class="progress flex-grow-1 me-2" style="height: 12px;">
                  <div class="progress-bar bg-success"
                       role="progressbar"
                       style="width: ${percent}%;"
                       aria-valuenow="${available}" aria-valuemin="0" aria-valuemax="${Math.max(1, onHand)}">
                  </div>
                  <div class="progress-bar bg-danger"
                       role="progressbar"
                       style="width: ${Math.max(0, 100 - percent)}%;">
                  </div>
                </div>
                <small class="text-muted">${percent.toFixed(2)}%</small>
              </div>
            `;
          }
        }
      ],
      order: [[1, 'asc']],
      displayLength: 10,
      layout: {
        topStart: {
          rowClass: 'card-header d-flex border-top rounded-0 flex-wrap py-0 flex-column flex-md-row align-items-start',
          features: [
            {
              search: {
                className: 'me-5 ms-n4 pe-5 mb-n6 mb-md-0',
                placeholder: 'Search inventory',
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
                menu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                text: '_MENU_'
              }
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
      responsive: {
        details: {
          display: DataTable.Responsive.display.modal({
            header: function (row) {
              const data = row.data();
              return 'Inventory for ' + (data['product_name'] || 'Product');
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            const data = columns
              .map(function (col) {
                return col.title !== ''
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

    const categoryFilter = document.querySelector('#inventory_category');
    if (categoryFilter) {
      if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
        const $category = window.jQuery(categoryFilter).select2({ width: 'resolve' });
        $category.on('change', function () {
          dt_inventory.ajax.reload();
        });
      } else {
        categoryFilter.addEventListener('change', function () {
          dt_inventory.ajax.reload();
        });
      }
    }

    // Styling tweaks similar to other tables
    setTimeout(() => {
      const elementsToModify = [
        { selector: '.dt-search .form-control', classToRemove: 'form-control-sm', classToAdd: 'ms-0' },
        { selector: '.dt-search', classToAdd: 'mb-0 mb-md-6' },
        { selector: '.dt-length .form-select', classToRemove: 'form-select-sm' },
        { selector: '.dt-layout-end', classToAdd: 'gap-md-2 gap-0 mt-0' },
        { selector: '.dt-layout-start', classToAdd: 'mt-0' },
        { selector: '.dt-layout-table', classToRemove: 'row mt-2' },
        { selector: '.dt-layout-full', classToRemove: 'col-md col-12', classToAdd: 'table-responsive' }
      ];

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
  }
});

