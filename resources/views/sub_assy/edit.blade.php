@extends('layouts.admin')

@section('content')
    <x-plant-header title="Edit Data Checksheet" :plant="request('plant')" />
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Data Checksheet</h1>
        <a href="{{ route('admin.checksheets.index', ['plant' => request('plant')]) }}"
            class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Edit Checksheet</h6>
        </div>
        <div class="card-body">
            <form
                action="{{ route('admin.checksheets.update', ['checksheet' => $checksheet->id, 'plant' => request('plant')]) }}"
                method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="plant" value="{{ request('plant') }}">

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="item_id">Item Part</label>
                            <select name="item_id" id="item_id" class="form-control" required>
                                <option value="" disabled style="font-weight: bold; color: #6c757d;">Pilih Item Part
                                </option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}" {{ $checksheet->item_id == $item->id ? 'selected' : '' }}>
                                        {{ $item->name }} ({{ $item->customer }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date">Tanggal</label>
                            <input type="date" name="date" id="date" class="form-control" value="{{ $checksheet->date }}"
                                required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="shift">Shift</label>
                            <select name="shift" id="shift" class="form-control" required>
                                <option value="1" {{ $checksheet->shift == '1' ? 'selected' : '' }}>Shift 1</option>
                                <option value="2" {{ $checksheet->shift == '2' ? 'selected' : '' }}>Shift 2</option>
                                <option value="3" {{ $checksheet->shift == '3' ? 'selected' : '' }}>Shift 3</option>
                            </select>
                        </div>
                        @php
                            $plant = strtolower(auth()->user()->plant ?? $checksheet->plant ?? '');
                            $tableOptions = range(1, 15);
                            if ($plant === 'jakarta') {
                                $tableOptions = [1, 2, 4, 5, 6, 7, 8, 9, 10, 11];
                            }
                        @endphp
                        <div class="form-group">
                            <label for="line">Meja</label>
                            <select name="line" id="line" class="form-control" required>
                                <option value="">Pilih Meja</option>
                                @foreach ($tableOptions as $i)
                                    <option value="{{ $i }}" {{ $checksheet->line == $i ? 'selected' : '' }}>Meja {{ $i }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                @if(auth()->user()->role !== 'inspector')
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="jam_before">Jam (Before)</label>
                                <input type="time" name="jam_before" id="jam_before" class="form-control"
                                    value="{{ $checksheet->created_at->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="jam_after">Jam (After)</label>
                                <input type="time" name="jam_after" id="jam_after" class="form-control"
                                    value="{{ $checksheet->created_at->format('H:i') }}">
                            </div>
                        </div>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="total_qty">Total Qty</label>
                            <input type="number" name="total_qty" id="total_qty" class="form-control"
                                value="{{ $checksheet->total_qty }}" min="0" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="sampling_qty">Sampling Qty</label>
                            <input type="number" name="sampling_qty" id="sampling_qty" class="form-control"
                                value="{{ $checksheet->sampling_qty }}" min="0" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="total_ok">Total OK</label>
                            <input type="number" name="total_ok" id="total_ok" class="form-control"
                                value="{{ $checksheet->total_ok }}" min="0" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="total_ng">Total NG</label>
                            <input type="number" name="total_ng" id="total_ng" class="form-control"
                                value="{{ $checksheet->total_ng }}" min="0" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="judgment">Judgment</label>
                            <select name="judgment" id="judgment" class="form-control" required>
                                <option value="OK" {{ $checksheet->judgment == 'OK' ? 'selected' : '' }}>OK</option>
                                <option value="NG" {{ $checksheet->judgment == 'NG' ? 'selected' : '' }}>NG</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="operator_initials">Inisial Operator</label>
                            <input type="text" name="operator_initials" id="operator_initials" class="form-control"
                                value="{{ $checksheet->operator_initials }}">
                        </div>
                    </div>
                </div>

                <div id="nextProsesContainer" style="display: {{ $checksheet->judgment == 'NG' ? 'block' : 'none' }};">
                    <div class="form-group">
                        <label for="next_proses" class="text-danger font-weight-bold">Next Proses</label>
                        <select name="next_proses" id="next_proses" class="form-control">
                            <option value="">-- Pilih Next Proses --</option>
                            <option value="CRUSHING" {{ $checksheet->next_proses == 'CRUSHING' ? 'selected' : '' }}>CRUSHING
                            </option>
                            <option value="SORTIR" {{ $checksheet->next_proses == 'SORTIR' ? 'selected' : '' }}>SORTIR
                            </option>
                            <option value="FINISHING" {{ $checksheet->next_proses == 'FINISHING' ? 'selected' : '' }}>
                                FINISHING</option>
                            <option value="REPAIR" {{ $checksheet->next_proses == 'REPAIR' ? 'selected' : '' }}>REPAIR
                            </option>
                            @if($checksheet->next_proses && !in_array($checksheet->next_proses, ['CRUSHING', 'SORTIR', 'FINISHING', 'REPAIR']))
                                <option value="{{ $checksheet->next_proses }}" selected>{{ $checksheet->next_proses }}</option>
                            @endif
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="remarks">Keterangan</label>
                    <textarea name="remarks" id="remarks" class="form-control"
                        rows="3">{{ $checksheet->remarks }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const judgmentSelect = document.getElementById('judgment');
            const nextProsesContainer = document.getElementById('nextProsesContainer');
            const nextProsesSelect = document.getElementById('next_proses');

            function toggleNextProses() {
                if (judgmentSelect.value === 'NG') {
                    $(nextProsesContainer).slideDown();
                } else {
                    $(nextProsesContainer).slideUp();
                    nextProsesSelect.value = '';
                }
            }

            judgmentSelect.addEventListener('change', toggleNextProses);
        });
    </script>
@endpush