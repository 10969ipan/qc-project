@props(['level', 'plant' => null])

@php
    $label = getApprovalLabel($level, $plant);
@endphp

{{ $label }}
