@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Selamat Datang, {{ Auth::user()->name }}!</h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <img class="img-fluid dashboard-image px-3 px-sm-4 mt-3 mb-4"
                            src="{{ asset('startbootstrap-sb-admin-2-gh-pages/img/Walpaper Dashboard.jpg') }}" alt="Dashboard">
                    </div>
                    <p class="mb-2">Anda telah berhasil masuk sebagai <strong>{{ ucfirst(Auth::user()->role) }}</strong>.</p>
                    <p class="mb-0">Selamat bekerja!</p>
                </div>
            </div>
        </div>
    </div>
@endsection
