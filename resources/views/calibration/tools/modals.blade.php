<!-- PDF Modal -->
<div class="modal fade" id="pdfModal" tabindex="-1" role="dialog" aria-labelledby="pdfModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header py-2" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                <h5 class="modal-title font-weight-bold text-dark" id="pdfModalLabel" style="font-size: 0.9rem;">Lihat Sertifikat</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <iframe id="pdfFrame" src="" width="100%" height="600px" style="border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <a id="downloadPdf" href="#" class="btn btn-primary" download>
                    <i class="fas fa-download"></i> Download
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Alat -->
<div class="modal fade" id="modalTambahAlat" tabindex="-1" role="dialog" aria-labelledby="modalTambahAlatLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content border-0 shadow-sm">
            <div class="modal-header py-2" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                <h5 class="modal-title font-weight-bold text-dark" id="modalTambahAlatLabel" style="font-size: 0.9rem;">
                    <i class="fas fa-plus-circle mr-2 text-primary"></i> Tambah Master Data Alat
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('calibration.tools.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="plant" value="{{ $plantCode }}">
                <input type="hidden" name="year" value="{{ $year }}">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 text-left">
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Bagian</label>
                                <input type="text" name="bagian" class="form-control form-control-sm no-autoupper" required>
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Nama Alat</label>
                                <input type="text" name="name_alat" class="form-control form-control-sm no-autoupper" required>
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Merk</label>
                                <input type="text" name="merk" class="form-control form-control-sm no-autoupper">
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">No. Seri</label>
                                <input type="text" name="serial_number" class="form-control form-control-sm no-autoupper" required>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold">Range</label>
                                        <input type="text" name="range" class="form-control form-control-sm no-autoupper">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold">Resolusi</label>
                                        <input type="text" name="resolusi" class="form-control form-control-sm no-autoupper">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 text-left">
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Tanggal Beli</label>
                                <input type="date" name="tanggal_beli" class="form-control form-control-sm">
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Frekuensi Kalibrasi</label>
                                <input type="text" name="frekuensi_kalibrasi" class="form-control form-control-sm no-autoupper"
                                    placeholder="Contoh: 1 Tahun" required>
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Jenis Kalibrasi</label>
                                <select name="jenis_kalibrasi" class="form-control form-control-sm" required>
                                    <option value="Internal">Internal</option>
                                    <option value="Eksternal">Eksternal</option>
                                </select>
                            </div>
                            <div class="form-group mb-2" id="modal-schedule-container">
                                <label class="small font-weight-bold">Schedule Planning</label>
                                <div class="input-group input-group-sm mb-2">
                                    <input type="date" name="schedule_planning[]" class="form-control">
                                    <div class="input-group-append">
                                        <button class="btn btn-success" type="button" id="modal-add-schedule-btn">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group text-left mb-2">
                        <label class="small font-weight-bold">Riwayat Kalibrasi</label>
                        <textarea name="riwayat_kalibrasi" class="form-control form-control-sm no-autoupper" rows="2"></textarea>
                    </div>

                    <div class="form-group text-left mb-0">
                        <label class="small font-weight-bold">Sertifikasi (PDF)</label>
                        <input type="file" name="certification" class="form-control-file" accept=".pdf">
                    </div>
                </div>
                <div class="modal-footer bg-light p-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Alat -->
<div class="modal fade" id="modalEditAlat" tabindex="-1" role="dialog" aria-labelledby="modalEditAlatLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow-sm">
            <div class="modal-header py-2" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                <h5 class="modal-title font-weight-bold text-dark" id="modalEditAlatLabel" style="font-size: 0.9rem;">
                    <i class="fas fa-edit mr-2 text-info"></i> Edit Master Data Alat
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditAlat" action="" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="plant" value="{{ $plantCode }}">
                <input type="hidden" name="year" value="{{ $year }}">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 text-left">
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Bagian <span class="text-danger">*</span></label>
                                <input type="text" name="bagian" id="edit_bagian" class="form-control form-control-sm no-autoupper"
                                    required>
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Nama Alat <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="name_alat" id="edit_name_alat"
                                    class="form-control form-control-sm no-autoupper" required>
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Merk</label>
                                <input type="text" name="merk" id="edit_merk" class="form-control form-control-sm no-autoupper">
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">No. Seri <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="serial_number" id="edit_serial_number"
                                    class="form-control form-control-sm no-autoupper" required>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold">Range</label>
                                        <input type="text" name="range" id="edit_range"
                                            class="form-control form-control-sm no-autoupper">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold">Resolusi</label>
                                        <input type="text" name="resolusi" id="edit_resolusi"
                                            class="form-control form-control-sm no-autoupper">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 text-left">
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Tgl. Beli</label>
                                <input type="date" name="tanggal_beli" id="edit_tanggal_beli"
                                    class="form-control form-control-sm">
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Freq. Kalibrasi <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="frekuensi_kalibrasi" id="edit_frekuensi_kalibrasi"
                                    class="form-control form-control-sm no-autoupper" required>
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Jenis Kalibrasi <span
                                        class="text-danger">*</span></label>
                                <select name="jenis_kalibrasi" id="edit_jenis_kalibrasi"
                                    class="form-control form-control-sm" required>
                                    <option value="Internal">Internal</option>
                                    <option value="Eksternal">Eksternal</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group text-left mb-2">
                        <label class="small font-weight-bold">Schedule Kalibrasi (Planning)</label>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0" id="edit-schedule-table">
                                <thead class="bg-light small text-center">
                                    <tr>
                                        <th>Tanggal Planning</th>
                                        <th>PR Number</th>
                                        <th width="40"><button type="button"
                                                class="btn btn-xs btn-success add-edit-schedule-row"><i
                                                    class="fas fa-plus"></i></button></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Will be filled by JS --}}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="form-group text-left mb-2">
                        <label class="small font-weight-bold">Riwayat Kalibrasi</label>
                        <textarea name="riwayat_kalibrasi" id="edit_riwayat_kalibrasi"
                            class="form-control form-control-sm no-autoupper" rows="2"></textarea>
                    </div>

                    <div class="form-group text-left mb-0">
                        <label class="small font-weight-bold">Sertifikasi (PDF Baru)</label>
                        <input type="file" name="certification" class="form-control-file" accept=".pdf">
                        <div id="edit_existing_cert" class="mt-1"></div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info btn-sm px-4 shadow-sm">
                        <i class="fas fa-save mr-1"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Lapor Masalah -->
<div class="modal fade" id="modalReportProblem" tabindex="-1" role="dialog" aria-labelledby="modalReportProblemLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0 shadow-sm">
            <div class="modal-header py-2" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                <h5 class="modal-title font-weight-bold text-dark" id="modalReportProblemLabel" style="font-size: 0.9rem;">
                    <i class="fas fa-exclamation-triangle mr-2 text-warning"></i> Lapor Masalah Alat
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('calibration.tools.store-problem') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="plant" value="{{ $plantCode }}">
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="calibration_tool_id" id="problem_tool_id">
                <div class="modal-body text-left">
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold">Nama Alat</label>
                        <input type="text" id="problem_tool_name" class="form-control form-control-sm bg-light" readonly>
                    </div>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold">Tanggal Kejadian <span class="text-danger">*</span></label>
                        <input type="date" name="reported_date" class="form-control form-control-sm" 
                            value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold">Jenis Problem <span class="text-danger">*</span></label>
                        <select name="problem_type" id="problem_type" class="form-control form-control-sm" required>
                            <option value="">-- Pilih Jenis --</option>
                            <option value="ERROR">ERROR (Masih bisa diperbaiki)</option>
                            <option value="RUSAK">RUSAK (Mati total / pecah / tidak bisa dipakai)</option>
                        </select>
                    </div>
                    <div class="form-group mb-3" id="action_taken_wrapper" style="display: none;">
                        <label class="small font-weight-bold">Aksi Lanjut <span class="text-danger">*</span></label>
                        <input type="text" name="action_taken" id="action_taken" class="form-control form-control-sm" 
                            placeholder="Contoh: Service Internal, PO GA, dll..." list="action_suggestions">
                        <datalist id="action_suggestions">
                            <option value="SERVICE_INTERNAL">
                            <option value="SERVICE_EXTERNAL">
                            <option value="PO_GA">
                            <option value="REPLACE">
                        </datalist>
                        <div class="alert alert-danger small mt-2 py-1 mb-0" id="rusak_info" style="display: none;">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            Alat <strong>RUSAK</strong> akan otomatis di-set statusnya menjadi <strong>BROKEN</strong> dan seluruh jadwal mendatang akan dihapus.
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold">Bukti Foto / PDF</label>
                        <input type="file" name="evidence_report" class="form-control-file form-control-sm" 
                            accept=".jpg,.jpeg,.png,.pdf">
                        <small class="text-muted">Max: 5MB (JPG/PDF)</small>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Detail Masalah <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control form-control-sm no-autoupper" rows="3" 
                            placeholder="Jelaskan detail masalahnya..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light p-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning btn-sm px-4 shadow-sm">
                        <i class="fas fa-paper-plane mr-1"></i> Kirim Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
