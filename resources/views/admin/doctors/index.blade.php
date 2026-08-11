@extends('admin.layout')

@section('title', $moduleTitle)
@section('heading', $moduleTitle)

@section('content')
    <section class="admin-panel">
        <form class="admin-filters" method="GET" data-live-admin-filters onsubmit="return false">
            <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Doctor name, speciality or city" autocomplete="off">

            @if(!isset($filters['content_type']))
                <select name="content_type">
                    <option value="">All types</option>
                    @foreach(['video', 'audio', 'card'] as $type)
                        <option value="{{ $type }}" @selected(($filters['content_type'] ?? '') === $type)>{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
            @endif

            <select name="status">
                <option value="">All statuses</option>
                @foreach(['awaiting_recording', 'processing', 'completed', 'failed'] as $status)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>
                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                    </option>
                @endforeach
            </select>

            <input type="date" name="from" value="{{ $filters['from'] ?? '' }}">
            <input type="date" name="to" value="{{ $filters['to'] ?? '' }}">
            <a class="btn-gold export-action" href="{{ route('admin.doctors.export', $filters) }}">Export Excel ↓</a>
        </form>

        <div class="admin-results-shell">
            <div class="admin-live-status" aria-live="polite"></div>
            <div class="admin-results" data-admin-results>
                @include('admin.partials.reels-table', ['items' => $reels])
                <div class="pagination">{{ $reels->links() }}</div>
            </div>
        </div>
    </section>
@endsection
