<!-- Modal Sampling Pengukuran Dimensi Kashift / Karu -->
<style>
    #modalLeaderSamplingTable.dimension-table {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        margin: 0 !important;
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        overflow: hidden !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04) !important;
        width: 100% !important;
    }
    #modalLeaderSamplingTable.dimension-table td,
    #modalLeaderSamplingTable.dimension-table th {
        border-right: 1px solid #e2e8f0 !important;
        border-bottom: 1px solid #e2e8f0 !important;
        border-top: none !important;
        border-left: none !important;
        padding: 4px 6px !important;
        text-align: center !important;
        vertical-align: middle !important;
        white-space: nowrap !important;
    }
    #modalLeaderSamplingTable.dimension-table th:last-child,
    #modalLeaderSamplingTable.dimension-table td:last-child {
        border-right: none !important;
    }
    #modalLeaderSamplingTable.dimension-table tr:last-child td {
        border-bottom: none !important;
    }
    #modalLeaderSamplingTable.dimension-table th:first-child,
    #modalLeaderSamplingTable.dimension-table td:first-child {
        min-width: 75px !important;
        width: 75px !important;
    }
    #modalLeaderSamplingTable.dimension-table thead th {
        background-color: #ffffff !important;
        color: #334155 !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        font-size: 0.7rem !important;
        letter-spacing: 0.2px;
        border-bottom: 1px solid #cbd5e1 !important;
    }
    #modalLeaderSamplingTable.dimension-table thead tr.std-row th {
        background-color: #f8fafc !important;
        color: #475569 !important;
        font-size: 0.68rem !important;
        font-weight: 600 !important;
        text-transform: none !important;
    }
    #modalLeaderSamplingTable.dimension-table tbody td {
        color: #334155 !important;
        font-size: 0.72rem !important;
        background-color: #ffffff !important;
    }
    #modalLeaderSamplingTable.dimension-table tbody td:first-child {
        background-color: #ffffff !important;
        color: #334155 !important;
        font-weight: 700 !important;
        font-size: 0.72rem !important;
    }
    #modalLeaderSamplingTable.dimension-table .dimension-input {
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        color: #1e293b !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 6px !important;
        background: #ffffff !important;
        text-align: center;
        padding: 3px 5px !important;
        width: 100%;
        min-width: 55px;
        transition: all 0.15s ease-in-out;
    }
    #modalLeaderSamplingTable.dimension-table .dimension-input:focus {
        border-color: #6366f1 !important;
        background: #fff !important;
        box-shadow: 0 0 0 2px rgba(99,102,241,0.1) !important;
    }
    #modalLeaderSamplingTable.dimension-table .dimension-input.is-invalid {
        border-color: #ef4444 !important;
        background-color: #fee2e2 !important;
        color: #dc2626 !important;
        font-weight: 700 !important;
        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.25) !important;
    }
    #modalLeaderSamplingTable.dimension-table .dimension-input.is-valid {
        border-color: #22c55e !important;
        background-color: #f0fdf4 !important;
        color: #15803d !important;
    }
</style>

<div class="modal fade" id="modalLeaderDimensionSampling" tabindex="-1" role="dialog" aria-labelledby="modalLeaderSamplingTitle" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 0;">
            <!-- Modal Header -->
            <div class="modal-header py-3 px-4 bg-white" style="border-radius: 12px 12px 0 0; border-bottom: 1px solid #e2e8f0;">
                <div>
                    <h5 class="modal-title font-weight-bold text-dark mb-0" id="modalLeaderSamplingTitle" style="font-size: 1.05rem;">
                        Input Sampling Pengukuran Dimensi (Kashift / Kepala Regu)
                    </h5>
                    <small class="text-muted" id="modalLeaderSamplingSubtitle">
                        Memuat rincian checksheet...
                    </small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="outline: none;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body px-4 py-4" style="background-color: #f8fafc; max-height: 68vh; overflow-y: auto;">
                <!-- Error Alert Box -->
                <div id="modalLeaderSamplingAlert" class="alert alert-danger d-none shadow-sm" style="font-size: 0.8rem; border-radius: 8px;">
                    <i class="fas fa-exclamation-triangle mr-1"></i> <span id="modalLeaderSamplingAlertMsg"></span>
                </div>

                <!-- Section 1: Ringkasan Part & Inspeksi -->
                <div class="font-weight-bold text-dark mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">
                    INFORMASI CHECKSHEET
                </div>
                <div class="row mb-3" style="font-size: 0.8rem;">
                    <div class="col-md-4 mb-2">
                        <span class="d-block small font-weight-bold text-dark">Part Name:</span>
                        <span class="font-weight-bold text-dark" id="modalPartName">-</span>
                    </div>
                    <div class="col-md-4 mb-2">
                        <span class="d-block small font-weight-bold text-dark">Part Number:</span>
                        <span class="font-weight-bold text-dark" id="modalPartNumber">-</span>
                    </div>
                    <div class="col-md-4 mb-2">
                        <span class="d-block small font-weight-bold text-dark">No. Mesin / Tanggal:</span>
                        <span class="font-weight-bold text-dark" id="modalMachineDate">-</span>
                    </div>
                    <div class="col-md-4 mb-2">
                        <span class="d-block small font-weight-bold text-dark">Inspector:</span>
                        <span class="font-weight-bold text-dark" id="modalInspectorName">-</span>
                    </div>
                    <div class="col-md-8 mb-2">
                        <span class="d-block small font-weight-bold text-dark">Alat Ukur Inspector:</span>
                        <span class="text-dark" id="modalInspectorTools">-</span>
                    </div>
                </div>

                <!-- Section 2: Pemilihan Alat Ukur Pimpinan -->
                <div class="font-weight-bold text-dark mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">
                    ALAT UKUR SAMPLING
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="small font-weight-bold text-dark">Pilih / Ketik Alat Ukur yang Digunakan <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <input type="text" id="leaderAlatUkurInput" list="leaderToolsList" class="form-control form-control-sm border-0 shadow-sm" placeholder="Pilih atau ketik alat ukur..." style="font-size: 0.8rem;">
                            <datalist id="leaderToolsList">
                                <!-- Populated dynamically -->
                            </datalist>
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <small class="text-muted mt-3">
                            <i class="fas fa-info-circle text-info mr-1"></i> Pastikan alat ukur yang dipilih telah terkalibrasi dan sesuai dengan point standar.
                        </small>
                    </div>
                </div>

                <!-- Section 3: Form Pengukuran Sampling Seluruh Cavity & Point -->
                <div class="font-weight-bold text-dark mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">
                    HASIL PENGUKURAN SAMPLING (SELURUH CAVITY & POINT)
                </div>

                <!-- Loader Table -->
                <div id="modalLeaderSamplingLoader" class="text-center py-4">
                    <div class="spinner-border text-primary spinner-border-sm mr-2" role="status"></div>
                    <span class="text-muted small">Menyiapkan form pengukuran...</span>
                </div>

                <!-- Dynamic Dimension Input Table Container -->
                <div class="table-responsive bg-white rounded shadow-sm p-2 mb-2 d-none" id="modalLeaderTableWrapper" style="max-height: 380px; overflow-x: auto; overflow-y: auto;">
                    <table class="table table-sm table-bordered mb-0 dimension-table" id="modalLeaderSamplingTable">
                        <thead class="text-center bg-white" id="modalLeaderTableHead">
                            <!-- Populated dynamically via JS -->
                        </thead>
                        <tbody id="modalLeaderTableBody">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Auto Judgment Sampling & Progress Bar Container -->
                <div class="d-flex flex-wrap justify-content-between align-items-center bg-white p-2 rounded border mt-2 mb-1 shadow-sm">
                    <div class="d-flex align-items-center my-1">
                        <span class="text-dark font-weight-bold mr-2" style="font-size: 0.78rem;">
                            JUDGMENT SAMPLING:
                        </span>
                        <span id="modalLeaderJudgmentBadge" class="badge px-3 py-1 font-weight-bold" style="font-size: 0.82rem; border-radius: 6px; border: 1px solid #cbd5e1; background-color: #f1f5f9; color: #475569;">
                            -
                        </span>
                    </div>
                    <div id="modalInputProgress" class="font-weight-bold text-dark my-1" style="font-size: 0.78rem;"></div>
                </div>

                <div class="d-flex justify-content-between align-items-center px-1" style="font-size: 0.72rem;">
                    <span class="text-muted font-italic">
                        * Nilai input akan otomatis di-judge terhadap batas toleransi Min/Max standar (kolom hijau = OK, kolom merah = NG).
                    </span>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer py-3 px-4 bg-white" style="border-radius: 0 0 12px 12px; border-top: 1px solid #e2e8f0;">
                <button type="button" class="btn btn-light btn-sm border shadow-sm px-3" data-dismiss="modal" style="font-size: 0.8rem;">
                    Batal
                </button>
                <button type="button" class="btn btn-primary btn-sm shadow-sm px-4" id="btnSubmitLeaderSampling" style="font-size: 0.8rem;">
                    <i class="fas fa-check-circle mr-1"></i> Simpan & Approve Sampling
                </button>
            </div>
        </div>
    </div>
</div>
