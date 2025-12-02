/**
 * App eCommerce Edit Brand Script
 */
'use strict';

//Javascript to handle the e-commerce brand edit page

(function () {

  // Basic Dropzone
  const previewTemplate = `<div class="dz-preview dz-file-preview">
<div class="dz-details">
  <div class="dz-thumbnail">
    <img data-dz-thumbnail>
    <span class="dz-nopreview">No preview</span>
    <div class="dz-success-mark"></div>
    <div class="dz-error-mark"></div>
    <div class="dz-error-message"><span data-dz-errormessage></span></div>
    <div class="progress">
      <div class="progress-bar progress-bar-primary" role="progressbar" aria-valuemin="0" aria-valuemax="100" data-dz-uploadprogress></div>
    </div>
  </div>
  <div class="dz-filename" data-dz-name></div>
  <div class="dz-size" data-dz-size></div>
</div>
</div>`;
  const dropzoneBasic = document.querySelector('#dropzone-basic');
  if (dropzoneBasic) {
    const myDropzone = new Dropzone(dropzoneBasic, {
      url: "#",
      previewTemplate: previewTemplate,
      autoProcessQueue: false,
      parallelUploads: 1,
      maxFilesize: 5,
      acceptedFiles: '.jpg,.jpeg,.png,.webp',
      addRemoveLinks: true,
      maxFiles: 1
    });

    myDropzone.on("addedfile", function (file) {
      const dataTransfer = new DataTransfer();
      dataTransfer.items.add(file);
      document.getElementById("brandImage").files = dataTransfer.files;
    });
  }

  // Basic Tags

  const tagifyBasicEl = document.querySelector('#brand-tags');
  if (tagifyBasicEl) {
    const TagifyBasic = new Tagify(tagifyBasicEl);
    void TagifyBasic;
  }

  //For form validation
  const editBrandForm = document.getElementById('editBrandForm');

  if (editBrandForm) {
    //Add New customer Form Validation
    const fv = FormValidation.formValidation(editBrandForm, {
      fields: {
        brandTitle: {
          validators: {
            notEmpty: {
              message: 'Please enter brand name'
            }
          }
        },
        categories: {
          selector: 'select[name="categories[]"]', // target the checkbox group
          validators: {
            notEmpty: {
              message: 'Please select at least one category'
            }
          }
        }
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          // Use this for enabling/changing valid/invalid class
          eleValidClass: 'is-valid',
          rowSelector: function (field, ele) {
            // field is the field name & ele is the field element
            return '.form-control-validation';
          }
        }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        // Don't use defaultSubmit, we'll handle submission manually
        autoFocus: new FormValidation.plugins.AutoFocus()
      }
    });

    // Handle form submission with Quill editor content
    fv.on('core.form.valid', function () {
     
      editBrandForm.submit();
    });
  }

})();

//Jquery to handle the e-commerce brand edit page
$(function () {
  // Select2 for all dropdowns
  var select2 = $('.select2');
  if (select2.length) {
    select2.each(function () {
      var $this = $(this);
      var isCategoryDropdown = $this.attr('name') === 'categories[]';

      $this.wrap('<div class="position-relative"></div>').select2({
        dropdownParent: $this.parent(),
        placeholder: $this.data('placeholder'), // for dynamic placeholder
        closeOnSelect: isCategoryDropdown ? false : true, // keep dropdown open only for categories
        templateResult: function (data) {
          if (!data.id) {
            return data.text;
          }
          
          // Only add checkboxes for category dropdown
          if (isCategoryDropdown) {
            // Create the checkbox input
            var $result = $('<span class="category-option"></span>');
            var checkbox = $('<input class="form-check-input" type="checkbox" style="margin-right:5px;" data-value="' + data.id + '" />');

            // Check if the option is selected and update checkbox state
            if (data.selected) {
              checkbox.prop('checked', true);
            }

            $result.append(checkbox);
            $result.append(' ' + data.text); // Add the option text

            return $result;
          } else {
            // Regular dropdown without checkboxes
            return data.text;
          }
        },
        templateSelection: function (data) {
          return data.text;
        }
      });

      // Only add checkbox event handlers for category dropdown
      if (isCategoryDropdown) {
        // Keep checkboxes in sync with selections
        $this.on('select2:select', function (e) {
          var el = $(e.params.data.element);
          el.prop('selected', true);
          
          // Clear the search input after selection
          setTimeout(function() {
            // $this.select2('close');
            // Clear the search input
            $('.select2-search__field').val('').focus();
          }, 50);
          
          // Update the specific checkbox in the dropdown to show as checked
          setTimeout(function() {
            $('.select2-dropdown').find('input[data-value="' + e.params.data.id + '"]').prop('checked', true);
          }, 10);
        });

        $this.on('select2:unselect', function (e) {
          var el = $(e.params.data.element);
          el.prop('selected', false);

          // Uncheck the specific checkbox when the item is unselected
          setTimeout(function() {
            $('.select2-dropdown').find('input[data-value="' + e.params.data.id + '"]').prop('checked', false);
          }, 10);
        });
      }
    });
  }
});
