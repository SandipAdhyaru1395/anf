<?php

namespace App\traits;

use Illuminate\Http\Request;

trait BulkDeletes
{
    public function deleteMultiple(Request $request)
    {
        $this->model::whereIn('id', $request->ids)->delete();

        return response()->json(['success' => true]);
    }
}
