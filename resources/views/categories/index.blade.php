@extends('layouts.admin')

@section('title', 'Kategori Item')

@section('content')
    <x-plant-header title="Master Data Kategori" :plant="request()->get('plant')" />
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Kategori</h6>
            @if(!in_array(auth()->user()->role, ['manager', 'asst_manager', 'inspector']))
                <button type="button" class="btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#modalAddCategory">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Kategori
                </button>
            @endif
        </div>
        <div class="card-body">
            @if(auth()->user()->role === 'admin')
                <form action="{{ route('admin.categories.index') }}" method="GET" class="mb-4">
                    <div class="row align-items-end">
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3">
                            <label for="plant_select" class="font-weight-bold">Plant Context</label>
                            <select name="plant" id="plant_select" class="form-control form-control-sm shadow-sm"
                                onchange="this.form.submit()">
                                <option value="" {{ !request('plant') ? 'selected' : '' }}>Semua Plant</option>
                                <option value="karawang" {{ request('plant') == 'karawang' ? 'selected' : '' }}>Karawang</option>
                                <option value="jakarta" {{ request('plant') == 'jakarta' ? 'selected' : '' }}>Jakarta</option>
                            </select>
                        </div>
                    </div>
                </form>
            @endif

            @php
                /** @var \Illuminate\Support\ViewErrorBag $errors */
            @endphp
            @if(isset($errors) && method_exists($errors, 'any') && $errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kategori</th>
                            <th>Jumlah Item</th>
                            <th>Plant</th>
                            @if(!in_array(auth()->user()->role, ['manager', 'asst_manager', 'inspector']))
                                <th>Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $index => $category)
                            <tr>
                                <td>{{ $categories->firstItem() + $index }}</td>
                                <td>{{ $category->name }}</td>
                                <td>
                                    <span class="badge badge-info">{{ $category->items_count }} item</span>
                                </td>
                                <td>
                                    <span
                                        class="badge {{ optional($category->plant)->code === 'jakarta' ? 'badge-primary' : 'badge-info' }}">
                                        {{ strtoupper(optional($category->plant)->name ?? '-') }}
                                    </span>
                                </td>
                                @if(!in_array(auth()->user()->role, ['manager', 'asst_manager', 'inspector']))
                                    <td class="text-nowrap">
                                        <button type="button" class="btn btn-warning btn-sm btn-edit-category" data-id="{{ $category->id }}"
                                            style="min-width: 80px;">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="plant" value="{{ request('plant') }}">
                                            <button type="submit" class="btn btn-danger btn-sm delete-btn"
                                                style="min-width: 80px;">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>

                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                        @for($i = count($categories); $i < 10; $i++)
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                @if(!in_array(auth()->user()->role, ['manager', 'asst_manager', 'inspector']))
                                    <td>&nbsp;</td>
                                @endif
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $categories->links() }}
            </div>
        </div>
    </div>


    <!-- Modal Tambah Kategori -->
    <div class="modal fade" id="modalAddCategory" tabindex="-1" role="dialog" aria-labelledby="modalAddCategoryLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalAddCategoryLabel">Tambah Kategori Baru</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.categories.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plant" value="{{ request('plant') }}">
                    <div class="modal-body">

                        <div class="form-group">
                            <label class="font-weight-bold">Plant</label>
                            <input type="text" class="form-control bg-light"
                                value="{{ strtoupper(optional(auth()->user()->plant)->name ?? 'DEFAULT') }}" readonly>
                            <small class="text-muted">Kategori akan otomatis didaftarkan untuk plant ini.</small>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="Contoh: Sub Assy">
                            <small class="text-muted">Nama kategori harus unik</small>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm px-4">
                            <i class="fas fa-save mr-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Kategori -->
    <div class="modal fade" id="modalEditCategory" tabindex="-1" role="dialog" aria-labelledby="modalEditCategoryLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="modalEditCategoryLabel">Edit Kategori</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formEditCategory" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="plant" value="{{ request('plant') }}">
                    <div class="modal-body text-left">

                        <div class="form-group">
                            <label class="font-weight-bold">Plant</label>
                            <input type="text" id="edit_plant_name" class="form-control bg-light" readonly>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_category_name" class="form-control" required placeholder="Contoh: Sub Assy">
                            <small class="text-muted">Nama kategori harus unik</small>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning btn-sm px-4">
                            <i class="fas fa-save mr-1"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.btn-edit-category').on('click', function() {
                var id = $(this).data('id');
                var btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: `/admin/categories/${id}/edit`,
                    type: 'GET',
                    success: function(response) {
                        $('#edit_category_name').val(response.category.name);
                        $('#edit_plant_name').val(response.plant ? response.plant.name.toUpperCase() : '-');
                        
                        var url = "{{ route('admin.categories.update', ':id') }}";
                        url = url.replace(':id', id);
                        $('#formEditCategory').attr('action', url);
                        
                        $('#modalEditCategory').modal('show');
                        btn.prop('disabled', false).html('<i class="fas fa-edit"></i> Edit');
                    },
                    error: function() {
                        alert('Gagal mengambil data kategori.');
                        btn.prop('disabled', false).html('<i class="fas fa-edit"></i> Edit');
                    }
                });
            });

            // SweetAlert for Delete Confirmation
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    const form = this.closest('form');

                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: "Data kategori ini akan dihapus permanen!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endpush
