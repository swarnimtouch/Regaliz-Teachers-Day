@extends('admin.layout')

@section('title', 'Dashboard')
@section('heading', 'Good '.(now()->hour < 12 ? 'morning' : 'afternoon').', '.auth()->user()->name)

@section('content')
    <section class="stat-grid dashboard-only">
        @foreach([
            'doctors' => ['Total Doctors', 'fa-user-doctor'],
            'videos' => ['Total Videos', 'fa-video'],
            'audios' => ['Total Audios', 'fa-microphone'],
            'cards' => ['Total Cards', 'fa-address-card'],
        ] as $key => $data)
            <article>
                <span><i class="fa-solid {{ $data[1] }}"></i></span>
                <small>{{ $data[0] }}</small>
                <b>{{ number_format($stats[$key]) }}</b>
            </article>
        @endforeach
    </section>
@endsection
