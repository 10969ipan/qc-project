<div class="modal fade" id="modalTambahRecord" tabindex="-1" role="dialog" aria-labelledby="modalTambahRecordLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-white border-bottom py-3 px-4" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title font-weight-bold text-gray-800" style="font-size: 1.1rem;" id="modalTambahRecordLabel">
                    <i class="fas fa-plus-circle text-primary mr-2"></i>Tambah Data Claim
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formTambahRecord" action="{{ route('admin.customer-claim-records.store') }}" method="POST"
                enctype="multipart/form-data" novalidate>
                @csrf
                <div class="modal-body px-4 py-4" style="background-color: #f8fafc; max-height: 65vh; overflow-y: auto;">
                    <input type="hidden" name="plant_id" value="{{ $plantId }}">

                    <!-- Section 1: Informasi Dasar -->
                    <div class="font-weight-bold text-info mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">INFORMASI DASAR</div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Tgl Claim <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <input type="date" name="tanggal_claim" id="tambah_tanggal_claim" class="form-control form-control-sm border-0 shadow-sm" required>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Customer <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <select id="tambah_customer_select" class="form-control form-control-sm customer-select border-0 shadow-sm" required>
                                        <option value="">-- Pilih --</option>
                                        <option value="YIMM">YIMM</option>
                                        <option value="AHM">AHM</option>
                                        <option value="SANKO">SANKO</option>
                                        <option value="SIM">SIM</option>
                                        <option value="YUJU">YUJU</option>
                                        <option value="PTI">PTI</option>
                                        <option value="MSI">MSI</option>
                                        <option value="TSC">TSC</option>
                                        <option value="USRA">USRA</option>
                                        <option value="SUNSHINE">SUNSHINE</option>
                                        <option value="PRIMA K">PRIMA K</option>
                                        <option value="OPSINDO">OPSINDO</option>
                                        <option value="OTHER">Other...</option>
                                    </select>
                                    <input type="text" name="customer" id="tambah_customer_manual" class="form-control form-control-sm border-0 shadow-sm mt-1 customer-manual d-none no-autoupper" placeholder="Nama Customer">
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Plant / UP</label>
                                <div class="col-sm-8">
                                    <input type="text" name="plant_up_customer" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Type <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <select name="claim_type" class="form-control form-control-sm border-0 shadow-sm" required>
                                        <option value="OFFICIAL">OFFICIAL</option>
                                        <option value="NON OFFICIAL">NON OFFICIAL</option>
                                        <option value="SUSPECT">SUSPECT</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Doc No (Report)</label>
                                <div class="col-sm-8">
                                    <input type="text" name="no_report" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Project</label>
                                <div class="col-sm-8">
                                    <select name="project" class="form-control form-control-sm border-0 shadow-sm">
                                        <option value="MP">MP</option>
                                        <option value="NM">NM</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Qty (pcs) <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <input type="number" name="qty" class="form-control form-control-sm border-0 shadow-sm" value="0" required>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Akomodasi (Rp)</label>
                                <div class="col-sm-8">
                                    <input type="number" step="1" name="total_akomodasi" class="form-control form-control-sm border-0 shadow-sm" value="0">
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Overtime (Rp)</label>
                                <div class="col-sm-8">
                                    <input type="number" step="1" name="total_overtime" class="form-control form-control-sm border-0 shadow-sm" value="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Detail Part & Problem -->
                    <div class="font-weight-bold text-info mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">DETAIL PART & PROBLEM</div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Nama Part <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" name="nama_part" class="form-control form-control-sm border-0 shadow-sm no-autoupper" required>
                                </div>
                            </div>
                            <div class="form-group row align-items-start mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700 pt-2">Problem <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <textarea name="problem" class="form-control form-control-sm border-0 shadow-sm no-autoupper" rows="4" required></textarea>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Kat Problem</label>
                                <div class="col-sm-8">
                                    <select name="kategori_defect" class="form-control form-control-sm border-0 shadow-sm">
                                        <option value="">-- Pilih --</option>
                                        <option value="Qty">Qty</option>
                                        <option value="Appearance">Appearance</option>
                                        <option value="Function">Function</option>
                                        <option value="Performance">Performance</option>
                                        <option value="Handling">Handling</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Kat Penyimpangan</label>
                                <div class="col-sm-8">
                                    <select name="kategori_penyimpangan" class="form-control form-control-sm border-0 shadow-sm">
                                        <option value="">-- PILIH --</option>
                                        <optgroup label="4M">
                                            <option value="4M - MAN">4M - MAN</option>
                                            <option value="4M - METHOD">4M - METHOD</option>
                                            <option value="4M - MATERIAL">4M - MATERIAL</option>
                                            <option value="4M - MESIN">4M - MESIN</option>
                                        </optgroup>
                                        <optgroup label="IPQ">
                                            <option value="IPQ - MSK">IPQ - MSK</option>
                                            <option value="IPQ - KT">IPQ - KT</option>
                                            <option value="IPQ - PSSP">IPQ - PSSP</option>
                                            <option value="IPQ - PNG">IPQ - PNG</option>
                                        </optgroup>
                                        <optgroup label="OTHER">
                                            <option value="OTHER">OTHER</option>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Initial Operator</label>
                                <div class="col-sm-8">
                                    <input type="text" name="initial_operator" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Initial Inspektor</label>
                                <div class="col-sm-8">
                                    <input type="text" name="initial_inspektor" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Temp Action</label>
                                <div class="col-sm-8">
                                    <select name="action_taken" class="form-control form-control-sm border-0 shadow-sm">
                                        <option value="">-- Pilih --</option>
                                        <option value="Report">Report</option>
                                        <option value="No Report">No Report</option>
                                        <option value="Replacement">Replacement</option>
                                        <option value="Sortir">Sortir</option>
                                        <option value="Tukar Guling">Tukar Guling</option>
                                        <option value="Repair">Repair</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Evaluasi Problem</label>
                                <div class="col-sm-8">
                                    <input type="text" name="evaluasi" id="tambah_evaluasi" class="form-control form-control-sm border-0 shadow-sm bg-light" placeholder="(Otomatis 6 Bln Kedepan)" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Feedback & Evaluasi -->
                    <div class="font-weight-bold text-info mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">FEEDBACK & EVALUASI</div>
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <div class="form-group row align-items-start mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700 pt-2">Feedback</label>
                                <div class="col-sm-8">
                                    <textarea name="feedback" class="form-control form-control-sm border-0 shadow-sm no-autoupper" rows="4"></textarea>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Status Feedback</label>
                                <div class="col-sm-8">
                                    <input type="text" name="status_feedback" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Status (C/M)</label>
                                <div class="col-sm-8">
                                    <select name="status_cm" class="form-control form-control-sm border-0 shadow-sm">
                                        <option value="">-- Pilih --</option>
                                        <option value="Progres">Progres</option>
                                        <option value="Open">Open</option>
                                        <option value="Close">Close</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Monitoring</label>
                                <div class="col-sm-8">
                                    <select name="monitoring" class="form-control form-control-sm border-0 shadow-sm">
                                        <option value="">-- Pilih --</option>
                                        <option value="Kepala Regu">Kepala Regu</option>
                                        <option value="Kepala Shift">Kepala Shift</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Mon Status</label>
                                <div class="col-sm-8">
                                    <select name="monitoring_status" class="form-control form-control-sm border-0 shadow-sm">
                                        <option value="Open">Open</option>
                                        <option value="Close">Close</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row align-items-start mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700 pt-2">Evidence</label>
                                <div class="col-sm-8">
                                    <input type="file" name="attachments[]" class="form-control-file border-0 p-1 shadow-sm rounded bg-white" multiple>
                                    <small class="text-muted">Max 10MB (PDF/XLS/PPT/DOC)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top py-3 px-4" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-light border px-4 font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 font-weight-bold shadow-sm">
                        <i class="fas fa-save mr-1"></i> Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditRecord" tabindex="-1" role="dialog" aria-labelledby="modalEditRecordLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-white border-bottom py-3 px-4" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title font-weight-bold text-gray-800" style="font-size: 1.1rem;" id="modalEditRecordLabel">
                    <i class="fas fa-edit text-warning mr-2"></i>Edit Data Claim
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditRecord" action="" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-body px-4 py-4" style="background-color: #f8fafc; max-height: 65vh; overflow-y: auto;">
                    <input type="hidden" name="plant_id" id="edit_plant_id">

                    <!-- Section 1: Informasi Dasar -->
                    <div class="font-weight-bold text-warning mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">INFORMASI DASAR</div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Tgl Claim <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <input type="date" name="tanggal_claim" id="edit_tanggal_claim" class="form-control form-control-sm border-0 shadow-sm" required>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Customer <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <select id="edit_customer_select" class="form-control form-control-sm customer-select border-0 shadow-sm" required>
                                        <option value="">-- Pilih --</option>
                                        <option value="YIMM">YIMM</option>
                                        <option value="AHM">AHM</option>
                                        <option value="SANKO">SANKO</option>
                                        <option value="SIM">SIM</option>
                                        <option value="YUJU">YUJU</option>
                                        <option value="PTI">PTI</option>
                                        <option value="MSI">MSI</option>
                                        <option value="TSC">TSC</option>
                                        <option value="USRA">USRA</option>
                                        <option value="SUNSHINE">SUNSHINE</option>
                                        <option value="PRIMA K">PRIMA K</option>
                                        <option value="OPSINDO">OPSINDO</option>
                                        <option value="OTHER">Other...</option>
                                    </select>
                                    <input type="text" name="customer" id="edit_customer_manual" class="form-control form-control-sm border-0 shadow-sm mt-1 customer-manual d-none no-autoupper" required>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Plant / UP</label>
                                <div class="col-sm-8">
                                    <input type="text" name="plant_up_customer" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Type <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <select name="claim_type" class="form-control form-control-sm border-0 shadow-sm" required>
                                        <option value="OFFICIAL">OFFICIAL</option>
                                        <option value="NON OFFICIAL">NON OFFICIAL</option>
                                        <option value="SUSPECT">SUSPECT</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Doc No (Report)</label>
                                <div class="col-sm-8">
                                    <input type="text" name="no_report" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Project</label>
                                <div class="col-sm-8">
                                    <select name="project" class="form-control form-control-sm border-0 shadow-sm">
                                        <option value="MP">MP</option>
                                        <option value="NM">NM</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Qty (pcs) <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <input type="number" name="qty" class="form-control form-control-sm border-0 shadow-sm" required>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Akomodasi (Rp)</label>
                                <div class="col-sm-8">
                                    <input type="number" step="1" name="total_akomodasi" class="form-control form-control-sm border-0 shadow-sm">
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Overtime (Rp)</label>
                                <div class="col-sm-8">
                                    <input type="number" step="1" name="total_overtime" class="form-control form-control-sm border-0 shadow-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Detail Part & Problem -->
                    <div class="font-weight-bold text-warning mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">DETAIL PART & PROBLEM</div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Nama Part <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" name="nama_part" class="form-control form-control-sm border-0 shadow-sm no-autoupper" required>
                                </div>
                            </div>
                            <div class="form-group row align-items-start mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700 pt-2">Problem <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <textarea name="problem" class="form-control form-control-sm border-0 shadow-sm no-autoupper" rows="4" required></textarea>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Kat Problem</label>
                                <div class="col-sm-8">
                                    <select name="kategori_defect" class="form-control form-control-sm border-0 shadow-sm">
                                        <option value="">-- Pilih --</option>
                                        <option value="Qty">Qty</option>
                                        <option value="Appearance">Appearance</option>
                                        <option value="Function">Function</option>
                                        <option value="Performance">Performance</option>
                                        <option value="Handling">Handling</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Kat Penyimpangan</label>
                                <div class="col-sm-8">
                                    <select name="kategori_penyimpangan" class="form-control form-control-sm border-0 shadow-sm">
                                        <option value="">-- PILIH --</option>
                                        <optgroup label="4M">
                                            <option value="4M - MAN">4M - MAN</option>
                                            <option value="4M - METHOD">4M - METHOD</option>
                                            <option value="4M - MATERIAL">4M - MATERIAL</option>
                                            <option value="4M - MESIN">4M - MESIN</option>
                                        </optgroup>
                                        <optgroup label="IPQ">
                                            <option value="IPQ - MSK">IPQ - MSK</option>
                                            <option value="IPQ - KT">IPQ - KT</option>
                                            <option value="IPQ - PSSP">IPQ - PSSP</option>
                                            <option value="IPQ - PNG">IPQ - PNG</option>
                                        </optgroup>
                                        <optgroup label="OTHER">
                                            <option value="OTHER">OTHER</option>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Initial Operator</label>
                                <div class="col-sm-8">
                                    <input type="text" name="initial_operator" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Initial Inspektor</label>
                                <div class="col-sm-8">
                                    <input type="text" name="initial_inspektor" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Temp Action</label>
                                <div class="col-sm-8">
                                    <select name="action_taken" class="form-control form-control-sm border-0 shadow-sm">
                                        <option value="">-- Pilih --</option>
                                        <option value="Report">Report</option>
                                        <option value="No Report">No Report</option>
                                        <option value="Replacement">Replacement</option>
                                        <option value="Sortir">Sortir</option>
                                        <option value="Tukar Guling">Tukar Guling</option>
                                        <option value="Repair">Repair</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Evaluasi Problem</label>
                                <div class="col-sm-8">
                                    <input type="text" name="evaluasi" id="edit_evaluasi" class="form-control form-control-sm border-0 shadow-sm no-autoupper" placeholder="Otomatis 6 Bulan / Bebas Diisi">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Feedback & Evaluasi -->
                    <div class="font-weight-bold text-warning mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">FEEDBACK & EVALUASI</div>
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <div class="form-group row align-items-start mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700 pt-2">Feedback</label>
                                <div class="col-sm-8">
                                    <textarea name="feedback" class="form-control form-control-sm border-0 shadow-sm no-autoupper" rows="4"></textarea>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Status Feedback</label>
                                <div class="col-sm-8">
                                    <input type="text" name="status_feedback" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Status (C/M)</label>
                                <div class="col-sm-8">
                                    <select name="status_cm" class="form-control form-control-sm border-0 shadow-sm">
                                        <option value="">-- Pilih --</option>
                                        <option value="Progres">Progres</option>
                                        <option value="Open">Open</option>
                                        <option value="Close">Close</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Monitoring</label>
                                <div class="col-sm-8">
                                    <select name="monitoring" class="form-control form-control-sm border-0 shadow-sm">
                                        <option value="">-- Pilih --</option>
                                        <option value="Kepala Regu">Kepala Regu</option>
                                        <option value="Kepala Shift">Kepala Shift</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Mon Status</label>
                                <div class="col-sm-8">
                                    <select name="monitoring_status" class="form-control form-control-sm border-0 shadow-sm">
                                        <option value="Open">Open</option>
                                        <option value="Close">Close</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row align-items-start mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700 pt-2">Add Evidence</label>
                                <div class="col-sm-8">
                                    <input type="file" name="attachments[]" class="form-control-file border-0 p-1 shadow-sm rounded bg-white" multiple>
                                    <small class="text-muted">Max 10MB (PDF/XLS/PPT/DOC). <i>Biarkan kosong jika tidak ingin menambah file.</i></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Existing Attachments -->
                    <div id="edit-existing-attachments" class="mt-3 p-3 bg-white border rounded shadow-sm d-none">
                        <label class="small font-weight-bold text-gray-700 border-bottom pb-2 mb-3 d-block"><i class="fas fa-paperclip mr-1 text-secondary"></i>File Evidence Saat Ini</label>
                        <ul id="edit-attachment-list" class="list-unstyled mb-0" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px;"></ul>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top py-3 px-4" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-light border px-4 font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning px-4 font-weight-bold shadow-sm">
                        <i class="fas fa-sync mr-1"></i> Update Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
