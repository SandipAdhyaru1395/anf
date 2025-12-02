<!-- Offcanvas to add new customer -->
  
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddSubCategory"
    aria-labelledby="offcanvasAddSubCategoryLabel">
    <!-- Offcanvas Header -->
    <div class="offcanvas-header py-6">
      <h5 id="offcanvasAddSubCategoryLabel" class="offcanvas-title">Add Sub Category</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <!-- Offcanvas Body -->
    <div class="offcanvas-body border-top">
      <form class="pt-0" id="addSubCategoryForm" action="{{ route('subcategory.create') }}" method="POST">
        @csrf
        <!-- Title -->
        <div class="mb-6 form-control-validation">
          <label class="form-label" for="add-sub-category-title">Title</label>
          <input type="text" class="form-control" id="add-sub-category-title" placeholder="Enter sub category title"
            name="subcategoryTitle" aria-label="category title" />
          </div>
          
       
        <!-- Description -->
        <div class="mb-6">
          <label class="form-label">Description</label>
          <div class="form-control p-0 py-1">
            <input type="hidden" name="subcategoryDescription" id="sub-category-description-hidden">
            <div class="comment-editor-add border-0" id="add-sub-category-description"></div>
            <div class="comment-toolbar border-0 rounded">
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
        <div class="mb-6 form-control-validation">
          <label class="form-label">Select Category</label>
          <select id="add-sub-category-select" name="category_id" class="select2 form-select" data-placeholder="Select category">
            <option value="">Select category</option>
              @forelse ($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
              @empty

              @endforelse
          </select>
        </div>
        <!-- Status -->
        <div class="mb-6">
          <label class="form-label">Status</label>
          <select id="add-sub-category-status" name="subcategoryStatus" class="select2 form-select" data-placeholder="Select category status">
            <option value="">Select status</option>
            <option value="active" selected>Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <!-- Submit and reset -->
        <div class="mb-6">
          <button type="submit" class="btn btn-primary me-sm-3 me-1 data-submit">Add</button>
          <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Discard</button>
        </div>
      </form>
    </div>
  </div>