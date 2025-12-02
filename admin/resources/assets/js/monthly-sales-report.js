/**
 * Monthly Sales Report Script
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  const currencySymbol = window.currencySymbol || '';
  let currentYear = new Date().getFullYear();

  // Load calendar for current year
  loadCalendar(currentYear);

  // Previous year button
  document.getElementById('prev-year').addEventListener('click', function() {
    currentYear--;
    loadCalendar(currentYear);
  });

  // Next year button
  document.getElementById('next-year').addEventListener('click', function() {
    currentYear++;
    loadCalendar(currentYear);
  });

  function loadCalendar(year) {
    // Show loading state
    const calendarBody = document.getElementById('calendar-body');
    calendarBody.innerHTML = `
      <tr>
        <td colspan="4" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </td>
      </tr>
    `;

    // Fetch monthly sales data
    fetch(`${baseUrl}report/monthly-sales/ajax?year=${year}`, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Update year display
        document.getElementById('current-year').textContent = year;
        
        // Generate calendar
        generateCalendar(year, data.monthly_data);
      } else {
        calendarBody.innerHTML = `
          <tr>
            <td colspan="4" class="text-center py-5 text-danger">
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
          <td colspan="4" class="text-center py-5 text-danger">
            Error loading calendar data
          </td>
        </tr>
      `;
    });
  }

  function generateCalendar(year, monthlyData) {
    const calendarBody = document.getElementById('calendar-body');
    calendarBody.innerHTML = '';

    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                       'July', 'August', 'September', 'October', 'November', 'December'];
    
    // Create 3 rows with 4 months each (3x4 grid)
    const monthsPerRow = 4;
    
    for (let rowIndex = 0; rowIndex < 3; rowIndex++) {
      const row = document.createElement('tr');
      
      for (let colIndex = 0; colIndex < monthsPerRow; colIndex++) {
        const monthIndex = rowIndex * monthsPerRow + colIndex;
        const month = monthIndex + 1;
        const monthKey = `${year}-${String(month).padStart(2, '0')}`;
        const monthData = monthlyData[monthKey] || null;
        const monthName = monthNames[monthIndex];
        
        const cell = document.createElement('td');
        cell.className = 'align-top';
        cell.innerHTML = generateMonthCell(monthName, monthData);
        row.appendChild(cell);
      }
      
      calendarBody.appendChild(row);
    }
  }

  function generateMonthCell(monthName, monthData) {
    let monthHtml = `
      <div style="padding: 0.70rem; min-height: 200px;">
        <div class="fw-bold mb-2" style="font-size: 0.95rem !important; color: #566a7f !important;">
          ${monthName}
        </div>
    `;

    if (monthData && (monthData.total !== 0 || monthData.subtotal !== 0 || monthData.shipping !== 0 || monthData.product_tax !== 0)) {
      monthHtml += `
        <table class="table table-striped table-bordered mt-3 small">
          <tbody>
            <tr>
              <td class="p-1 text-center" style="font-size: 0.9rem !important; color: #696cff !important; background-color: #f5f5f9 !important; border-bottom: 1px solid #e0e0e0 !important;">
                Subtotal
              </td>
            </tr>
            <tr>
              <td class="p-1 text-center" style="font-size: 0.9rem !important; color: #566a7f !important;">
                ${currencySymbol}${parseFloat(monthData.subtotal || 0).toFixed(2)}
              </td>
            </tr>
            <tr>
              <td class="p-1 text-center" style="font-size: 0.9rem !important; color: #696cff !important; background-color: #f5f5f9 !important; border-bottom: 1px solid #e0e0e0 !important;">
                Shipping
              </td>
            </tr>
            <tr>
              <td class="p-1 text-center" style="font-size: 0.9rem !important; color: #566a7f !important;">
                ${currencySymbol}${parseFloat(monthData.shipping || 0).toFixed(2)}
              </td>
            </tr>
            <tr>
              <td class="p-1 text-center" style="font-size: 0.9rem !important; color: #696cff !important; background-color: #f5f5f9 !important; border-bottom: 1px solid #e0e0e0 !important;">
                Product Tax
              </td>
            </tr>
            <tr>
              <td class="p-1 text-center" style="font-size: 0.9rem !important; color: #566a7f !important;">
                ${currencySymbol}${parseFloat(monthData.product_tax || 0).toFixed(2)}
              </td>
            </tr>
            <tr>
              <td class="p-1 text-center" style="font-size: 0.9rem !important; font-weight: 700 !important; color: #696cff !important; background-color: #f5f5f9 !important; border-bottom: 1px solid #d0d7ff !important;">
                Total
              </td>
            </tr>
            <tr>
              <td class="p-1 text-center" style="font-size: 0.9rem !important; font-weight: 700 !important; color: #566a7f !important;">
                ${currencySymbol}${parseFloat(monthData.total || 0).toFixed(2)}
              </td>
            </tr>
          </tbody>
        </table>
      `;
    } else {
      monthHtml += `
        <div class="text-muted text-center mt-3" style="font-size: 0.85rem !important;">
          No sales data
        </div>
      `;
    }

    monthHtml += `</div>`;
    return monthHtml;
  }
});

