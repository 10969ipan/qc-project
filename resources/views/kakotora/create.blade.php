@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Tambah Data KAKOTORA {{ $plant ? '(' . strtoupper($plant) . ')' : '' }}</h1>
            <a href="{{ route('kakotora.index', ['plant' => $plant]) }}" class="btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
            </a>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Form Input KAKOTORA</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('kakotora.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="plant" value="{{ $plant }}">

                    <div class="row">
                        <!-- Column 1 -->
                        <div class="col-md-6 border-right">
                            <div class="form-group">
                                <label>TANGGAL ENTRY</label>
                                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="form-group">
                                <label>NO REGISTRASI</label>
                                <input type="text" name="no_reg" class="form-control" placeholder="Input No Registrasi">
                            </div>
                            <div class="form-group">
                                <label>ISSUE DATE</label>
                                <input type="date" name="issue_date" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>NO. REVMODEL</label>
                                <input type="text" name="rev_model" class="form-control" placeholder="Input No Revmodel">
                            </div>
                            <div class="form-group">
                                <label>FAMILY (M, C, S)</label>
                                <select name="family" class="form-control">
                                    <option value="">- Pilih Family -</option>
                                    <option value="M">M</option>
                                    <option value="C">C</option>
                                    <option value="S">S</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>CATEGORY (NM , MP)</label>
                                <select name="category_nm_mp" class="form-control">
                                    <option value="">- Pilih Kategori -</option>
                                    <option value="NM">NM</option>
                                    <option value="MP">MP</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>CATEGORY Claim (External, Internal)</label>
                                <select name="category_claim" class="form-control">
                                    <option value="">- Pilih Kategori Claim -</option>
                                    <option value="External">External</option>
                                    <option value="Internal">Internal</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>MODEL</label>
                                <input type="text" name="model" class="form-control" placeholder="Input Model">
                            </div>
                            <div class="form-group">
                                <label>PART NO.</label>
                                <input type="text" name="part_number" class="form-control" placeholder="Input Part Number">
                            </div>
                            <div class="form-group">
                                <label>PART NAME</label>
                                <input type="text" name="part_name" class="form-control" placeholder="Input Part Name">
                            </div>
                            <div class="form-group">
                                <label>MOULD</label>
                                <input type="text" name="mould" class="form-control" placeholder="Input Mould">
                            </div>
                            <div class="form-group">
                                <label>OWNER OF MOULD</label>
                                <input type="text" name="owner_mould" class="form-control" placeholder="Input Owner Mould">
                            </div>
                        </div>

                        <!-- Column 2 -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>SIMILAR PART</label>
                                <input type="text" name="similar_part" class="form-control"
                                    placeholder="Input Similar Part">
                            </div>
                            <div class="form-group">
                                <label>SECTION</label>
                                <input type="text" name="section" class="form-control" placeholder="Input Section">
                            </div>
                            <div class="form-group">
                                <label>PROSES</label>
                                <input type="text" name="process" class="form-control" placeholder="Input Proses">
                            </div>
                            <div class="form-group">
                                <label>PROBLEM</label>
                                <textarea name="problem" class="form-control" rows="3"
                                    placeholder="Input Problem"></textarea>
                            </div>
                            <div class="form-group">
                                <label>CAUSE</label>
                                <textarea name="cause" class="form-control" rows="3" placeholder="Input Cause"></textarea>
                            </div>
                            <div class="form-group">
                                <label>COUNTERMEASURE</label>
                                <textarea name="countermeasure" class="form-control" rows="3"
                                    placeholder="Input Countermeasure"></textarea>
                            </div>
                            <div class="form-group">
                                <label>PIC</label>
                                <input type="text" name="pic" class="form-control" placeholder="Input PIC">
                            </div>
                            <div class="form-group">
                                <label>Supplier</label>
                                <input type="text" name="supplier" class="form-control" placeholder="Input Supplier">
                            </div>
                            <div class="form-group">
                                <label>KATEGORI DEFECT</label>
                                <input type="text" name="defect_category" class="form-control"
                                    placeholder="Input Kategori Defect">
                            </div>
                            <div class="form-group">
                                <label>STATUS</label>
                                <select name="status" class="form-control">
                                    <option value="Open">Open</option>
                                    <option value="Closed">Closed</option>
                                    <option value="On Progress">On Progress</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>FORM ANALYSIS (PICA / AR / SA / dll)</label>
                                <input type="file" name="form_analysis" class="form-control-file">
                                <small class="text-muted">Allowed: pptx, xlsx, doc, docx, pdf (Max 10MB)</small>
                            </div>
                            <div class="form-group">
                                <label>REMARKS</label>
                                <textarea name="remarks" class="form-control" rows="2"
                                    placeholder="Input Remarks"></textarea>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary px-4">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection