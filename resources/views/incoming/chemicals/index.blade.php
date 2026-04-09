@extends('layouts.admin')

@section('title', 'Incoming Chemical')

@section('content')
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-body py-3">
            <div class="row align-items-start">
                <div class="col-md-8 border-right">
                    <h1 class="h4 mb-0 text-gray-800 font-weight-bold text-uppercase">
                        LAPORAN DATA INCOMING CHEMICAL
                        @php
                            $plant = request('plant') ?? auth()->user()->plant_id;
                            $plantCode = (is_string($plant) && strlen($plant) > 30) ? \App\Models\Plant::where('id', $plant)->value('code') : (string) $plant;
                            $plantCode = strtolower($plantCode ?: 'karawang');

                            // Resolve menu ID for permission checks
                            $currentMenu = \App\Models\AppMenu::where('route', 'incoming.chemicals.index')->first();
                            $menuId = $currentMenu ? $currentMenu->id : null;
                            $canExport = $menuId ? auth()->user()->hasPermission($menuId, 'export') : true;
                        @endphp
                        <span
                            class="badge badge-{{ $plantCode === 'jakarta' ? 'info' : 'primary' }} d-block d-md-inline-block ml-md-2 mt-2 mt-md-0"
                            style="font-size: 0.8rem; width: fit-content;">
                            <i class="fas fa-building mr-1"></i>
                            Plant {{ ucfirst($plantCode) }}
                        </span>
                    </h1>
                </div>
                <div class="col-md-4 d-flex justify-content-end text-xs font-weight-bold">
                    <div style="max-width: 250px;">
                        <div class="row mb-1">
                            <div class="col-5">No. Dokumen</div>
                            <div class="col-7">: QC-KRW-F-0214</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-5">Tgl. Terbit</div>
                            <div class="col-7">: 01/01/2026</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 pt-4">
            <h6 class="m-0 font-weight-bold text-primary">Data Masuk Incoming Chemical</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('incoming.chemicals.index') }}" method="GET" class="mb-4">
                <input type="hidden" name="plant" value="{{ request('plant') }}">
                <div class="row align-items-end small font-weight-bold">
                    <div class="col-lg-3 mb-2"><label>Pencarian</label><input type="text" name="search"
                            class="form-control form-control-sm" placeholder="Cari..." value="{{ request('search') }}">
                    </div>
                    <div class="col-lg-2 mb-2"><label>Dari Tanggal</label><input type="date" name="start_date"
                            class="form-control form-control-sm" value="{{ request('start_date') }}"></div>
                    <div class="col-lg-2 mb-2"><label>Sampai Tanggal</label><input type="date" name="end_date"
                            class="form-control form-control-sm" value="{{ request('end_date') }}"></div>
                    <div class="col-lg-3 mb-2">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Cari</button>
                        <a href="{{ route('incoming.chemicals.index', ['plant' => request('plant')]) }}"
                            class="btn btn-secondary btn-sm"><i class="fas fa-undo"></i> Reset</a>
                        @if($canExport)
                        <a href="{{ route('incoming.chemicals.export_pdf', request()->query()) }}"
                            class="btn btn-danger btn-sm no-loader btn-download"><i class="fas fa-file-pdf"></i> PDF</a>
                        @endif
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered text-center small" width="100%" cellspacing="0">
                    <thead class="bg-light align-middle text-uppercase">
                        <tr>
                            <th rowspan="2">No</th>
                            <th rowspan="2">Tgl Check</th>
                            <th rowspan="2">Chemical Name</th>
                            <th rowspan="2">Tgl Datang</th>
                            <th rowspan="2">Lot Number</th>
                            <th colspan="3">Qty (Kg)</th>
                            <th rowspan="2">Expired</th>
                            <th rowspan="2">Jdg</th>
                            <th colspan="2">Detail NG</th>
                            <th rowspan="2">QC</th>
                            <th colspan="4">Approval</th>
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
                                <td class="align-middle text-nowrap">{{ date('d/m/Y', strtotime($cs->date)) }}</td>
                                <td class="align-middle text-left font-weight-bold">{{ $cs->item->name }}</td>
                                <td class="align-middle text-nowrap">{{ date('d/m/Y', strtotime($cs->tanggal_datang)) }}</td>
                                <td class="align-middle">{{ $cs->lot_batch_number }}</td>
                                <td class="align-middle font-weight-bold">{{ $cs->quantity_kg }}</td>
                                <td class="align-middle">{{ $cs->komper_jirigen_kg }}</td>
                                <td class="align-middle">{{ $cs->sampling_size_jirigen_kg }}</td>
                                <td class="align-middle text-nowrap">{{ date('d/m/Y', strtotime($cs->expired_date)) }}</td>
                                <td class="align-middle">
                                    <span
                                        class="badge badge-{{ $cs->judgment == 'OK' ? 'success' : 'danger' }}">{{ $cs->judgment }}</span>
                                </td>
                                @php $defects = is_array($cs->defects) ? $cs->defects : json_decode($cs->defects, true); @endphp
                                <td class="p-0 align-middle">
                                    @foreach($defects ?? [] as $d) <div class="border-bottom py-1">{{ $d['qty'] ?? 0 }}</div>
                                    @endforeach
                                </td>
                                <td class="p-0 align-middle">
                                    @foreach($defects ?? [] as $d) <div class="border-bottom py-1 text-nowrap px-1">
                                        {{ $d['type'] ?? '-' }}
                                    </div> @endforeach
                                </td>
                                <td class="align-middle text-uppercase">{{ $cs->operator_initials }}</td>
                                @foreach(['kashift_qc', 'supervisor_qc', 'asst_manager_qc', 'manager_qc'] as $lvl)
                                    <td class="align-middle">
                                        <span
                                            class="badge badge-{{ $cs->$lvl == 'REJECTED' ? 'danger' : ($cs->$lvl ? 'success' : 'warning') }}">
                                            {{ $cs->$lvl == 'REJECTED' ? 'REJ' : ($cs->$lvl ? 'APP' : 'PEN') }}
                                        </span>
                                    </td>
                                @endforeach
                                <td class="align-middle">
                                    <div class="btn-group">
                                        @if(!in_array(auth()->user()->role, ['inspector', 'oshef']))
                                            <a href="{{ route('incoming.chemicals.edit', $cs->id) }}"
                                                class="btn btn-warning btn-xs px-2"><i class="fas fa-edit"></i></a>
                                            <form action="{{ route('incoming.chemicals.destroy', $cs->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-xs px-2"
                                                    onclick="return confirm('Hapus?')"><i class="fas fa-trash"></i></button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="18" class="py-4 text-muted">Data tidak ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $checksheets->withQueryString()->links() }}</div>
        </div>
    </div>
@endsection
