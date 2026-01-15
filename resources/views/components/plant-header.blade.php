@props(['title', 'plant' => null])

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        {{ $title }}
        @if($plant)
            <span class="badge badge-{{ $plant === 'jakarta' ? 'info' : 'primary' }} ml-2" style="font-size: 0.9rem;">
                <i class="fas fa-building mr-1"></i>
                Plant {{ ucfirst($plant) }}
            </span>
        @endif
    </h1>
    {{ $slot }}
</div>