/**
 * Order Details Script
 * Used for order/edit.blade.php (edit form)
 */

'use strict';

// Order Edit Form Functionality (for order/edit.blade.php)
document.addEventListener('DOMContentLoaded', function() {
  if (typeof window.$ !== 'undefined' && $('#editOrderForm').length) {
    const currencySymbol = window.currencySymbol || '';
    
    // Initialize flatpickr for date & time
    $('.flatpickr').flatpickr({
      enableTime: true,
      dateFormat: 'd/m/Y H:i',
      time_24hr: true,
      allowInput: true
    });

    // Initialize Quill editor for delivery_note
    const noteEditor = document.querySelector('#note-editor');
    var quill;
    if (noteEditor) {
      quill = new Quill(noteEditor, {
        modules: {
          toolbar: '.comment-toolbar'
        },
        placeholder: 'Add delivery note...',
        theme: 'snow'
      });

      // Set initial content from delivery_note
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

    // Product search functionality for orders
    const MAX_RESULTS = 20;
    const $productSearch = $('#product-search');
    const $productResults = $('#product-search-results');
    const $productSpinner = $('#product-search-spinner');
    let activeIndex = -1;
    let searchTimeout = null;
    let currentQuery = '';
    let currentRequest = null;
    let productDataMap = {};

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
        const $item = $items.eq(index);
        // Skip disabled items when navigating
        if (!$item.prop('disabled') && !$item.data('isDisabled')) {
          $item.addClass('active');
          activeIndex = index;
        }
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
        const stockQuantity = parseFloat(item.stock_quantity || 0);
        const isDisabled = stockQuantity <= 0;
        
        const $button = $('<button type="button" class="list-group-item list-group-item-action bg-white text-dark"></button>')
          .text(item.text)
          .data('productId', item.id)
          .data('productText', item.text)
          .data('isDisabled', isDisabled);
        
        // Store price (sale price for orders) in data attribute
        // Use price field if available, otherwise fall back to unit_cost
        const salePrice = item.price !== undefined ? parseFloat(item.price) : (item.unit_cost !== undefined ? parseFloat(item.unit_cost) : 0);
        const vatAmount = parseFloat(item.vat_amount || 0);
        $button.data('productPrice', salePrice);
        $button.data('productVat', vatAmount);
        
        // Disable button if quantity <= 0
        if (isDisabled) {
          $button.prop('disabled', true)
            .addClass('disabled')
            .css({
              'opacity': '0.6',
              'cursor': 'not-allowed'
            });
        }
        
        $productResults.append($button);
      });

      $productResults.show();
      // Set active item to first enabled item, or -1 if all disabled
      const $enabledItems = $productResults.find('.list-group-item:not(.disabled)');
      if ($enabledItems.length > 0) {
        setActiveItem($enabledItems.first().index());
      } else {
        activeIndex = -1;
      }
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
        
        // Store product data by ID for quick lookup
        productDataMap = {};
        results.forEach(function(item) {
          productDataMap[item.id] = item;
        });
        
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

    if ($productSearch.length) {
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
          // Find next enabled item
          let nextIndex = activeIndex;
          let attempts = 0;
          do {
            nextIndex = nextIndex >= $items.length - 1 ? 0 : nextIndex + 1;
            attempts++;
            if (attempts > $items.length) break; // Prevent infinite loop
          } while (attempts <= $items.length && ($items.eq(nextIndex).prop('disabled') || $items.eq(nextIndex).data('isDisabled')));
          setActiveItem(nextIndex);
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          // Find previous enabled item
          let prevIndex = activeIndex;
          let attempts = 0;
          do {
            prevIndex = prevIndex <= 0 ? $items.length - 1 : prevIndex - 1;
            attempts++;
            if (attempts > $items.length) break; // Prevent infinite loop
          } while (attempts <= $items.length && ($items.eq(prevIndex).prop('disabled') || $items.eq(prevIndex).data('isDisabled')));
          setActiveItem(prevIndex);
        } else if (e.key === 'Enter') {
          if (activeIndex >= 0) {
            const $activeItem = $items.eq(activeIndex);
            if (!$activeItem.prop('disabled') && !$activeItem.data('isDisabled')) {
              e.preventDefault();
              $activeItem.trigger('click');
            }
          }
        } else if (e.key === 'Escape') {
          resetResults();
        }
      });

      $productResults.on('mousedown', '.list-group-item', function(e) {
        e.preventDefault();
      });

      $productResults.on('mouseenter', '.list-group-item', function() {
        // Skip disabled items when hovering
        if (!$(this).prop('disabled') && !$(this).data('isDisabled')) {
          setActiveItem($(this).index());
        }
      });

      $productResults.on('click', '.list-group-item', function() {
        if (!$(this).data('productId')) {
          return; // Non-selectable (e.g., No results found)
        }
        // Prevent adding disabled products
        if ($(this).data('isDisabled') || $(this).prop('disabled')) {
          return;
        }
        const productId = $(this).data('productId');
        const productText = $(this).data('productText');
        // Get price from data attribute (for orders, we use price as sale price)
        let productPrice = $(this).data('productPrice');
        // Fallback: try to get from productDataMap if not in data attribute
        if (productPrice === undefined && productDataMap[productId]) {
          const item = productDataMap[productId];
          productPrice = item.price !== undefined ? parseFloat(item.price) : (item.unit_cost !== undefined ? parseFloat(item.unit_cost) : 0);
        }
        productPrice = productPrice || 0;
        
        // Get VAT amount from button data or productDataMap
        let productVat = $(this).data('productVat');
        if (productVat === undefined && productDataMap[productId]) {
          productVat = parseFloat(productDataMap[productId].vat_amount || 0);
        }
        productVat = productVat || 0;
        
        if (typeof window.addProductToTable === 'function') {
          window.addProductToTable(productId, productText, 1, productPrice, productVat);
        }
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
    }

    // Calculate totals (quantity, VAT, and amount)
    function calculateTotal() {
      var totalQty = 0;
      var totalVat = 0;
      var totalAmount = 0;
      $('#products-table tbody tr:not(.total-row)').each(function() {
        var $row = $(this);
        var qty = parseFloat($row.find('.quantity-input').val()) || 0;
        var cost = parseFloat($row.find('.cost-input').val()) || 0;
        var unitVat = parseFloat($row.data('unit-vat') || 0);
        var priceSubtotal = qty * cost;
        // For totals we use VAT * quantity,
        // but the VAT column itself should show per-unit VAT (unitVat), independent of quantity.
        var vatForTotals = qty * unitVat;
        var sub = priceSubtotal + vatForTotals; // Subtotal = (sale price * quantity) + (vat * quantity)
        // Show per-unit VAT in the VAT column
        $row.find('.vat-cell').text(currencySymbol + unitVat.toFixed(2));
        $row.find('.subtotal-cell').text(currencySymbol + sub.toFixed(2));
        totalQty += qty;
        totalVat += vatForTotals;
        totalAmount += sub;
      });
      $('.total-quantity').text(totalQty.toFixed(2));
      // Do not show total VAT in the total row anymore
      $('.total-vat').text('');
      $('.total-amount').text(currencySymbol + totalAmount.toFixed(2));
    }

    // Add product row - make it available globally for initialization
    window.addProductToTable = function(productId, productText, quantity = 1, unit_price = 0, vat_amount = 0) {
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
      var row = '<tr data-product-id="' + productId + '" data-unit-vat="' + vat_amount + '">' +
        '<td>' + productText + '<input type="hidden" name="products[' + productId + '][product_id]" value="' + productId + '"></td>' +
        '<td><input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)" class="form-control form-control-sm cost-input" name="products[' + productId + '][unit_cost]" value="' + unit_price + '" autocomplete="off"></td>' +
        '<td><input type="text" onkeypress="return /^[0-9]+$/.test(event.key)" class="form-control form-control-sm quantity-input" name="products[' + productId + '][quantity]" value="' + quantity + '" autocomplete="off"></td>' +
        '<td class="vat-cell">' + currencySymbol + '0.00</td>' +
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
    $(document).on('input', '.quantity-input, .cost-input', calculateTotal);

    // Calculate totals on initial load to format with currency symbol
    setTimeout(function() {
      calculateTotal();
    }, 300);

    // FormValidation for order edit form
    const editOrderForm = document.getElementById('editOrderForm');
    if (editOrderForm) {
      const fv = FormValidation.formValidation(editOrderForm, {
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
                message: 'Sale price is required and must be a valid number (0 or greater)',
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
        editOrderForm.submit();
      });
    }
  }
});
