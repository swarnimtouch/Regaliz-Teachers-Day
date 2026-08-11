@extends('admin.layout')

@section('title', 'Dashboard')
@section('heading', 'Good '.(now()->hour < 12 ? 'morning' : 'afternoon').', '.auth()->user()->name)

@section('content')
    <section class="stat-grid dashboard-only">
        @foreach([
            'total' => ['Total doctors', '◆'],
            'recordings' => ['Recordings', '●'],
            'completed' => ['Completed', '✓'],
            'processing' => ['Processing', '↻'],
            'failed' => ['Failed', '!'],
            'today' => ["Today's entries", '+'],
        ] as $key => $data)
            <article>
                <span>{{ $data[1] }}</span>
                <small>{{ $data[0] }}</small>
                <b>{{ number_format($stats[$key]) }}</b>
            </article>
        @endforeach
    </section>

    <div class="dashboard-charts">
        <section class="admin-panel dashboard-chart-card">
            <div class="panel-title">
                <div><h2>Last 7 days</h2><p>Daily campaign participation</p></div>
                <span class="chart-badge">Live overview</span>
            </div>
            @php($dailyMax = max(1, (int) $daily->max('total')))
            <div class="dashboard-bars">
                @foreach($daily as $point)
                    <div class="dashboard-bar">
                        <b>{{ $point->total }}</b>
                        <i><span style="height: {{ max(7, ($point->total / $dailyMax) * 100) }}%"></span></i>
                        <small>{{ $point->label }}</small>
                        <em>{{ $point->date }}</em>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="admin-panel dashboard-chart-card mix-card">
            <div class="panel-title"><div><h2>Content mix</h2><p>All submission formats</p></div></div>
            @php($mixTotal = max(1, (int) $contentMix->sum()))
            <div class="mix-ring" style="--video: {{ (($contentMix['video'] ?? 0) / $mixTotal) * 100 }}%; --audio: {{ (($contentMix['audio'] ?? 0) / $mixTotal) * 100 }}%">
                <div><b>{{ $contentMix->sum() }}</b><small>Total</small></div>
            </div>
            <div class="mix-legend">
                @foreach(['video' => 'Video', 'audio' => 'Audio', 'card' => 'Card'] as $type => $label)
                    <span class="{{ $type }}"><i></i>{{ $label }} <b>{{ $contentMix[$type] ?? 0 }}</b></span>
                @endforeach
            </div>
        </section>
    </div>
@endsection
