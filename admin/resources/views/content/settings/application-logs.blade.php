@extends('layouts/layoutMaster')

@section('title', 'Application logs')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="mb-0">Application logs</h5>
                </div>
                <div class="card-body">
                    <form method="get" action="{{ route('settings.applicationLogs') }}" class="row g-2 align-items-end mb-3">
                        <div class="col-12 col-md-6 col-lg-4">
                            <label for="log-filter-q" class="form-label mb-0">Search</label>
                            <input type="text" class="form-control" id="log-filter-q" name="q"
                                value="{{ $filter }}" maxlength="200" autocomplete="off">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">Apply</button>
                            @if ($filter !== '')
                                <a href="{{ route('settings.applicationLogs') }}"
                                    class="btn btn-outline-secondary">Clear</a>
                            @endif
                        </div>
                    </form>

                    <pre class="mb-0 p-3 rounded border log-viewer-pre"
                        style="max-height: 70vh; overflow: auto; font-size: 0.8125rem; white-space: pre-wrap; word-break: break-word;">
@if (!$log['exists'])
{{ 'Log file does not exist yet.' }}
@elseif (!$log['readable'])
{{ 'Log file is not readable.' }}
@elseif ($log['content'] === '')
{{ '(empty)' }}
@else
{{ $log['content'] }}
@endif
</pre>
                </div>
            </div>
        </div>
    </div>
@endsection
