/* global $, baseUrl */

$(function () {
  const table = $('.datatables-currencies');
  if (table.length) {
    const dt = table.DataTable({
      processing: true,
      serverSide: false,
      ajax: {
        url: baseUrl + 'settings/currencies/list/ajax',
        dataSrc: 'data'
      },
      columns: [
        { data: 'currency_code' },
        { data: 'currency_name' },
        { data: 'symbol' },
        { data: 'exchange_rate' },
        { data: null }
      ],
      columnDefs: [
        {
          targets: 4,
          orderable: false,
          searchable: false,
          render: function (data, type, row) {
            return (
              '<div class="d-inline-block text-nowrap">' +
              '<button class="btn btn-text-secondary rounded-pill waves-effect btn-edit-currency" data-id="' + row.id + '"><i class="ti tabler-edit icon-base icon-22px"></i></button>' +
              '<button class="btn rounded-pill waves-effect btn-delete-currency" data-id="' + row.id + '"><i class="ti tabler-trash icon-base icon-22px"></i></button>' +
              '</div>'
            );
          }
        }
      ]
    });

    table.on('click', '.btn-edit-currency', function () {
      const id = $(this).data('id');
      $.get(baseUrl + 'settings/currencies/ajax/show', { id: id }, function (res) {
        if (res) {
          const modal = $('#ajaxEditCurrencyModal');
          modal.find('input#currency_id').val(res.id);
          modal.find('input#edit_currency_code').val(res.currency_code);
          modal.find('input#edit_currency_name').val(res.currency_name);
          modal.find('input#edit_symbol').val(res.symbol);
          modal.find('input#edit_exchange_rate').val(res.exchange_rate);
          modal.modal('show');
        }
      });
    });

    table.on('click', '.btn-delete-currency', function () {
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
          $.ajax({
            url: baseUrl + 'settings/currencies/delete/' + id,
            type: 'GET',
            dataType: 'json'
          }).done(function (res) {
            if (res && res.success) {
              Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: res.message || 'Currency deleted successfully!',
                customClass: { confirmButton: 'btn btn-primary' },
                buttonsStyling: false
              }).then(function () {
                dt.ajax.reload(null, false);
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: (res && res.message) || 'Failed to delete currency.',
                customClass: { confirmButton: 'btn btn-primary' },
                buttonsStyling: false
              });
            }
          }).fail(function (xhr) {
            const msg = (xhr.responseJSON && xhr.responseJSON.message) || xhr.statusText || 'Failed to delete currency.';
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: msg,
              customClass: { confirmButton: 'btn btn-primary' },
              buttonsStyling: false
            });
          });
        }
      });
    });

    $('#ajaxEditCurrencyForm, #addCurrencyForm').on('submit', function () {
      setTimeout(function () { dt.ajax.reload(null, false); }, 400);
    });
  }

  const addForm = document.getElementById('addCurrencyForm');
  if (addForm) {
    FormValidation.formValidation(addForm, {
      fields: {
        currency_code: { validators: { notEmpty: { message: 'Currency Code is required' } } },
        currency_name: { validators: { notEmpty: { message: 'Currency Name is required' } } },
        symbol: { validators: { notEmpty: { message: 'Symbol is required' } } },
        exchange_rate: { validators: { notEmpty: { message: 'Exchange Rate is required' } } }
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

  const editForm = document.getElementById('ajaxEditCurrencyForm');
  if (editForm) {
    FormValidation.formValidation(editForm, {
      fields: {
        currency_code: { validators: { notEmpty: { message: 'Currency Code is required' } } },
        currency_name: { validators: { notEmpty: { message: 'Currency Name is required' } } },
        symbol: { validators: { notEmpty: { message: 'Symbol is required' } } },
        exchange_rate: { validators: { notEmpty: { message: 'Exchange Rate is required' } } }
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
