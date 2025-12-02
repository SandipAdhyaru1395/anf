/**
 * Net VAT Report Script
 */
'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const currencySymbol = window.currencySymbol || '£';
  const baseUrl = window.baseUrl || '';
  let dateRangePicker = null;
  let customFromPicker = null;
  let customToPicker = null;
  let currentStartDate = null;
  let currentEndDate = null;

  // Initialize default date range (last 30 days)
  // Try to get initial dates from the display element
  const displayEl = document.getElementById('date-range-display');
  let defaultEndDate = new Date();
  let defaultStartDate = new Date();
  defaultStartDate.setDate(defaultStartDate.getDate() - 30);
  
  // Parse initial dates from display if available (format: "dd/mm/yyyy hh:mm - dd/mm/yyyy hh:mm")
  if (displayEl && displayEl.textContent && displayEl.textContent.includes(' - ')) {
    const parts = displayEl.textContent.split(' - ');
    if (parts.length === 2) {
      try {
        // Parse start date (dd/mm/yyyy hh:mm)
        const startParts = parts[0].trim().split(' ');
        if (startParts.length >= 2) {
          const datePart = startParts[0].split('/');
          const timePart = startParts[1].split(':');
          if (datePart.length === 3 && timePart.length >= 2) {
            defaultStartDate = new Date(
              parseInt(datePart[2]),
              parseInt(datePart[1]) - 1,
              parseInt(datePart[0]),
              parseInt(timePart[0]),
              parseInt(timePart[1])
            );
          }
        }
        // Parse end date (dd/mm/yyyy hh:mm)
        const endParts = parts[1].trim().split(' ');
        if (endParts.length >= 2) {
          const datePart = endParts[0].split('/');
          const timePart = endParts[1].split(':');
          if (datePart.length === 3 && timePart.length >= 2) {
            defaultEndDate = new Date(
              parseInt(datePart[2]),
              parseInt(datePart[1]) - 1,
              parseInt(datePart[0]),
              parseInt(timePart[0]),
              parseInt(timePart[1])
            );
          }
        }
      } catch (e) {
        // If parsing fails, use defaults
        console.warn('Failed to parse initial dates, using defaults', e);
      }
    }
  }
  
  currentStartDate = defaultStartDate;
  currentEndDate = defaultEndDate;

  // Format date for display
  function formatDateForDisplay(date) {
    if (!date) return '';
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    const hours = String(d.getHours()).padStart(2, '0');
    const minutes = String(d.getMinutes()).padStart(2, '0');
    return `${day}/${month}/${year} ${hours}:${minutes}`;
  }

  // Format date for API
  function formatDateForAPI(date) {
    if (!date) return '';
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    const hours = String(d.getHours()).padStart(2, '0');
    const minutes = String(d.getMinutes()).padStart(2, '0');
    return `${day}/${month}/${year} ${hours}:${minutes}`;
  }

  // Update date range display
  function updateDateRangeDisplay() {
    const displayEl = document.getElementById('date-range-display');
    if (displayEl && currentStartDate && currentEndDate) {
      displayEl.textContent = `${formatDateForDisplay(currentStartDate)} - ${formatDateForDisplay(currentEndDate)}`;
    }
  }

  // Load VAT data
  function loadVatData() {
    const startDateStr = currentStartDate ? formatDateForAPI(currentStartDate) : '';
    const endDateStr = currentEndDate ? formatDateForAPI(currentEndDate) : '';

    // Show loading state
    const cards = document.querySelectorAll('.vat-card');
    cards.forEach(card => {
      const valueEl = card.querySelector('.vat-value');
      if (valueEl) {
        valueEl.textContent = 'Loading...';
      }
    });

    // Fetch data
    const url = `${baseUrl}report/net-vat/ajax?start_date=${encodeURIComponent(startDateStr)}&end_date=${encodeURIComponent(endDateStr)}`;
    fetch(url)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Update Net VAT To Pay card
          const netVatCard = document.getElementById('net-vat-card');
          if (netVatCard) {
            const valueEl = netVatCard.querySelector('.vat-value');
            const breakdownEl = netVatCard.querySelector('.vat-breakdown');
            if (valueEl) {
              valueEl.textContent = `${currencySymbol}${parseFloat(data.netVatToPay || 0).toFixed(2)}`;
            }
            if (breakdownEl) {
              breakdownEl.innerHTML = `
                ${currencySymbol}${parseFloat(data.soVatTotal || 0).toFixed(2)} Sales Tax - 
                (${currencySymbol}${parseFloat(data.purchaseVatTotal || 0).toFixed(2)} Purchase Tax + 
                ${currencySymbol}${parseFloat(data.cnVatTotal || 0).toFixed(2)} Credit Note Tax)
              `;
            }
          }

          // Update Purchase VAT card
          const purchaseVatCard = document.getElementById('purchase-vat-card');
          if (purchaseVatCard) {
            const valueEl = purchaseVatCard.querySelector('.vat-value');
            if (valueEl) {
              valueEl.textContent = `${currencySymbol}${parseFloat(data.purchaseVatTotal || 0).toFixed(2)}`;
            }
          }
        }
      })
      .catch(error => {
        console.error('Error loading VAT data:', error);
        // Show error state
        const cards = document.querySelectorAll('.vat-card');
        cards.forEach(card => {
          const valueEl = card.querySelector('.vat-value');
          if (valueEl) {
            valueEl.textContent = 'Error';
          }
        });
      });
  }

  // Initialize date range picker dropdown
  const dateRangeDropdown = document.getElementById('date-range-dropdown');
  const dateRangeMenu = document.getElementById('date-range-menu');
  const customRangeOption = document.getElementById('custom-range-option');
  const customRangePanel = document.getElementById('custom-range-panel');
  const customFromInput = document.getElementById('custom-from-date');
  const customToInput = document.getElementById('custom-to-date');
  const applyBtn = document.getElementById('apply-date-range');
  const cancelBtn = document.getElementById('cancel-date-range');

  // Predefined range options
  const predefinedRanges = {
    'today': () => {
      const today = new Date();
      return {
        start: new Date(today.getFullYear(), today.getMonth(), today.getDate(), 0, 0),
        end: new Date(today.getFullYear(), today.getMonth(), today.getDate(), 23, 59)
      };
    },
    'yesterday': () => {
      const yesterday = new Date();
      yesterday.setDate(yesterday.getDate() - 1);
      return {
        start: new Date(yesterday.getFullYear(), yesterday.getMonth(), yesterday.getDate(), 0, 0),
        end: new Date(yesterday.getFullYear(), yesterday.getMonth(), yesterday.getDate(), 23, 59)
      };
    },
    'last-7-days': () => {
      const end = new Date();
      const start = new Date();
      start.setDate(start.getDate() - 7);
      return {
        start: new Date(start.getFullYear(), start.getMonth(), start.getDate(), 0, 0),
        end: new Date(end.getFullYear(), end.getMonth(), end.getDate(), 23, 59)
      };
    },
    'last-30-days': () => {
      const end = new Date();
      const start = new Date();
      start.setDate(start.getDate() - 30);
      return {
        start: new Date(start.getFullYear(), start.getMonth(), start.getDate(), 0, 0),
        end: new Date(end.getFullYear(), end.getMonth(), end.getDate(), 23, 59)
      };
    },
    'this-month': () => {
      const now = new Date();
      return {
        start: new Date(now.getFullYear(), now.getMonth(), 1, 0, 0),
        end: new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59)
      };
    },
    'last-month': () => {
      const now = new Date();
      return {
        start: new Date(now.getFullYear(), now.getMonth() - 1, 1, 0, 0),
        end: new Date(now.getFullYear(), now.getMonth(), 0, 23, 59)
      };
    }
  };

  // Handle predefined range selection
  document.querySelectorAll('[data-range]').forEach(item => {
    item.addEventListener('click', function (e) {
      e.preventDefault();
      const range = this.getAttribute('data-range');
      if (range === 'custom') {
        // Stop propagation to prevent dropdown from closing
        e.stopPropagation();
        // Show custom range panel
        customRangePanel.style.display = 'block';
        // Set current dates in custom inputs
        if (currentStartDate && customFromPicker) {
          customFromPicker.setDate(currentStartDate, false);
        }
        if (currentEndDate && customToPicker) {
          customToPicker.setDate(currentEndDate, false);
        }
      } else if (predefinedRanges[range]) {
        const dates = predefinedRanges[range]();
        currentStartDate = dates.start;
        currentEndDate = dates.end;
        updateDateRangeDisplay();
        loadVatData();
        // Hide custom range panel
        customRangePanel.style.display = 'none';
        // Close dropdown
        if (dateRangeMenu) {
          const bsDropdown = bootstrap.Dropdown.getInstance(dateRangeDropdown);
          if (bsDropdown) {
            bsDropdown.hide();
          }
        }
      }
    });
  });

  // Initialize custom date pickers
  if (window.flatpickr && customFromInput && customToInput) {
    customFromPicker = flatpickr(customFromInput, {
      enableTime: true,
      dateFormat: 'd/m/Y H:i',
      time_24hr: true,
      allowInput: true,
      defaultDate: currentStartDate
    });

    customToPicker = flatpickr(customToInput, {
      enableTime: true,
      dateFormat: 'd/m/Y H:i',
      time_24hr: true,
      allowInput: true,
      defaultDate: currentEndDate
    });
  }

  // Apply custom range
  if (applyBtn) {
    applyBtn.addEventListener('click', function () {
      if (customFromPicker && customToPicker) {
        const fromDate = customFromPicker.selectedDates[0];
        const toDate = customToPicker.selectedDates[0];
        
        if (fromDate && toDate) {
          if (fromDate > toDate) {
            alert('Start date cannot be after end date');
            return;
          }
          currentStartDate = fromDate;
          currentEndDate = toDate;
          updateDateRangeDisplay();
          loadVatData();
          // Hide custom range panel
          customRangePanel.style.display = 'none';
          // Close dropdown
          if (dateRangeMenu) {
            const bsDropdown = bootstrap.Dropdown.getInstance(dateRangeDropdown);
            if (bsDropdown) {
              bsDropdown.hide();
            }
          }
        } else {
          alert('Please select both start and end dates');
        }
      }
    });
  }

  // Cancel custom range
  if (cancelBtn) {
    cancelBtn.addEventListener('click', function () {
      // Hide custom range panel
      customRangePanel.style.display = 'none';
      // Close dropdown
      if (dateRangeMenu) {
        const bsDropdown = bootstrap.Dropdown.getInstance(dateRangeDropdown);
        if (bsDropdown) {
          bsDropdown.hide();
        }
      }
    });
  }

  // Initialize display and load data
  updateDateRangeDisplay();
  loadVatData();
});

