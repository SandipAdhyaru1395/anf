/**
 * app-ecommerce-product-list
 */

'use strict';

// Datatable (js)
document.addEventListener('DOMContentLoaded', function (e) {
  let borderColor, bodyBg, headingColor;

  borderColor = config.colors.borderColor;
  bodyBg = config.colors.bodyBg;
  headingColor = config.colors.headingColor;

  // Variable declaration for table
  const dt_brand_table = document.querySelector('.datatables-brands'),
    brandAdd = baseUrl + 'brand/add',
     brandEdit = baseUrl + 'brand/edit',
     publishedObj = {
       0 : { title: 'Inactive', class: 'bg-label-danger' },
        1 : { title: 'Active', class: 'bg-label-success' }
    };

  // E-commerce Products datatable

  if (dt_brand_table) {
    var dt_products = new DataTable(dt_brand_table, {
      processing: true,
      stateSave: true,
      serverSide: true,
      // ajax: assetsPath + 'json/ecommerce-product-list.json',
      ajax: baseUrl + 'brand/list/ajax',
      columns: [
        // columns according to JSON
        { data: 'id',orderable: false, searchable: false },
        { data: 'id', orderable: false, searchable: false, render: DataTable.render.select() },
        { data: 'brand'},
        { data: 'categories', orderable: false},
        { data: 'is_active'},
        // { data: 'status'},
        { data: 'id'}
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
          orderable: false,
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
          width: '30%',
          render: function (data, type, full, meta) {
            let name = full['brand'],
              id = full['id'],
              image = full['image'];

            let output;
            const defaultImagePath = baseUrl + 'public/assets/img/default_brand.png';
            
            if(image){
              output = `<img src="${image}" alt="Brand-${id}" class="rounded" onerror="this.onerror=null; this.src='${defaultImagePath}';">`;
            }else{
              output = `<img src="${defaultImagePath}" alt="Brand-${id}" class="rounded">`;
            }
             
            let rowOutput = `
              <div class="d-flex justify-content-start align-items-center product-name">
                <div class="avatar-wrapper">
                  <div class="avatar avatar me-2 me-sm-4 rounded-2 bg-label-secondary">${output}</div>
                </div>
                <div class="d-flex flex-column">
                  <span class="d-inline-block mb-0" style="max-width: 200px; white-space: normal; word-break: break-word;">${name}</span>
                </div>
              </div>
            `;

            return rowOutput;
          }
        },
        {
          targets: 3,
          responsivePriority: 5,
          width: '40%',
          render: function (data, type, full, meta) {
            return `
              <div style="max-width: 260px; white-space: normal; word-break: break-word;">${full['categories']}</div>
            `;
          }
        },
        {
          targets: 4,
          responsivePriority: 4,
          render: function (data, type, full, meta) {
            const is_active = full['is_active'];

             return (
              '<span class="badge ' +
              publishedObj[is_active].class +
              '" text-capitalized>' +
              publishedObj[is_active].title +
              '</span>'
            );
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
                <a href="${brandEdit}/${full['id']}"><button class="btn btn-text-secondary rounded-pill waves-effect btn-icon"><i class="icon-base ti tabler-edit icon-22px"></i></button></a>
                <a href="javascript:;" onclick="deleteBrand(${full['id']})"><button class="btn btn-text-danger rounded-pill waves-effect btn-icon"><i class="icon-base ti tabler-trash icon-22px"></i></button></a>
              </div>
            `;
          }
        }
      ],
      select: {
        style: 'multi+shift',
        selector: 'td:nth-child(2)'
      },
      // order: [[0, 'desc']],
      displayLength: 7,
      lengthMenu: [[7, 10, 25, 50, 100, -1], [7, 10, 25, 50, 100, "All"]],
      layout: {
        topStart: {
          rowClass: 'card-header d-flex border-top rounded-0 flex-wrap py-0 flex-column flex-md-row align-items-start',
          features: [
            {
              search: {
                className: 'me-5 ms-n4 pe-5 mb-n6 mb-md-0',
                placeholder: 'Search Brand',
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
                          url: baseUrl + 'brand/delete-multiple',
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
                              text: 'Selected brands have been deleted.',
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
                        columns: [2, 3, 4],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Check if inner is HTML content
                            if (inner.indexOf('<') > -1) {
                              const parser = new DOMParser();
                              const doc = parser.parseFromString(inner, 'text/html');

                              // Get all text content
                              let text = '';

                              // Handle specific elements
                              const userNameElements = doc.querySelectorAll('.product-name');
                              if (userNameElements.length > 0) {
                                userNameElements.forEach(el => {
                                  // Remove avatar-wrapper before extracting text
                                  const avatarWrapper = el.querySelector('.avatar-wrapper');
                                  if (avatarWrapper) {
                                    avatarWrapper.remove();
                                  }
                                  // Get text from nested structure
                                  const nameText =
                                    el.querySelector('.fw-medium')?.textContent ||
                                    el.querySelector('.d-block')?.textContent ||
                                    el.textContent;
                                  text += nameText.trim() + ' ';
                                });
                              } else {
                                // Get regular text content
                                text = doc.body.textContent || doc.body.innerText;
                              }

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
                        columns: [2, 3, 4],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Parse HTML content
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(inner, 'text/html');

                            let text = '';

                            // Handle product-name elements specifically
                            const userNameElements = doc.querySelectorAll('.product-name');
                            if (userNameElements.length > 0) {
                              userNameElements.forEach(el => {
                                // Remove avatar-wrapper before extracting text
                                const avatarWrapper = el.querySelector('.avatar-wrapper');
                                if (avatarWrapper) {
                                  avatarWrapper.remove();
                                }
                                // Get text from nested structure - try different selectors
                                const nameText =
                                  el.querySelector('.fw-medium')?.textContent ||
                                  el.querySelector('.d-block')?.textContent ||
                                  el.textContent;
                                text += nameText.trim() + ' ';
                              });
                            } else {
                              // Handle other elements (status, role, etc)
                              text = doc.body.textContent || doc.body.innerText;
                            }

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
                        columns: [2, 3, 4],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Parse HTML content
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(inner, 'text/html');

                            let text = '';

                            // Handle product-name elements specifically
                            const userNameElements = doc.querySelectorAll('.product-name');
                            if (userNameElements.length > 0) {
                              userNameElements.forEach(el => {
                                // Remove avatar-wrapper before extracting text
                                const avatarWrapper = el.querySelector('.avatar-wrapper');
                                if (avatarWrapper) {
                                  avatarWrapper.remove();
                                }
                                // Get text from nested structure - try different selectors
                                const nameText =
                                  el.querySelector('.fw-medium')?.textContent ||
                                  el.querySelector('.d-block')?.textContent ||
                                  el.textContent;
                                text += nameText.trim() + ' ';
                              });
                            } else {
                              // Handle other elements (status, role, etc)
                              text = doc.body.textContent || doc.body.innerText;
                            }

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
                        columns: [2, 3, 4],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Parse HTML content
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(inner, 'text/html');

                            let text = '';

                            // Handle product-name elements specifically
                            const userNameElements = doc.querySelectorAll('.product-name');
                            if (userNameElements.length > 0) {
                              userNameElements.forEach(el => {
                                // Remove avatar-wrapper before extracting text
                                const avatarWrapper = el.querySelector('.avatar-wrapper');
                                if (avatarWrapper) {
                                  avatarWrapper.remove();
                                }
                                // Get text from nested structure - try different selectors
                                const nameText =
                                  el.querySelector('.fw-medium')?.textContent ||
                                  el.querySelector('.d-block')?.textContent ||
                                  el.textContent;
                                text += nameText.trim() + ' ';
                              });
                            } else {
                              // Handle other elements (status, role, etc)
                              text = doc.body.textContent || doc.body.innerText;
                            }

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
                        columns: [2, 3, 4],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Parse HTML content
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(inner, 'text/html');

                            let text = '';

                            // Handle product-name elements specifically
                            const userNameElements = doc.querySelectorAll('.product-name');
                            if (userNameElements.length > 0) {
                              userNameElements.forEach(el => {
                                // Remove avatar-wrapper before extracting text
                                const avatarWrapper = el.querySelector('.avatar-wrapper');
                                if (avatarWrapper) {
                                  avatarWrapper.remove();
                                }
                                // Get text from nested structure - try different selectors
                                const nameText =
                                  el.querySelector('.fw-medium')?.textContent ||
                                  el.querySelector('.d-block')?.textContent ||
                                  el.textContent;
                                text += nameText.trim() + ' ';
                              });
                            } else {
                              // Handle other elements (status, role, etc)
                              text = doc.body.textContent || doc.body.innerText;
                            }

                            return text.trim();
                          }
                        }
                      }
                    }
                  ]
                },
                {
                  text: '<i class="icon-base ti tabler-plus me-0 me-sm-1 icon-16px"></i><span class="d-none d-sm-inline-block">Add Brand</span>',
                  className: 'add-new btn btn-primary',
                  action: function () {
                    window.location.href = brandAdd;
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
              return 'Details of ' + data['brand'];
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

             }
    });

    dt_products.on('select deselect', function () {
      let selectedCount = dt_products.rows({ selected: true }).count();
      dt_products.button(0).enable(selectedCount > 0);
    });
  }

  // Filter form control to default size
  // ? setTimeout used for product-list table initialization
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
