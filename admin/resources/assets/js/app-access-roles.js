/**
 * App user list
 */

'use strict';


// Datatable (js)
document.addEventListener('DOMContentLoaded', function (e) {
  const dtUserTable = document.querySelector('.datatables-users'),
    statusObj = {
      "active": { title: 'Active', class: 'bg-label-success' },
      "inactive": { title: 'Inactive', class: 'bg-label-danger' }
    };
  let dt_User,
    userView = baseUrl + 'user/view/account',
    changeStatus = baseUrl + 'user/change/status';

  // Users List datatable
  if (dtUserTable) {
    const userRole = document.createElement('div');
    userRole.classList.add('user_role');
    const userPlan = document.createElement('div');
    userPlan.classList.add('user_plan');
    dt_User = new DataTable(dtUserTable, {
      ajax: baseUrl + 'user/ajax/list/with/roles', // JSON file to add data
      columns: [
        // columns according to JSON
        { data: 'id' },
        { data: 'id', orderable: false, render: DataTable.render.select() },
        { data: 'full_name' },
        { data: 'role', width: '15%' },
        { data: 'status', width: '15%' },
        { data: 'id', width: '15%' }
      ],
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          orderable: false,
          searchable: false,
          responsivePriority: 5,
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
          render: function (data, type, full, meta) {
            const name = full['full_name'];
            const email = full['email'];
            const image = full['avatar'];
            let output;

            if (image) {
              // For Avatar image
              output = `<img src="${image}" alt="Avatar" class="rounded-circle">`;
            } else {
              // For Avatar badge
              const stateNum = Math.floor(Math.random() * 6) + 1;
              const states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
              const state = states[stateNum];
              const initials = (name.match(/\b\w/g) || []).slice(0, 2).join('').toUpperCase();
              output = `<span class="avatar-initial rounded-circle bg-label-${state}">${initials}</span>`;
            }

            // Creates full output for row
            const rowOutput = `
              <div class="d-flex justify-content-left align-items-center role-name">
                <div class="avatar-wrapper">
                  <div class="avatar avatar-sm me-3">
                    ${output}
                  </div>
                </div>
                <div class="d-flex flex-column">
                  <a href="${userView}/${full['id']}"><span class="fw-medium">${name}</span></a>
                  <small>${email}</small>
                </div>
              </div>
            `;

            return rowOutput;
          }
        },
        {
          targets: 3,
          render: function (data, type, full, meta) {
            const role = full['role'];
            // const roleBadgeObj = {
            //   "User": '<span class="me-2"><i class="icon-base ti tabler-user icon-22px text-success"></i></span>',
            //   // "Manager":
            //   //   '<span class="me-2"><i class="icon-base ti tabler-device-desktop icon-22px text-danger"></i></span>',
            //   "Manager": '<span class="me-2"><i class="icon-base ti tabler-chart-pie icon-22px text-info"></i></span>',
            //   // "Manager": '<span class="me-2"><i class="icon-base ti tabler-edit icon-22px text-warning"></i></span>',
            //   "Administrator": '<span class="me-2"><i class="icon-base ti tabler-crown icon-22px text-primary"></i></span>'
            // };

            return `<span class='text-truncate d-flex align-items-center'>${role}</span>`;
          }
        },

        {
          // User Status
          targets: 4,
          render: function (data, type, full, meta) {
            let status = full['status'];

            return (
              '<span class="badge ' +
              statusObj[status].class +
              '" text-capitalized>' +
              statusObj[status].title +
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
              <div class="d-flex align-items-center">
                <a href="javascript:;"  onclick="deleteRecord(${full['id']})">
                  <button class="btn btn-icon  rounded-pill waves-effect">
                  <i class="icon-base ti tabler-trash icon-md"></i>
                  </button>
                </a>
                <a href="${userView}/${full['id']}" >
                  <button class="btn btn-icon  rounded-pill waves-effect">
                    <i class="icon-base ti tabler-eye icon-md"></i>
                    </button>
                </a>
                <a href="javascript:;"  data-bs-toggle="dropdown">
                  <button class="btn btn-icon  rounded-pill waves-effect dropdown-toggle hide-arrow">
                    <i class="icon-base ti tabler-dots-vertical icon-md"></i>
                  </button>
                </a>
                  <div class="dropdown-menu dropdown-menu-end m-0">
                    <a href="javascript:;" class="dropdown-item" data-id="${full['id']}" data-bs-target="#ajaxEditUserModal" data-bs-toggle="modal">Edit</a>
                    <a href="${changeStatus}/${full['id']}" class="dropdown-item">${full['status'] == 'active' ? 'Inactive' : 'Active'}</a>
                  </div>
              </div>
            `;
          }
        }
      ],
      select: {
        style: 'multi+shift',
        selector: 'td:nth-child(2)'
      },
      order: [[2, 'desc']],
      lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
      layout: {
        topStart: {
          rowClass: 'row my-md-0 me-3 ms-0 justify-content-between',
          features: [
            {
              pageLength: {
                menu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                text: '_MENU_'
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
                          url: baseUrl + 'user/delete-multiple',
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
                              text: response.message ?? 'Selected users have been deleted.',
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
          features: [
            {
              search: {
                placeholder: 'Search User',
                text: '_INPUT_'
              }
            },
            {
              buttons: [
                {
                  extend: 'collection',
                  className: 'btn btn-label-secondary dropdown-toggle me-4',
                  text: '<span class="d-flex align-items-center gap-1"><i class="icon-base ti tabler-upload icon-xs"></i> <span class="d-inline-block">Export</span></span>',
                  buttons: [
                    {
                      extend: 'print',
                      text: `<span class="d-flex align-items-center"><i class="icon-base ti tabler-printer me-1"></i>Print</span>`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [2, 3, 4],
                        modifier: {
                          page: 'current'
                        },
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
                              const userNameElements = doc.querySelectorAll('.role-name');
                              if (userNameElements.length > 0) {
                                userNameElements.forEach(el => {
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
                        modifier: {
                          page: 'current'
                        },
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Parse HTML content
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(inner, 'text/html');

                            let text = '';

                            // Handle role-name elements specifically
                            const userNameElements = doc.querySelectorAll('.role-name');
                            if (userNameElements.length > 0) {
                              userNameElements.forEach(el => {
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
                      text: `<span class="d-flex align-items-center"><i class="icon-base ti tabler-file-export me-1"></i>Excel</span>`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [2, 3, 4],
                        modifier: {
                          page: 'current'
                        },
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Parse HTML content
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(inner, 'text/html');

                            let text = '';

                            // Handle role-name elements specifically
                            const userNameElements = doc.querySelectorAll('.role-name');
                            if (userNameElements.length > 0) {
                              userNameElements.forEach(el => {
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
                        modifier: {
                          page: 'current'
                        },
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Parse HTML content
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(inner, 'text/html');

                            let text = '';

                            // Handle role-name elements specifically
                            const userNameElements = doc.querySelectorAll('.role-name');
                            if (userNameElements.length > 0) {
                              userNameElements.forEach(el => {
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
                        modifier: {
                          page: 'current'
                        },
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Parse HTML content
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(inner, 'text/html');

                            let text = '';

                            // Handle role-name elements specifically
                            const userNameElements = doc.querySelectorAll('.role-name');
                            if (userNameElements.length > 0) {
                              userNameElements.forEach(el => {
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
                  text: '<i class="icon-base ti tabler-plus me-0 me-sm-1 icon-16px"></i><span class="d-none d-sm-inline-block">Add New User</span>',
                  className: 'add-new btn btn-primary rounded-2 waves-effect waves-light',
                  attr: {
                    'data-bs-toggle': 'modal',
                    'data-bs-target': '#addUserModal'
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
              return 'Details of ' + data['full_name'];
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

    dt_User.on('select deselect', function () {
      let selectedCount = dt_User.rows({ selected: true }).count();
      dt_User.button(0).enable(selectedCount > 0);
    });
    //? The 'delete-record' class is necessary for the functionality of the following code.


    function bindDeleteEvent() {
      const userTable = document.querySelector('.datatables-users');
      const modal = document.querySelector('.dtr-bs-modal');

      if (userTable && userTable.classList.contains('collapsed')) {
        if (modal) {
          modal.addEventListener('click', function (event) {
            if (event.target.parentElement.classList.contains('delete-record')) {
              deleteRecord();
              const closeButton = modal.querySelector('.btn-close');
              if (closeButton) closeButton.click(); // Simulates a click on the close button
            }
          });
        }
      } else {
        const tableBody = userTable?.querySelector('tbody');
        if (tableBody) {
          tableBody.addEventListener('click', function (event) {
            if (event.target.parentElement.classList.contains('delete-record')) {
              deleteRecord(event);
            }
          });
        }
      }
    }

    // Initial event binding
    bindDeleteEvent();

    // Re-bind events when modal is shown or hidden
    document.addEventListener('show.bs.modal', function (event) {
      if (event.target.classList.contains('dtr-bs-modal')) {
        bindDeleteEvent();
      }
    });

    document.addEventListener('hide.bs.modal', function (event) {
      if (event.target.classList.contains('dtr-bs-modal')) {
        bindDeleteEvent();
      }
    });
  }

  // Filter form control to default size
  // ? setTimeout used for multilingual table initialization
  setTimeout(() => {
    const elementsToModify = [
      { selector: '.dt-buttons .btn', classToRemove: 'btn-secondary' },
      { selector: '.dt-buttons.btn-group .btn-group', classToRemove: 'btn-group' },
      { selector: '.dt-buttons.btn-group', classToRemove: 'btn-group', classToAdd: 'd-flex' },
      { selector: '.dt-search .form-control', classToRemove: 'form-control-sm' },
      { selector: '.dt-length .form-select', classToRemove: 'form-select-sm' },
      { selector: '.dt-length', classToAdd: 'mb-md-6 mb-0' },
      { selector: '.dt-layout-start', classToAdd: 'ps-3 mt-0' },
      {
        selector: '.dt-layout-end',
        classToRemove: 'justify-content-between',
        classToAdd: 'justify-content-md-between justify-content-center d-flex flex-wrap gap-4 mt-0 mb-md-0 mb-6'
      },
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

  // On edit role click, update text
  // var roleEditList = document.querySelectorAll('.role-edit-modal'),
  //   roleAdd = document.querySelector('.add-new-role'),
  //   roleTitle = document.querySelector('.role-title');

  // roleAdd.onclick = function () {
  //   roleTitle.innerHTML = 'Add New Role'; // reset text
  // };
  // if (roleEditList) {
  //   roleEditList.forEach(function (roleEditEl) {
  //     roleEditEl.onclick = function () {
  //       roleTitle.innerHTML = 'Edit Role'; // reset text
  //     };
  //   });
  // }
});
