/**
 * Purchase Add Script
 */
'use strict';

(function () {
  $(document).ready(function() {
    // Get currency symbol
    const currencySymbol = window.currencySymbol || '';
    // Initialize flatpickr for date
    $('.flatpickr').flatpickr({
      enableTime: true,
      dateFormat: 'd/m/Y H:i',
      time_24hr: true,
      allowInput: true,
      defaultDate: new Date()
    });

    // Initialize select2 for supplier dropdown
    const $supplierSelect = $('#supplier_id');
    if ($supplierSelect.length) {
      $supplierSelect.select2({
        placeholder: 'Select Supplier',
        allowClear: false,
        width: '100%',
        dropdownParent: $('#addPurchaseForm')
      }).on('change', saveFormStateDebounced);
    }

    // Initialize select2 for deliver dropdown
    const $deliverSelect = $('#deliver');
    if ($deliverSelect.length) {
      $deliverSelect.select2({
        minimumResultsForSearch: Infinity, // hide search for two static options
        width: '100%',
        dropdownParent: $('#addPurchaseForm')
      }).on('change', saveFormStateDebounced);
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
          .data('productCost', item.unit_cost)
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
      // Prevent input from losing focus before click handler runs
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
      const productCost = $(this).data('productCost');
      const productVat = $(this).data('productVat') || 0;

      addProductToTable(productId, productText, productCost, productVat);
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

    // Add product row
    function addProductToTable(productId, productText, productCost, productVat) {
      productVat = productVat || 0;
      // Check if product already exists in the table
      var existingRow = $('#products-table tbody tr[data-product-id="' + productId + '"]:not(.total-row)');
      
      if (existingRow.length > 0) {
        // Product exists, increment quantity
        var quantityInput = existingRow.find('.quantity-input');
        var currentQty = parseFloat(quantityInput.val()) || 0;
        var newQty = currentQty + 1;
        quantityInput.val(newQty);
        calculateTotal();
        return;
      }
      
      // Product doesn't exist, add new row
      var row = '<tr data-product-id="' + productId + '">' +
        '<td>' + productText + '<input type="hidden" name="products[' + productId + '][product_id]" value="' + productId + '"></td>' +
        '<td><input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)" class="form-control form-control-sm cost-input" name="products[' + productId + '][unit_cost]" value="'+productCost+'" autocomplete="off"></td>' +
        '<td><input type="text" onkeypress="return /^[0-9]+$/.test(event.key)" class="form-control form-control-sm quantity-input" name="products[' + productId + '][quantity]" value="1" autocomplete="off"></td>' +
        '<td class="vat-cell">' + currencySymbol + parseFloat(productVat || 0).toFixed(2) + '<input type="hidden" class="vat-input" name="products[' + productId + '][unit_vat]" value="'+productVat+'"></td>' +
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
    }

    // Remove product row
    $(document).on('click', '.remove-product', function() {
      $(this).closest('tr').remove();
      calculateTotal();
    });

    // Update total on quantity change
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

      // Update hidden input on editor change
      quill.on('text-change', function() {
        let content = quill.root.innerHTML;
        if (content === '<p><br></p>') {
          content = ''; // Treat as empty
        }
        $('#note').val(content);
        saveFormStateDebounced();
      });
    }

    // ---------- Local Storage Persistence ----------
    const STORAGE_KEY = 'purchase_add_form_v1';
    let saveTimeoutId = null;

    function collectFormState() {
      const products = [];
      $('#products-table tbody tr:not(.total-row)').each(function() {
        const $row = $(this);
        const productId = $row.data('product-id');
        const productText = ($row.find('td').eq(0).text() || '').trim();
        const unit_cost = $row.find('.cost-input').val();
        const quantity = $row.find('.quantity-input').val();
        const unit_vat = $row.find('.vat-input').val();
        products.push({ productId, productText, unit_cost, quantity, unit_vat });
      });
      return {
        date: ($('#date').val() || '').trim(),
        supplier_id: ($('#supplier_id').val() || '').trim(),
        deliver: ($('#deliver').val() || '').trim(),
        shipping_charge: ($('#shipping_charge').val() || '').trim(),
        note: ($('#note').val() || '').trim(),
        products
      };
    }

    function saveFormState() {
      try {
        const state = collectFormState();
        localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
      } catch (e) {
        // ignore quota or JSON errors silently
      }
    }

    function saveFormStateDebounced() {
      clearPendingSave();
      saveTimeoutId = setTimeout(saveFormState, 200);
    }

    function clearPendingSave() {
      if (saveTimeoutId) {
        clearTimeout(saveTimeoutId);
        saveTimeoutId = null;
      }
    }

    function buildProductRowHtml(productId, productText, quantity, unit_cost, unit_vat) {
      const safeQty = (quantity == null || quantity === '') ? '1' : String(quantity);
      const safeCost = (unit_cost == null || unit_cost === '') ? '0.00' : String(unit_cost);
      const safeVat = (unit_vat == null || unit_vat === '') ? '0.00' : String(unit_vat);
      const vatDisplay = parseFloat(safeVat || 0).toFixed(2);
      return '' +
        '<tr data-product-id="' + productId + '">' +
          '<td>' + productText + '<input type="hidden" name="products[' + productId + '][product_id]" value="' + productId + '"></td>' +
          '<td><input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)" class="form-control form-control-sm cost-input" name="products[' + productId + '][unit_cost]" value="' + safeCost + '" autocomplete="off"></td>' +
          '<td><input type="text" onkeypress="return /^[0-9]+$/.test(event.key)" class="form-control form-control-sm quantity-input" name="products[' + productId + '][quantity]" value="' + safeQty + '" autocomplete="off"></td>' +
          '<td class="vat-cell">' + currencySymbol + vatDisplay + '<input type="hidden" class="vat-input" name="products[' + productId + '][unit_vat]" value="' + safeVat + '"></td>' +
          '<td class="subtotal-cell">' + currencySymbol + '0.00</td>' +
          '<td><a href="javascript:;" title="Remove" class="remove-product"><i class="icon-base ti tabler-x"></i></a></td>' +
        '</tr>';
    }

    function restoreFormState() {
      try {
        const raw = localStorage.getItem(STORAGE_KEY);
        if (!raw) return;
        const state = JSON.parse(raw);
        if (state.date) $('#date').val(state.date);
        if (state.supplier_id && $('#supplier_id option[value="' + state.supplier_id + '"]').length) {
          $('#supplier_id').val(state.supplier_id).trigger('change.select2');
        }
        if (state.deliver !== undefined && state.deliver !== null && state.deliver !== '') {
          if ($('#deliver option[value="' + state.deliver + '"]').length) {
            $('#deliver').val(state.deliver).trigger('change.select2');
          }
        } else {
          // If no deliver value in state, ensure it's cleared
          $('#deliver').val('').trigger('change.select2');
        }
        if (state.shipping_charge !== undefined && state.shipping_charge !== null && state.shipping_charge !== '') {
          $('#shipping_charge').val(state.shipping_charge);
        }

        // Restore note (both hidden input and quill if available)
        if (typeof state.note === 'string') {
          $('#note').val(state.note);
          if (typeof quill !== 'undefined' && quill) {
            quill.root.innerHTML = state.note || '';
          }
        }

        // Restore products
        if (Array.isArray(state.products)) {
          // Remove any existing non-total rows first
          $('#products-table tbody tr:not(.total-row)').remove();
          var $totalRow = $('#products-table tbody tr.total-row');
          state.products.forEach(function(p) {
            if (!p || !p.productId || !p.productText) return;
            const rowHtml = buildProductRowHtml(p.productId, p.productText, p.quantity, p.unit_cost, p.unit_vat);
            // Insert before the total row so total stays at bottom
            if ($totalRow.length > 0) {
              $totalRow.before(rowHtml);
            } else {
              $('#products-table tbody').prepend(rowHtml);
            }
          });
          calculateTotal();
        }
      } catch (e) {
        // ignore malformed JSON
      }
    }

    // Save on basic input changes
    $('#date, #supplier_id, #deliver, #shipping_charge').on('input change', saveFormStateDebounced);

    // Enhance existing handlers to save state as well
    const originalAddProductToTable = addProductToTable;
    addProductToTable = function(productId, productText, productCost, productVat) {
      originalAddProductToTable(productId, productText, productCost, productVat);
      saveFormStateDebounced();
    }

    const originalRemoveHandler = $(document).data('purchaseRemoveBound');
    if (!originalRemoveHandler) {
      $(document).on('click', '.remove-product', function() {
        saveFormStateDebounced();
      });
      $(document).data('purchaseRemoveBound', true);
    }

      $(document).on('input', '.quantity-input', saveFormStateDebounced);

    // On initial load, restore any saved state after a short delay to ensure Select2 is ready
    setTimeout(function() {
      restoreFormState();
      // Calculate totals on initial load to format with currency symbol
      calculateTotal();
    }, 100);

    // Clear storage on successful submit
    $('#addPurchaseForm').on('submit', function() {
      clearPendingSave();
      try { localStorage.removeItem(STORAGE_KEY); } catch (e) {}
    });

    // Clear storage on logout (handles both link-triggered and form POST logouts)
    $(document).on('click', 'a[href*="logout"]', function() {
      clearPendingSave();
      try { localStorage.removeItem(STORAGE_KEY); } catch (e) {}
    });
    $(document).on('submit', 'form[action*="logout"]', function() {
      clearPendingSave();
      try { localStorage.removeItem(STORAGE_KEY); } catch (e) {}
    });

    // FormValidation for date, supplier, products, cost price, and quantity
    const addPurchaseForm = document.getElementById('addPurchaseForm');
    if (addPurchaseForm) {
      const fv = FormValidation.formValidation(addPurchaseForm, {
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
                  // Basic format check: dd/mm/yyyy hh:mm
                  const re = /^\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2}$/;
                  if (!re.test(value)) return false;
                  // Use flatpickr to parse/validate
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
                // Place feedback around the products card body
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
      
      // Also revalidate after programmatic add
      const originalAdd = $('#products-table').data('fvAddHookSet');
      if (!originalAdd) {
        const observer = new MutationObserver(function() {
          revalidateProductFields();
        });
        const tbody = document.querySelector('#products-table tbody');
        if (tbody) {
          observer.observe(tbody, { childList: true, subtree: false });
        }
        $('#products-table').data('fvAddHookSet', true);
      }

      // Submit form after successful validation
      fv.on('core.form.valid', function () {
        clearPendingSave();
        try { localStorage.removeItem(STORAGE_KEY); } catch (e) {}
        addPurchaseForm.submit();
      });
    }
  });
})();

