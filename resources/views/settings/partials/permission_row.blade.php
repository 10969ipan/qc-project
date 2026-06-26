@php
    $isDeep      = $level > 0;
    $hasChildren = $menu->children->isNotEmpty();
    $rootMenuId  = $level === 0 ? $menu->id : ($rootMenuId ?? $menu->parent_id);

    // Build ancestor class chain so every descendant gets child-check-{ancestorId}
    // for EACH ancestor — enabling cascade-off at any depth.
    $ancestorIds      = $ancestorIds ?? [];
    $myChildClasses   = $level === 0
        ? 'parent-check'
        : implode(' ', array_map(fn($id) => "child-check-{$id}", $ancestorIds));

    // Ancestors to pass down to this menu's children
    $childAncestorIds = array_merge($ancestorIds, [$menu->id]);

    // Indent per level (level 1 = no additional indent, level 2 = 18px, etc.)
    $indent = ($level > 1) ? ($level - 1) * 18 : 0;
@endphp

<div class="permission-row-item {{ $isDeep ? 'sub-module-row' : 'parent-module-row' }}"
     data-level="{{ $level }}"
     data-menu-id="{{ $menu->id }}"
     @if($isDeep) data-belongs-to="{{ $rootMenuId }}" @endif>

    {{-- ── Module Name Column ────────────────────────────────── --}}
    <div class="permission-name">

        @if($level === 0)
            {{-- ── LEVEL 0: "Semua" row — cascade via card header master toggle ── --}}
            <div class="d-flex align-items-center">
                <span class="cascade-badge mr-2" title="Mengubah baris ini cascade ke semua sub-modul">
                    <i class="fas fa-layer-group"></i>
                </span>
                <span>
                    @if($hasChildren)
                        <span class="font-weight-bold">Semua</span>
                        <span class="text-muted" style="font-size:0.78rem;font-weight:400;"> — cascade ke sub-modul</span>
                    @else
                        <span class="font-weight-bold">{{ $menu->name }}</span>
                    @endif
                </span>
            </div>

        @elseif($hasChildren)
            {{-- ── LEVEL 1+: Intermediate node (has children) → show power toggle ── --}}
            <div class="d-flex align-items-center" style="padding-left:{{ $indent }}px;">
                <button type="button"
                        class="sub-module-toggle mr-2"
                        data-menu-id="{{ $menu->id }}"
                        title="Nonaktifkan sub-modul ini beserta isinya">
                    <i class="fas fa-power-off"></i>
                </button>
                <i class="fas fa-folder-open text-muted mr-2" style="font-size:0.72rem;flex-shrink:0;"></i>
                <span class="font-weight-semibold">{{ $menu->name }}</span>
                <span class="sub-count-badge ml-1">{{ $menu->children->count() }}</span>
            </div>

        @else
            {{-- ── LEAF NODE: no children → power toggle clears only this row's checkboxes ── --}}
            <div class="d-flex align-items-center" style="padding-left:{{ $indent }}px;">
                <button type="button"
                        class="sub-module-toggle mr-2"
                        data-menu-id="{{ $menu->id }}"
                        title="Nonaktifkan baris ini">
                    <i class="fas fa-power-off"></i>
                </button>
                <i class="fas fa-level-up-alt fa-rotate-90 text-muted mr-2"
                   style="font-size:0.65rem;opacity:0.45;flex-shrink:0;"></i>
                <span>{{ $menu->name }}</span>
            </div>
        @endif
    </div>

    {{-- ── Permission Checkboxes ─────────────────────────────── --}}
    @foreach(['view', 'input', 'edit', 'approve', 'approve_all', 'export'] as $type)
    <div class="custom-control custom-switch custom-switch-success custom-switch-md d-inline-block">
        <input type="checkbox"
               class="custom-control-input perm-check {{ $myChildClasses }}"
               id="{{ $type }}_{{ $menu->id }}"
               {{ ($permissions[$menu->id]->{"can_$type"} ?? false) ? 'checked' : '' }}
               data-menu-id="{{ $menu->id }}"
               data-root-id="{{ $rootMenuId }}"
               data-type="{{ $type }}"
               data-level="{{ $level }}">
        <label class="custom-control-label" for="{{ $type }}_{{ $menu->id }}"></label>
    </div>
    @endforeach
</div>

@if($hasChildren)
    @foreach($menu->children as $child)
        @include('settings.partials.permission_row', [
            'menu'            => $child,
            'level'           => $level + 1,
            'rootMenuId'      => $rootMenuId,
            'ancestorIds'     => $childAncestorIds,
        ])
    @endforeach
@endif
