'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const productSelect = document.querySelector('#goods_in_product');
  const addButton = document.querySelector('#goods_in_add');
  const tableBody = document.querySelector('#goods_in_table tbody');
  const emptyMessage = document.querySelector('#goods_in_empty');

  if (!productSelect || !addButton || !tableBody) return;

  // Initialize Select2 with remote search
  if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
    window.jQuery(productSelect).select2({
      placeholder: 'Search by name or SKU',
      ajax: {
        url: baseUrl + 'inventory/product/search/ajax',
        dataType: 'json',
        delay: 250,
        data: function (params) {
          return {
            q: params.term || '',
            limit: 20
          };
        },
        processResults: function (data) {
          const results = (data.results || []).map(function (item) {
            return {
              id: item.id,
              text: item.text,
              sku: item.sku,
              on_hand: item.on_hand,
              available: item.available
            };
          });
          return { results: results };
        }
      },
      minimumInputLength: 1,
    });
  }

  addButton.addEventListener('click', function () {
    let data;
    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
      const selected = window.jQuery(productSelect).select2('data');
      data = selected && selected.length ? selected[0] : null;
    } else {
      const option = productSelect.options[productSelect.selectedIndex];
      if (option && option.value) {
        data = {
          id: option.value,
          text: option.text,
          sku: option.getAttribute('data-sku') || '',
          on_hand: Number(option.getAttribute('data-on-hand') || '0'),
          available: Number(option.getAttribute('data-available') || '0'),
        };
      }
    }

    if (!data || !data.id) {
      return;
    }

    // Avoid duplicate rows: update existing if present
    const existingRow = tableBody.querySelector('tr[data-product-id="' + data.id + '"]');
    if (existingRow) {
      const onHandInput = existingRow.querySelector('.goods-in-onhand-input');
      if (onHandInput) {
        onHandInput.value = data.on_hand ?? 0;
      }
      const availableCell = existingRow.querySelector('.goods-in-available');
      if (availableCell) {
        availableCell.textContent = data.available ?? 0;
      }
      return;
    }

    const tr = document.createElement('tr');
    tr.setAttribute('data-product-id', String(data.id));

    const onHandVal = data.on_hand ?? 0;
    const availableVal = data.available ?? 0;

    tr.innerHTML = `
      <td>
        ${data.text}
        <input type="hidden" name="products[${data.id}][id]" value="${data.id}">
      </td>
      <td>${data.sku || ''}</td>
      <td class="text-end">
        <input
          type="text"
          name="products[${data.id}][on_hand]"
          class="form-control form-control-sm text-end goods-in-onhand-input"
          value="${onHandVal}"
          onkeypress="return /^[0-9.]+$/.test(event.key)"
        >
      </td>
      <td class="text-end goods-in-available">${availableVal}</td>
    `;

    tableBody.appendChild(tr);

    if (emptyMessage) {
      emptyMessage.classList.add('d-none');
    }
  });
});

