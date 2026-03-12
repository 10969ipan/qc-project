{{-- Modal Tambah Record --}}
<div class="modal fade" id="modalTambahRecord" tabindex="-1" role="dialog" aria-labelledby="modalTambahRecordLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="modalTambahRecordLabel">
                    <i class="fas fa-plus-circle mr-2"></i>Tambah Data Claim
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.customer-claim-records.store') }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <div class="modal-body px-4 py-4">
                    <input type="hidden" name="plant_id" value="{{ $plantId }}">
                    {{-- Section: Basic Information --}}
                    <div class="mb-4">
                        <h6 class="text-primary font-weight-bold mb-3 border-bottom pb-2">
                            <i class="fas fa-info-circle mr-1"></i> Informasi Dasar
                        </h6>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Tanggal Claim <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_claim" id="tambah_tanggal_claim"
                                        class="form-control form-control-sm" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Customer <span
                                            class="text-danger">*</span></label>
                                    <select id="tambah_customer_select"
                                        class="form-control form-control-sm customer-select" required>
                                        <option value="">-- Pilih Customer --</option>
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
                                        <option value="OTHER">Other (Type manually...)</option>
                                    </select>
                                    <input type="text" name="customer" id="tambah_customer_manual"
                                        class="form-control form-control-sm mt-2 customer-manual d-none"
                                        placeholder="Enter customer name">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Plant / UP (Customer)</label>
                                    <input type="text" name="plant_up_customer" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Officially / Non Officially / Suspect <span
                                            class="text-danger">*</span></label>
                                    <select name="claim_type" class="form-control form-control-sm" required>
                                        <option value="OFFICIAL">OFFICIAL</option>
                                        <option value="NON OFFICIAL">NON OFFICIAL</option>
                                        <option value="SUSPECT">SUSPECT</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">No. Dokumen (Report)</label>
                                    <input type="text" name="no_report" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Project (NM/MP)</label>
                                    <select name="project" class="form-control form-control-sm">
                                        <option value="MP">MP</option>
                                        <option value="NM">NM</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section: Part & Problem --}}
                    <div class="mb-4">
                        <h6 class="text-primary font-weight-bold mb-3 border-bottom pb-2">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Detail Part & Problem
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Nama Part <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="nama_part" class="form-control form-control-sm" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Problem <span
                                            class="text-danger">*</span></label>
                                    <textarea name="problem" class="form-control form-control-sm" rows="3"
                                        required></textarea>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Kategori Problem</label>
                                    <select name="kategori_defect" class="form-control form-control-sm">
                                        <option value="">-- Pilih --</option>
                                        <option value="Qty">Qty</option>
                                        <option value="Appearance">Appearance</option>
                                        <option value="Function">Function</option>
                                        <option value="Performance">Performance</option>
                                        <option value="Handling">Handling</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Kategori Penyimpangan</label>
                                    <select name="kategori_penyimpangan" class="form-control form-control-sm">
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
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Qty (pcs) <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="qty" class="form-control form-control-sm" value="0"
                                        required>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Temporary Action</label>
                                    <select name="action_taken" class="form-control form-control-sm">
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
                        </div>
                    </div>

                    {{-- Section: Financials & Resources --}}
                    <div class="mb-4">
                        <h6 class="text-primary font-weight-bold mb-3 border-bottom pb-2">
                            <i class="fas fa-money-bill-wave mr-1"></i> Biaya & Sumber Daya
                        </h6>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Initial Operator</label>
                                    <input type="text" name="initial_operator" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Initial Inspektor</label>
                                    <input type="text" name="initial_inspektor" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Cost Akomodasi (Rp)</label>
                                    <input type="number" step="1" name="total_akomodasi"
                                        class="form-control form-control-sm" value="0">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Cost Overtime (Rp)</label>
                                    <input type="number" step="1" name="total_overtime"
                                        class="form-control form-control-sm" value="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section: Feedback & Status --}}
                    <div class="mb-0">
                        <h6 class="text-primary font-weight-bold mb-3 border-bottom pb-2">
                            <i class="fas fa-comments mr-1"></i> Feedback & Evaluasi Problem
                        </h6>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Feedback</label>
                                    <textarea name="feedback" class="form-control form-control-sm" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Status Feedback</label>
                                    <input type="text" name="status_feedback" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Status (C/M)</label>
                                    <select name="status_cm" class="form-control form-control-sm">
                                        <option value="">-- Pilih --</option>
                                        <option value="Progres">Progres</option>
                                        <option value="Open">Open</option>
                                        <option value="Close">Close</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Dokumen Evidential (PDF/XLS/PPT/DOC)</label>
                                    <input type="file" name="attachments[]" class="form-control-file small" multiple>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Monitoring</label>
                                    <select name="monitoring" class="form-control form-control-sm">
                                        <option value="">-- Pilih --</option>
                                        <option value="Kepala Regu">Kepala Regu</option>
                                        <option value="Kepala Shift">Kepala Shift</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Evaluasi Problem (Otomatis 6 Bulan
                                        Kedepan)</label>
                                    <input type="text" name="evaluasi" id="tambah_evaluasi"
                                        class="form-control form-control-sm bg-light" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="small font-weight-bold">Monitoring Status</label>
                                    <select name="monitoring_status" class="form-control form-control-sm">
                                        <option value="Open">Open</option>
                                        <option value="Close">Close</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Record --}}
<div class="modal fade" id="modalEditRecord" tabindex="-1" role="dialog" aria-labelledby="modalEditRecordLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title font-weight-bold" id="modalEditRecordLabel">
                    <i class="fas fa-edit mr-2"></i>Edit Data Claim
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditRecord" action="" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body px-4 py-4">
                    <input type="hidden" name="plant_id" id="edit_plant_id">
                    {{-- Same structure for Edit Modal --}}
                    {{-- Section: Basic Information --}}
                    <div class="mb-4">
                        <h6 class="text-warning font-weight-bold mb-3 border-bottom pb-2">
                            <i class="fas fa-info-circle mr-1"></i> Informasi Dasar
                        </h6>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Tanggal Claim <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_claim" id="edit_tanggal_claim"
                                        class="form-control form-control-sm" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Customer <span
                                            class="text-danger">*</span></label>
                                    <select id="edit_customer_select"
                                        class="form-control form-control-sm customer-select" required>
                                        <option value="">-- Pilih Customer --</option>
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
                                        <option value="OTHER">Other (Type manually...)</option>
                                    </select>
                                    <input type="text" name="customer" id="edit_customer_manual"
                                        class="form-control form-control-sm mt-2 customer-manual d-none" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Plant / UP (Customer)</label>
                                    <input type="text" name="plant_up_customer" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Officially / Non Officially / Suspect <span
                                            class="text-danger">*</span></label>
                                    <select name="claim_type" class="form-control form-control-sm" required>
                                        <option value="OFFICIAL">OFFICIAL</option>
                                        <option value="NON OFFICIAL">NON OFFICIAL</option>
                                        <option value="SUSPECT">SUSPECT</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">No. Dokumen (Report)</label>
                                    <input type="text" name="no_report" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Project (NM/MP)</label>
                                    <select name="project" class="form-control form-control-sm">
                                        <option value="MP">MP</option>
                                        <option value="NM">NM</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section: Part & Problem --}}
                    <div class="mb-4">
                        <h6 class="text-warning font-weight-bold mb-3 border-bottom pb-2">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Detail Part & Problem
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Nama Part <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="nama_part" class="form-control form-control-sm" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Problem <span
                                            class="text-danger">*</span></label>
                                    <textarea name="problem" class="form-control form-control-sm" rows="3"
                                        required></textarea>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Kategori Problem</label>
                                    <select name="kategori_defect" class="form-control form-control-sm">
                                        <option value="">-- Pilih --</option>
                                        <option value="Qty">Qty</option>
                                        <option value="Appearance">Appearance</option>
                                        <option value="Function">Function</option>
                                        <option value="Performance">Performance</option>
                                        <option value="Handling">Handling</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Kategori Penyimpangan</label>
                                    <select name="kategori_penyimpangan" class="form-control form-control-sm">
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
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Qty (pcs) <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="qty" class="form-control form-control-sm" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Temporary Action</label>
                                    <select name="action_taken" class="form-control form-control-sm">
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
                        </div>
                    </div>

                    {{-- Section: Financials & Resources --}}
                    <div class="mb-4">
                        <h6 class="text-warning font-weight-bold mb-3 border-bottom pb-2">
                            <i class="fas fa-money-bill-wave mr-1"></i> Biaya & Sumber Daya
                        </h6>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Initial Operator</label>
                                    <input type="text" name="initial_operator" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Initial Inspektor</label>
                                    <input type="text" name="initial_inspektor"
                                        class="form-control font-control-sm text-sm">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Cost Akomodasi (Rp)</label>
                                    <input type="number" step="1" name="total_akomodasi"
                                        class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Cost Overtime (Rp)</label>
                                    <input type="number" step="1" name="total_overtime"
                                        class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section: Feedback & Status --}}
                    <div class="mb-0">
                        <h6 class="text-warning font-weight-bold mb-3 border-bottom pb-2">
                            <i class="fas fa-comments mr-1"></i> Feedback & Evaluasi Problem
                        </h6>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Feedback</label>
                                    <textarea name="feedback" class="form-control form-control-sm" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Status Feedback</label>
                                    <input type="text" name="status_feedback" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Status (C/M)</label>
                                    <select name="status_cm" class="form-control form-control-sm">
                                        <option value="">-- Pilih --</option>
                                        <option value="Progres">Progres</option>
                                        <option value="Open">Open</option>
                                        <option value="Close">Close</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Add More Dokumen Evidential
                                        (PDF/XLS/PPT/DOC)</label>
                                    <input type="file" name="attachments[]" class="form-control-file small" multiple>
                                    <div id="edit_attachments_list" class="mt-2">
                                        <!-- List of existing files will be populated here by JS -->
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Monitoring</label>
                                    <select name="monitoring" class="form-control form-control-sm">
                                        <option value="">-- Pilih --</option>
                                        <option value="Kepala Regu">Kepala Regu</option>
                                        <option value="Kepala Shift">Kepala Shift</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Evaluasi Problem (Otomatis 6 Bulan
                                        Kedepan)</label>
                                    <input type="text" name="evaluasi" id="edit_evaluasi"
                                        class="form-control form-control-sm bg-light" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="small font-weight-bold">Monitoring Status</label>
                                    <select name="monitoring_status" class="form-control form-control-sm">
                                        <option value="Open">Open</option>
                                        <option value="Close">Close</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning btn-sm px-4 shadow-sm">
                        <i class="fas fa-sync mr-1"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function () {
            function updateEvaluasiDate(inputSelector, targetSelector) {
                const dateVal = $(inputSelector).val();
                if (dateVal) {
                    const date = new Date(dateVal);
                    date.setMonth(date.getMonth() + 6);
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    $(targetSelector).val(`${day}-${month}-${year}`);
                }
            }

            $('#tambah_tanggal_claim').on('change', function () {
                updateEvaluasiDate('#tambah_tanggal_claim', '#tambah_evaluasi');
            });

            $('#edit_tanggal_claim').on('change', function () {
                updateEvaluasiDate('#edit_tanggal_claim', '#edit_evaluasi');
            });

            // Customer selection logic
            $('.customer-select').on('change', function () {
                const select = $(this);
                const manual = select.siblings('.customer-manual');
                if (select.val() === 'OTHER') {
                    manual.removeClass('d-none').val('').focus().attr('required', true);
                } else {
                    manual.addClass('d-none').val(select.val()).attr('required', false);
                }
            });
        });
    </script>
@endpush