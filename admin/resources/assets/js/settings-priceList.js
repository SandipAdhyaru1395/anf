/**
 * Price List & Modals
 */
'use strict';

$(function () {

  // Initialize Select2 (if any)
  $('.select2').each(function () {
    var $this = $(this);
    $this.wrap('<div class="position-relative"></div>').select2({
      placeholder: 'Select value',
      dropdownParent: $this.parent()
    });
  });

  const dt_table = $('.datatables-priceList');
  if (dt_table.length) {
    const dt = new DataTable(dt_table[0], {
      ajax: baseUrl + 'settings/priceList/list/ajax',
      columns: [
        { data: 'id', visible: false },           // hidden ID column for sorting
        { data: 'name' },
        { data: 'price_list_type' },
        { data: 'customers_count', width: '10%' },
        { data: null, defaultContent: '', width: '10%' }       // Actions column
      ],
      columnDefs: [
        // Name column
        {
          targets: 1,
          render: function (data) {
            return `<span class="text-heading fw-medium">${data || '-'}</span>`;
          }
        },
        // Price list type
        {
          targets: 2,
          render: function (data) {
            return data;
          }
        },
        // Assigned Customers
        {
          targets: 3,
          render: function (data) {
            return data !== null && data !== undefined ? data : '0';
          }
        },
        // Actions
        {
          targets: 4,
          orderable: false,
          searchable: false,
          render: function (data, type, full) {
            return `
          <div class="d-inline-block text-nowrap">
            <a  href="${baseUrl}settings/priceList/edit/${full.id}">
              <button class="btn btn-text-secondary rounded-pill waves-effect">
              <i class="ti tabler-edit icon-base icon-22px"></i>
              </button>  
            </a>
            <button class="btn rounded-pill waves-effect btn-delete-priceList" data-id="${full.id}">
              <i class="ti tabler-trash icon-base icon-22px"></i>
            </button>
          </div>`;
          }
        }
      ],
      order: [[0, 'desc']],  // Sort by hidden ID column descending
      responsive: true
    });


    // Delete handler
    $(dt_table).on('click', '.btn-delete-priceList', function () {

      var id = $(this).data('id');
      if (!id) return;

      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        customClass: {
          confirmButton: 'btn btn-primary me-3',
          cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
      }).then(function (result) {

        if (result.isConfirmed) {

          $.ajax({
            url: baseUrl + 'settings/priceList/delete/' + id,
            type: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {

              if (response.status) {

                Swal.fire({
                  icon: 'success',
                  title: 'Deleted!',
                  text: response.message,
                  customClass: {
                    confirmButton: 'btn btn-success'
                  }
                }).then(() => {
                  dt_table.DataTable().ajax.reload();
                });

              } else {

                Swal.fire({
                  icon: 'error',
                  title: 'Cannot Delete',
                  text: response.message,
                  customClass: {
                    confirmButton: 'btn btn-danger'
                  }
                });
              }
            },
            error: function (xhr) {

              let message = 'Something went wrong.';

              if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
              }

              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message,
                customClass: {
                  confirmButton: 'btn btn-danger'
                }
              });
            }
          });
        }
      });
    });


    // Refresh after submit
    $('#ajaxEditPriceListForm, #addPriceListForm').on('submit', function () {
      setTimeout(function () { dt.ajax.reload(null, false); }, 400);
    });
  }

  // Show/hide categories table
  const restrictSelect = document.getElementById('restrict_categories');
  const categoriesTree = document.getElementById('categories_tree');

  if (restrictSelect) {
    restrictSelect.addEventListener('change', function () {
      categoriesTree.style.display = this.value == '1' ? 'block' : 'none';
    });
  }

  // Parent selects/unselects all children
  document.querySelectorAll('.parent-category').forEach(parent => {
    parent.addEventListener('change', function () {
      const row = this.closest('tr');
      const children = row.querySelectorAll('.child-category');
      children.forEach(c => c.checked = this.checked);
    });
  });

  // Child toggles parent checkbox if any child is selected
  document.querySelectorAll('.child-category').forEach(child => {
    child.addEventListener('change', function () {
      const row = this.closest('tr');
      const parent = row.querySelector('.parent-category');
      const children = row.querySelectorAll('.child-category');
      parent.checked = Array.from(children).some(c => c.checked);
    });
  });

  document.querySelectorAll('.parent-category').forEach(parent => {
    parent.addEventListener('change', function () {
      const row = this.closest('tr');
      const children = row.querySelectorAll('.brand-checkbox');
      children.forEach(c => c.checked = this.checked);
    });
  });

  // Child toggles parent checkbox if any child is selected
  document.querySelectorAll('.brand-checkbox').forEach(child => {
    child.addEventListener('change', function () {
      const row = this.closest('tr');
      const parent = row.querySelector('.parent-category');
      const children = row.querySelectorAll('.brand-checkbox');
      parent.checked = Array.from(children).some(c => c.checked);
    });
  });
  // FormValidation JS for Add Price List
  const addPriceListForm = document.getElementById('addPriceListForm');

  if (addPriceListForm) {
    FormValidation.formValidation(addPriceListForm, {
      fields: {
        name: {
          validators: {
            notEmpty: {
              message: 'Please enter price list name'
            },
            stringLength: {
              min: 2,
              max: 255,
              message: 'Name must be between 2 and 255 characters'
            },
            remote: {
              message: 'Price list name already exists',
              url: baseUrl + 'settings/priceList/check-name',
              method: 'GET',
              data: function () {
                return {
                  id: $('#addPriceListForm').find('#id').val() || null
                };
              }
            }
          }
        },
        conversion_rate: {
          validators: {
            notEmpty: {
              message: 'Please enter conversion rate'
            },
            numeric: {
              message: 'Conversion rate must be a number'
            },
            greaterThan: {
              min: 0,
              inclusive: true,
              message: 'Conversion rate cannot be negative'
            },
            lessThan: {
              max: 100000,
              inclusive: true,
              message: 'Conversion rate cannot exceed 100000.00'
            },
            regexp: {
              regexp: /^\d+(\.\d{1,2})?$/,
              message: 'Maximum 2 decimal places allowed'
            }
          }
        },
        price_list_type: {
          validators: {
            notEmpty: {
              message: 'Please select price list type'
            },
            regexp: {
              // Valid values: 0 or 1
              regexp: /^[01]$/,
              message: 'Invalid price list type selected'
            }
          }
        }
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          eleValidClass: '',
          rowSelector: function (field, ele) {
            return '.form-control-validation';
          }
        }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
        autoFocus: new FormValidation.plugins.AutoFocus()
      }
    });
  }


  // FormValidation JS for Edit Price List
  const editPriceListForm = document.getElementById('editPriceListForm');
  if (editPriceListForm) {
    FormValidation.formValidation(editPriceListForm, {
      fields: {
        name: {
          validators: {
            notEmpty: {
              message: 'Please enter price list name'
            },
            stringLength: {
              min: 2,
              max: 255,
              message: 'Name must be between 2 and 255 characters'
            },
            remote: {
              message: 'Price list name already exists',
              url: baseUrl + 'settings/priceList/check-name',
              method: 'GET',
              data: function () {
                return {
                  id: $('#editPriceListForm').find('#id').val() || null
                };
              }
            }
          }
        },
        conversion_rate: {
          validators: {
            notEmpty: {
              message: 'Please enter conversion rate'
            },
            numeric: {
              message: 'Conversion rate must be a number'
            },
            greaterThan: {
              min: 0,
              inclusive: true,
              message: 'Conversion rate cannot be negative'
            },
            lessThan: {
              max: 100000,
              inclusive: true,
              message: 'Conversion rate cannot exceed 100000.00'
            },
            regexp: {
              regexp: /^\d+(\.\d{1,2})?$/,
              message: 'Maximum 2 decimal places allowed'
            }
          }
        },
        price_list_type: {
          validators: {
            notEmpty: {
              message: 'Please select price list type'
            },
            regexp: {
              // Valid values: 0 or 1
              regexp: /^[01]$/,
              message: 'Invalid price list type selected'
            }
          }
        }
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          eleValidClass: '',
          rowSelector: function (field, ele) { return '.form-control-validation'; }
        }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
        autoFocus: new FormValidation.plugins.AutoFocus()
      }
    });
  }

});
