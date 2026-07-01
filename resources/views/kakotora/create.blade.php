@extends('layouts.admin')

@section('content')
<style>
    .minimalist-card {
        background: #ffffff;
        border: none;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    .minimalist-body {
        padding: 2rem 3rem;
    }
    .form-group.row {
        margin-bottom: 1.25rem;
        align-items: center;
    }
    .custom-label {
        color: #475569;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .custom-input {
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 0.85rem;
        padding: 0.5rem 0.75rem;
        color: #334155;
        background-color: #f8fafc;
        transition: all 0.2s;
    }
    .custom-input:focus {
        background-color: #ffffff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        outline: none;
    }
    .section-title {
        color: #0f172a;
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #f1f5f9;
    }
    .btn-minimalist {
        border-radius: 6px;
        font-weight: 600;
        padding: 0.6rem 1.5rem;
        letter-spacing: 0.3px;
    }
</style>

<div class="container-fluid pb-5">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Tambah Data KAKOTORA {{ $plant ? '(' . ucfirst(strtolower($plant)) . ')' : '' }}</h1>
        <a href="{{ route('kakotora.index', ['plant' => $plant]) }}" class="btn btn-sm btn-light border shadow-sm btn-minimalist text-secondary">
            <i class="fas fa-arrow-left fa-sm mr-1"></i> Kembali
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12">
            <div class="card minimalist-card">
                <div class="minimalist-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" style="border-radius: 8px;" role="alert">
                            <strong><i class="fas fa-exclamation-triangle mr-1"></i> Terjadi Kesalahan!</strong> Mohon periksa kembali input Anda:
                            <ul class="mb-0 mt-2 pl-4">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <form id="formCreateKakotora" action="{{ route('kakotora.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="plant" value="{{ $plant }}">

                        <!-- SECTION 1: INFORMASI UMUM -->
                        <div class="section-title mt-2"><i class="fas fa-info-circle text-primary mr-2"></i>Informasi Umum</div>
                        
                        <div class="form-group row">
                            <label class="col-sm-3 text-sm-right custom-label">Tanggal Entry</label>
                            <div class="col-sm-8">
                                <input type="date" name="date" class="form-control custom-input" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 text-sm-right custom-label">No Registrasi</label>
                            <div class="col-sm-8">
                                <input type="text" name="no_reg" class="form-control custom-input" placeholder="Misal: REG-2026-001">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 text-sm-right custom-label">Issue Date</label>
                            <div class="col-sm-8">
                                <input type="date" name="issue_date" class="form-control custom-input">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 text-sm-right custom-label">No. Revmodel</label>
                            <div class="col-sm-8">
                                <input type="text" name="rev_model" class="form-control custom-input" placeholder="Misal: REV-01">
                            </div>
                        </div>

                        <!-- SECTION 2: DETAIL PART -->
                        <div class="section-title mt-5"><i class="fas fa-cogs text-primary mr-2"></i>Detail Part & Kategori</div>

                        <div class="form-group row">
                            <label class="col-sm-3 text-sm-right custom-label">Family</label>
                            <div class="col-sm-8">
                                <select name="family" class="form-control custom-input">
                                    <option value="">- Pilih Family -</option>
                                    <option value="M">M</option>
                                    <option value="C">C</option>
                                    <option value="S">S</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 text-sm-right custom-label">Category</label>
                            <div class="col-sm-8">
                                <select name="category_nm_mp" class="form-control custom-input">
                                    <option value="">- Pilih Kategori -</option>
                                    <option value="NM">NM</option>
                                    <option value="MP">MP</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 text-sm-right custom-label">Kategori Claim</label>
                            <div class="col-sm-8">
                                <select name="category_claim" class="form-control custom-input">
                                    <option value="">- Pilih Claim -</option>
                                    <option value="External">External</option>
                                    <option value="Internal">Internal</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 text-sm-right custom-label">Model</label>
                            <div class="col-sm-8">
                                <input type="text" name="model" class="form-control custom-input" placeholder="Nama Model">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 text-sm-right custom-label">Part No.</label>
                            <div class="col-sm-8">
                                <input type="text" name="part_number" class="form-control custom-input" placeholder="Nomor Part">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 text-sm-right custom-label">Part Name</label>
                            <div class="col-sm-8">
                                <input type="text" name="part_name" class="form-control custom-input" placeholder="Nama Part">
                            </div>
                        </div>
                        <div class="form-group row" style="align-items: flex-start;">
                            <label class="col-sm-3 text-sm-right custom-label mt-2">Similar Part</label>
                            <div class="col-sm-8">
                                <textarea name="similar_part" class="form-control custom-input" rows="2" placeholder="Part yang mirip..."></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 text-sm-right custom-label">Mould</label>
                            <div class="col-sm-8">
                                <input type="text" name="mould" class="form-control custom-input" placeholder="Nomor / Nama Mould">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 text-sm-right custom-label">Owner of Mould</label>
                            <div class="col-sm-8">
                                <input type="text" name="owner_mould" class="form-control custom-input" placeholder="Pemilik Mould">
                            </div>
                        </div>

                        <!-- SECTION 3: ANALISIS PROBLEM -->
                        <div class="section-title mt-5"><i class="fas fa-exclamation-triangle text-primary mr-2"></i>Analisis Problem & Tindakan</div>

                        <div class="form-group row">
                            <label class="col-sm-3 text-sm-right custom-label">Section & Proses</label>
                            <div class="col-sm-4 mb-2 mb-sm-0">
                                <input type="text" name="section" class="form-control custom-input" placeholder="Section (Misal: IPP)">
                            </div>
                            <div class="col-sm-4">
                                <input type="text" name="process" class="form-control custom-input" placeholder="Proses (Misal: Injection)">
                            </div>
                        </div>
                        <div class="form-group row" style="align-items: flex-start;">
                            <label class="col-sm-3 text-sm-right custom-label mt-2">Problem</label>
                            <div class="col-sm-8">
                                <textarea name="problem" class="form-control custom-input" rows="3" placeholder="Deskripsikan masalah..."></textarea>
                            </div>
                        </div>
                        <div class="form-group row" style="align-items: flex-start;">
                            <label class="col-sm-3 text-sm-right custom-label mt-2">Cause</label>
                            <div class="col-sm-8">
                                <textarea name="cause" class="form-control custom-input" rows="3" placeholder="Akar penyebab..."></textarea>
                            </div>
                        </div>
                        <div class="form-group row" style="align-items: flex-start;">
                            <label class="col-sm-3 text-sm-right custom-label mt-2">Countermeasure</label>
                            <div class="col-sm-8">
                                <textarea name="countermeasure" class="form-control custom-input" rows="4" placeholder="Tindakan penanggulangan..."></textarea>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label class="col-sm-3 text-sm-right custom-label">PIC & Supplier</label>
                            <div class="col-sm-4 mb-2 mb-sm-0">
                                <input type="text" name="pic" class="form-control custom-input" placeholder="PIC">
                            </div>
                            <div class="col-sm-4">
                                <input type="text" name="supplier" class="form-control custom-input" placeholder="Supplier">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 text-sm-right custom-label">Kategori Defect</label>
                            <div class="col-sm-8">
                                <input type="text" name="defect_category" class="form-control custom-input" placeholder="Kategori Defect">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 text-sm-right custom-label">Status</label>
                            <div class="col-sm-8">
                                <select name="status" class="form-control custom-input">
                                    <option value="Open">Open</option>
                                    <option value="Closed">Closed</option>
                                    <option value="On Progress">On Progress</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row" style="align-items: flex-start;">
                            <label class="col-sm-3 text-sm-right custom-label mt-2">Remarks</label>
                            <div class="col-sm-8">
                                <textarea name="remarks" class="form-control custom-input" rows="2" placeholder="Catatan tambahan..."></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 text-sm-right custom-label">Form Analysis</label>
                            <div class="col-sm-8">
                                <div class="custom-file mb-1">
                                    <input type="file" name="form_analysis" class="custom-file-input" id="customFile" onchange="document.getElementById('file-label').innerText = this.files[0].name">
                                    <label class="custom-file-label custom-input" for="customFile" id="file-label" style="color: #94a3b8; line-height: 1.5;">Pilih file (PICA/AR/SA)...</label>
                                </div>
                                <small class="text-muted"><i class="fas fa-paperclip"></i> Format: pptx, xlsx, doc, docx, pdf (Max 10MB)</small>
                            </div>
                        </div>

                        <hr class="mt-5 mb-4" style="border-color: #f1f5f9;">
                        
                        <div class="form-group row mb-0">
                            <div class="col-sm-8 offset-sm-3">
                                <button type="submit" class="btn btn-primary btn-minimalist shadow-sm mr-2">
                                    <i class="fas fa-save mr-1"></i> Simpan Data
                                </button>
                                <a href="{{ route('kakotora.index', ['plant' => $plant]) }}" class="btn btn-light border btn-minimalist text-secondary">Batal</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Custom file input behavior
    document.querySelector('.custom-file-input').addEventListener('change', function(e) {
        var fileName = document.getElementById("customFile").files[0].name;
        var nextSibling = e.target.nextElementSibling;
        nextSibling.innerText = fileName;
        nextSibling.style.color = '#334155';
    });

    document.getElementById('formCreateKakotora').addEventListener('submit', function(e) {
        let emptyFields = [];
        const similarPart = document.querySelector('textarea[name="similar_part"]').value.trim();
        const problem = document.querySelector('textarea[name="problem"]').value.trim();
        const cause = document.querySelector('textarea[name="cause"]').value.trim();
        const cm = document.querySelector('textarea[name="countermeasure"]').value.trim();

        if (!similarPart) emptyFields.push('Similar Part');
        if (!problem) emptyFields.push('Problem');
        if (!cause) emptyFields.push('Cause');
        if (!cm) emptyFields.push('Countermeasure');

        if (emptyFields.length > 0) {
            e.preventDefault();
            Swal.fire({
                title: 'Data Belum Lengkap!',
                text: 'Field berikut wajib diisi: ' + emptyFields.join(', ') + '.',
                icon: 'warning'
            });
        }
    });
</script>
@endsection
