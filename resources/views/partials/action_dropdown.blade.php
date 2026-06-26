{{-- Action Dropdown (Three-dot menu) for checksheet rows
 Variables expected:
   $canEdit      bool
   $canDelete    bool
   $editUrl      string  (href for edit)
   $deleteRoute  string  (route string for delete form action)
   $statusUrl    string|null  (href for admin Status button, optional)
--}}
@if($canEdit || $canDelete || (isset($statusUrl) && $statusUrl))
<div class="dropdown d-inline-block">
    <button class="btn btn-light btn-sm border shadow-sm" type="button"
            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            style="width:32px;height:32px;border-radius:8px;padding:0;">
        <i class="fas fa-ellipsis-v text-secondary"></i>
    </button>
    <div class="dropdown-menu dropdown-menu-right shadow border-0" style="border-radius:8px;min-width:160px;">
        @if(isset($statusUrl) && $statusUrl)
            <a href="{{ $statusUrl }}" class="dropdown-item no-loader btn-status-modal">
                <i class="fas fa-user-check text-info fa-fw mr-2"></i> Status Approval
            </a>
            <div class="dropdown-divider"></div>
        @endif
        @if($canEdit)
            <a href="{{ $editUrl }}" class="dropdown-item no-loader btn-edit-modal">
                <i class="fas fa-edit text-warning fa-fw mr-2"></i> Edit
            </a>
        @endif
        @if($canDelete)
            <div class="dropdown-divider"></div>
            <form action="{{ $deleteRoute }}" method="POST" class="d-inline w-100">
                @csrf
                @method('DELETE')
                @if(isset($deleteParams))
                    @foreach($deleteParams as $key => $val)
                        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                    @endforeach
                @endif
                <button type="submit" class="dropdown-item text-danger btn-delete w-100 text-left">
                    <i class="fas fa-trash fa-fw mr-2"></i> Hapus
                </button>
            </form>
        @endif
    </div>
</div>
@endif
