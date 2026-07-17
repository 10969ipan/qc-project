@extends('layouts.admin')

@section('title', 'Incoming Sub-Part')

@section('content')
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-body py-3">
            <div class="row align-items-start">
                <div class="col-md-8 border-right">
                    <h1 class="h4 mb-0 text-gray-800 font-weight-bold text-uppercase">
                        LAPORAN DATA INCOMING SUB-PART
                        @php
                            $plant = request('plant') ?? auth()->user()->plant_id;
                            $plantCode = (is_string($plant) && strlen($plant) > 30) ? \App\Models\Plant::where('id', $plant)->value('code') : (string) $plant;
                            $plantCode = strtolower($plantCode ?: 'karawang');

                            // Resolve menu ID for permission checks
                            $currentMenu = \App\Models\AppMenu::where('route', 'incoming.sub_parts.index')->first();
                            $menuId = $currentMenu ? $currentMenu->id : null;
                            $canExport = $menuId ? auth()->user()->hasPermission($menuId, 'export') : true;
                            $canEdit = $menuId ? auth()->user()->hasPermission($menuId, 'edit') : true;
                            $canDelete = $menuId ? auth()->user()->hasPermission($menuId, 'delete') : true;
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
                            <div class="col-5 text-uppercase">No. Dokumen</div>
                            <div class="col-7">: QC-KRW-F-0212</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-5 text-uppercase">Tgl. Terbit</div>
                            <div class="col-7">: 01/01/2026</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 pt-4 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Data Masuk Incoming Sub-Part</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('incoming.sub_parts.index') }}" method="GET" class="mb-4">
                <div class="row align-items-end">
                    <input type="hidden" name="plant" value="{{ request('plant') }}">
                    <div class="col-lg-3 mb-2 small font-weight-bold">
                        <label>Pencarian</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-lg-2 mb-2 small font-weight-bold">
                        <label>Dari Tanggal</label>
                        <input type="date" name="start_date" class="form-control form-control-sm"
                            value="{{ request('start_date') }}">
                    </div>
                    <div class="col-lg-2 mb-2 small font-weight-bold">
                        <label>Sampai Tanggal</label>
                        <input type="date" name="end_date" class="form-control form-control-sm"
                            value="{{ request('end_date') }}">
                    </div>
                    <div class="col-lg-3 mb-2">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Cari</button>
                        <a href="{{ route('incoming.sub_parts.index', ['plant' => request('plant')]) }}"
                            class="btn btn-secondary btn-sm"><i class="fas fa-undo"></i> Reset</a>
                        @if($canExport)
                        <a href="{{ route('incoming.sub_parts.export_pdf', request()->query()) }}"
                            class="btn btn-danger btn-sm no-loader btn-download"><i class="fas fa-file-pdf"></i> PDF</a>
                        @endif
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered text-center small" width="100%" cellspacing="0">
                    <thead class="bg-light">
                        <tr class="align-middle">
                            <th rowspan="2">No</th>
                            <th rowspan="2">Tanggal Check</th>
                            <th rowspan="2">Sub-Part Name</th>
                            <th rowspan="2">Tgl Datang</th>
                            <th rowspan="2">Lot Number</th>
                            <th rowspan="2">Qty (Pcs)</th>
                            <th rowspan="2">Samp.</th>
                            <th rowspan="2">Dimensi</th>
                            <th rowspan="2">Expired</th>
                            <th rowspan="2">Jdg</th>
                            <th colspan="2">Detail NG</th>
                            <th rowspan="2">QC</th>
                            <th colspan="4">Approval</th>
                            <th rowspan="2">Action</th>
                        </tr>
                        <tr>
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
                                <td class="align-middle text-left">{{ $cs->item->name }}</td>
                                <td class="align-middle">{{ date('d/m/Y', strtotime($cs->tanggal_datang)) }}</td>
                                <td class="align-middle">{{ $cs->lot_batch_number }}</td>
                                <td class="align-middle font-weight-bold">{{ $cs->quantity }}</td>
                                <td class="align-middle">{{ $cs->sampling_size_pcs }}</td>
                                <td class="align-middle">
                                    <span
                                        class="badge badge-{{ $cs->check_dimensi == 'OK' ? 'success' : 'danger' }}">{{ $cs->check_dimensi }}</span>
                                </td>
                                <td class="align-middle">{{ date('d/m/Y', strtotime($cs->expired_date)) }}</td>
                                <td class="align-middle">
                                    <span
                                        class="badge badge-{{ $cs->judgment == 'OK' ? 'success' : 'danger' }} font-weight-bold">{{ $cs->judgment }}</span>
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
                                    @if($loop->first)
                                        @include('partials.bulk_approve_button')
                                    @endif
                                    <div class="btn-group">
                                        @if(!in_array(auth()->user()->role, ['inspector']))
                                            @if($canEdit)
                                                <a href="{{ route('incoming.sub_parts.edit', $cs->id) }}"
                                                    class="btn btn-warning btn-xs px-2"><i class="fas fa-edit"></i></a>
                                            @endif
                                            @if($canDelete)
                                                <form action="{{ route('incoming.sub_parts.destroy', $cs->id) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-xs px-2"
                                                        onclick="return confirm('Hapus?')"><i class="fas fa-trash"></i></button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="18" class="text-center py-4 text-muted">Data tidak ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $checksheets->withQueryString()->links() }}</div>
        </div>
    </div>
    @php $bulkApproveRoute = route('incoming.sub_parts.bulk_approve'); @endphp
    @include('partials.bulk_approve_script')
@endsection
