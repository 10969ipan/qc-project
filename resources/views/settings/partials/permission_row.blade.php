@php
    $indentClass = 'pl-' . ($level * 4);
    $isDeep = $level > 0;
@endphp

<div class="permission-row-item">
    <div class="permission-name {{ $indentClass }} {{ $isDeep ? 'opacity-80' : 'text-primary' }}">
        @if($isDeep)
            <i class="fas fa-level-up-alt fa-rotate-90 text-muted mr-3 small opacity-50"></i>
        @else
            <i class="fas fa-star mr-2 small"></i>
        @endif
        {{ $menu->name }}
    </div>
    @foreach(['view', 'input', 'edit', 'approve', 'export'] as $type)
    <div class="custom-control custom-switch custom-switch-success custom-switch-md d-inline-block">
        <input type="checkbox" 
               class="custom-control-input {{ $level == 0 ? 'parent-check' : 'child-check-' . ($menu->parent_id ?: $menu->id) }}" 
               id="{{ $type }}_{{ $menu->id }}" 
               {{ ($permissions[$menu->id]->{"can_$type"} ?? false) ? 'checked' : '' }} 
               data-menu-id="{{ $menu->id }}" 
               data-type="{{ $type }}">
        <label class="custom-control-label" for="{{ $type }}_{{ $menu->id }}"></label>
    </div>
    @endforeach
</div>

@if($menu->children->isNotEmpty())
    @foreach($menu->children as $child)
        @include('settings.partials.permission_row', ['menu' => $child, 'level' => $level + 1])
    @endforeach
@endif
