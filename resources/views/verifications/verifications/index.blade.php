@extends('layouts.admin')

@section('title', 'Hasil Verifikasi')

@section('content')
<style>
    #dataTable { font-size: 0.75rem; }
    #dataTable thead th {
        background-color: #1cc88a !important;
        color: white !important;
        padding: 0.5rem !important;
        vertical-align: middle;
        text-transform: uppercase;
        font-size: 0.70rem;
    }
    #dataTable td { vertical-align: middle; padding: 0.4rem !important; }
</style>

<div class="container-fluid">
    <div class="card shadow mb-2">
        <div class="card-body p-0">
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td style="width:75px; border:1px solid #dee2e6; padding:5px; text-align:center; vertical-align:middle;">
                        <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo" style="max-width:58px; max-height:44px;">
                    </td>
                    <td style="border:1px solid #dee2e6; border-left:none; padding:5px 8px; text-align:center; vertical-align:middle;">
                        <h1 class="mb-0 font-weight-bold text-uppercase text-gray-800" style="font-size:0.85rem;">
                            HASIL VERIFIKASI ALAT (JIG, MAL, C/F)
                        </h1>
                    </td>
                    <td style="width:1px; border:1px solid #dee2e6; border-left:none; padding:4px 8px; vertical-align:middle; white-space:nowrap;">
                        <table style="border-collapse:collapse; font-size:0.68rem;">
                            <tr>
                                <td style="padding:1px 3px; font-weight:600;">No. Dokumen</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600;">QC-F-VER-002</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-success">Riwayat Verifikasi</h6>
            <button type="button" class="btn btn-sm btn-success shadow-sm" data-toggle="modal" data-target="#modalVerifikasiBaru">
                <i class="fas fa-plus fa-sm text-white-50"></i> Input Verifikasi
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm text-center" id="dataTable" width="100%">
                    <thead>
                        <tr>
                            <th>NO.</th>
                            <th>NAMA PART</th>
                            <th>NO. PART</th>
                            <th>TANGGAL VERIFIKASI</th>
                            <th>NEXT VERIFIKASI</th>
                            <th>JUDGMENT</th>
                            <th>REMARKS</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($verifications as $index => $v)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="text-left">{{ $v->name_part }}</td>
                                <td>{{ $v->no_part }}</td>
                                <td>{{ $v->tanggal_verifikasi?->format('d/m/Y') }}</td>
                                <td>{{ $v->next_verifikasi?->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge {{ $v->judgment == 'OK' ? 'badge-success' : 'badge-danger' }}">
                                        {{ $v->judgment }}
                                    </span>
                                </td>
                                <td class="small">{{ $v->remarks }}</td>
                                <td>
                                    <div class="d-flex justify-content-center" style="gap: 5px;">
                                        @if($v->certification_path)
                                            <a href="{{ asset('storage/' . $v->certification_path) }}" target="_blank" class="btn btn-sm btn-primary shadow-sm">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        @endif
                                        <button class="btn btn-sm btn-info shadow-sm"><i class="fas fa-edit"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Input Verifikasi (Simplified) -->
<div class="modal fade" id="modalVerifikasiBaru" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Input Verifikasi Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('verifications.verifications.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="small font-weight-bold">Pilih Alat</label>
                        <select name="tool_id" class="form-control form-control-sm" required>
                            <option value="">-- Pilih --</option>
                            @php 
                                $filteredPlant = \App\Models\Plant::where('code', $plantCode)->first();
                                $tools = \App\Models\VerificationTool::where('plant_id', $filteredPlant->id)->get(); 
                            @endphp
                            @foreach($tools as $tool)
                                <option value="{{ $tool->id }}">{{ $tool->name_part }} ({{ $tool->no_part }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Tanggal Verifikasi</label>
                        <input type="date" name="tanggal_verifikasi" class="form-control form-control-sm" required>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Judgment</label>
                        <select name="judgment" class="form-control form-control-sm">
                            <option value="OK">OK</option>
                            <option value="NG">NG</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-success btn-sm px-4">Simpan</button></div>
            </form>
        </div>
    </div>
</div>
@endsection
