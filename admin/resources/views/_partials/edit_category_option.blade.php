<option value="{{ $category->id }}" @selected(in_array($category->id, $brandCategories))>
    {{ $prefix . $category->name }}
</option>

@if ($category->children && $category->children->count())
    @foreach ($category->children as $child)
        @include('_partials.edit_category_option', [
            'category' => $child,
            'prefix' => $prefix . '- '
        ])
    @endforeach
@endif

