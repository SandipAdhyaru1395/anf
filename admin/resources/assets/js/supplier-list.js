/**
 * supplier-list
 */

'use strict';

// Datatable (js)
document.addEventListener('DOMContentLoaded', function (e) {
  let borderColor, bodyBg, headingColor;
  borderColor = config.colors.borderColor;
  bodyBg = config.colors.bodyBg;
  headingColor = config.colors.headingColor;

  // Variable declaration for table
  const dt_supplier_table = document.querySelector('.datatables-suppliers'),
    supplierAdd = baseUrl + 'supplier/add',
    supplierEdit = baseUrl + 'supplier/edit';

  // Suppliers datatable

  if (dt_supplier_table) {
    var dt_suppliers = new DataTable(dt_supplier_table, {
      processing: true,
      stateSave: true,
      serverSide: true,
      ajax: baseUrl + 'supplier/list/ajax',
      columns: [
        { data: 'id' },
        { data: 'id', orderable: false, render: DataTable.render.select() },
        { data: 'company' },
        { data: 'full_name' },
        { data: 'email' },
        { data: 'phone' },
        { data: 'is_active' },
        { data: 'id' }
      ],
      columnDefs: [
        {
          className: 'control',
          searchable: false,
          responsivePriority: 2,
          targets: 0,
          render: function (data, type, full, meta) {
            return '';
          }
        },
        {
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
          targets: 2,
          responsivePriority: 1,
          width: '20%',
          render: function (data, type, full, meta) {
            const company = full['company'];
            return '<span class="d-inline-block text-truncate" style="max-width: 200px;">' + (company ? company : '-') + '</span>';
          }
        },
        {
          targets: 3,
          width: '20%',
          render: function (data, type, full, meta) {
            const fullName = full['full_name'];
            return '<span class="d-inline-block text-truncate" style="max-width: 200px;">' + (fullName ? fullName : '-') + '</span>';
          }
        },
        {
          targets: 4,
          width: '15%',
          render: function (data, type, full, meta) {
            const email = full['email'];
            return '<span>' + (email ? email : '-') + '</span>';
          }
        },
        {
          targets: 5,
          width: '10%',
          render: function (data, type, full, meta) {
            const phone = full['phone'];
            return '<span>' + (phone ? phone : '-') + '</span>';
          }
        },
        {
          targets: 6,
          width: '10%',
          render: function (data, type, full, meta) {
            const active = Number(full['is_active']) === 1;
            const badgeClass = active ? 'bg-label-success' : 'bg-label-danger';
            const label = active ? 'Active' : 'Inactive';
            return `<span class="badge ${badgeClass}">${label}</span>`;
          }
        },
        {
          targets: -1,
          title: 'Actions',
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            return `
              <div class="d-inline-block text-nowrap">
                <a href="${supplierEdit}/${full['id']}" class="rounded-pill waves-effect btn-icon"><button class="btn btn-text-secondary "><i class="icon-base ti tabler-edit icon-22px"></i></button></a>
                <a href="javascript:;" onclick="deleteSupplier(${full['id']})" class="rounded-pill waves-effect btn-icon"><button class="btn"><i class="icon-base ti tabler-trash icon-22px"></i></button></a>
              </div>
            `;
          }
        }
      ],
      select: {
        style: 'multi+shift',
        selector: 'td:nth-child(2)'
      },
      order: [0, 'desc'],
      displayLength: 7,
      layout: {
        topStart: {
          rowClass: 'card-header d-flex border-top rounded-0 flex-wrap py-0 flex-column flex-md-row align-items-start',
          features: [
            {
              search: {
                className: 'me-5 ms-n4 pe-5 mb-n6 mb-md-0',
                placeholder: 'Search Supplier',
                text: '_INPUT_'
              }
            },
            {
              buttons: [
                {
                  text: '<i class="icon-base ti tabler-trash me-0 me-sm-1 icon-16px"></i><span class="d-none d-sm-inline-block">Delete Selected</span>',
                  className: 'btn btn-danger',
                  enabled: false,
                  action: function (e, dt, node, config) {

                    let selectedRows = dt.rows({ selected: true }).data();
                    let ids = [];

                    selectedRows.each(function (row) {
                      ids.push(row.id);
                    });

                    if (ids.length === 0) return;

                    Swal.fire({
                      title: 'Are you sure?',
                      text: "You won't be able to revert this!",
                      icon: 'warning',
                      showCancelButton: true,
                      confirmButtonText: 'Yes, delete them!',
                      cancelButtonText: 'Cancel',
                      customClass: {
                        confirmButton: 'btn btn-danger me-3',
                        cancelButton: 'btn btn-label-secondary'
                      },
                      buttonsStyling: false
                    }).then(function (result) {
                      if (result.isConfirmed) {

                        $.ajax({
                          url: baseUrl + 'supplier/delete-multiple',
                          type: 'POST',
                          data: {
                            ids: ids,
                            _token: $('meta[name="csrf-token"]').attr('content')
                          },
                          success: function (response) {

                            dt.ajax.reload();

                            dt.button(0).enable(false);

                            Swal.fire({
                              icon: 'success',
                              title: 'Deleted!',
                              text: 'Selected suppliers have been deleted.',
                              customClass: {
                                confirmButton: 'btn btn-success'
                              }
                            });
                          },
                          error: function (xhr) {

                            let message = 'Something went wrong.';

                            if (xhr.responseJSON && xhr.responseJSON.message) {
                              message = xhr.responseJSON.message;
                            }

                            Swal.fire({
                              icon: 'error',
                              title: 'Error!',
                              text: message,
                              customClass: {
                                confirmButton: 'btn btn-danger'
                              }
                            });
                          }
                        });
                      }
                    });
                  }
                }
              ],
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
                        columns: [2, 3, 4, 5, 6],
                      }
                    },
                    {
                      extend: 'csv',
                      text: `<span class="d-flex align-items-center"><i class="icon-base ti tabler-file me-1"></i>Csv</span>`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [2, 3, 4, 5, 6],
                      }
                    },
                    {
                      extend: 'excel',
                      text: `<span class="d-flex align-items-center"><i class="icon-base ti tabler-upload me-1"></i>Excel</span>`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [2, 3, 4, 5, 6],
                      }
                    },
                    {
                      extend: 'pdf',
                      text: `<span class="d-flex align-items-center"><i class="icon-base ti tabler-file-text me-1"></i>Pdf</span>`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [2, 3, 4, 5, 6],
                      }
                    },
                    {
                      extend: 'copy',
                      text: `<i class="icon-base ti tabler-copy me-1"></i>Copy`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [2, 3, 4, 5, 6],
                      }
                    }
                  ]
                },
                {
                  text: '<i class="icon-base ti tabler-plus me-0 me-sm-1 icon-16px"></i><span class="d-none d-sm-inline-block">Add Supplier</span>',
                  className: 'add-new btn btn-primary',
                  action: function () {
                    window.location.href = supplierAdd;
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
      responsive: {
        details: {
          display: DataTable.Responsive.display.modal({
            header: function (row) {
              const data = row.data();
              return 'Details of ' + data['company'];
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
      },
      initComplete: function () {
        const api = this.api();
      }
    });

    dt_suppliers.on('select deselect', function () {
      let selectedCount = dt_suppliers.rows({ selected: true }).count();
      dt_suppliers.button(0).enable(selectedCount > 0);
    });
  }

  // Filter form control to default size
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

