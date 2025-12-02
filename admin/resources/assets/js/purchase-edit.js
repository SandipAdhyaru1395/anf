/**
 * Purchase Edit Script
 */
'use strict';

(function () {
  $(document).ready(function() {
    // Get currency symbol
    const currencySymbol = window.currencySymbol || '';
    // Initialize flatpickr for date & time
    $('.flatpickr').flatpickr({
      enableTime: true,
      dateFormat: 'd/m/Y H:i',
      time_24hr: true,
      allowInput: true
    });

    // Initialize select2 for supplier dropdown
    const $supplierSelect = $('#supplier_id');
    if ($supplierSelect.length) {
      $supplierSelect.select2({
        placeholder: 'Select Supplier',
        allowClear: false,
        width: '100%',
        dropdownParent: $('#editPurchaseForm')
      });
    }

    // Initialize select2 for deliver dropdown
    const $deliverSelect = $('#deliver');
    if ($deliverSelect.length) {
      $deliverSelect.select2({
        minimumResultsForSearch: Infinity,
        width: '100%',
        dropdownParent: $('#editPurchaseForm')
      });
    }

    // Product search textbox
    const MAX_RESULTS = 20;
    const $productSearch = $('#product-search');
    const $productResults = $('#product-search-results');
    const $productSpinner = $('#product-search-spinner');
    let activeIndex = -1;
    let searchTimeout = null;
    let currentQuery = '';
    let currentRequest = null;

    function resetResults() {
      if (currentRequest && currentRequest.readyState !== 4) {
        currentRequest.abort();
      }
      currentRequest = null;
      activeIndex = -1;
      $productResults.empty().hide();
    }

    function setActiveItem(index) {
      const $items = $productResults.find('.list-group-item');
      $items.removeClass('active');
      if (index >= 0 && index < $items.length) {
        $items.eq(index).addClass('active');
        activeIndex = index;
      }
    }

    function renderResults(items) {
      $productResults.empty();
      if (!items.length) {
        activeIndex = -1;
        $productResults
          .append('<div class="list-group-item bg-white text-muted">No results found</div>')
          .show();
        return;
      }

      items.forEach(function(item) {
        const $button = $('<button type="button" class="list-group-item list-group-item-action bg-white text-dark"></button>')
          .text(item.text)
          .data('productId', item.id)
          .data('productText', item.text)
          .data('productCost', item.unit_cost || 0)
          .data('productVat', item.vat_amount || 0);
        $productResults.append($button);
      });

      $productResults.show();
      setActiveItem(0);
    }

    function fetchProducts(query) {
      if (currentRequest) {
        currentRequest.abort();
      }

      currentRequest = $.ajax({
        url: baseUrl + 'purchase/search/ajax',
        dataType: 'json',
        data: {
          q: query,
          limit: MAX_RESULTS
        }
      }).done(function(data) {
        if (currentQuery !== query) {
          return;
        }
        const results = data && data.results ? data.results.slice(0, MAX_RESULTS) : [];
        renderResults(results);
      }).fail(function(xhr, status) {
        if (status !== 'abort') {
          resetResults();
        }
      }).always(function() {
        currentRequest = null;
        $productSpinner.hide();
      });
    }

    $productSearch.on('input', function() {
      const query = $(this).val().trim();
      currentQuery = query;
      clearTimeout(searchTimeout);

      if (!query) {
        resetResults();
        return;
      }

      searchTimeout = setTimeout(function() {
        $productSpinner.show();
        fetchProducts(query);
      }, 200);
    });

    $productSearch.on('keydown', function(e) {
      if (!$productResults.is(':visible')) {
        return;
      }

      const $items = $productResults.find('.list-group-item');
      if (!$items.length) {
        return;
      }

      if (e.key === 'ArrowDown') {
        e.preventDefault();
        const nextIndex = activeIndex >= $items.length - 1 ? 0 : activeIndex + 1;
        setActiveItem(nextIndex);
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        const prevIndex = activeIndex <= 0 ? $items.length - 1 : activeIndex - 1;
        setActiveItem(prevIndex);
      } else if (e.key === 'Enter') {
        if (activeIndex >= 0) {
          e.preventDefault();
          $items.eq(activeIndex).trigger('click');
        }
      } else if (e.key === 'Escape') {
        resetResults();
      }
    });

    $productResults.on('mousedown', '.list-group-item', function(e) {
      e.preventDefault();
    });

    $productResults.on('mouseenter', '.list-group-item', function() {
      setActiveItem($(this).index());
    });

    $productResults.on('click', '.list-group-item', function() {
      if (!$(this).data('productId')) {
        return; // Non-selectable (e.g., No results found)
      }
      const productId = $(this).data('productId');
      const productText = $(this).data('productText');
      const productCost = $(this).data('productCost') || 0;
      const productVat = $(this).data('productVat') || 0;
      window.addProductToTable(productId, productText, 1, productCost, productVat);
      $productSearch.val('').trigger('focus');
      resetResults();
    });

    $(document).on('click', function(e) {
      if (!$(e.target).closest('#product-search-container').length) {
        resetResults();
      }
    });

    setTimeout(function() {
      $productSearch.trigger('focus');
    }, 0);

    // Calculate totals (quantity and amount)
    function calculateTotal() {
      var totalQty = 0;
      var totalAmount = 0;
      $('#products-table tbody tr:not(.total-row)').each(function() {
        var $row = $(this);
        var qty = parseFloat($row.find('.quantity-input').val()) || 0;
        var cost = parseFloat($row.find('.cost-input').val()) || 0;
        var vat = parseFloat($row.find('.vat-input').val()) || 0;
        
        // Calculate totals
        var totalCost = qty * cost;
        var totalVat = qty * vat;
        var sub = totalCost + totalVat;
        
        $row.find('.subtotal-cell').text(currencySymbol + sub.toFixed(2));
        totalQty += qty;
        totalAmount += sub;
      });
      $('.total-quantity').text(totalQty.toFixed(2));
      $('.total-amount').text(currencySymbol + totalAmount.toFixed(2));
    }

    // Add product row - make it available globally for initialization
    window.addProductToTable = function(productId, productText, quantity = 1, unit_cost = 0, unit_vat = 0) {
      // Check if product already exists in the table
      var existingRow = $('#products-table tbody tr[data-product-id="' + productId + '"]:not(.total-row)');
      
      if (existingRow.length > 0) {
        // Product exists, increment quantity
        var quantityInput = existingRow.find('.quantity-input');
        var currentQty = parseFloat(quantityInput.val()) || 0;
        var newQty = currentQty + quantity;
        quantityInput.val(newQty);
        calculateTotal();
        return;
      }
      
      // Product doesn't exist, add new row
      var row = '<tr data-product-id="' + productId + '">' +
        '<td>' + productText + '<input type="hidden" name="products[' + productId + '][product_id]" value="' + productId + '"></td>' +
        '<td><input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)" class="form-control form-control-sm cost-input" name="products[' + productId + '][unit_cost]" value="' + unit_cost + '" autocomplete="off"></td>' +
        '<td><input type="text" onkeypress="return /^[0-9]+$/.test(event.key)" class="form-control form-control-sm quantity-input" name="products[' + productId + '][quantity]" value="' + quantity + '" autocomplete="off"></td>' +
        '<td class="vat-cell">' + currencySymbol + parseFloat(unit_vat || 0).toFixed(2) + '<input type="hidden" class="vat-input" name="products[' + productId + '][unit_vat]" value="' + unit_vat + '"></td>' +
        '<td class="subtotal-cell">' + currencySymbol + '0.00</td>' +
        '<td><a href="javascript:;" title="Remove" class="remove-product"><i class="icon-base ti tabler-x"></i></a></td>' +
        '</tr>';
      
      // Insert before the total-row, or prepend if total-row doesn't exist
      var $totalRow = $('#products-table tbody tr.total-row');
      if ($totalRow.length > 0) {
        $totalRow.before(row);
      } else {
        $('#products-table tbody').prepend(row);
      }
      calculateTotal();
    };

    // Remove product row
    $(document).on('click', '.remove-product', function() {
      $(this).closest('tr').remove();
      calculateTotal();
    });

    // Update totals on input change
    $(document).on('input', '.quantity-input, .cost-input, .vat-input', calculateTotal);

    // Initialize Quill editor for note
    const noteEditor = document.querySelector('#note-editor');
    var quill;
    if (noteEditor) {
      quill = new Quill(noteEditor, {
        modules: {
          toolbar: '.comment-toolbar'
        },
        placeholder: 'Add note...',
        theme: 'snow'
      });

      // Set initial content - will be set from blade file
      const initialNote = document.getElementById('note')?.value || '';
      if (initialNote) {
        quill.root.innerHTML = initialNote;
      }

      // Update hidden input on editor change
      quill.on('text-change', function() {
        let content = quill.root.innerHTML;
        if (content === '<p><br></p>') {
          content = ''; // Treat as empty
        }
        $('#note').val(content);
      });
    }

    // Calculate totals on initial load to format with currency symbol
    setTimeout(function() {
      calculateTotal();
    }, 300);

    // FormValidation for date, supplier, products, cost price, and quantity
    const editPurchaseForm = document.getElementById('editPurchaseForm');
    if (editPurchaseForm) {
      const fv = FormValidation.formValidation(editPurchaseForm, {
        fields: {
          date: {
            validators: {
              notEmpty: {
                message: 'Date is required'
              },
              callback: {
                message: 'Enter a valid date in dd/mm/yyyy hh:mm format',
                callback: function(input) {
                  const value = (input.value || '').trim();
                  if (!value) return false;
                  const re = /^\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2}$/;
                  if (!re.test(value)) return false;
                  try {
                    const parsed = flatpickr.parseDate(value, 'd/m/Y H:i');
                    return parsed instanceof Date && !isNaN(parsed.getTime());
                  } catch (e) {
                    return false;
                  }
                }
              }
            }
          },
          supplier_id: {
            validators: {
              notEmpty: {
                message: 'Supplier is required'
              }
            }
          },
          products: {
            selector: '#products-table',
            validators: {
              callback: {
                message: 'Please add at least one product',
                callback: function() {
                  return $('#products-table tbody tr:not(.total-row)').length > 0;
                }
              }
            }
          },
          cost_price: {
            selector: '#products-table',
            validators: {
              callback: {
                message: 'Cost price is required and must be a valid number (0 or greater)',
                callback: function() {
                  let isValid = true;
                  $('#products-table tbody tr:not(.total-row)').each(function() {
                    const costInput = $(this).find('.cost-input');
                    const costValue = costInput.val().trim();
                    
                    // Check if required (not empty)
                    if (!costValue || costValue === '') {
                      isValid = false;
                      return false; // break loop
                    }
                    
                    // Check if numeric
                    if (isNaN(costValue) || !/^\d+(\.\d+)?$/.test(costValue)) {
                      isValid = false;
                      return false; // break loop
                    }
                    
                    // Check if >= 0
                    const costNum = parseFloat(costValue);
                    if (costNum < 0) {
                      isValid = false;
                      return false; // break loop
                    }
                  });
                  return isValid;
                }
              }
            }
          },
          quantity: {
            selector: '#products-table',
            validators: {
              callback: {
                message: 'Quantity is required and must be a valid number (at least 1)',
                callback: function() {
                  let isValid = true;
                  $('#products-table tbody tr:not(.total-row)').each(function() {
                    const qtyInput = $(this).find('.quantity-input');
                    const qtyValue = qtyInput.val().trim();
                    
                    // Check if required (not empty)
                    if (!qtyValue || qtyValue === '') {
                      isValid = false;
                      return false; // break loop
                    }
                    
                    // Check if numeric (integer)
                    if (isNaN(qtyValue) || !/^\d+$/.test(qtyValue)) {
                      isValid = false;
                      return false; // break loop
                    }
                    
                    // Check if >= 1
                    const qtyNum = parseFloat(qtyValue);
                    if (qtyNum < 1) {
                      isValid = false;
                      return false; // break loop
                    }
                  });
                  return isValid;
                }
              }
            }
          },
          shipping_charge: {
            validators: {
              callback: {
                message: 'Shipping charge must be a valid number (0 or greater)',
                callback: function(input) {
                  const value = (input.value || '').trim();
                  // Allow empty (optional field)
                  if (!value || value === '') {
                    return true;
                  }
                  // Check if numeric
                  if (isNaN(value) || !/^\d+(\.\d+)?$/.test(value)) {
                    return false;
                  }
                  // Check if >= 0
                  const numValue = parseFloat(value);
                  return numValue >= 0;
                }
              }
            }
          }
        },
        plugins: {
          trigger: new FormValidation.plugins.Trigger(),
          bootstrap5: new FormValidation.plugins.Bootstrap5({
            eleValidClass: 'is-valid',
            rowSelector: function(field, ele) {
              if (field === 'products' || field === 'cost_price' || field === 'quantity') {
                return '.card-body';
              }
              return '.form-control-validation';
            }
          }),
          submitButton: new FormValidation.plugins.SubmitButton(),
          autoFocus: new FormValidation.plugins.AutoFocus()
        }
      });

      // Revalidate products, cost_price, and quantity fields when rows change
      function revalidateProductFields() {
        fv.revalidateField('products');
        fv.revalidateField('cost_price');
        fv.revalidateField('quantity');
      }
      
      $(document).on('click', '.remove-product', function() {
        revalidateProductFields();
      });
      $(document).on('click', '#product-search-results .list-group-item, #product-search-results button', function() {
        revalidateProductFields();
      });
      
      // Revalidate cost_price and quantity on input change
      $(document).on('input', '.cost-input', function() {
        fv.revalidateField('cost_price');
      });
      $(document).on('input', '.quantity-input', function() {
        fv.revalidateField('quantity');
      });
      $(document).on('input', '#shipping_charge', function() {
        fv.revalidateField('shipping_charge');
      });
      
      const tbody = document.querySelector('#products-table tbody');
      if (tbody) {
        const observer = new MutationObserver(function() {
          revalidateProductFields();
        });
        observer.observe(tbody, { childList: true, subtree: false });
      }

      // Submit form after successful validation
      fv.on('core.form.valid', function () {
        editPurchaseForm.submit();
      });
    }
  });
})();

