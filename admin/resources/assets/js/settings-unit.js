/* global $, $document */

$(function () {
  const table = $('.datatables-units');
  if (table.length) {
    const dt = table.DataTable({
      processing: true,
      serverSide: false,
      ajax: {
        url: baseUrl + 'settings/unit/list/ajax',
        dataSrc: 'data'
      },
      columns: [
        { data: 'name',width: '70%' },
        { data: 'status' },
        { data: null }
      ],
      columnDefs: [
        {
          targets: 1,
          render: function (data) {
            return data === 'Active' ? '<span class="badge bg-label-success">Active</span>' : '<span class="badge bg-label-danger">Inactive</span>';
          }
        },
        {
          targets: 2,
          orderable: false,
          searchable: false,
          render: function (data, type, row) {
            return (
              '<div class="d-inline-block text-nowrap">' +
              '<button class="btn btn-text-secondary rounded-pill waves-effect btn-edit-unit" data-id="' + row.id + '"><i class="ti tabler-edit icon-base icon-22px"></i></button>' +
              '<button class="btn rounded-pill waves-effect btn-delete-unit" data-id="' + row.id + '"><i class="ti tabler-trash icon-base icon-22px"></i></button>' +
              '</div>'
            );
          }
        }
      ]
    });

    table.on('click', '.btn-edit-unit', function () {
      const id = $(this).data('id');
      $.get(baseUrl +'settings/unit/ajax/show', { id }, function (res) {
        if (res) {
          const modal = $('#ajaxEditUnitModal');
          modal.find('input#id').val(res.id);
          modal.find('input#unitName').val(res.name);
          modal.find('select#unitStatus').val(res.status);
          modal.modal('show');
        }
      });
    });

    // Delete handler
    table.on('click', '.btn-delete-unit', function () {
      const id = $(this).data('id');
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
          window.location.href = baseUrl + 'settings/unit/delete/' + id;
        }
      });
    });

    // Refresh after submit (both add and edit)
    $('#ajaxEditUnitForm, #addUnitForm').on('submit', function () {
      setTimeout(function () { dt.ajax.reload(null, false); }, 400);
    });
  }

  // Front-end validation: Add Unit
  const addForm = document.getElementById('addUnitForm');
  if (addForm) {
    FormValidation.formValidation(addForm, {
      fields: {
        unitName: { validators: { notEmpty: { message: 'Please enter name' } } },
        unitStatus: { validators: { notEmpty: { message: 'Please select status' } } }
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

  // Front-end validation: Edit Unit
  const editForm = document.getElementById('ajaxEditUnitForm');
  if (editForm) {
    FormValidation.formValidation(editForm, {
      fields: {
        unitName: { validators: { notEmpty: { message: 'Please enter name' } } },
        unitStatus: { validators: { notEmpty: { message: 'Please select status' } } }
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


