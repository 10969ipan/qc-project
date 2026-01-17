<?php

if (!function_exists('getApprovalLabel')) {
    /**
     * Get approval label based on plant and level
     * 
     * @param string $level
     * @param string|null $plant
     * @return string
     */
    function getApprovalLabel(string $level, ?string $plant = null): string
    {
        // If no plant specified, use current request plant or auth user plant
        if (!$plant) {
            $plant = request('plant') ?? auth()->user()->plant ?? 'karawang';
        }

        $labels = [
            'karawang' => [
                'kashift' => 'Kashift QC',
                'supervisor' => 'Supervisor QC',
                'asst_manager' => 'Asst Manager QC',
                'manager' => 'Manager QC',
            ],
            'jakarta' => [
                'kashift' => 'Kepala Regu QC',  // Different for Jakarta
                'supervisor' => 'Supervisor QC',
                'asst_manager' => 'Asst Manager QC',
                'manager' => 'Manager QC',
            ],
        ];

        return $labels[strtolower($plant)][$level] ?? $labels['karawang'][$level];
    }
}

if (!function_exists('getApprovalLabelShort')) {
    /**
     * Get short approval label based on plant and level
     * 
     * @param string $level
     * @param string|null $plant
     * @return string
     */
    function getApprovalLabelShort(string $level, ?string $plant = null): string
    {
        // If no plant specified, use current request plant or auth user plant
        if (!$plant) {
            $plant = request('plant') ?? auth()->user()->plant ?? 'karawang';
        }

        $labels = [
            'karawang' => [
                'kashift' => 'Kashift',
                'supervisor' => 'Supervisor',
                'asst_manager' => 'Asst Manager',
                'manager' => 'Manager',
            ],
            'jakarta' => [
                'kashift' => 'Kepala Regu',  // Different for Jakarta
                'supervisor' => 'Supervisor',
                'asst_manager' => 'Asst Manager',
                'manager' => 'Manager',
            ],
        ];

        return $labels[strtolower($plant)][$level] ?? $labels['karawang'][$level];
    }
}
