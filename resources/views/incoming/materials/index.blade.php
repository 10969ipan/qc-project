@extends('layouts.admin')

@section('title', 'Incoming Material')

@section('content')
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-body py-3">
            <div class="row align-items-start">
                <div class="col-md-8 border-right">
                    <h1 class="h4 mb-0 text-gray-800 font-weight-bold text-uppercase">
                        LAPORAN DATA INCOMING MATERIAL
                        @php
                            $plant = request('plant') ?? auth()->user()->plant_id;
                            $plantCode = (is_string($plant) && strlen($plant) > 30) ? \App\Models\Plant::where('id', $plant)->value('code') : (string) $plant;
                            $plantCode = strtolower($plantCode ?: 'karawang');
                        @endphp
                        <span
                            class="badge badge-{{ $plantCode === 'jakarta' ? 'info' : 'primary' }} d-block d-md-inline-block ml-md-2 mt-2 mt-md-0"
                            style="font-size: 0.8rem; width: fit-content;">
                            <i class="fas fa-building mr-1"></i>
                            Plant {{ ucfirst($plantCode) }}
                        </span>
                    </h1>
                </div>
                <div class="col-md-4 d-flex justify-content-end">
                    <div class="col p-0" style="max-width: 250px;">
                        <div class="row mb-1">
                            <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">No. Dokumen</div>
                            <div class="col-7 text-xs font-weight-bold text-gray-800">: QC-KRW-F-0211</div>
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
            <h6 class="m-0 font-weight-bold text-primary">Data Masuk Incoming Material</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('incoming.materials.index') }}" method="GET" class="mb-4">
                <div class="row align-items-end">
                    <input type="hidden" name="plant" value="{{ request('plant') }}">

                    <div class="col-lg-3 mb-2">
                        <label class="small font-weight-bold">Pencarian</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari..."
                            value="{{ request('search') }}">
                    </div>

                    <div class="col-lg-2 mb-2">
                        <label class="small font-weight-bold">Dari Tanggal</label>
                        <input type="date" name="start_date" class="form-control form-control-sm"
                            value="{{ request('start_date') }}">
                    </div>

                    <div class="col-lg-2 mb-2">
                        <label class="small font-weight-bold">Sampai Tanggal</label>
                        <input type="date" name="end_date" class="form-control form-control-sm"
                            value="{{ request('end_date') }}">
                    </div>

                    <div class="col-lg-3 mb-2">
                        <button type="submit" class="btn btn-primary btn-sm mr-2">
                            <i class="fas fa-search"></i> Cari
                        </button>
                        <a href="{{ route('incoming.materials.index', ['plant' => request('plant')]) }}"
                            class="btn btn-secondary btn-sm mr-2">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                        <a href="{{ route('incoming.materials.export_pdf', request()->query()) }}"
                            class="btn btn-danger btn-sm no-loader btn-download">
                            <i class="fas fa-file-pdf"></i> Export
                        </a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered text-center small" width="100%" cellspacing="0">
                    <thead class="bg-light">
                        <tr class="align-middle">
                            <th rowspan="2">No</th>
                            <th rowspan="2">Tanggal Check</th>
                            <th rowspan="2">Material Name</th>
                            <th rowspan="2">Tgl Datang</th>
                            <th rowspan="2">Lot/Batch</th>
                            <th colspan="3">Qty (Kg)</th>
                            <th rowspan="2">Expired</th>
                            <th rowspan="2">Result</th>
                            <th colspan="2">Detail NG</th>
                            <th rowspan="2">QC</th>
                            <th colspan="4">Approval</th>
                            <th rowspan="2">Ket</th>
                            <th rowspan="2">Aksi</th>
                        </tr>
                        <tr>
                            <th>Total</th>
                            <th>Komp.</th>
                            <th>Samp.</th>
                            <th>Pcs</th>
                            <th>Jenis</th>
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
                                <td class="align-middle">{{ $cs->item->name }}</td>
                                <td class="align-middle">{{ date('d/m/Y', strtotime($cs->tanggal_datang)) }}</td>
                                <td class="align-middle">{{ $cs->lot_batch_number }}</td>
                                <td class="align-middle font-weight-bold">{{ $cs->quantity_kg }}</td>
                                <td class="align-middle">{{ $cs->komper_karung_kg }}</td>
                                <td class="align-middle">{{ $cs->sampling_size_karung_kg }}</td>
                                <td class="align-middle text-nowrap">{{ date('d/m/Y', strtotime($cs->expired_date)) }}</td>
                                <td class="align-middle">
                                    <span class="badge badge-{{ $cs->judgment == 'OK' ? 'success' : 'danger' }}">
                                        {{ $cs->judgment }}
                                    </span>
                                </td>

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

                                <td class="align-middle text-uppercase">{{ $cs->operator_initials }}</td>

                                {{-- Approval Columns --}}
                                @foreach(['kashift_qc', 'supervisor_qc', 'asst_manager_qc', 'manager_qc'] as $lvl)
                                    <td class="align-middle">
                                        @if($cs->$lvl == 'REJECTED')
                                            <span class="badge badge-danger">REJ</span>
                                        @elseif($cs->$lvl)
                                            <span class="badge badge-success">APP</span>
                                        @else
                                            <span class="badge badge-warning">PEN</span>
                                        @endif
                                    </td>
                                @endforeach

                                <td class="align-middle small">{{ $cs->remarks }}</td>
                                <td class="align-middle text-nowrap">
                                    <div class="btn-group">
                                        @if(!in_array(auth()->user()->role, ['inspector', 'oshef']))
                                            <a href="{{ route('incoming.materials.edit', $cs->id) }}"
                                                class="btn btn-warning btn-xs mx-1">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('incoming.materials.destroy', $cs->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-xs mx-1"
                                                    onclick="return confirm('Hapus data?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="20" class="text-center">Data tidak ditemukan.</td>
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
