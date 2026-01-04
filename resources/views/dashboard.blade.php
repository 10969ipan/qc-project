@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Selamat Datang, {{ Auth::user()->name }}!</h6>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-calendar-alt fa-lg text-primary mr-2"></i>
                        <h6 class="m-0 font-weight-bold text-gray-700" id="current-date">Loading...</h6>
                    </div>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <img class="img-fluid dashboard-image px-3 px-sm-4 mt-3 mb-4" style="max-width: 250px;"
                            src="{{ asset('startbootstrap-sb-admin-2-gh-pages/img/Walpaper Dashboard.jpg') }}"
                            alt="Dashboard">
                    </div>
                    <p class="mb-2">Anda telah berhasil masuk sebagai <strong>{{ ucfirst(Auth::user()->role) }}</strong>.
                    </p>
                    <p class="mb-0">Selamat bekerja!</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateDate() {
            const now = new Date();

            // Format date
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

            const dayName = days[now.getDay()];
            const date = now.getDate();
            const monthName = months[now.getMonth()];
            const year = now.getFullYear();

            const dateString = `${dayName}, ${date} ${monthName} ${year}`;

            // Update DOM
            const dateElement = document.getElementById('current-date');
            if (dateElement) {
                dateElement.textContent = dateString;
            }
        }

        // Update immediately
        updateDate();
    </script>
@endsection