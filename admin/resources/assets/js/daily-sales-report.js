/**
 * Daily Sales Report Script
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  const currencySymbol = window.currencySymbol || '';
  let currentYear = new Date().getFullYear();
  let currentMonth = new Date().getMonth() + 1; // 1-12

  // Load calendar for current month
  loadCalendar(currentYear, currentMonth);

  // Previous month button
  document.getElementById('prev-month').addEventListener('click', function() {
    currentMonth--;
    if (currentMonth < 1) {
      currentMonth = 12;
      currentYear--;
    }
    loadCalendar(currentYear, currentMonth);
  });

  // Next month button
  document.getElementById('next-month').addEventListener('click', function() {
    currentMonth++;
    if (currentMonth > 12) {
      currentMonth = 1;
      currentYear++;
    }
    loadCalendar(currentYear, currentMonth);
  });

  function loadCalendar(year, month) {
    // Show loading state
    const calendarBody = document.getElementById('calendar-body');
    calendarBody.innerHTML = `
      <tr>
        <td colspan="7" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </td>
      </tr>
    `;

    // Fetch daily sales data
    fetch(`${baseUrl}report/daily-sales/ajax?year=${year}&month=${month}`, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Update month/year display
        document.getElementById('current-month-year').textContent = data.month_name;
        
        // Generate calendar
        generateCalendar(year, month, data.daily_data);
      } else {
        calendarBody.innerHTML = `
          <tr>
            <td colspan="7" class="text-center py-5 text-danger">
              Error loading calendar data
            </td>
          </tr>
        `;
      }
    })
    .catch(error => {
      console.error('Error loading calendar:', error);
      calendarBody.innerHTML = `
        <tr>
          <td colspan="7" class="text-center py-5 text-danger">
            Error loading calendar data
          </td>
        </tr>
      `;
    });
  }

  function generateCalendar(year, month, dailyData) {
    const calendarBody = document.getElementById('calendar-body');
    calendarBody.innerHTML = '';

    // Get first day of month and number of days
    const firstDay = new Date(year, month - 1, 1);
    const lastDay = new Date(year, month, 0);
    const daysInMonth = lastDay.getDate();
    const startingDayOfWeek = firstDay.getDay(); // 0 = Sunday, 6 = Saturday

    let date = 1;
    let rows = [];

    // Create rows
    for (let i = 0; i < 6; i++) {
      const row = document.createElement('tr');
      let rowHasData = false;

      // Create cells for each day of week
      for (let j = 0; j < 7; j++) {
        const cell = document.createElement('td');
        cell.className = 'align-top';

        if (i === 0 && j < startingDayOfWeek) {
          // Empty cells before first day of month
          cell.innerHTML = '';
        } else if (date > daysInMonth) {
          // Empty cells after last day of month
          cell.innerHTML = '';
        } else {
          rowHasData = true;
          const currentDate = `${year}-${String(month).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
          const dayData = dailyData[currentDate] || null;
          
          cell.innerHTML = generateDayCell(date, dayData);
          date++;
        }

        row.appendChild(cell);
      }

      if (rowHasData || rows.length === 0) {
        rows.push(row);
      }
    }

    rows.forEach(row => calendarBody.appendChild(row));
  }

  function generateDayCell(day, dayData) {
    if (!dayData || (dayData.total === 0 && dayData.subtotal === 0 && dayData.shipping === 0 && dayData.product_tax === 0)) {
      return `
        <div style="padding: 0.25rem;">
          <div class="fw-bold" style="font-size: 0.95rem !important; margin-bottom: 0.25rem; color: #566a7f !important;">${day}</div>
        </div>
      `;
    }

    const hasData = dayData.total !== 0 || dayData.shipping !== 0 || dayData.product_tax !== 0;

    return `
      <div style="padding: 0.70rem; min-height: 150px;">
        <div class="fw-bold" style="font-size: 0.95rem !important; margin-bottom: 0.25rem; color: #566a7f !important;">${day}</div>
        ${hasData ? `
          <table class="table table-striped table-bordered mt-3 small">
            <tbody>
              <tr>
                <td class="p-1 text-center" style="font-size: 0.9rem !important;  color: #696cff !important; background-color: #f5f5f9 !important; border-bottom: 1px solid #e0e0e0 !important;">
                  Sale
                </td>
              </tr>
              <tr>
                <td class="p-1 text-center" style="font-size: 0.9rem !important; color: #566a7f !important;">
                  ${currencySymbol}${parseFloat(dayData.subtotal || 0).toFixed(2)}
                </td>
              </tr>
              <tr>
                <td class="p-1 text-center" style="font-size: 0.9rem !important;  color: #696cff !important; background-color: #f5f5f9 !important; border-bottom: 1px solid #e0e0e0 !important;">
                  Shipping
                </td>
              </tr>
              <tr>
                <td class="p-1 text-center" style="font-size: 0.9rem !important; color: #566a7f !important;">
                  ${currencySymbol}${parseFloat(dayData.shipping || 0).toFixed(2)}
                </td>
              </tr>
              <tr>
                <td class="p-1 text-center" style="font-size: 0.9rem !important;  color: #696cff !important; background-color: #f5f5f9 !important; border-bottom: 1px solid #e0e0e0 !important;">
                  Product Tax
                </td>
              </tr>
              <tr>
                <td class="p-1 text-center" style="font-size: 0.9rem !important; color: #566a7f !important;">
                  ${currencySymbol}${parseFloat(dayData.product_tax || 0).toFixed(2)}
                </td>
              </tr>
              <tr>
                <td class="p-1 text-center" style="font-size: 0.9rem !important; font-weight: 700 !important; color: #696cff !important; background-color: #f5f5f9 !important; border-bottom: 1px solid #d0d7ff !important;">
                  Total
                </td>
              </tr>
              <tr>
                <td class="p-1 text-center" style="font-size: 0.9rem !important; font-weight: 700 !important; color: #566a7f !important;">
                  ${currencySymbol}${parseFloat(dayData.total || 0).toFixed(2)}
                </td>
              </tr>
            </tbody>
          </table>
        ` : ''}
      </div>
    `;
  }
});

