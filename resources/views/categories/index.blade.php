@extends('layouts.admin')

@section('title', 'Kategori Item')

@section('content')
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Kategori</h6>
            @if(auth()->user()->role !== 'inspector')
                <a href="{{ route('admin.categories.create') }}" class="btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Kategori
                </a>
            @endif
        </div>
        <div class="card-body">
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
                            @if(auth()->user()->role !== 'inspector')
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
                                @if(auth()->user()->role !== 'inspector')
                                    <td class="text-nowrap">
                                        <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-warning btn-sm"
                                            style="min-width: 80px;">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?');"
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
                                @if(auth()->user()->role !== 'inspector')
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
@endsection