@extends('layouts.admin')

@section('title', 'Monthly Report')

@section('content')
    <div class="container-fluid">
        <div class="container-fluid">
            <x-plant-header title="Laporan Bulanan" :plant="request()->get('plant')">
                <a href="{{ route('admin.monthly-reports.create') }}" class="btn btn-primary btn-icon-split shadow-sm">
                    <span class="icon text-white-50">
                        <i class="fas fa-plus"></i>
                    </span>
                    <span class="text">Upload Laporan Baru</span>
                </a>
            </x-plant-header>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Laporan Bulanan</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="15%">Periode</th>
                                    <th width="30%">Judul</th>
                                    <th width="15%">Diupload Oleh</th>
                                    <th width="10%">Status</th>
                                    <th width="25%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reports as $index => $report)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $report->period }}</td>
                                        <td>{{ $report->title }}</td>
                                        <td>{{ $report->creator->name ?? '-' }}</td>
                                        <td>
                                            @if($report->is_active)
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check-circle"></i> Aktif di Dashboard
                                                </span>
                                            @else
                                                <span class="badge badge-secondary">Tidak Aktif</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('monthly_reports.pdf', $report->id) }}" target="_blank"
                                                class="btn btn-info btn-sm" title="Lihat PDF">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>

                                            @if(!$report->is_active)
                                                <form action="{{ route('admin.monthly_reports.set_active', $report->id) }}"
                                                    method="POST" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm"
                                                        title="Tampilkan di Dashboard">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            <a href="{{ route('admin.monthly-reports.edit', $report->id) }}"
                                                class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <form action="{{ route('admin.monthly-reports.destroy', $report->id) }}"
                                                method="POST" style="display: inline;"
                                                onsubmit="return confirm('Yakin ingin menghapus laporan ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Belum ada laporan bulanan</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
@endsection