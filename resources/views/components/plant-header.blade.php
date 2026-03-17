@props(['title', 'plant' => null])

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        {{ $title }}
        @if($plant)
            @php
                if (is_object($plant)) {
                    $plantCode = $plant->code;
                } else {
                    $plantCode = (is_string($plant) && strlen($plant) > 30) ? \App\Models\Plant::where('id', $plant)->value('code') : (string) $plant;
                }
                $plantCode = strtolower($plantCode);
            @endphp
            <span
                class="badge badge-{{ $plantCode === 'jakarta' ? 'info' : 'primary' }} d-block d-md-inline-block ml-md-2 mt-2 mt-md-0"
                style="font-size: 0.9rem; width: fit-content;">
                <i class="fas fa-building mr-1"></i>
                Plant {{ ucfirst($plantCode) }}
            </span>
        @endif
    </h1>
    {{ $slot }}
</div>
