@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Edit Data KAKOTORA ({{ ucfirst(strtolower($plant)) }})</h1>
            <a href="{{ route('kakotora.index', ['plant' => $plant]) }}" class="btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
            </a>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Form Edit KAKOTORA</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('kakotora.update', $kakotora->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <!-- Column 1 -->
                        <div class="col-md-6 border-right">
                            <div class="form-group">
                                <label>Tanggal Entry</label>
                                <input type="date" name="date" class="form-control" value="{{ $kakotora->date }}">
                            </div>
                            <div class="form-group">
                                <label>No Registrasi</label>
                                <input type="text" name="no_reg" class="form-control" value="{{ $kakotora->no_reg }}">
                            </div>
                            <div class="form-group">
                                <label>Issue Date</label>
                                <input type="date" name="issue_date" class="form-control"
                                    value="{{ $kakotora->issue_date }}">
                            </div>
                            <div class="form-group">
                                <label>No. Revmodel</label>
                                <input type="text" name="rev_model" class="form-control" value="{{ $kakotora->rev_model }}">
                            </div>
                            <div class="form-group">
                                <label>Family (M, C, S)</label>
                                <select name="family" class="form-control">
                                    <option value="">- Pilih Family -</option>
                                    <option value="M" {{ $kakotora->family == 'M' ? 'selected' : '' }}>M</option>
                                    <option value="C" {{ $kakotora->family == 'C' ? 'selected' : '' }}>C</option>
                                    <option value="S" {{ $kakotora->family == 'S' ? 'selected' : '' }}>S</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Category (NM, MP)</label>
                                <select name="category_nm_mp" class="form-control">
                                    <option value="">- Pilih Kategori -</option>
                                    <option value="NM" {{ $kakotora->category_nm_mp == 'NM' ? 'selected' : '' }}>NM</option>
                                    <option value="MP" {{ $kakotora->category_nm_mp == 'MP' ? 'selected' : '' }}>MP</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Category Claim (External, Internal)</label>
                                <select name="category_claim" class="form-control">
                                    <option value="">- Pilih Kategori Claim -</option>
                                    <option value="External" {{ $kakotora->category_claim == 'External' ? 'selected' : '' }}>
                                        External</option>
                                    <option value="Internal" {{ $kakotora->category_claim == 'Internal' ? 'selected' : '' }}>
                                        Internal</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>MODEL</label>
                                <input type="text" name="model" class="form-control" value="{{ $kakotora->model }}">
                            </div>
                            <div class="form-group">
                                <label>Part No.</label>
                                <input type="text" name="part_number" class="form-control"
                                    value="{{ $kakotora->part_number }}">
                            </div>
                            <div class="form-group">
                                <label>Part Name</label>
                                <input type="text" name="part_name" class="form-control" value="{{ $kakotora->part_name }}">
                            </div>
                            <div class="form-group">
                                <label>Mould</label>
                                <input type="text" name="mould" class="form-control" value="{{ $kakotora->mould }}">
                            </div>
                            <div class="form-group">
                                <label>Owner of Mould</label>
                                <input type="text" name="owner_mould" class="form-control"
                                    value="{{ $kakotora->owner_mould }}">
                            </div>
                        </div>

                        <!-- Column 2 -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Similar Part</label>
                                <textarea name="similar_part" class="form-control"
                                    rows="3">{{ $kakotora->similar_part }}</textarea>
                            </div>
                            <div class="form-group">
                                <label>Section</label>
                                <input type="text" name="section" class="form-control" value="{{ $kakotora->section }}">
                            </div>
                            <div class="form-group">
                                <label>Proses</label>
                                <input type="text" name="process" class="form-control" value="{{ $kakotora->process }}">
                            </div>
                            <div class="form-group">
                                <label>Problem</label>
                                <textarea name="problem" class="form-control" rows="3">{{ $kakotora->problem }}</textarea>
                            </div>
                            <div class="form-group">
                                <label>Cause</label>
                                <textarea name="cause" class="form-control" rows="3">{{ $kakotora->cause }}</textarea>
                            </div>
                            <div class="form-group">
                                <label>Countermeasure</label>
                                <textarea name="countermeasure" class="form-control"
                                    rows="5">{{ $kakotora->countermeasure }}</textarea>
                            </div>
                            <div class="form-group">
                                <label>PIC</label>
                                <input type="text" name="pic" class="form-control" value="{{ $kakotora->pic }}">
                            </div>
                            <div class="form-group">
                                <label>Supplier</label>
                                <input type="text" name="supplier" class="form-control" value="{{ $kakotora->supplier }}">
                            </div>
                            <div class="form-group">
                                <label>Kategori Defect</label>
                                <input type="text" name="defect_category" class="form-control"
                                    value="{{ $kakotora->defect_category }}">
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="Open" {{ $kakotora->status == 'Open' ? 'selected' : '' }}>Open</option>
                                    <option value="Closed" {{ $kakotora->status == 'Closed' ? 'selected' : '' }}>Closed
                                    </option>
                                    <option value="On Progress" {{ $kakotora->status == 'On Progress' ? 'selected' : '' }}>On
                                        Progress</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>FORM ANALYSIS (PICA / AR / SA / dll)</label>
                                @if($kakotora->form_analysis_path)
                                    <div class="mb-2">
                                        <a href="{{ $kakotora->form_analysis_url }}" target="_blank"
                                            class="btn btn-info btn-sm">
                                            <i class="fas fa-file-download"></i> View Current File
                                        </a>
                                    </div>
                                @endif
                                <input type="file" name="form_analysis" class="form-control-file">
                                <small class="text-muted">Upload new file to replace existing. Allowed: pptx, xlsx, doc,
                                    docx, pdf (Max 10MB)</small>
                            </div>
                            <div class="form-group">
                                <label>Remarks</label>
                                <textarea name="remarks" class="form-control" rows="2">{{ $kakotora->remarks }}</textarea>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary px-4">Update Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
