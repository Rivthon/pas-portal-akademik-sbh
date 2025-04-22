@extends('layouts.mahasiswa')

@section('title', $berita['title'])

@section('content')
<div class="row">
    <!-- Kolom utama untuk berita -->
    <div class="col-lg-8">
        <div class="card shadow
            <div class=" card shadow-sm mb-4">
            <div class="card-body">
                <!-- Judul Berita di Atas Gambar -->
                <h2 class="fw-bold text-dark text-left mb-3">{{ $berita['title'] }}</h2>
                <span class="d-flex align-items-center mb-3">
                    <i class="bx bxs-calendar-event me-2"></i>
                    <p class="small mb-0">{{ $berita['date'] }}</p>
                </span>
                <!-- Gambar Featured Image Full Width -->
                <div class="news-header position-relative">
                    <img src="{{ $berita['image'] }}" class="img-fluid w-100 rounded shadow-sm news-featured-img"
                        alt="{{ $berita['title'] }}">
                    {{-- <div class="news-overlay position-absolute bottom-0 start-0 end-0 text-center p-3 text-white"
                        style="background: rgba(0, 0, 0, 0.5);">

                    </div> --}}
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <!-- Kontainer Berita dengan Grid -->
                <div class="news-container mt-4 mb-3">
                    <div class="news-content">
                        {!! $berita['content'] !!}
                    </div>
                </div>

                <!-- Tombol Kembali -->
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary mt-3">⬅ Kembali</a>
            </div>
        </div>
    </div>

    <!-- Sidebar untuk berita terkait -->
    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="fw-bold">Berita Populer</h5>
                <ul class="list-group">
                    @foreach ($beritaTerkait as $news)
                    <li class="list-group-item d-flex align-items-center">
                        <img src="{{ $news['image'] }}" alt="{{ $news['title'] }}" class="img-thumbnail news-thumbnail">
                        <a href="{{ $news['link'] }}" target="_blank" class="text-dark text-decoration-none">
                            {{ Str::limit(strip_tags($news['title']), 50) }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
</div>

<style>
    /* Membuat ukuran gambar berita utama lebih proporsional */
    .news-featured-img {
        max-height: 400px;
        object-fit: cover;
        border-radius: 15px;
    }

    /* Menjaga ukuran gambar di dalam konten agar tetap seragam */
    .news-content img {
        width: 100%;
        height: auto;
        max-height: 400px;
        object-fit: cover;
        border-radius: 10px;
        display: block;
        margin: 10px auto;
        text-align: center;
    }

    /* Gambar berita populer di sidebar */
    .news-thumbnail {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 10px;
    }

    /* Membuat teks justify */
    .news-content {
        text-align: justify;
    }
</style>
@endsection