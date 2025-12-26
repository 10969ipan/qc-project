@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Checksheet Cross Cut</h1>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Input Data Checksheet Cross Cut</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('cross_cut.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr class="text-center">
                                <th>Item Part</th>
                                <th>Customer</th>
                                <th>Tanggal & Shift Produksi / QC</th>
                                <th>Hasil Cross Cut</th>
                                <th>Kimia</th>
                                <th>Posisi Remark (Judgement / No Lot)</th>
                                <th>Result Remark</th>
                                <th>Inisial QC</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <!-- Item Part -->
                                <td class="align-middle" style="min-width: 200px;">
                                    <select class="form-control" id="item_id" name="item_id" required>
                                        <option value="">-- Select Item --</option>
                                        @foreach($items as $item)
                                            <option value="{{ $item->id }}" data-customer="{{ $item->customer }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <!-- Customer -->
                                <td class="align-middle" style="min-width: 150px;">
                                    <input type="text" class="form-control" name="customer" id="customer" readonly>
                                </td>
                                <!-- Tanggal & Shift Produksi / QC -->
                                <td class="align-middle" style="min-width: 250px;">
                                    <div class="form-group mb-2">
                                        <label>Tgl. & Shift Produksi</label>
                                        <div class="input-group">
                                            <input type="datetime-local" class="form-control" name="production_datetime" required>
                                            <select class="form-control" name="production_shift" required>
                                                <option value="1">Shift 1</option>
                                                <option value="2">Shift 2</option>
                                                <option value="3">Shift 3</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label>Tgl. & Shift QC</label>
                                        <div class="input-group">
                                            <input type="datetime-local" class="form-control" name="qc_datetime" required>
                                            <select class="form-control" name="qc_shift" required>
                                                <option value="1">Shift 1</option>
                                                <option value="2">Shift 2</option>
                                                <option value="3">Shift 3</option>
                                            </select>
                                        </div>
                                    </div>
                                </td>
                                <!-- Hasil Cross Cut (Image) -->
                                <td class="align-middle text-center">
                                    <label for="image" class="mb-2">Ambil Gambar</label>
                                    <input type="file" class="form-control-file mb-2" id="image" name="image" accept="image/*" required>
                                    <button type="button" id="previewBtn" class="btn btn-info btn-sm" style="display: none;">Preview Foto</button>
                                </td>
                                <!-- Kimia -->
                                <td class="align-middle" style="min-width: 200px;">
                                    <div class="form-group mb-2"><label>Copper</label><input type="text" class="form-control" name="chemical_copper"></div>
                                    <div class="form-group mb-2"><label>Nikel</label><input type="text" class="form-control" name="chemical_nikel"></div>
                                    <div class="form-group mb-2"><label>Eching</label><input type="text" class="form-control" name="chemical_eching"></div>
                                    <div class="form-group mb-0"><label>Abu</label><input type="text" class="form-control" name="chemical_abu"></div>
                                </td>
                                <!-- Posisi Remark -->
                                <td class="align-middle" style="min-width: 200px;">
                                    <div class="form-group mb-2">
                                        <label>Judgment</label>
                                        <select class="form-control" name="position_remark_judgment" required><option value="OK">OK</option><option value="NG">NG</option></select>
                                    </div>
                                    <div class="form-group mb-0"><label>No Lot</label><input type="text" class="form-control" name="position_remark_no_lot" required></div>
                                </td>
                                <!-- Result Remark -->
                                <td class="align-middle"><input type="text" class="form-control" name="result_remark"></td>
                                <!-- Inisial QC -->
                                <td class="align-middle"><input type="text" class="form-control" name="operator_initials" placeholder="Inisial"></td>
                                <!-- Keterangan -->
                                <td class="align-middle"><textarea class="form-control" name="keterangan" rows="3"></textarea></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12 text-right d-flex justify-content-end align-items-center">
                        <h5 class="mr-3 mb-0 font-weight-bold text-gray-800" id="timerDisplay">00:00:00</h5>
                        <input type="hidden" name="cycle_time" id="cycleTimeInput" value="0">
                        
                        <button type="button" class="btn btn-success mr-3" id="startTimerBtn">
                            <i class="fas fa-play"></i> Start
                        </button>
                        <button type="submit" class="btn btn-primary" id="saveBtn" disabled>
                            <i class="fas fa-save fa-sm"></i> Simpan Data
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" role="dialog" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imagePreviewModalLabel">Image Preview</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="previewImage" src="" class="img-fluid" alt="Image Preview">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image Preview Logic
    $('#image').on('change', function(event) {
        var file = event.target.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#previewImage').attr('src', e.target.result);
                $('#previewBtn').show(); // Show the preview button
            }
            reader.readAsDataURL(file);
        }
    });

    $('#previewBtn').on('click', function() {
        $('#imagePreviewModal').modal('show'); // Open the modal on button click
    });

    // Timer Logic
    var timerInterval = null;
    var totalSeconds = 0;
    var timerRunning = false;
    var timerDisplay = document.getElementById('timerDisplay');
    var cycleTimeInput = document.getElementById('cycleTimeInput');
    var startTimerBtn = document.getElementById('startTimerBtn');
    var saveBtn = document.getElementById('saveBtn');

    function updateTimerDisplay() {
        var hours = Math.floor(totalSeconds / 3600);
        var minutes = Math.floor((totalSeconds % 3600) / 60);
        var seconds = totalSeconds % 60;
        var text = [hours, minutes, seconds].map(v => v < 10 ? "0" + v : v).join(":");
        timerDisplay.textContent = text;
        cycleTimeInput.value = totalSeconds;
    }

    startTimerBtn.addEventListener('click', function() {
        if (!timerRunning) {
            timerRunning = true;
            this.classList.remove('btn-success');
            this.classList.add('btn-secondary');
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-clock"></i> Running...';
            saveBtn.disabled = false;
            
            timerInterval = setInterval(function() {
                totalSeconds++;
                updateTimerDisplay();
            }, 1000);
        }
    });

    document.querySelector('form').addEventListener('submit', function() {
        if (timerRunning) {
            clearInterval(timerInterval);
            timerRunning = false;
            cycleTimeInput.value = totalSeconds;
        }
    });

    // Auto-fill Customer based on Item Selection
    $('#item_id').on('change', function() {
        var selectedOption = $(this).find(':selected');
        var customer = selectedOption.data('customer');
        $('#customer').val(customer || '');
    });
});
</script>
@endpush
