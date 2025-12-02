/**
 * App Settings Script
 */
'use strict';

// Delivery Method List & Modals
$(function () {
  const select2 = $('.select2');
  if (select2.length) {
    select2.each(function () {
      var $this = $(this);
      $this.wrap('<div class="position-relative"></div>').select2({
        placeholder: 'Select value',
        dropdownParent: $this.parent()
      });
    });
  }

  const dt_table = $('.datatables-deliveryMethods');
  if (dt_table.length) {
    const dt = new DataTable(dt_table[0], {
      ajax: baseUrl + 'settings/delivery-method/list/ajax',
      columns: [
        { data: 'id' },
        { data: 'name',width: '30%'  },
        { data: 'time',width: '30%'  },
        { data: 'rate' },
        { data: 'status' },
        { data: null, defaultContent: '' }
      ],
      columnDefs: [
        {
          className: 'control',
          searchable: false,
          orderable: false,
          responsivePriority: 2,
          targets: 0,
          render: function () { return ''; }
        },
        {
          targets: 1,
          responsivePriority: 3,
          render: function (data) { return '<span class="text-heading fw-medium">' + (data || '-') + '</span>'; }
        },
        {
          targets: 2,
          render: function (data) { return data ? data : '-'; }
        },
        {
          targets: 3,
          render: function (data) { return data !== null && data !== undefined ? Number(data).toFixed(2) : '-'; }
        },
        {
          targets: 4,
          render: function (data) {
            if (data === 'Active') {
              return '<span class="badge bg-label-success">Active</span>';
            } else {
              return '<span class="badge bg-label-danger">Inactive</span>';
            }
          }
        },
        {
          targets: 5,
          orderable: false,
          searchable: false,
          render: function (data, type, full) {
            return (
              '<div class="d-inline-block text-nowrap">' +
              '<button class="btn btn-text-secondary rounded-pill waves-effect" data-bs-toggle="modal" data-bs-target="#ajaxEditDeliveryMethodModal" data-id="' + full.id + '"><i class="ti tabler-edit icon-base icon-22px"></i></button>' +
              '<button class="btn rounded-pill waves-effect btn-delete-delivery" data-id="' + full.id + '"><i class="ti tabler-trash icon-base icon-22px"></i></button>' +
              '</div>'
            );
          }
        }
      ],
      order: [[2, 'asc']],
      responsive: true
    });

    // Load data into Edit modal
    $('#ajaxEditDeliveryMethodModal').on('show.bs.modal', function (e) {
      var id = $(e.relatedTarget).data('id');
      if (id) {
        $.ajax({
          url: baseUrl + 'settings/delivery-method/ajax/show',
          type: 'GET',
          data: { id: id },
          success: function (response) {
            $('#ajaxEditDeliveryMethodForm').find('#id').val(response.id);
            $('#ajaxEditDeliveryMethodForm').find('#dmName').val(response.name);
            $('#ajaxEditDeliveryMethodForm').find('#dmTime').val(response.time);
            $('#ajaxEditDeliveryMethodForm').find('#dmPrice').val(response.rate);
            $('#ajaxEditDeliveryMethodForm').find('#dmStatus').val(response.status).trigger('change');
            $('#ajaxEditDeliveryMethodForm').find('#dmSortOrder').val(response.sort_order || '');
          }
        });
      }
    });

    // Delete handler
    $(dt_table).on('click', '.btn-delete-delivery', function () {
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
          window.location.href = baseUrl + 'settings/delivery-method/delete/' + id;
        }
      });
    });

    // Refresh after submit
    $('#ajaxEditDeliveryMethodForm, #addDeliveryMethodForm').on('submit', function () {
      setTimeout(function () { dt.ajax.reload(null, false); }, 400);
    });
  }

  // Front-end validation: Add form
  const addForm = document.getElementById('addDeliveryMethodForm');
  if (addForm) {
    FormValidation.formValidation(addForm, {
      fields: {
        dmName: { validators: { notEmpty: { message: 'Please enter name' } } },
        dmTime: { validators: { notEmpty: { message: 'Please enter delivery time' } } },
        dmPrice: { validators: { notEmpty: { message: 'Please enter rate' }, numeric: { message: 'Rate must be a number' } } },
        dmStatus: { validators: { notEmpty: { message: 'Please select status' } } },
        dmSortOrder: { validators: { integer: { message: 'Sort order must be an integer' } } }
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({ eleValidClass: 'is-valid', rowSelector: function () { return '.form-control-validation'; } }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
        autoFocus: new FormValidation.plugins.AutoFocus()
      }
    });
  }

  // Front-end validation: Edit form
  const editForm = document.getElementById('ajaxEditDeliveryMethodForm');
  if (editForm) {
    FormValidation.formValidation(editForm, {
      fields: {
        dmName: { validators: { notEmpty: { message: 'Please enter name' } } },
        dmTime: { validators: { notEmpty: { message: 'Please enter delivery time' } } },
        dmPrice: { validators: { notEmpty: { message: 'Please enter rate' }, numeric: { message: 'Rate must be a number' } } },
        dmStatus: { validators: { notEmpty: { message: 'Please select status' } } },
        dmSortOrder: { validators: { integer: { message: 'Sort order must be an integer' } } }
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({ eleValidClass: 'is-valid', rowSelector: function () { return '.form-control-validation'; } }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
        autoFocus: new FormValidation.plugins.AutoFocus()
      }
    });
  }
});
