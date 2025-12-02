/**
 * Sales Report Script
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  let borderColor, bodyBg, headingColor, currencySymbol;

  borderColor = config.colors.borderColor;
  bodyBg = config.colors.bodyBg;
  headingColor = config.colors.headingColor;
  currencySymbol = window.currencySymbol || '';

  const dt_sales_report_table = document.querySelector('.datatables-sales-report');
  let startDatePicker, endDatePicker;
  let dt_sales_report;

  // Function to disable autocomplete on search input (reusable)
  const disableSearchAutocomplete = function() {
    const selectors = [
      '.datatables-sales-report input[type="search"]',
      '.datatables-sales-report input.dt-input',
      '.dataTables_filter input[type="search"]',
      '.dataTables_filter input'
    ];
    
    selectors.forEach(selector => {
      const inputs = document.querySelectorAll(selector);
      inputs.forEach(input => {
        input.setAttribute('autocomplete', 'off');
        input.setAttribute('autocapitalize', 'off');
        input.setAttribute('autocorrect', 'off');
        input.setAttribute('spellcheck', 'false');
      });
    });
  };

  // Use MutationObserver to watch for search input creation
  if (dt_sales_report_table) {
    const observer = new MutationObserver(function(mutations) {
      disableSearchAutocomplete();
    });
    
    observer.observe(document.body, {
      childList: true,
      subtree: true
    });
  }

  if (dt_sales_report_table) {
    dt_sales_report = new DataTable(dt_sales_report_table, {
      processing: true,
      stateSave: true,
      serverSide: true,
      ajax: {
        url: baseUrl + 'report/sales/ajax',
        data: function(d) {
          d.customer = document.getElementById('filter-customer')?.value || '';
          d.start_date = document.getElementById('filter-start-date')?.value || '';
          d.end_date = document.getElementById('filter-end-date')?.value || '';
          d.payment_status = document.getElementById('filter-payment-status')?.value || '';
        }
      },
      columns: [
        { data: 'order_date' },
        { data: 'order_number' },
        { data: 'customer_name' },
        { data: 'subtotal' },
        { data: 'vat_amount' },
        { data: 'total_amount' },
        { data: 'paid_amount' },
        { data: 'unpaid_amount' },
        { data: 'payment_status' }
      ],
      columnDefs: [
        {
          targets: 3,
          render: function (data, type, full, meta) {
            const amount = parseFloat(data || 0);
            return `<span class="text-nowrap">${currencySymbol}${amount.toFixed(2)}</span>`;
          }
        },
        {
          targets: 4,
          render: function (data, type, full, meta) {
            const amount = parseFloat(data || 0);
            return `<span class="text-nowrap">${currencySymbol}${amount.toFixed(2)}</span>`;
          }
        },
        {
          targets: 5,
          render: function (data, type, full, meta) {
            const amount = parseFloat(data || 0);
            return `<span class="text-nowrap fw-bold">${currencySymbol}${amount.toFixed(2)}</span>`;
          }
        },
        {
          targets: 6,
          render: function (data, type, full, meta) {
            const amount = parseFloat(data || 0);
            return `<span class="text-nowrap">${currencySymbol}${amount.toFixed(2)}</span>`;
          }
        },
        {
          targets: 7,
          render: function (data, type, full, meta) {
            const amount = parseFloat(data || 0);
            return `<span class="text-nowrap">${currencySymbol}${amount.toFixed(2)}</span>`;
          }
        }
      ],
      order: [[0, 'desc']], // Order by first column (Date) descending, but backend orders by ID
      layout: {
        topStart: {
          search: {
            placeholder: 'Search Sales Report',
            text: '_INPUT_',
            autocomplete: 'off'
          }
        },
        topEnd: {
          rowClass: 'row mx-3 my-0 justify-content-between',
          features: [
            {
              pageLength: {
                menu: [10, 25, 50, 100],
                text: '_MENU_'
              }
            },
            {
              buttons: [
                {
                  extend: 'collection',
                  className: 'btn btn-label-primary dropdown-toggle me-4',
                  text: '<span class="d-flex align-items-center gap-1"><i class="icon-base ti tabler-upload icon-xs"></i> <span class="d-none d-sm-inline-block">Export</span></span>',
                  buttons: [
                    {
                      extend: 'print',
                      text: `<span class="d-flex align-items-center"><i class="icon-base ti tabler-printer me-1"></i>Print</span>`,
                      className: 'dropdown-item',
                      action: function (e, dt, button, config) {
                        const originalPageLength = dt.page.len();
                        dt.page.len(-1).draw(false);
                        setTimeout(() => {
                          $.fn.dataTable.ext.buttons.print.action.call(this, e, dt, button, config);
                          dt.page.len(originalPageLength).draw(false);
                        }, 500);
                      },
                      exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;
                            const el = new DOMParser().parseFromString(inner, 'text/html').body.childNodes;
                            let result = '';
                            el.forEach(item => {
                              result += item.textContent || item.innerText || '';
                            });
                            return result;
                          }
                        }
                      },
                      customize: function (win) {
                        win.document.body.style.color = headingColor;
                        win.document.body.style.borderColor = borderColor;
                        win.document.body.style.backgroundColor = bodyBg;
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
                      action: function (e, dt, button, config) {
                        const originalPageLength = dt.page.len();
                        dt.page.len(-1).draw(false);
                        setTimeout(() => {
                          $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
                          dt.page.len(originalPageLength).draw(false);
                        }, 500);
                      },
                      exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;
                            const el = new DOMParser().parseFromString(inner, 'text/html').body.childNodes;
                            let result = '';
                            el.forEach(item => {
                              result += item.textContent || item.innerText || '';
                            });
                            return result;
                          }
                        }
                      }
                    },
                    {
                      extend: 'excel',
                      text: `<span class="d-flex align-items-center"><i class="icon-base ti tabler-upload me-1"></i>Excel</span>`,
                      className: 'dropdown-item',
                      action: function (e, dt, button, config) {
                        const originalPageLength = dt.page.len();
                        dt.page.len(-1).draw(false);
                        setTimeout(() => {
                          $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, button, config);
                          dt.page.len(originalPageLength).draw(false);
                        }, 500);
                      },
                      exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;
                            const el = new DOMParser().parseFromString(inner, 'text/html').body.childNodes;
                            let result = '';
                            el.forEach(item => {
                              result += item.textContent || item.innerText || '';
                            });
                            return result;
                          }
                        }
                      }
                    },
                    {
                      extend: 'pdf',
                      text: `<span class="d-flex align-items-center"><i class="icon-base ti tabler-file-text me-1"></i>Pdf</span>`,
                      className: 'dropdown-item',
                      action: function (e, dt, button, config) {
                        const originalPageLength = dt.page.len();
                        dt.page.len(-1).draw(false);
                        setTimeout(() => {
                          $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
                          dt.page.len(originalPageLength).draw(false);
                        }, 500);
                      },
                      exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;
                            const el = new DOMParser().parseFromString(inner, 'text/html').body.childNodes;
                            let result = '';
                            el.forEach(item => {
                              result += item.textContent || item.innerText || '';
                            });
                            return result;
                          }
                        }
                      }
                    },
                    {
                      extend: 'copy',
                      text: `<i class="icon-base ti tabler-copy me-1"></i>Copy`,
                      className: 'dropdown-item',
                      action: function (e, dt, button, config) {
                        const originalPageLength = dt.page.len();
                        dt.page.len(-1).draw(false);
                        setTimeout(() => {
                          $.fn.dataTable.ext.buttons.copyHtml5.action.call(this, e, dt, button, config);
                          dt.page.len(originalPageLength).draw(false);
                        }, 500);
                      },
                      exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;
                            const el = new DOMParser().parseFromString(inner, 'text/html').body.childNodes;
                            let result = '';
                            el.forEach(item => {
                              result += item.textContent || item.innerText || '';
                            });
                            return result;
                          }
                        }
                      }
                    }
                  ]
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
      footerCallback: function (row, data, start, end, display) {
        const api = this.api();
        
        // Get totals from server response
        const json = api.ajax.json();
        if (json && json.totals) {
          const totals = json.totals;
          
          // Update footer cells with totals
          $(api.column(3).footer()).html(
            '<span class="text-nowrap fw-bold">' + currencySymbol + parseFloat(totals.subtotal || 0).toFixed(2) + '</span>'
          );
          $(api.column(4).footer()).html(
            '<span class="text-nowrap fw-bold">' + currencySymbol + parseFloat(totals.vat || 0).toFixed(2) + '</span>'
          );
          $(api.column(5).footer()).html(
            '<span class="text-nowrap fw-bold">' + currencySymbol + parseFloat(totals.amount || 0).toFixed(2) + '</span>'
          );
          $(api.column(6).footer()).html(
            '<span class="text-nowrap fw-bold">' + currencySymbol + parseFloat(totals.paid || 0).toFixed(2) + '</span>'
          );
          $(api.column(7).footer()).html(
            '<span class="text-nowrap fw-bold">' + currencySymbol + parseFloat(totals.balance || 0).toFixed(2) + '</span>'
          );
        } else {
          // If totals not available, show zeros
          $(api.column(3).footer()).html('<span class="text-nowrap fw-bold">' + currencySymbol + '0.00</span>');
          $(api.column(4).footer()).html('<span class="text-nowrap fw-bold">' + currencySymbol + '0.00</span>');
          $(api.column(5).footer()).html('<span class="text-nowrap fw-bold">' + currencySymbol + '0.00</span>');
          $(api.column(6).footer()).html('<span class="text-nowrap fw-bold">' + currencySymbol + '0.00</span>');
          $(api.column(7).footer()).html('<span class="text-nowrap fw-bold">' + currencySymbol + '0.00</span>');
        }
      },
      drawCallback: function (settings) {
        // Update footer totals after each draw (including search, page length change, pagination)
        const api = this.api();
        
        // Disable autocomplete on search input (in case it gets recreated)
        const searchInput = document.querySelector('.datatables-sales-report input[type="search"]') || 
                           document.querySelector('.dataTables_filter input') ||
                           document.querySelector('input[type="search"]');
        if (searchInput) {
          searchInput.setAttribute('autocomplete', 'off');
          searchInput.setAttribute('autocapitalize', 'off');
          searchInput.setAttribute('autocorrect', 'off');
          searchInput.setAttribute('spellcheck', 'false');
        }
        
        // Use a small delay to ensure AJAX response is available
        setTimeout(() => {
          const json = api.ajax.json();
          if (json && json.totals) {
            const totals = json.totals;
            
            // Update footer cells with totals
            $(api.column(3).footer()).html(
              '<span class="text-nowrap fw-bold">' + currencySymbol + parseFloat(totals.subtotal || 0).toFixed(2) + '</span>'
            );
            $(api.column(4).footer()).html(
              '<span class="text-nowrap fw-bold">' + currencySymbol + parseFloat(totals.vat || 0).toFixed(2) + '</span>'
            );
            $(api.column(5).footer()).html(
              '<span class="text-nowrap fw-bold">' + currencySymbol + parseFloat(totals.amount || 0).toFixed(2) + '</span>'
            );
            $(api.column(6).footer()).html(
              '<span class="text-nowrap fw-bold">' + currencySymbol + parseFloat(totals.paid || 0).toFixed(2) + '</span>'
            );
            $(api.column(7).footer()).html(
              '<span class="text-nowrap fw-bold">' + currencySymbol + parseFloat(totals.balance || 0).toFixed(2) + '</span>'
            );
          } else {
            // If totals not available, show zeros
            $(api.column(3).footer()).html('<span class="text-nowrap fw-bold">' + currencySymbol + '0.00</span>');
            $(api.column(4).footer()).html('<span class="text-nowrap fw-bold">' + currencySymbol + '0.00</span>');
            $(api.column(5).footer()).html('<span class="text-nowrap fw-bold">' + currencySymbol + '0.00</span>');
            $(api.column(6).footer()).html('<span class="text-nowrap fw-bold">' + currencySymbol + '0.00</span>');
            $(api.column(7).footer()).html('<span class="text-nowrap fw-bold">' + currencySymbol + '0.00</span>');
          }
        }, 100);
      },
      initComplete: function () {
        const api = this.api();

        // Function to disable autocomplete on search input
        const disableSearchAutocomplete = function() {
          // Try multiple selectors to find the search input
          const selectors = [
            '.datatables-sales-report input[type="search"]',
            '.datatables-sales-report input.dt-input',
            'input[type="search"]',
            '.dataTables_filter input'
          ];
          
          selectors.forEach(selector => {
            const searchInput = document.querySelector(selector);
            if (searchInput) {
              searchInput.setAttribute('autocomplete', 'off');
              searchInput.setAttribute('autocapitalize', 'off');
              searchInput.setAttribute('autocorrect', 'off');
              searchInput.setAttribute('spellcheck', 'false');
            }
          });
        };

        // Set autocomplete off immediately
        disableSearchAutocomplete();
        
        // Also set it after a small delay in case DataTables creates it later
        setTimeout(disableSearchAutocomplete, 100);
        setTimeout(disableSearchAutocomplete, 500);

        // Initialize Select2 for customer dropdown
        if (typeof $ !== 'undefined' && $.fn.select2) {
          const $customerSelect = $('#filter-customer');
          if ($customerSelect.length && !$customerSelect.hasClass('select2-hidden-accessible')) {
            $customerSelect.select2({
              placeholder: 'All Customers',
              allowClear: true,
              width: '100%',
              dropdownParent: $customerSelect.closest('.card-header')
            });
          }
        }

        // Initialize flatpickr for date filters
        if (window.flatpickr) {
          const startDateEl = document.getElementById('filter-start-date');
          const endDateEl = document.getElementById('filter-end-date');
          
          if (startDateEl && !startDatePicker) {
            startDatePicker = flatpickr(startDateEl, {
              dateFormat: 'd/m/Y',
              allowInput: true,
              onChange: function(selectedDates, dateStr, instance) {
                dt_sales_report.draw();
              },
              onClose: function(selectedDates, dateStr, instance) {
                dt_sales_report.draw();
              }
            });
            
            startDateEl.addEventListener('input', function() {
              if (!this.value || this.value.trim() === '') {
                dt_sales_report.draw();
              }
            });
            
            startDateEl.addEventListener('blur', function() {
              if (!this.value || this.value.trim() === '') {
                dt_sales_report.draw();
              }
            });
          }
          
          if (endDateEl && !endDatePicker) {
            endDatePicker = flatpickr(endDateEl, {
              dateFormat: 'd/m/Y',
              allowInput: true,
              onChange: function(selectedDates, dateStr, instance) {
                dt_sales_report.draw();
              },
              onClose: function(selectedDates, dateStr, instance) {
                dt_sales_report.draw();
              }
            });
            
            endDateEl.addEventListener('input', function() {
              if (!this.value || this.value.trim() === '') {
                dt_sales_report.draw();
              }
            });
            
            endDateEl.addEventListener('blur', function() {
              if (!this.value || this.value.trim() === '') {
                dt_sales_report.draw();
              }
            });
          }
        }

        // Setup filter event listeners
        const filterCustomer = document.getElementById('filter-customer');
        const filterPaymentStatus = document.getElementById('filter-payment-status');
        const btnClearFilters = document.getElementById('btn-clear-filters');
        
        if (filterCustomer) {
          // Use jQuery if Select2 is initialized, otherwise use native change event
          if (typeof $ !== 'undefined' && $.fn.select2 && $('#filter-customer').hasClass('select2-hidden-accessible')) {
            $('#filter-customer').on('change', function() {
              dt_sales_report.draw();
            });
          } else {
            filterCustomer.addEventListener('change', function() {
              dt_sales_report.draw();
            });
          }
        }
        
        if (filterPaymentStatus) {
          filterPaymentStatus.addEventListener('change', function() {
            dt_sales_report.draw();
          });
        }
        
        if (btnClearFilters) {
          btnClearFilters.addEventListener('click', function() {
            if (filterCustomer) {
              if (typeof $ !== 'undefined' && $.fn.select2 && $('#filter-customer').hasClass('select2-hidden-accessible')) {
                $('#filter-customer').val('').trigger('change');
              } else {
                filterCustomer.value = '';
              }
            }
            if (startDatePicker) startDatePicker.clear();
            if (endDatePicker) endDatePicker.clear();
            if (filterPaymentStatus) filterPaymentStatus.value = '';
            dt_sales_report.draw();
          });
        }
      }
    });
  }

  // Filter form control to default size
  setTimeout(() => {
    const elementsToModify = [
      { selector: '.dt-buttons .btn:not(.btn-primary)', classToRemove: 'btn-secondary', classToAdd: 'btn-label-secondary' },
      { selector: '.dt-search .form-control', classToRemove: 'form-control-sm', classToAdd: 'ms-0' },
      { selector: '.dt-length .form-select', classToRemove: 'form-select-sm' },
      { selector: '.dt-layout-table', classToRemove: 'row mt-2' },
      { selector: '.dt-layout-end', classToAdd: 'gap-md-2 gap-0' },
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

