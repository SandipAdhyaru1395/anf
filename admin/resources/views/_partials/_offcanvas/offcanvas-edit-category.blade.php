<!-- Offcanvas to add new customer -->
  
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEditCategory"
    aria-labelledby="offcanvasEditCategoryLabel">
    <!-- Offcanvas Header -->
    <div class="offcanvas-header py-6">
      <h5 id="offcanvasEditCategoryLabel" class="offcanvas-title">Edit Category</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <!-- Offcanvas Body -->
    <div class="offcanvas-body border-top">
      <form class="pt-0" id="updateCategoryForm" action="{{ route('category.update') }}" method="POST">
        @csrf
        <input type="hidden" name="id" id="id">
        <!-- Title -->
        <div class="mb-6 form-control-validation">
          <label class="form-label" for="edit-category-title">Title</label>
          <input type="text" class="form-control" id="edit-category-title" placeholder="Enter category title"
            name="categoryTitle" aria-label="category title" />
          </div>
          
       
        <!-- Description -->
        <div class="mb-6">
          <label class="form-label">Description</label>
          <div class="form-control p-0 py-1">
            <input type="hidden" name="categoryDescription" id="category-description-hidden-edit">
            <div class="comment-editor-edit border-0" id="edit-category-description"></div>
            <div class="comment-toolbar-edit border-0 rounded">
              <div class="d-flex justify-content-end">
                <span class="ql-formats me-0">
                  <button class="ql-bold"></button>
                  <button class="ql-italic"></button>
                  <button class="ql-underline"></button>
                  <button class="ql-list" value="ordered"></button>
                  <button class="ql-list" value="bullet"></button>
                  <button class="ql-link"></button>
                  <button class="ql-image"></button>
                </span>
              </div>
            </div>
          </div>
        </div>
        <!-- Status -->
        <div class="mb-6">
          <label class="form-label">Status</label>
          <select id="edit-category-status" name="categoryStatus" class="select2 form-select" data-placeholder="Select category status">
            <option value="">Select status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <!-- Submit and reset -->
        <div class="mb-6">
          <button type="submit" class="btn btn-primary me-sm-3 me-1 data-submit">Update</button>
          <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Discard</button>
        </div>
      </form>
    </div>
  </div>