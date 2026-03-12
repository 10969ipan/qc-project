@extends('layouts.admin')

@section('title', 'Data Kakotora')

@section('content')
    <div class="container-fluid ">
        <div class="card shadow mb-4 border-left-primary">
            <div class="card-body py-3">
                <div class="row align-items-start">
                    <div class="col-md-8 border-right">
                        <div class="d-flex align-items-center mb-3">
                            <h1 class="h4 mb-0 text-gray-800 font-weight-bold mr-3">
                                Database KAKOTORA
                            </h1>
                            <span class="badge badge-{{ strtolower($plant) === 'jakarta' ? 'info' : 'primary' }}"
                                style="font-size: 0.8rem;">
                                <i class="fas fa-building mr-1"></i>
                                Plant {{ ucfirst($plant) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex justify-content-end">
                        <div class="col p-0" style="max-width: 280px;">
                            <div class="row mb-1">
                                <div class="col-5 text-xs font-weight-bold text-gray-800">No. Dokumen</div>
                                <div class="col-7 text-xs font-weight-bold text-gray-800">:
                                    {{ strtoupper($plant) === 'JAKARTA' ? 'ENG-JKT-F-037' : 'ENG-KRW-F-037' }}
                                </div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-5 text-xs font-weight-bold text-gray-800">Tgl. Terbit</div>
                                <div class="col-7 text-xs font-weight-bold text-gray-800">: 17-06-2020</div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-5 text-xs font-weight-bold text-gray-800">Revisi Ke</div>
                                <div class="col-7 text-xs font-weight-bold text-gray-800">: 1</div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-5 text-xs font-weight-bold text-gray-800">Tgl. Revisi</div>
                                <div class="col-7 text-xs font-weight-bold text-gray-800">: 06-04-2023</div>
                            </div>
                            <div class="row">
                                <div class="col-5 text-xs font-weight-bold text-gray-800">Hlm</div>
                                <div class="col-7 text-xs font-weight-bold text-gray-800">: ... / ...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Data KAKOTORA</h6>
                <button type="button" class="btn btn-sm btn-primary shadow-sm" data-toggle="modal"
                    data-target="#modalTambahKakotora">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Data
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTableKakotora" cellspacing="0">
                        <thead>
                            <tr>
                                <th><i class="fas fa-info-circle" title="View Details"></i></th>
                                <th>No</th>
                                <th>Tanggal Entry</th>
                                <th>No Registrasi</th>
                                <th>Issue Date</th>
                                <th>No. Revmodel</th>
                                <th>Family</th>
                                <th>Category (NM / MP)</th>
                                <th>Claim Category</th>
                                <th>Model</th>
                                <th>Part Name</th>
                                <th>Part No.</th>
                                <th>Mould</th>
                                <th>Owner Mould</th>
                                <th class="col-similar-part">Similar Part</th>
                                <th>Section</th>
                                <th>Problem</th>
                                <th>Proses</th>
                                <th>Cause</th>
                                <th class="col-countermeasure">Countermeasure</th>
                                <th>PIC</th>
                                <th>Supplier</th>
                                <th>Defect Category</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($kakotoras as $item)
                                <tr>
                                    <td class="details-control"><i class="fas fa-plus-circle text-primary fa-lg"></i></td>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->date ? \Carbon\Carbon::parse($item->date)->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $item->no_reg ?? '-' }}</td>
                                    <td>{{ $item->issue_date ? \Carbon\Carbon::parse($item->issue_date)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td>{{ $item->rev_model ?? '-' }}</td>
                                    <td>{{ $item->family ?? '-' }}</td>
                                    <td>{{ $item->category_nm_mp ?? '-' }}</td>
                                    <td>{{ $item->category_claim ?? '-' }}</td>
                                    <td>{{ $item->model ?? '-' }}</td>
                                    <td>{{ $item->part_name ?? '-' }}</td>
                                    <td>{{ $item->part_number ?? '-' }}</td>
                                    <td>{{ $item->mould ?? '-' }}</td>
                                    <td>{{ $item->owner_mould ?? '-' }}</td>
                                    <td>{{ $item->similar_part ?? '-' }}</td>
                                    <td>{{ $item->section ?? '-' }}</td>
                                    <td>{{ $item->problem ?? '-' }}</td>
                                    <td>{{ $item->process ?? '-' }}</td>
                                    <td>{{ $item->cause ?? '-' }}</td>
                                    <td>{{ $item->countermeasure ?? '-' }}</td>
                                    <td>{{ $item->pic ?? '-' }}</td>
                                    <td>{{ $item->supplier ?? '-' }}</td>
                                    <td>{{ $item->defect_category ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $item->status == 'Closed' ? 'success' : 'warning' }}">
                                            {{ $item->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @if ($item->form_analysis_path)
                                                <a href="{{ $item->form_analysis_url }}" class="btn btn-info btn-sm" target="_blank"
                                                    title="Download Form Analysis">
                                                    <i class="fas fa-file-download"></i>
                                                </a>
                                            @endif
                                            <button type="button" class="btn btn-primary btn-sm btn-edit-kakotora"
                                                data-id="{{ $item->id }}" data-date="{{ $item->date }}"
                                                data-no_reg="{{ $item->no_reg }}" data-issue_date="{{ $item->issue_date }}"
                                                data-rev_model="{{ $item->rev_model }}" data-family="{{ $item->family }}"
                                                data-category_nm_mp="{{ $item->category_nm_mp }}"
                                                data-category_claim="{{ $item->category_claim }}"
                                                data-model="{{ $item->model }}" data-part_number="{{ $item->part_number }}"
                                                data-part_name="{{ $item->part_name }}" data-mould="{{ $item->mould }}"
                                                data-owner_mould="{{ $item->owner_mould }}"
                                                data-similar_part="{{ $item->similar_part }}"
                                                data-section="{{ $item->section }}" data-process="{{ $item->process }}"
                                                data-problem="{{ $item->problem }}" data-cause="{{ $item->cause }}"
                                                data-countermeasure="{{ $item->countermeasure }}" data-pic="{{ $item->pic }}"
                                                data-supplier="{{ $item->supplier }}"
                                                data-defect_category="{{ $item->defect_category }}"
                                                data-status="{{ $item->status }}" data-remarks="{{ $item->remarks }}"
                                                data-file_url="{{ $item->form_analysis_path ? $item->form_analysis_url : '' }}"
                                                title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('kakotora.destroy', $item->id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
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

    <!-- Modal Tambah Kakotora -->
    <div class="modal fade" id="modalTambahKakotora" tabindex="-1" role="dialog" aria-labelledby="modalTambahKakotoraLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTambahKakotoraLabel">Tambah Data Kakotora</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('kakotora.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="plant" value="{{ $plant }}">
                    <div class="modal-body text-left">
                        <div class="row">
                            <div class="col-md-6 border-right">
                                <div class="form-group">
                                    <label class="small font-weight-bold">Tanggal Entry</label>
                                    <input type="date" name="date" class="form-control form-control-sm"
                                        value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">No Registrasi</label>
                                    <input type="text" name="no_reg" class="form-control form-control-sm no-autoupper"
                                        placeholder="Input No Registrasi">
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Issue Date</label>
                                    <input type="date" name="issue_date" class="form-control form-control-sm">
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">No. Revmodel</label>
                                    <input type="text" name="rev_model" class="form-control form-control-sm no-autoupper"
                                        placeholder="Input No Revmodel">
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">FAMILY (M, C, S)</label>
                                    <select name="family" class="form-control form-control-sm">
                                        <option value="">- Pilih Family -</option>
                                        <option value="M">M</option>
                                        <option value="C">C</option>
                                        <option value="S">S</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">CATEGORY (NM , MP)</label>
                                    <select name="category_nm_mp" class="form-control form-control-sm">
                                        <option value="">- Pilih Kategori -</option>
                                        <option value="NM">NM</option>
                                        <option value="MP">MP</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">CATEGORY Claim (External, Internal)</label>
                                    <select name="category_claim" class="form-control form-control-sm">
                                        <option value="">- Pilih Kategori Claim -</option>
                                        <option value="External">External</option>
                                        <option value="Internal">Internal</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">MODEL</label>
                                    <input type="text" name="model" class="form-control form-control-sm no-autoupper"
                                        placeholder="Input Model">
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Part No.</label>
                                    <input type="text" name="part_number" class="form-control form-control-sm no-autoupper"
                                        placeholder="Input Part Number">
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Part Name</label>
                                    <input type="text" name="part_name" class="form-control form-control-sm no-autoupper"
                                        placeholder="Input Part Name">
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Mould</label>
                                    <input type="text" name="mould" class="form-control form-control-sm no-autoupper"
                                        placeholder="Input Mould">
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Owner of Mould</label>
                                    <input type="text" name="owner_mould" class="form-control form-control-sm no-autoupper"
                                        placeholder="Input Owner Mould">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small font-weight-bold">Similar Part</label>
                                    <textarea name="similar_part" class="form-control form-control-sm no-autoupper" rows="3"
                                        placeholder="Input Similar Part"></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Section</label>
                                    <input type="text" name="section" class="form-control form-control-sm no-autoupper"
                                        placeholder="Input Section">
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Proses</label>
                                    <input type="text" name="process" class="form-control form-control-sm no-autoupper"
                                        placeholder="Input Proses">
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">PROBLEM</label>
                                    <textarea name="problem" class="form-control form-control-sm no-autoupper" rows="2"
                                        placeholder="Input Problem"></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">CAUSE</label>
                                    <textarea name="cause" class="form-control form-control-sm no-autoupper" rows="2"
                                        placeholder="Input Cause"></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">COUNTERMEASURE</label>
                                    <textarea name="countermeasure" class="form-control form-control-sm no-autoupper" rows="5"
                                        placeholder="Input Countermeasure"></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">PIC</label>
                                    <input type="text" name="pic" class="form-control form-control-sm no-autoupper"
                                        placeholder="Input PIC">
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Supplier</label>
                                    <input type="text" name="supplier" class="form-control form-control-sm no-autoupper"
                                        placeholder="Input Supplier">
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Kategori Defect</label>
                                    <input type="text" name="defect_category" class="form-control form-control-sm no-autoupper"
                                        placeholder="Input Kategori Defect">
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Status</label>
                                    <select name="status" class="form-control form-control-sm">
                                        <option value="Open">Open</option>
                                        <option value="Closed">Closed</option>
                                        <option value="On Progress">On Progress</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Form Analysis (PICA / AR / SA / dll)</label>
                                    <input type="file" name="form_analysis" class="form-control-file">
                                    <small class="text-muted">Allowed: pptx, xlsx, doc, docx, pdf (Max 10MB)</small>
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Remarks</label>
                                    <textarea name="remarks" class="form-control form-control-sm no-autoupper" rows="2"
                                        placeholder="Input Remarks"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Kakotora -->
    <div class="modal fade" id="modalEditKakotora" tabindex="-1" role="dialog" aria-labelledby="modalEditKakotoraLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="modalEditKakotoraLabel">Edit Data Kakotora</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formEditKakotora" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="plant" value="{{ $plant }}">
                    <div class="modal-body text-left">
                        <div class="row">
                            <div class="col-md-6 border-right">
                                <div class="form-group">
                                    <label class="small font-weight-bold">Tanggal Entry</label>
                                    <input type="date" name="date" id="edit_date" class="form-control form-control-sm">
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">No Registrasi</label>
                                    <input type="text" name="no_reg" id="edit_no_reg" class="form-control form-control-sm no-autoupper">
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Issue Date</label>
                                    <input type="date" name="issue_date" id="edit_issue_date"
                                        class="form-control form-control-sm">
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">No. Revmodel</label>
                                    <input type="text" name="rev_model" id="edit_rev_model"
                                        class="form-control form-control-sm no-autoupper">
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">FAMILY (M, C, S)</label>
                                    <select name="family" id="edit_family" class="form-control form-control-sm">
                                        <option value="">- Pilih Family -</option>
                                        <option value="M">M</option>
                                        <option value="C">C</option>
                                        <option value="S">S</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">CATEGORY (NM , MP)</label>
                                    <select name="category_nm_mp" id="edit_category_nm_mp"
                                        class="form-control form-control-sm">
                                        <option value="">- Pilih Kategori -</option>
                                        <option value="NM">NM</option>
                                        <option value="MP">MP</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">CATEGORY Claim (External, Internal)</label>
                                    <select name="category_claim" id="edit_category_claim"
                                        class="form-control form-control-sm">
                                        <option value="">- Pilih Kategori Claim -</option>
                                        <option value="External">External</option>
                                        <option value="Internal">Internal</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">MODEL</label>
                                    <input type="text" name="model" id="edit_model" class="form-control form-control-sm no-autoupper">
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Part No.</label>
                                    <input type="text" name="part_number" id="edit_part_number"
                                        class="form-control form-control-sm no-autoupper">
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Part Name</label>
                                    <input type="text" name="part_name" id="edit_part_name"
                                        class="form-control form-control-sm no-autoupper">
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Mould</label>
                                    <input type="text" name="mould" id="edit_mould" class="form-control form-control-sm no-autoupper">
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Owner of Mould</label>
                                    <input type="text" name="owner_mould" id="edit_owner_mould"
                                        class="form-control form-control-sm no-autoupper">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small font-weight-bold">Similar Part</label>
                                    <textarea name="similar_part" id="edit_similar_part"
                                        class="form-control form-control-sm no-autoupper" rows="3"></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Section</label>
                                    <input type="text" name="section" id="edit_section"
                                        class="form-control form-control-sm no-autoupper">
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Proses</label>
                                    <input type="text" name="process" id="edit_process"
                                        class="form-control form-control-sm no-autoupper">
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">PROBLEM</label>
                                    <textarea name="problem" id="edit_problem" class="form-control form-control-sm no-autoupper"
                                        rows="2"></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">CAUSE</label>
                                    <textarea name="cause" id="edit_cause" class="form-control form-control-sm no-autoupper"
                                        rows="2"></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">COUNTERMEASURE</label>
                                    <textarea name="countermeasure" id="edit_countermeasure"
                                        class="form-control form-control-sm no-autoupper" rows="5"></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">PIC</label>
                                    <input type="text" name="pic" id="edit_pic" class="form-control form-control-sm no-autoupper">
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Supplier</label>
                                    <input type="text" name="supplier" id="edit_supplier"
                                        class="form-control form-control-sm no-autoupper">
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Kategori Defect</label>
                                    <input type="text" name="defect_category" id="edit_defect_category"
                                        class="form-control form-control-sm no-autoupper">
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Status</label>
                                    <select name="status" id="edit_status" class="form-control form-control-sm">
                                        <option value="Open">Open</option>
                                        <option value="Closed">Closed</option>
                                        <option value="On Progress">On Progress</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Form Analysis (PICA / AR / SA / dll)</label>
                                    <div id="edit_file_preview" class="mb-2"></div>
                                    <input type="file" name="form_analysis" class="form-control-file">
                                    <small class="text-muted">Upload baru untuk ganti file. Allowed: pptx, xlsx, doc, docx,
                                        pdf (Max 10MB)</small>
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Remarks</label>
                                    <textarea name="remarks" id="edit_remarks" class="form-control form-control-sm no-autoupper"
                                        rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info btn-sm px-4 shadow-sm">Update Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link href="{{ asset('vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    <style>
        #dataTableKakotora {
            table-layout: fixed !important;
            width: auto !important;
            min-width: 100% !important;
            border-collapse: separate !important;
            border-spacing: 0 !important;
            margin-bottom: 0 !important;
        }

        /* Fix for DataTables scrollX header gap */
        .dataTables_scrollHead {
            background-color: #4e73df !important;
            border-radius: 4px 4px 0 0 !important;
        }

        #dataTableKakotora th {
            font-size: 0.75rem;
            vertical-align: middle;
            text-align: center;
            background-color: #4e73df !important;
            color: white !important;
            white-space: nowrap;
            padding: 8px 10px !important;
            border: 1px solid #ffffff33 !important;
        }

        #dataTableKakotora td {
            font-size: 0.8rem;
            vertical-align: middle;
            border: 1px solid #e3e6f0 !important;
        }

        td.details-control {
            cursor: pointer;
            text-align: center;
            vertical-align: middle !important;
        }

        .col-expandable {
            width: 400px !important;
        }

        .col-similar-part,
        .col-countermeasure {
            width: 1000px !important;
        }

        .text-wrap-logic {
            white-space: pre-wrap !important;
            word-wrap: break-word;
            word-break: break-word;
        }

        .modal-body label {
            margin-bottom: 2px;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            var formatChildRow = function (d) {
                return '<div class="p-3" style="background-color: #f8f9fc; border-left: 4px solid #4e73df;">' +
                    '<table class="table table-sm table-borderless mb-0">' +
                    '<tr>' +
                    '<td style="width: 15%; font-weight: bold; padding: 0.5rem;">Similar Part</td>' +
                    '<td style="white-space: pre-wrap; padding: 0.5rem; border-left: 1px solid #e3e6f0;">' + (d[14] || '-') + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td style="font-weight: bold; padding: 0.5rem;">Problem</td>' +
                    '<td style="white-space: pre-wrap; padding: 0.5rem; border-left: 1px solid #e3e6f0;">' + (d[16] || '-') + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td style="font-weight: bold; padding: 0.5rem;">Cause</td>' +
                    '<td style="white-space: pre-wrap; padding: 0.5rem; border-left: 1px solid #e3e6f0;">' + (d[18] || '-') + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td style="font-weight: bold; padding: 0.5rem;">Countermeasure</td>' +
                    '<td style="white-space: pre-wrap; padding: 0.5rem; border-left: 1px solid #e3e6f0;">' + (d[19] || '-') + '</td>' +
                    '</tr>' +
                    '</table>' +
                    '</div>';
            };

            var table = $('#dataTableKakotora').DataTable({
                "order": [[2, "desc"]],
                "autoWidth": false,
                "scrollX": true,
                "columnDefs": [
                    { "orderable": false, "targets": 0 },
                    { "visible": false, "targets": [14, 16, 18, 19] }
                ]
            });

            // Add event listener for opening and closing details
            $('#dataTableKakotora tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = table.row(tr);
                var icon = $(this).find('i');

                if (row.child.isShown()) {
                    // This row is already open - close it
                    row.child.hide();
                    tr.removeClass('shown');
                    icon.removeClass('fa-minus-circle').addClass('fa-plus-circle');
                }
                else {
                    // Open this row
                    row.child(formatChildRow(row.data())).show();
                    tr.addClass('shown');
                    icon.removeClass('fa-plus-circle').addClass('fa-minus-circle');
                }
            });

            $('#dataTableKakotora tbody').on('click', '.btn-edit-kakotora', function () {
                var id = $(this).data('id');
                var date = $(this).data('date');
                var no_reg = $(this).data('no_reg');
                var issue_date = $(this).data('issue_date');
                var rev_model = $(this).data('rev_model');
                var family = $(this).data('family');
                var category_nm_mp = $(this).data('category_nm_mp');
                var category_claim = $(this).data('category_claim');
                var model = $(this).data('model');
                var part_number = $(this).data('part_number');
                var part_name = $(this).data('part_name');
                var mould = $(this).data('mould');
                var owner_mould = $(this).data('owner_mould');
                var similar_part = $(this).data('similar_part');
                var section = $(this).data('section');
                var process = $(this).data('process');
                var problem = $(this).data('problem');
                var cause = $(this).data('cause');
                var countermeasure = $(this).data('countermeasure');
                var pic = $(this).data('pic');
                var supplier = $(this).data('supplier');
                var defect_category = $(this).data('defect_category');
                var status = $(this).data('status');
                var remarks = $(this).data('remarks');
                var file_url = $(this).data('file_url');

                // Set values to Edit Modal
                $('#edit_date').val(date);
                $('#edit_no_reg').val(no_reg);
                $('#edit_issue_date').val(issue_date);
                $('#edit_rev_model').val(rev_model);
                $('#edit_family').val(family);
                $('#edit_category_nm_mp').val(category_nm_mp);
                $('#edit_category_claim').val(category_claim);
                $('#edit_model').val(model);
                $('#edit_part_number').val(part_number);
                $('#edit_part_name').val(part_name);
                $('#edit_mould').val(mould);
                $('#edit_owner_mould').val(owner_mould);
                $('#edit_similar_part').val(similar_part);
                $('#edit_section').val(section);
                $('#edit_process').val(process);
                $('#edit_problem').val(problem);
                $('#edit_cause').val(cause);
                $('#edit_countermeasure').val(countermeasure);
                $('#edit_pic').val(pic);
                $('#edit_supplier').val(supplier);
                $('#edit_defect_category').val(defect_category);
                $('#edit_status').val(status);
                $('#edit_remarks').val(remarks);

                if (file_url) {
                    $('#edit_file_preview').html('<a href="' + file_url + '" target="_blank" class="btn btn-xs btn-info"><i class="fas fa-file-download"></i> Lihat File Sekarang</a>');
                } else {
                    $('#edit_file_preview').html('<span class="text-muted small">Tidak ada file</span>');
                }

                // Set Action URL
                $('#formEditKakotora').attr('action', '{{ url("kakotora") }}/' + id);

                // Show Modal
                $('#modalEditKakotora').modal('show');
            });
        });
    </script>
@endpush