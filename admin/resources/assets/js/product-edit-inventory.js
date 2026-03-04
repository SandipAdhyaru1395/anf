'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const onHandInput = document.querySelector('#inventory-onhand');
  const orderedHidden = document.querySelector('#inventory-ordered-hidden');
  const availableBadge = document.querySelector('#inventory-available');

  if (!onHandInput || !orderedHidden || !availableBadge) return;

  function recalcAvailable() {
    const ordered = parseInt(orderedHidden.value || '0', 10) || 0;
    let onHand = parseInt(onHandInput.value || '0', 10);
    if (isNaN(onHand) || onHand < 0) onHand = 0;

    const available = Math.max(0, onHand - ordered);
    availableBadge.textContent = available;
  }

  onHandInput.addEventListener('input', recalcAvailable);
  recalcAvailable();
});

