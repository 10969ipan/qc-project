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
                                <th>Item Part / Shift</th>
                                <th>Tanggal Produksi / QC</th>
                                <th>Hasil Cross Cut</th>
                                <th>Kimia</th>
                                <th>Posisi Remark (Judgement / No Lot)</th>
                                <th>Result Remark / Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <!-- Item Part / Shift -->
                                <td class="align-middle">
                                    <div class="form-group mb-2">
                                        <label for="item_id">Item Part</label>
                                        <select class="form-control" id="item_id" name="item_id" required>
                                            <option value="">-- Select Item --</option>
                                            @foreach($items as $item)
                                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label for="shift">Shift</label>
                                        <select class="form-control" id="shift" name="shift" required>
                                            <option value="">-- Select Shift --</option>
                                            <option value="A">A</option>
                                            <option value="B">B</option>
                                            <option value="C">C</option>
                                        </select>
                                    </div>
                                </td>
                                <!-- Tanggal Produksi / QC -->
                                <td class="align-middle">
                                    <div class="form-group mb-2">
                                        <label for="production_datetime">Tanggal Jam Produksi</label>
                                        <input type="datetime-local" class="form-control" id="production_datetime" name="production_datetime" required>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label for="qc_datetime">Tanggal Jam QC</label>
                                        <input type="datetime-local" class="form-control" id="qc_datetime" name="qc_datetime" required>
                                    </div>
                                </td>
                                <!-- Hasil Cross Cut (Image) -->
                                <td class="align-middle text-center">
                                    <label for="image">Ambil Gambar</label>
                                    <input type="file" class="form-control-file" id="image" name="image" accept="image/*" required>
                                </td>
                                <!-- Kimia -->
                                <td class="align-middle">
                                    <div class="form-group mb-2">
                                        <label for="chemical_copper">Copper</label>
                                        <input type="text" class="form-control" id="chemical_copper" name="chemical_copper">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label for="chemical_nikel">Nikel</label>
                                        <input type="text" class="form-control" id="chemical_nikel" name="chemical_nikel">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label for="chemical_eching">Eching</label>
                                        <input type="text" class="form-control" id="chemical_eching" name="chemical_eching">
                                    </div>
                                    <div class="form-group mb-0">
                                        <label for="chemical_abu">Abu</label>
                                        <input type="text" class="form-control" id="chemical_abu" name="chemical_abu">
                                    </div>
                                </td>
                                <!-- Posisi Remark -->
                                <td class="align-middle">
                                    <div class="form-group mb-2">
                                        <label for="position_remark_judgment">Judgment</label>
                                        <select class="form-control" id="position_remark_judgment" name="position_remark_judgment" required>
                                            <option value="OK">OK</option>
                                            <option value="NG">NG</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label for="position_remark_no_lot">No Lot</label>
                                        <input type="text" class="form-control" id="position_remark_no_lot" name="position_remark_no_lot" required>
                                    </div>
                                </td>
                                <!-- Result Remark / Keterangan -->
                                <td class="align-middle">
                                    <div class="form-group mb-2">
                                        <label for="result_remark">Result Remark</label>
                                        <input type="text" class="form-control" id="result_remark" name="result_remark">
                                    </div>
                                    <div class="form-group mb-0">
                                        <label for="keterangan">Keterangan</label>
                                        <textarea class="form-control" id="keterangan" name="keterangan" rows="2"></textarea>
                                    </div>
                                </td>
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
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Timer Logic (Cycle Time) ---
        var timerInterval = null;
        var totalSeconds = 0;
        var timerRunning = false;

        function updateTimerDisplay() {
            var hours = Math.floor(totalSeconds / 3600);
            var minutes = Math.floor((totalSeconds % 3600) / 60);
            var seconds = totalSeconds % 60;

            var text = 
                (hours < 10 ? "0" + hours : hours) + ":" + 
                (minutes < 10 ? "0" + minutes : minutes) + ":" + 
                (seconds < 10 ? "0" + seconds : seconds);
            
            $('#timerDisplay').text(text);
            $('#cycleTimeInput').val(totalSeconds);
        }

        $('#startTimerBtn').click(function() {
            if (!timerRunning) {
                timerRunning = true;
                $(this).removeClass('btn-success').addClass('btn-secondary').attr('disabled', true).html('<i class="fas fa-clock"></i> Running...');
                $('#saveBtn').prop('disabled', false);
                
                timerInterval = setInterval(function() {
                    totalSeconds++;
                    updateTimerDisplay();
                }, 1000);
            }
        });

        // Stop timer on form submit
        $('form').on('submit', function() {
            if (timerRunning) {
                clearInterval(timerInterval);
                timerRunning = false;
                // Update final value
                $('#cycleTimeInput').val(totalSeconds);
            }
        });
    });
</script>
@endpush
