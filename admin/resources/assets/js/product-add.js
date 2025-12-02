/**
 * App eCommerce Add Product Script
 */
'use strict';

//Javascript to handle the e-commerce product add page

(function () {
  // Comment editor

  let quill;
  const commentEditor = document.querySelector('.comment-editor');

  if (commentEditor) {
    quill = new Quill(commentEditor, {
      modules: {
        toolbar: '.comment-toolbar'
      },
      placeholder: 'Product Description',
      theme: 'snow'
    });
  }

  // previewTemplate: Updated Dropzone default previewTemplate

  // ! Don't change it unless you really know what you are doing

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

  // ? Start your code from here

  // Basic Dropzone

  const dropzoneBasic = document.querySelector('#dropzone-basic');
  let myDropzone = null;
  if (dropzoneBasic) {
    myDropzone = new Dropzone(dropzoneBasic, {
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
      document.getElementById("productImage").files = dataTransfer.files;
    });
  }

  //For form validation
  const addProductForm = document.getElementById('addProductForm');

  if (addProductForm) {
    //Add New customer Form Validation
    const fv = FormValidation.formValidation(addProductForm, {
      fields: {
        productSku: {
          validators: {
              notEmpty: {
                  message: 'Product code is required'
              },
              remote: {
                  message: 'This product code already exists',
                  method: 'POST',
                  url: baseUrl + 'check-sku',
                  data: function() {
                      return {
                          _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                          sku: addProductForm.querySelector('[name="productSku"]').value,
                      };
                  },
                  delay: 500, // wait 0.5s after typing before sending request
              },
          },
      },
        productUnitSku: {
          validators: {
              notEmpty: {
                  message: 'Product unit code is required'
              },
              remote: {
                  message: 'This product unit code already exists',
                  method: 'POST',
                  url: baseUrl + 'check-unit-sku',
                  data: function() {
                      return {
                          _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                          sku: addProductForm.querySelector('[name="productUnitSku"]').value,
                      };
                  },
                  delay: 500,
              },
          },
        },
        brands: {
          selector: 'select[name="brands[]"]', // target the checkbox group
          validators: {
            notEmpty: {
              message: 'Please select at least one brand'
            }
          }
        },
        step: {
          validators: {
            notEmpty: {
              message: 'Step quantity is required'
            },
            numeric: {
              message: 'Step quantity must be a number'
            },
            greaterThan: {
              message: 'Must be greater than 0',
              min: 1
            }
          }
        },
        productTitle: {
          validators: {
            notEmpty: {
              message: 'Please enter product name'
            }
          }
        },
        productPrice: {
          validators: {
            notEmpty: {
              message: 'Please enter selling price'
            },
            numeric: {
              message: 'Selling price must be a number'
            },
          }
        },
        costPrice: {
          validators: {
            numeric: {
              message: 'Cost price must be a number'
            },
          }
        },
        walletCredit: {
          validators: {
            numeric: {
              message: 'Wallet credit must be a number'
            },
          }
        },
        weight: {
          validators: {
            numeric: {
              message: 'Weight must be a number'
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
        productImageUrl: {
          validators: {
            uri: {
              message: 'Please enter a valid URL',
              allowLocal: false,
              allowEmpty: true
            },
            callback: {
              message: 'Either an image file or image URL is required.',
              callback: function(value, validator, $field) {
                const productImageInput = document.getElementById('productImage');
                const hasFile = productImageInput && productImageInput.files && productImageInput.files.length > 0;
                const hasUrl = value && value.trim() !== '';
                
                // If URL is provided, it should be valid (handled by uri validator above)
                // If no URL and no file, show error
                if (!hasUrl && !hasFile) {
                  return false;
                }
                return true;
              }
            }
          }
        },
        productImage: {
          validators: {
            callback: {
              message: 'Either an image file or image URL is required.',
              callback: function(value, validator, $field) {
                const productImageUrlInput = document.getElementById('productImageUrl');
                const hasUrl = productImageUrlInput && productImageUrlInput.value && productImageUrlInput.value.trim() !== '';
                const productImageInput = document.getElementById('productImage');
                const hasFile = productImageInput && productImageInput.files && productImageInput.files.length > 0;
                
                // If file is provided, validate it (Dropzone handles file validation)
                // If no file and no URL, show error
                if (!hasFile && !hasUrl) {
                  return false;
                }
                return true;
              }
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

    // Revalidate both image fields when either changes
    const productImageInput = document.getElementById('productImage');
    const productImageUrlInput = document.getElementById('productImageUrl');
    
    if (productImageInput) {
      productImageInput.addEventListener('change', function() {
        fv.revalidateField('productImage');
        fv.revalidateField('productImageUrl');
      });
    }
    
    if (productImageUrlInput) {
      productImageUrlInput.addEventListener('input', function() {
        fv.revalidateField('productImageUrl');
        fv.revalidateField('productImage');
      });
    }
    
    // Handle Dropzone file addition
    if (dropzoneBasic && myDropzone) {
      myDropzone.on("addedfile", function (file) {
        setTimeout(function() {
          fv.revalidateField('productImage');
          fv.revalidateField('productImageUrl');
        }, 100);
      });
      
      myDropzone.on("removedfile", function (file) {
        setTimeout(function() {
          fv.revalidateField('productImage');
          fv.revalidateField('productImageUrl');
        }, 100);
      });
    }

    // Handle form submission with Quill editor content
    fv.on('core.form.valid', function () {
      let content = quill.root.innerHTML;
      if (content === '<p><br></p>') {
        content = ''; // Treat as empty
      }
      document.getElementById("productDescription").value = content;
      // Submit the form manually
      addProductForm.submit();
    });
  }
})();

//Jquery to handle the e-commerce product add page
$(function () {
  // Init flatpickr for expiry date
  if (window.flatpickr && $('.flatpickr').length) {
    $('.flatpickr').each(function(){
      flatpickr(this, { dateFormat: 'd/m/Y', allowInput: true });
    });
  }
  // Select2 for all dropdowns
  var select2 = $('.select2');
  if (select2.length) {
    select2.each(function () {
      var $this = $(this);
      var isBrandsDropdown = $this.attr('name') === 'brands[]';

      $this.wrap('<div class="position-relative"></div>').select2({
        dropdownParent: $this.parent(),
        placeholder: $this.data('placeholder'), // for dynamic placeholder
        closeOnSelect: isBrandsDropdown ? false : true, // keep dropdown open only for brands
        templateResult: function (data) {
          if (!data.id) {
            return data.text;
          }
          
          // Only add checkboxes for brands dropdown
          if (isBrandsDropdown) {
            // Create the checkbox input
            var $result = $('<span class="brand-option"></span>');
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

      // Only add checkbox event handlers for brands dropdown
      if (isBrandsDropdown) {
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
