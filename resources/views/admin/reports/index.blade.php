@extends('admin.layout')

@section('title', 'Reports')
@section('heading', 'Campaign analytics')

@section('content')
    <form class="report-toolbar" method="GET">
        <label>From<input type="date" name="from" value="{{ $from->format('Y-m-d') }}"></label>
        <label>To<input type="date" name="to" value="{{ $to->format('Y-m-d') }}"></label>
        <button class="btn-outline">Search</button>
        <a class="btn-gold export-action" href="{{ route('admin.reports.export', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}">Export Excel ↓</a>
    </form>

    <section class="stat-grid report-stats">
        @foreach(['total' => 'Submissions', 'completed' => 'Completed', 'failed' => 'Failed', 'downloads' => 'Downloads'] as $key => $label)
            <article>
                <small>{{ $label }}</small>
                <b>{{ number_format($summary[$key]) }}</b>
            </article>
        @endforeach
    </section>

    <section class="admin-panel chart-panel">
        <div class="panel-title">
            <div>
                <h2>Submission activity</h2>
                <p>Daily entries during the selected period</p>
            </div>
        </div>

        <div class="bar-chart">
            @php($chartMax = max(1, (int) $daily->max('total')))
            @forelse($daily as $point)
                <div class="bar-item">
                    <span style="height: {{ max(8, ($point->total / $chartMax) * 100) }}%"><b>{{ $point->total }}</b></span>
                    <small>{{ \Carbon\Carbon::parse($point->day)->format('d M') }}</small>
                </div>
            @empty
                <div class="chart-empty">No submissions in this date range.</div>
            @endforelse
        </div>
    </section>

    <div class="detail-grid">
        <section class="admin-panel">
            <h2>Content mix</h2>
            @php($typeMax = max(1, (int) $byType->max()))
            @foreach(['video', 'audio', 'card'] as $type)
                <div class="progress-metric">
                    <div>
                        <span>{{ ucfirst($type) }}</span>
                        <b>{{ number_format($byType[$type] ?? 0) }}</b>
                    </div>
                    <i><em style="width: {{ (($byType[$type] ?? 0) / $typeMax) * 100 }}%"></em></i>
                </div>
            @endforeach
        </section>

        <section class="admin-panel">
            <h2>Top cities</h2>
            @forelse($byCity as $row)
                <div class="metric-row">
                    <span>{{ $row->city }}</span>
                    <b>{{ $row->total }}</b>
                </div>
            @empty
                <p>No data.</p>
            @endforelse
        </section>
    </div>
@endsection
