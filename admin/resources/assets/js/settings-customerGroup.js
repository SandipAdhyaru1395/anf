/**
 * Customer Groups List & Modals
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

  const dt_table = $('.datatables-customerGroups');
  if (dt_table.length) {
    const dt = new DataTable(dt_table[0], {
      ajax: baseUrl + 'settings/groups/list/ajax',
      columns: [
        { data: 'id', visible: false },           // hidden ID column for sorting
        { data: 'name', width: '50%' },
        { data: 'customers_count', width: '20%' },
        { data: 'restrict_categories', width: '20%' },
        { data: null, defaultContent: '' }       // Actions column
      ],
      columnDefs: [
        // Name column
        {
          targets: 1,
          render: function (data) {
            return `<span class="text-heading fw-medium">${data || '-'}</span>`;
          }
        },
        // Assigned Customers
        {
          targets: 2,
          render: function (data) {
            return data !== null && data !== undefined ? data : '0';
          }
        },
        // Restrict Categories
        {
          targets: 3,
          render: function (data) {
            return data == 1
              ? '<span class="badge bg-label-danger">Yes</span>'
              : '<span class="badge bg-label-success">No</span>';
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
            <a class="btn btn-text-secondary rounded-pill waves-effect" href="${baseUrl}settings/groups/edit/${full.id}">
              <i class="ti tabler-edit icon-base icon-22px"></i>
            </a>
          </div>`;
          }
        }
      ],
      order: [[0, 'desc']],  // Sort by hidden ID column descending
      responsive: true
    });


    // Delete handler
    $(dt_table).on('click', '.btn-delete-customer-group', function () {
      var id = $(this).data('id');
      if (!id) return;
      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-label-secondary' },
        buttonsStyling: false
      }).then(function (result) {
        if (result.isConfirmed) {
          window.location.href = baseUrl + 'settings/groups/delete/' + id;
        }
      });
    });

    // Refresh after submit
    $('#ajaxEditCustomerGroupForm, #addCustomerGroupForm').on('submit', function () {
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
  // FormValidation JS for Add Customer Group
  const addCustomerGroupForm = document.getElementById('addCustomerGroupForm');
  if (addCustomerGroupForm) {
    FormValidation.formValidation(addCustomerGroupForm, {
      fields: {
        name: {
          validators: {
            notEmpty: { message: 'Please enter customer group name' },
            stringLength: { min: 2, message: 'Name must be at least 2 characters' },
            remote: {
              message: 'Customer group name already exists',
              url: baseUrl + 'settings/groups/check-name',
              method: 'GET',
              data: function () {
                return {
                  id: $('#addCustomerGroupForm').find('#id').val() || null
                };
              }
            }
          }
        },
        restrict_categories: {
          validators: {
            notEmpty: { message: 'Please select an option' }
          }
        }
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          eleValidClass: 'is-valid',
          rowSelector: function (field, ele) { return '.form-control-validation'; }
        }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
        autoFocus: new FormValidation.plugins.AutoFocus()
      }
    });
  }

  // FormValidation JS for Edit Customer Group
  const editCustomerGroupForm = document.getElementById('editCustomerGroupForm');
  if (editCustomerGroupForm) {
    FormValidation.formValidation(editCustomerGroupForm, {
      fields: {
        name: {
          validators: {
            notEmpty: { message: 'Please enter customer group name' },
            stringLength: { min: 2, message: 'Name must be at least 2 characters' },
            remote: {
              message: 'Customer group name already exists',
              url: baseUrl + 'settings/groups/check-name',
              method: 'GET',
              data: function () {
                return {
                  id: $('#editCustomerGroupForm').find('input[name="id"]').val() || null
                };
              }
            }
          }
        },
        restrict_categories: {
          validators: {
            notEmpty: { message: 'Please select an option' }
          }
        }
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          eleValidClass: 'is-valid',
          rowSelector: function (field, ele) { return '.form-control-validation'; }
        }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
        autoFocus: new FormValidation.plugins.AutoFocus()
      }
    });
  }

});
