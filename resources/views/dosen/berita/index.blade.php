@extends('layouts.dosen')
@section('title', 'Berita Sistem Informasi Akademik')
@section('content')

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <h5 class="card-title text-primary fw-bold mb-3">📰 Berita Terbaru</h5>

        <!-- Input Pencarian -->
        <div class="mb-3">
            <input type="text" id="searchInput" class="form-control" placeholder="Cari berita..."
                onkeyup="searchNews()">
        </div>

        <!-- Spinner Loading -->
        <div id="loading" class="text-center my-3">
            <span class="spinner-border text-primary"></span>
            <p class="text-muted">Memuat berita...</p>
        </div>

        <!-- Grid Berita -->
        <div id="newsGridContainer" class="row row-cols-1 row-cols-md-3 g-4" style="display: none;">
            <!-- Berita akan dimasukkan di sini melalui JavaScript -->
        </div>

        <!-- Pesan Jika Tidak Ada Berita -->
        <p id="noNewsMessage" class="text-muted text-center" style="display: none;">Tidak ada berita ditemukan.</p>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            <button id="prevPage" class="btn btn-outline-secondary me-2" onclick="changePage(-1)"
                disabled>Previous</button>
            <span id="pageInfo" class="align-self-center"></span>
            <button id="nextPage" class="btn btn-outline-secondary ms-2" onclick="changePage(1)">Next</button>
        </div>
    </div>
</div>

<script>
    let beritaData = [];
    let currentPage = 1;
    let perPage = 6; // Jumlah berita per halaman

    document.addEventListener("DOMContentLoaded", function () {
        fetch('/dosen/api/berita-kampus')
            .then(response => response.json())
            .then(data => {

                beritaData = data;
                displayPage(currentPage); // Tampilkan halaman pertama

                document.getElementById("loading").style.display = "none"; // Sembunyikan loading
                document.getElementById("newsGridContainer").style.display = "flex"; // Tampilkan grid
            })
            .catch(error => {
                console.error("Gagal mengambil berita:", error);
                document.getElementById("loading").innerHTML = `<p class="text-danger">Gagal memuat berita.</p>`;
            });
    });

    function displayPage(page) {
        let start = (page - 1) * perPage;
        let end = start + perPage;
        let paginatedData = beritaData.slice(start, end);

        let newsContainer = document.getElementById("newsGridContainer");
        newsContainer.innerHTML = ""; // Kosongkan sebelum mengisi ulang

        if (paginatedData.length === 0) {
            document.getElementById("noNewsMessage").style.display = "block";
        } else {
            document.getElementById("noNewsMessage").style.display = "none";
            paginatedData.forEach(post => {
                let imageUrl = post.image ? post.image : '/assets/img/no-image.jpg';

                const beritaCard = `
                    <div class="col news-item">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="ratio ratio-16x9">
                                <img src="${imageUrl}" class="card-img-top rounded shadow-sm" alt="${post.title}" style="object-fit: cover;">
                            </div>
                            <div class="card-body">
                                <h6 class="card-title fw-bold">${post.title}</h6>
                                <p class="text-muted small">${post.date}</p>
                                <a href="/dosen/berita/${post.id}" class="btn btn-outline-primary btn-sm">Baca Selengkapnya</a>
                            </div>
                        </div>
                    </div>
                `;
                newsContainer.innerHTML += beritaCard;
            });
        }

        // Update pagination
        document.getElementById("pageInfo").innerText = `Halaman ${page} dari ${Math.ceil(beritaData.length / perPage)}`;
        document.getElementById("prevPage").disabled = page === 1;
        document.getElementById("nextPage").disabled = page >= Math.ceil(beritaData.length / perPage);
    }

    function changePage(direction) {
        currentPage += direction;
        displayPage(currentPage);
    }

    // Fungsi Pencarian Berita
    function searchNews() {
        let input = document.getElementById('searchInput').value.toLowerCase();
        let filteredData = beritaData.filter(post => post.title.toLowerCase().includes(input));

        document.getElementById("newsGridContainer").innerHTML = ""; // Kosongkan kontainer berita

        if (filteredData.length === 0) {
            document.getElementById("noNewsMessage").style.display = "block";
        } else {
            document.getElementById("noNewsMessage").style.display = "none";
            filteredData.forEach(post => {
                let imageUrl = post.image ? post.image : '/assets/img/no-image.jpg';

                const beritaCard = `
                    <div class="col news-item">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="ratio ratio-16x9">
                                <img src="${imageUrl}" class="card-img-top rounded shadow-sm" alt="${post.title}" style="object-fit: cover;">
                            </div>
                            <div class="card-body">
                                <h6 class="card-title fw-bold">${post.title}</h6>
                                <p class="text-muted small">${post.date}</p>
                          <a href="/mahasiswa/berita/${post.id}" class="btn btn-outline-primary btn-sm">Baca Selengkapnya</a>
                            </div>
                        </div>
                    </div>
                `;
                document.getElementById("newsGridContainer").innerHTML += beritaCard;
            });
        }
    }
</script>

@endsection
