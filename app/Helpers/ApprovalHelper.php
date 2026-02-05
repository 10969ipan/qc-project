<?php

if (!function_exists('getApprovalLabel')) {
    /**
     * Get approval label based on plant and level
     * 
     * @param string $level
     * @param string|null $plant
     * @return string
     */
    function getApprovalLabel(string $level, $plant = null): string
    {
        // If no plant specified, use current request plant or auth user plant
        if (!$plant) {
            $plant = request('plant') ?? auth()->user()->plant ?? 'karawang';
        }

        // If plant is an object (Eloquent Model), get the code or name
        if (is_object($plant)) {
            $plant = $plant->code ?? $plant->name ?? 'karawang';
        }

        $labels = [
            'karawang' => [
                'karu_qc' => 'Kepala Regu QC',
                'kashift_plating' => 'Kashift Plating',
                'supervisor_plating' => 'Supervisor Plating',
                'manager_plating' => 'Manager Plating',
                'kashift' => 'Kashift QC',
                'supervisor' => 'Supervisor QC',
                'asst_manager' => 'Asst Manager QC',
                'manager' => 'Manager QC',
            ],
            'jakarta' => [
                'karu_qc' => 'Kepala Regu',
                'kashift_plating' => 'Kashift Plating',
                'supervisor_plating' => 'Supervisor Plating',
                'manager_plating' => 'Manager Plating',
                'kashift' => 'Kepala Regu', // Requested: Karu/Kepala Regu
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
     * @param string|mixed $plant
     * @return string
     */
    function getApprovalLabelShort(string $level, $plant = null): string
    {
        // If no plant specified, use current request plant or auth user plant
        if (!$plant) {
            $plant = request('plant') ?? auth()->user()->plant ?? 'karawang';
        }

        // If plant is an object (Eloquent Model), get the code or name
        if (is_object($plant)) {
            $plant = $plant->code ?? $plant->name ?? 'karawang';
        }

        $labels = [
            'karawang' => [
                'karu_qc' => 'Karu QC',
                'kashift_plating' => 'Kashift Ptg',
                'supervisor_plating' => 'SPV Ptg',
                'manager_plating' => 'Mgr Ptg',
                'kashift' => 'Kashift',
                'supervisor' => 'Supervisor',
                'asst_manager' => 'Asst Manager',
                'manager' => 'Manager',
            ],
            'jakarta' => [
                'karu_qc' => 'Karu',
                'kashift_plating' => 'Kashift Ptg',
                'supervisor_plating' => 'SPV Ptg',
                'manager_plating' => 'Mgr Ptg',
                'kashift' => 'Kepala Regu',  // Requested: Karu/Kepala Regu
                'supervisor' => 'Supervisor',
                'asst_manager' => 'Asst Manager',
                'manager' => 'Manager',
            ],
        ];

        return $labels[strtolower($plant)][$level] ?? $labels['karawang'][$level];
    }
}

if (!function_exists('getRoleDisplayName')) {
    /**
     * Get display name for a role
     * 
     * @param string $role
     * @return string
     */
    function getRoleDisplayName(string $role): string
    {
        $roles = [
            'admin' => 'Administrator',
            'oshef' => 'Sub Administrator',
            'inspector' => 'Inspector QC',
            'karu_qc' => 'Kepala Regu',
            'supervisor' => 'Supervisor QC',
            'asst_manager' => 'Asst Manager QC',
            'manager' => 'Manager QC',
            'kashift_plating' => 'Kashift Plating',
            'supervisor_plating' => 'Supervisor Plating',
            'manager_plating' => 'Manager Plating',
            'kashift' => 'Kashift QC',
        ];

        return $roles[strtolower($role)] ?? ucfirst(str_replace('_', ' ', $role));
    }
}

if (!function_exists('getRejectorName')) {
    /**
     * Parse rejector name from rejection remarks
     * Remarks Format: [Role] Reason - Name (Date)
     * 
     * @param string|null $remarks
     * @return string|null
     */
    function getRejectorName(?string $remarks): ?string
    {
        if (!$remarks)
            return null;

        // Pattern to match " - Name (Date)" at the end
        // Example: ... - John Doe (17/01/2026 20:04)
        if (preg_match('/ - (.*?) \(\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}\)$/', $remarks, $matches)) {
            return $matches[1];
        }

        return null;
    }
}

if (!function_exists('getApprovalField')) {
    function getApprovalField($type)
    {
        $fields = [
            'karu_qc' => 'karu_qc',
            'kashift_plating' => 'kashift_plating',
            'supervisor_plating' => 'supervisor_plating',
            'supervisor' => 'supervisor_qc',
            'manager_plating' => 'manager_plating',
            'manager' => 'manager_qc',
            'kashift' => 'kashift_qc',
            'asst_manager' => 'asst_manager_qc',
            'inspector' => 'inspector',
        ];
        return $fields[$type] ?? $type;
    }
}

if (!function_exists('getApprovalDateField')) {
    function getApprovalDateField($type)
    {
        $fields = [
            'karu_qc' => 'karu_qc_approved_at',
            'kashift_plating' => 'kashift_plating_approved_at',
            'supervisor_plating' => 'supervisor_plating_approved_at',
            'supervisor' => 'supervisor_approved_at',
            'manager_plating' => 'manager_plating_approved_at',
            'manager' => 'manager_approved_at',
            'kashift' => 'kashift_approved_at',
            'asst_manager' => 'asst_manager_approved_at',
        ];
        return $fields[$type] ?? "{$type}_approved_at";
    }
}
