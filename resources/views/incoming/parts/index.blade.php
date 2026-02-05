@extends('layouts.admin')

@section('title', 'Laporan Incoming Part')

@section('content')
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-body py-3">
            <div class="row align-items-start">
                <div class="col-md-8 border-right">
                    <h1 class="h4 mb-0 text-gray-800 font-weight-bold text-uppercase">
                        LAPORAN DATA INCOMING PART
                        @php
                            $plant = request('plant') ?? auth()->user()->plant_id;
                            $plantCode = (is_string($plant) && strlen($plant) > 30) ? \App\Models\Plant::where('id', $plant)->value('code') : (string) $plant;
                            $plantCode = strtolower($plantCode ?: 'karawang');
                        @endphp
                        <span class="badge badge-{{ $plantCode === 'jakarta' ? 'info' : 'primary' }} d-block d-md-inline-block ml-md-2 mt-2 mt-md-0" style="font-size: 0.8rem; width: fit-content;">
                            <i class="fas fa-building mr-1"></i>
                            Plant {{ ucfirst($plantCode) }}
                        </span>
                    </h1>
                </div>
                <div class="col-md-4 d-flex justify-content-end">
                    <div class="col p-0" style="max-width: 250px;">
                        <div class="row mb-1">
                            <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">No. Dokumen</div>
                            <div class="col-7 text-xs font-weight-bold text-gray-800">: QC-KRW-F-0210</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">Tgl. Terbit</div>
                            <div class="col-7 text-xs font-weight-bold text-gray-800">: 01/01/2026</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Masuk Incoming Part</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('incoming.parts.index') }}" method="GET" class="mb-4">
                <div class="row align-items-end">
                    <input type="hidden" name="plant" value="{{ request('plant') }}">
                    
                    <div class="col-lg-3 mb-2">
                        <label class="small font-weight-bold">Pencarian</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari..." value="{{ request('search') }}">
                    </div>

                    <div class="col-lg-2 mb-2">
                        <label class="small font-weight-bold">Dari Tanggal</label>
                        <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                    </div>

                    <div class="col-lg-2 mb-2">
                        <label class="small font-weight-bold">Sampai Tanggal</label>
                        <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                    </div>

                    <div class="col-lg-3 mb-2">
                        <button type="submit" class="btn btn-primary btn-sm mr-2">
                            <i class="fas fa-search"></i> Cari
                        </button>
                        <a href="{{ route('incoming.parts.index', ['plant' => request('plant')]) }}" class="btn btn-secondary btn-sm mr-2">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                        <a href="{{ route('incoming.parts.export_pdf', request()->query()) }}" class="btn btn-danger btn-sm">
                            <i class="fas fa-file-pdf"></i> Export
                        </a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered text-center" width="100%" cellspacing="0">
                    <thead class="bg-light">
                        <tr>
                            <th rowspan="2" class="align-middle">No</th>
                            <th rowspan="2" class="align-middle">Tanggal Check</th>
                            <th rowspan="2" class="align-middle">Shift</th>
                            <th rowspan="2" class="align-middle">Item Part</th>
                            <th rowspan="2" class="align-middle">Total Check</th>
                            <th rowspan="2" class="align-middle">Tgl Datang</th>
                            <th rowspan="2" class="align-middle">OK</th>
                            <th rowspan="2" class="align-middle">NG</th>
                            <th colspan="2">Detail NG</th>
                            <th rowspan="2" class="align-middle">Judgment</th>
                            <th rowspan="2" class="align-middle">Inisial</th>
                            <th colspan="4">Approval Status</th>
                            <th rowspan="2" class="align-middle">Remarks</th>
                            <th rowspan="2" class="align-middle">Aksi</th>
                        </tr>
                        <tr>
                            <th>Pcs</th>
                            <th>Jenis NG</th>
                            <th>KS</th>
                            <th>SPV</th>
                            <th>AM</th>
                            <th>MGR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($checksheets as $cs)
                            <tr>
                                <td class="align-middle">{{ $checksheets->firstItem() + $loop->index }}</td>
                                <td class="align-middle">{{ date('d/m/Y', strtotime($cs->date)) }}</td>
                                <td class="align-middle">{{ $cs->shift }}</td>
                                <td class="align-middle">
                                    {{ $cs->item->name }}<br>
                                    <small class="text-muted">{{ $cs->item->part_number }}</small>
                                </td>
                                <td class="align-middle">{{ $cs->total_check }}</td>
                                <td class="align-middle">{{ $cs->tanggal_datang ? date('d/m/Y', strtotime($cs->tanggal_datang)) : '-' }}</td>
                                <td class="align-middle text-success font-weight-bold">{{ $cs->total_check - $cs->total_ng }}</td>
                                <td class="align-middle text-danger font-weight-bold">{{ $cs->total_ng }}</td>
                                
                                @php
                                    $defects = is_array($cs->defects) ? $cs->defects : json_decode($cs->defects, true);
                                @endphp
                                <td class="p-0 align-middle">
                                    @foreach($defects ?? [] as $d)
                                        <div class="border-bottom py-1">{{ $d['qty'] ?? 0 }}</div>
                                    @endforeach
                                </td>
                                <td class="p-0 align-middle">
                                    @foreach($defects ?? [] as $d)
                                        <div class="border-bottom py-1">{{ $d['type'] ?? '-' }}</div>
                                    @endforeach
                                </td>

                                <td class="align-middle">
                                    <span class="badge badge-{{ $cs->judgment == 'OK' ? 'success' : 'danger' }}">
                                        {{ $cs->judgment }}
                                    </span>
                                </td>
                                <td class="align-middle">{{ $cs->operator_initials }}</td>
                                
                                {{-- Approval Columns --}}
                                @foreach(['kashift_qc', 'supervisor_qc', 'asst_manager_qc', 'manager_qc'] as $lvl)
                                    <td class="align-middle h6">
                                        @if($cs->$lvl == 'REJECTED')
                                            <span class="badge badge-danger">REJ</span>
                                        @elseif($cs->$lvl)
                                            <span class="badge badge-success">APP</span>
                                            <br><small class="text-muted">{{ $cs->$lvl }}</small>
                                        @else
                                            <span class="badge badge-warning">PEN</span>
                                        @endif
                                    </td>
                                @endforeach

                                <td class="align-middle small">{{ $cs->remarks }}</td>
                                <td class="align-middle text-nowrap">
                                    <div class="btn-group">
                                        {{-- Approval Actions --}}
                                        @if(!in_array(auth()->user()->role, ['inspector', 'oshef']))
                                            <button type="button" class="btn btn-success btn-xs mx-1 approve-btn" data-id="{{ $cs->id }}" data-type="kashift">
                                                Approve
                                            </button>
                                            <a href="{{ route('incoming.parts.edit', $cs->id) }}" class="btn btn-warning btn-sm mx-1">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('incoming.parts.destroy', $cs->id) }}" method="POST" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm mx-1" onclick="return confirm('Hapus data?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="18" class="text-center">Data tidak ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $checksheets->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection
