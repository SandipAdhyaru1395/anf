'use strict';

(function () {
  const pricingForm = document.getElementById('editProductPricingForm');

  if (!pricingForm) {
    return;
  }

  const fv = FormValidation.formValidation(pricingForm, {
    fields: {
      productPrice: {
        validators: {
          notEmpty: {
            message: 'Please enter unit price'
          },
          numeric: {
            message: 'Unit price must be a number'
          }
        }
      },
      rrp: {
        validators: {
          numeric: {
            message: 'RRP must be a number'
          }
        }
      },
      priceListUnitPrice: {
        selector: 'input[name^="price_list"][name$="[unit_price]"]',
        validators: {
          numeric: {
            message: 'Unit price must be a number'
          }
        }
      },
      priceListRrp: {
        selector: 'input[name^="price_list"][name$="[rrp]"]',
        validators: {
          numeric: {
            message: 'RRP must be a number'
          }
        }
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        eleValidClass: 'is-valid',
        rowSelector: '.form-control-validation'
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    }
  });

  fv.on('core.form.valid', function () {
    pricingForm.submit();
  });

  // Volume discount modal logic
  var volumeModalEl = document.getElementById('volumeDiscountModal');

  if (volumeModalEl && typeof bootstrap !== 'undefined') {
    var volumeModal = new bootstrap.Modal(volumeModalEl);
    var priceListInput = document.getElementById('volumeDiscountPriceListId');
    var selectView = document.getElementById('volumeDiscountSelectView');
    var createView = document.getElementById('volumeDiscountCreateView');
    var btnRemove = document.getElementById('volumeDiscountRemoveBtn');
    var btnNewGroup = document.getElementById('volumeDiscountNewGroupBtn');
    var btnBack = document.getElementById('volumeDiscountBackBtn');
    var btnSaveSelect = document.getElementById('volumeDiscountSaveSelectBtn');
    var addRowBtn = document.getElementById('vdAddRowBtn');
    var qtyTableBody = document.querySelector('#vdQuantityTable tbody');
    var groupNameInput = document.getElementById('vdGroupName');
    var productIdInput = pricingForm.querySelector('input[name="id"]');
    var currentListLabelEl = null;
    var currentGroupNameEl = null;
    var editBtn = null;
    var currentBreaks = [];
    var currentGroupName = '';
    var currentGroupId = null;
    var groupsListEl = document.getElementById('vdGroupsList');
    var usageInfoEl = document.getElementById('vdUsageInfo');
    var usageInfoTextEl = document.getElementById('vdUsageInfoText');

    var setUsageInfoText = function (count) {
      if (!usageInfoTextEl) return;
      count = typeof count === 'number' ? count : 0;
      usageInfoTextEl.textContent =
        'This discount group is used by ' +
        count +
        ' other product' +
        (count === 1 ? '' : 's') +
        '. Any changes made to it will affect all the products using it.';
    };

    var renderGroupsList = function (groups, selectedId) {
      if (!groupsListEl) return;
      if (!groups || !groups.length) {
        groupsListEl.innerHTML = '<div class="text-body-secondary small">No discount groups found.</div>';
        return;
      }

      var html = '<div class="list-group">';
      groups.forEach(function (g) {
        var active = selectedId && String(selectedId) === String(g.id) ? ' active' : '';
        html +=
          '<div class="list-group-item d-flex justify-content-between align-items-center js-vd-row' +
          active +
          '" data-group-id="' +
          g.id +
          '" role="button" tabindex="0">' +
          '<span class="text-body">' +
          (g.name || ('Group #' + g.id)) +
          '</span>' +
          '<button type="button" class="btn btn-sm btn-link text-decoration-none js-vd-edit-group" data-group-id="' +
          g.id +
          '" aria-label="Edit">' +
          '<i class="menu-icon icon-base ti tabler-pencil"></i>' +
          '</button>' +
          '</div>';
      });
      html += '</div>';
      groupsListEl.innerHTML = html;
    };

    var loadGroupsForList = function (options) {
      options = options || {};
      var openModal = !!options.openModal;
      if (!productIdInput) return;
      var productId = productIdInput.value;
      var priceListIdRaw = priceListInput ? priceListInput.value : '';
      var csrfMeta = document.querySelector('meta[name="csrf-token"]');
      var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

      fetch('/product/' + productId + '/volume-discount/groups?price_list_id=' + encodeURIComponent(priceListIdRaw), {
        headers: {
          Accept: 'application/json',
          'X-CSRF-TOKEN': csrfToken
        }
      })
        .then(function (r) {
          return r.json();
        })
        .then(function (data) {
          if (!data || data.status !== 'ok') return;
          currentGroupId = data.current_group_id || null;
          var groups = data.groups || [];

          renderGroupsList(groups, currentGroupId);

          if (groupsListEl) {
            groupsListEl._vd_groups = groups;
          }

          // Update usage info when opening form for current group
          if (usageInfoEl && usageInfoTextEl) {
            var current = null;
            if (currentGroupId && groupsListEl && groupsListEl._vd_groups) {
              groupsListEl._vd_groups.forEach(function (g) {
                if (String(g.id) === String(currentGroupId)) current = g;
              });
            }
            var count = current && typeof current.usage_count === 'number' ? current.usage_count : 0;
            setUsageInfoText(count);
          }

          if (openModal) {
            showSelectView();
            volumeModal.show();
          }
        })
        .catch(function () {
          // ignore
        });
    };

    var showSelectView = function () {
      if (!selectView || !createView) return;
      selectView.classList.remove('d-none');
      createView.classList.add('d-none');

      if (btnNewGroup) btnNewGroup.classList.remove('d-none');
      if (btnBack) btnBack.classList.add('d-none');
      if (btnSaveSelect) btnSaveSelect.classList.add('d-none');

      // Show remove button only if a group is currently assigned
      if (btnRemove) {
        if (currentGroupId) {
          btnRemove.classList.remove('d-none');
        } else {
          btnRemove.classList.add('d-none');
        }
      }
    };

    var showCreateView = function () {
      if (!selectView || !createView) return;
      selectView.classList.add('d-none');
      createView.classList.remove('d-none');

      if (btnRemove) btnRemove.classList.add('d-none');
      if (btnNewGroup) btnNewGroup.classList.add('d-none');
      if (btnBack) btnBack.classList.remove('d-none');
      if (btnSaveSelect) btnSaveSelect.classList.remove('d-none');
    };

    var resetCreateView = function () {
      if (groupNameInput) {
        groupNameInput.value = '';
      }
      if (qtyTableBody) {
        // Keep a single empty row
        qtyTableBody.innerHTML =
          '<tr>' +
          '<td><input type="text" class="form-control form-control-sm vd-from-qty" placeholder="0"></td>' +
          '<td class="pe-0">' +
          '<div class="d-flex align-items-center">' +
          '<input type="text" class="form-control form-control-sm me-2 vd-discount" placeholder="0">' +
          '<span class="me-2">%</span>' +
          '<button type="button" class="btn btn-sm vd-remove-row"><i class="menu-icon ti tabler-trash"></i></button>' +
          '</div>' +
          '</td>' +
          '</tr>';
      }
    };

    var populateCreateViewFromBreaks = function () {
      if (!qtyTableBody) return;
      if (!currentBreaks || !currentBreaks.length) {
        resetCreateView();
        return;
      }

      qtyTableBody.innerHTML = '';
      currentBreaks.forEach(function (br) {
        var row = document.createElement('tr');
        row.innerHTML =
          '<td><input type="text" class="form-control form-control-sm vd-from-qty" placeholder="0" value="' + (br.from_quantity || '') + '"></td>' +
          '<td class="pe-0">' +
          '<div class="d-flex align-items-center">' +
          '<input type="text" class="form-control form-control-sm me-2 vd-discount" placeholder="0" value="' + (br.discount_percentage || '') + '">' +
          '<span class="me-2">%</span>' +
          '<button type="button" class="btn btn-sm vd-remove-row"><i class="menu-icon ti tabler-trash"></i></button>' +
          '</div>' +
          '</td>';
        qtyTableBody.appendChild(row);
      });
    };

    var updateVolumeDiscountLinkLabel = function (priceListIdRaw, groupName) {
      var key = !priceListIdRaw || priceListIdRaw === '' ? 'default' : String(priceListIdRaw);
      var card = document.querySelector('.js-price-list-card[data-pricelist-id="' + key + '"]');
      if (!card) return;
      var link = card.querySelector('.js-add-volume-discount');
      if (!link) return;

      // Update data attributes
      link.setAttribute('data-vd-group-name', groupName || '');

      // Update visible text + underline style
      if (groupName) {
        link.innerHTML =
          '<small class="vd-prefix">Volume Discount:</small> ' +
          '<span class="vd-group-name-hover">' + groupName + '</span>';
        link.classList.remove('text-decoration-underline');
      } else {
        link.textContent = 'Add Volume Discount';
        if (!link.classList.contains('text-decoration-underline')) {
          link.classList.add('text-decoration-underline');
        }
      }
    };

    // Open modal from "Add Volume Discount" links
    var triggers = document.querySelectorAll('.js-add-volume-discount');
    if (triggers.length) {
      triggers.forEach(function (trigger) {
        trigger.addEventListener('click', function (event) {
          event.preventDefault();
          var priceListId = trigger.getAttribute('data-pricelist-id') || '';
          var groupName = trigger.getAttribute('data-vd-group-name') || '';
          var breaksRaw = trigger.getAttribute('data-vd-breaks') || '[]';
          try {
            currentBreaks = JSON.parse(breaksRaw);
          } catch (e) {
            currentBreaks = [];
          }
          if (priceListInput) {
            priceListInput.value = priceListId;
          }
          currentGroupName = groupName || '';
          currentGroupId = null; // will be set from AJAX
          loadGroupsForList({ openModal: true });
        });
      });
    }

    // Footer button actions
    if (btnNewGroup) {
      btnNewGroup.addEventListener('click', function () {
        // Start a completely new group (do NOT reuse existing one)
        currentGroupId = null;
        currentGroupName = '';
        currentBreaks = [];
        if (groupNameInput) {
          groupNameInput.value = '';
        }
        resetCreateView();
        showCreateView();
        setUsageInfoText(0);
      });
    }

    if (groupsListEl) {
      groupsListEl.addEventListener('click', function (event) {
        var target = event.target;
        if (!target) return;
        var edit = target.closest && target.closest('.js-vd-edit-group');
        if (edit) {
          var gidEdit = edit.getAttribute('data-group-id');
          if (!gidEdit || !groupsListEl._vd_groups) return;
          currentGroupId = gidEdit;
          var selectedEdit = null;
          groupsListEl._vd_groups.forEach(function (g) {
            if (String(g.id) === String(gidEdit)) selectedEdit = g;
          });
          if (selectedEdit) {
            currentGroupName = selectedEdit.name || '';
            currentBreaks = selectedEdit.breaks || [];
            if (groupNameInput) groupNameInput.value = currentGroupName;
            var countEdit = typeof selectedEdit.usage_count === 'number' ? selectedEdit.usage_count : 0;
            setUsageInfoText(countEdit);
            populateCreateViewFromBreaks();
            showCreateView();
          }
          return;
        }

        var row = target.closest && target.closest('.js-vd-row');
        if (!row) return;
        var gid = row.getAttribute('data-group-id');
        if (!gid || !productIdInput) return;

        // Apply selection (attach to product + price list) and close modal
        currentGroupId = gid;
        var selected = null;
        if (groupsListEl._vd_groups) {
          groupsListEl._vd_groups.forEach(function (g) {
            if (String(g.id) === String(gid)) selected = g;
          });
        }
        if (!selected) return;

        var productId = productIdInput.value;
        var priceListIdRaw = priceListInput ? priceListInput.value : '';
        var priceListId = priceListIdRaw === 'default' ? null : priceListIdRaw;

        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

        fetch('/product/' + productId + '/volume-discount/select', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify({
            group_id: selected.id,
            price_list_id: priceListId
          })
        })
          .then(function (r) {
            return r.json();
          })
          .then(function (data) {
            if (data && data.status === 'ok' && data.breaks) {
              if (data.group && data.group.name) {
                updateVolumeDiscountLinkLabel(priceListIdRaw, data.group.name);
              }
              applyVolumeDiscountHeaders(priceListIdRaw, data.breaks);
            } else if (selected.breaks) {
              updateVolumeDiscountLinkLabel(priceListIdRaw, selected.name || '');
              applyVolumeDiscountHeaders(priceListIdRaw, selected.breaks);
            }
            volumeModal.hide();
          })
          .catch(function () {
            volumeModal.hide();
          });
      });
    }

    if (btnBack) {
      btnBack.addEventListener('click', function () {
        showSelectView();
      });
    }

    if (btnSaveSelect) {
      btnSaveSelect.addEventListener('click', function () {
        if (!productIdInput) {
          volumeModal.hide();
          return;
        }

        var productId = productIdInput.value;
        var priceListIdRaw = priceListInput ? priceListInput.value : '';
        var priceListId = priceListIdRaw === 'default' ? null : priceListIdRaw;

        var groupName = groupNameInput ? groupNameInput.value.trim() : '';
        if (!groupName) {
          groupName = 'Volume discount';
        }

        var breaks = [];
        if (qtyTableBody) {
          var rows = qtyTableBody.querySelectorAll('tr');
          rows.forEach(function (row) {
            var fromInput = row.querySelector('.vd-from-qty');
            var discInput = row.querySelector('.vd-discount');
            var fromVal = fromInput ? fromInput.value.trim() : '';
            var discVal = discInput ? discInput.value.trim() : '';
            if (fromVal !== '' && discVal !== '') {
              breaks.push({
                from_quantity: parseInt(fromVal, 10),
                discount_percentage: parseFloat(discVal)
              });
            }
          });
        }

        if (!breaks.length) {
          volumeModal.hide();
          return;
        }

        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

        fetch('/product/' + productId + '/volume-discount/store', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify({
            group_id: currentGroupId,
            name: groupName,
            price_list_id: priceListId,
            breaks: breaks
          })
        })
          .then(function (response) {
            if (!response.ok) {
              throw new Error('Request failed');
            }
            return response.json();
          })
          .then(function (data) {
            if (data && data.status === 'ok' && data.breaks) {
              if (data.group && data.group.id) {
                currentGroupId = data.group.id;
                currentGroupName = data.group.name || '';
                updateVolumeDiscountLinkLabel(priceListIdRaw, currentGroupName);
              }
              applyVolumeDiscountHeaders(priceListIdRaw, data.breaks);
            }
            volumeModal.hide();
          })
          .catch(function () {
            volumeModal.hide();
          });
      });
    }

    if (btnRemove) {
      btnRemove.addEventListener('click', function () {
        if (!productIdInput) {
          volumeModal.hide();
          return;
        }

        var productId = productIdInput.value;
        var priceListIdRaw = priceListInput ? priceListInput.value : '';
        var priceListId = priceListIdRaw === 'default' ? null : priceListIdRaw;

        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

        fetch('/product/' + productId + '/volume-discount/remove', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify({
            price_list_id: priceListId
          })
        })
          .then(function (r) {
            return r.json();
          })
          .then(function (data) {
            var breaks = (data && data.status === 'ok' && Array.isArray(data.breaks)) ? data.breaks : [];
            applyVolumeDiscountHeaders(priceListIdRaw, breaks);

            // Clear current group selection and hide remove button
            currentGroupId = null;
            currentGroupName = '';
            if (btnRemove) btnRemove.classList.add('d-none');

            // Also reset the trigger link label and data attributes
            updateVolumeDiscountLinkLabel(priceListIdRaw, '');

            volumeModal.hide();
          })
          .catch(function () {
            volumeModal.hide();
          });
      });
    }

    // Add quantity break row
    if (addRowBtn && qtyTableBody) {
      addRowBtn.addEventListener('click', function () {
        var row = document.createElement('tr');
        row.innerHTML =
          '<td><input type="text" class="form-control form-control-sm vd-from-qty" placeholder="0"></td>' +
          '<td class="pe-0">' +
          '<div class="d-flex align-items-center">' +
          '<input type="text" class="form-control form-control-sm me-2 vd-discount" placeholder="0">' +
          '<span class="me-2">%</span>' +
          '<button type="button" class="btn btn-sm vd-remove-row"><i class="menu-icon ti tabler-trash"></i></button>' +
          '</div>' +
          '</td>';
        qtyTableBody.appendChild(row);
      });
    }

    // Delete quantity break row (event delegation)
    if (qtyTableBody) {
      qtyTableBody.addEventListener('click', function (event) {
        var target = event.target;
        if (!target) return;
        // Handle clicks on the delete button or its child icon
        var btn = target.closest && target.closest('.vd-remove-row');
        if (!btn) return;
        var row = btn.closest('tr');
        if (row && qtyTableBody.rows.length > 1) {
          qtyTableBody.removeChild(row);
        }
      });
    }

    var applyVolumeDiscountHeaders = function (priceListIdRaw, breaks) {
      var key = !priceListIdRaw || priceListIdRaw === '' ? 'default' : String(priceListIdRaw);
      var card = document.querySelector('.js-price-list-card[data-pricelist-id="' + key + '"]');
      if (!card) {
        return;
      }

      var table = card.querySelector('table');
      if (!table) {
        return;
      }

      var theadRow = table.querySelector('thead tr');
      var tbodyRow = table.querySelector('tbody tr');
      if (!theadRow || !tbodyRow) {
        return;
      }

      // Remove existing volume discount columns
      var oldHeaders = theadRow.querySelectorAll('.vd-col-header');
      oldHeaders.forEach(function (el) {
        el.parentNode.removeChild(el);
      });
      var oldCells = tbodyRow.querySelectorAll('.vd-col-cell');
      oldCells.forEach(function (el) {
        el.parentNode.removeChild(el);
      });

      // Add new columns
      breaks.forEach(function (br) {
        var th = document.createElement('th');
        th.className = 'border-0 border-bottom p-2 fw-normal text-center small vd-col-header';
        th.style.width = '150px';
        th.innerHTML =
          '<span class="vd-break-qty">' + br.from_quantity + '+</span> ' +
          '<span class="vd-break-disc">(-' + br.discount_percentage + '%)</span>';
        theadRow.appendChild(th);

        var td = document.createElement('td');
        td.className = 'border-0 p-2 text-end align-middle vd-col-cell';
        td.style.width = '150px';
        // Add override input under each break column
        var input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control form-control-sm text-end';
        input.setAttribute('onkeypress', 'return /^[0-9.]+$/.test(event.key)');
        input.name = 'volume_discount_price[' + key + '][' + br.id + ']';
        input.autocomplete = 'off';
        td.appendChild(input);
        tbodyRow.appendChild(td);
      });
    };
  }
})();

