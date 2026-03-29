@extends('layouts/layoutMaster')

@section('title', 'Terms & Conditions')

@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/quill/typography.scss',
    'resources/assets/vendor/libs/quill/katex.scss',
    'resources/assets/vendor/libs/quill/editor.scss'
  ])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/quill/quill.js'])
@endsection

@section('page-script')
  <script>
    $(function () {
      const editorElement = document.querySelector('#terms-editor');
      const hiddenInput = document.querySelector('#terms_and_conditions');
      if (!editorElement || !hiddenInput) return;

      const quill = new Quill(editorElement, {
        modules: { toolbar: '.terms-toolbar' },
        placeholder: 'Enter your terms and conditions...',
        theme: 'snow'
      });

      const initialValue = hiddenInput.value || '';
      if (initialValue) {
        quill.root.innerHTML = initialValue;
      }

      quill.on('text-change', function () {
        let content = quill.root.innerHTML;
        if (content === '<p><br></p>') content = '';
        hiddenInput.value = content;
      });
    });
  </script>
@endsection

@section('content')
<div class="row g-6">
  @include('content/settings/sidebar')

  <div class="col-12 col-lg-12 pt-6 pt-lg-0">
    <div class="tab-content p-0">
      <div class="tab-pane fade show active" id="termsAndConditions" role="tabpanel">
        <form action="{{ route('settings.termsAndConditions.update') }}" method="post">
          @csrf
          <div class="card mb-6">
            <div class="card-body">
              <div class="row mb-6 g-6">
                <div class="col-12">
                  <h5 class="mb-1">Terms & Conditions</h5>
                  <div class="form-control p-0 @error('terms_and_conditions') is-invalid @enderror">
                    <div class="terms-toolbar border-bottom">
                      <span class="ql-formats">
                        <select class="ql-header">
                          <option selected></option>
                          <option value="1"></option>
                          <option value="2"></option>
                        </select>
                      </span>
                      <span class="ql-formats">
                        <button class="ql-bold"></button>
                        <button class="ql-italic"></button>
                        <button class="ql-underline"></button>
                      </span>
                      <span class="ql-formats">
                        <button class="ql-list" value="ordered"></button>
                        <button class="ql-list" value="bullet"></button>
                      </span>
                      <span class="ql-formats">
                        <button class="ql-link"></button>
                        <button class="ql-clean"></button>
                      </span>
                    </div>
                    <div id="terms-editor" style="min-height: 320px;"></div>
                  </div>
                  <input
                    type="hidden"
                    id="terms_and_conditions"
                    name="terms_and_conditions"
                    value="{{ old('terms_and_conditions', $setting['terms_and_conditions'] ?? '') }}"
                  >
                  @error('terms_and_conditions')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-4">
            <button type="reset" class="btn btn-label-secondary">Discard</button>
            <button class="btn btn-primary" type="submit">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
