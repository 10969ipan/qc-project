<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kakotora extends Model
{
    protected $fillable = [
        'plant',
        'date',
        'no_reg',
        'issue_date',
        'rev_model',
        'family',
        'category_nm_mp',
        'category_claim',
        'model',
        'part_name',
        'part_number',
        'mould',
        'owner_mould',
        'similar_part',
        'section',
        'process',
        'problem',
        'cause',
        'countermeasure',
        'pic',
        'supplier',
        'defect_category',
        'status',
        'foto_path',
        'form_analysis_path',
        'remarks',
    ];

    /**
     * Get the URL for the form analysis file.
     */
    public function getFormAnalysisUrlAttribute()
    {
        return $this->form_analysis_path ? asset('storage/' . $this->form_analysis_path) : null;
    }

    /**
     * Get the URL for the foto file.
     */
    public function getFotoUrlAttribute()
    {
        return $this->foto_path ? asset('storage/' . $this->foto_path) : null;
    }

    /**
     * Safely format the date attribute.
     */
    public function formatDate(string $format = 'd/m/Y'): string
    {
        if (empty($this->date)) {
            return '-';
        }

        try {
            return \Carbon\Carbon::parse($this->date)->format($format);
        } catch (\Throwable $e) {
            return (string) $this->date;
        }
    }

    /**
     * Safely get array of formatted issue dates.
     */
    public function formatIssueDates(string $format = 'd/m/Y'): array
    {
        if (empty($this->issue_date)) {
            return [];
        }

        $dates = array_filter(array_map('trim', explode(',', $this->issue_date)));
        $result = [];

        foreach ($dates as $d) {
            try {
                $result[] = \Carbon\Carbon::parse($d)->format($format);
            } catch (\Throwable $e) {
                $result[] = $d;
            }
        }

        return $result;
    }
}

