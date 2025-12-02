/**
 * App eCommerce Category List
 */

'use strict';

let quillAdd;
window.quillEdit = null;

// Function to initialize Quill editor for edit offcanvas
window.initializeQuillEdit = function () {
  const commentEditorEdit = document.querySelector('#category-description');
  if (commentEditorEdit) {
    // Destroy existing Quill instance if it exists
    if (window.quillEdit && typeof window.quillEdit.destroy === 'function') {
      window.quillEdit.destroy();
    }

    // Create new Quill instance
    window.quillEdit = new Quill(commentEditorEdit, {
      modules: {
        toolbar: '.comment-toolbar-edit'
      },
      placeholder: 'Write a Comment...',
      theme: 'snow'
    });

    return window.quillEdit;
  } else {
    return null;
  }
}

// Datatable (js)
document.addEventListener('DOMContentLoaded', function (e) {

  // Initialize Quill for Add Category (this works because add offcanvas is always visible)
  const commentEditorAdd = document.querySelector('#category-description');

  if (commentEditorAdd) {
    quillAdd = new Quill(commentEditorAdd, {
      modules: {
        toolbar: '.comment-toolbar'
      },
      placeholder: 'Write a Comment...',
      theme: 'snow'
    });
  }


  //select2 for dropdowns in offcanvas

  var select2 = $('.select2');
  if (select2.length) {
    select2.each(function () {
      var $this = $(this);
      $this.wrap('<div class="position-relative"></div>').select2({
        dropdownParent: $this.parent(),
        placeholder: $this.data('placeholder') //for dynamic placeholder
      });
    });
  }

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
      acceptedFiles: '.jpg,.jpeg,.png',
      addRemoveLinks: true,
      maxFiles: 1
    });

    myDropzone.on("addedfile", function (file) {
      const dataTransfer = new DataTransfer();
      dataTransfer.items.add(file);
      document.getElementById("categoryImage").files = dataTransfer.files;
    });
  }

  //For form validation
  const editCategoryForm = document.getElementById('editCategoryForm');

  if (editCategoryForm) {
    //Add New customer Form Validation
    const fv = FormValidation.formValidation(editCategoryForm, {
      fields: {
        categoryName: {
          validators: {
            notEmpty: {
              message: 'Please enter category title'
            }
          }
        },
        sortOrder: {
          validators: {
            notEmpty: {
              message: 'Please enter sort order'
            },
            numeric: {
              message: 'Please enter numeric value'
            },
            greaterThan: {
              message: 'Must be greater than 0',
              min: 1
            }
          }
        },
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

      // Check if Quill editor exists
      if (quillAdd) {
        // Get the content of the Quill editor
        const quillContentAdd = quillAdd.root.innerHTML;

        // Set the content to the hidden input
        const hiddenInput = document.getElementById('category-description-hidden');
        hiddenInput.value = quillContentAdd;
      }

      // Submit the form manually
      editCategoryForm.submit();
    });
  }
});
