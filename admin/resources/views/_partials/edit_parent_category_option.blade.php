<option value="{{ $category->id }}" @selected($category->id == $main_category->parent_id)>
    {{ $prefix . $category->name }}
</option>

@if ($category->children && $category->children->count())
    @foreach ($category->children as $child)
        @include('_partials.edit_parent_category_option', [
            'category' => $child,
            'prefix' => $prefix . '- '
        ])
    @endforeach
@endif
