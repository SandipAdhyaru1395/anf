'use strict';

(function () {
  // Dropzone
  const dropzoneBanner = document.querySelector('#dropzone-banner');
  const bannerImageInput = document.querySelector('#bannerImage');
  const btnBrowseBanner = document.querySelector('#btnBrowseBanner');

  // Dropzone configuration
  if (dropzoneBanner) {
    const bannerDropzone = new Dropzone(dropzoneBanner, {
      url: 'javascript:void(0);',
      clickable: true,
      acceptedFiles: 'image/*',
      addRemoveLinks: false,
      maxFiles: 1,
      autoProcessQueue: false,
      init: function () {
        this.on('addedfile', function (file) {
          // Update the hidden input
          const dataTransfer = new DataTransfer();
          dataTransfer.items.add(file);
          bannerImageInput.files = dataTransfer.files;
          
          // Update the preview image
          const reader = new FileReader();
          reader.onload = function(e) {
            const img = document.querySelector('#dropzone-banner').parentElement.parentElement.querySelector('img');
            if (img) {
              img.src = e.target.result;
            }
          };
          reader.readAsDataURL(file);
        });

        this.on('removedfile', function (file) {
          // Clear the hidden input
          bannerImageInput.value = '';
          
          // Reset preview image to original
          const img = document.querySelector('#dropzone-banner').parentElement.parentElement.querySelector('img');
          if (img) {
            img.src = img.getAttribute('data-original-src') || '';
          }
        });
      }
    });

    // File input change handler
    if (bannerImageInput) {
      bannerImageInput.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
          // Add file to dropzone
          bannerDropzone.addFile(file);
        }
      });
    }
  }

  // Form validation
  const bannerForm = document.querySelector('#bannerSettingsForm');
  if (bannerForm) {
    bannerForm.addEventListener('submit', function (e) {
      e.preventDefault();
      
      // Check if banner image is selected
      if (!bannerImageInput.files.length) {
        alert('Please select a banner image');
        return;
      }

      // Submit the form
      this.submit();
    });
  }

  // Reset button handler
  const resetBtn = document.querySelector('button[type="reset"]');
  if (resetBtn) {
    resetBtn.addEventListener('click', function (e) {
      e.preventDefault();
      
      // Clear dropzone
      if (typeof bannerDropzone !== 'undefined') {
        bannerDropzone.removeAllFiles(true);
      }
      
      // Clear file input
      bannerImageInput.value = '';
      
      // Reset preview image
      const img = document.querySelector('#dropzone-banner').parentElement.parentElement.querySelector('img');
      if (img) {
        img.src = img.getAttribute('data-original-src') || '';
      }
    });
  }

})();
