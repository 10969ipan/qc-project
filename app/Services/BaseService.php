<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Base Service Class
 * 
 * Provides common functionality for all service classes
 */
abstract class BaseService
{
    /**
     * Resolve a plant identifier to a UUID string.
     * 
     * @param mixed $identifier
     * @return string|null
     */
    protected function resolvePlantId($identifier)
    {
        return \App\Models\Plant::resolveId($identifier);
    }

    /**
     * Apply common filters to query
     * 
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $field => $value) {
            if (!empty($value)) {
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, 'like', '%' . $value . '%');
                }
            }
        }

        return $query;
    }

    /**
     * Apply date range filter
     * 
     * @param Builder $query
     * @param string $field
     * @param string|null $startDate
     * @param string|null $endDate
     * @return Builder
     */
    protected function applyDateRangeFilter(Builder $query, string $field, ?string $startDate, ?string $endDate): Builder
    {
        if ($startDate) {
            $query->whereDate($field, '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate($field, '<=', $endDate);
        }

        return $query;
    }

    /**
     * Handle file upload
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $path
     * @param string|null $oldFilePath
     * @return string
     */
    protected function handleFileUpload($file, string $path, ?string $oldFilePath = null): string
    {
        // Delete old file if exists
        if ($oldFilePath && file_exists(public_path($oldFilePath))) {
            unlink(public_path($oldFilePath));
        }

        // Generate unique filename
        $filename = time() . '_' . $file->getClientOriginalName();

        // Create directory if not exists
        $uploadPath = public_path($path);
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Move file
        $file->move($uploadPath, $filename);

        return $path . '/' . $filename;
    }

    /**
     * Delete file from storage
     * 
     * @param string|null $filePath
     * @return bool
     */
    protected function deleteFile(?string $filePath): bool
    {
        if ($filePath && file_exists(public_path($filePath))) {
            try {
                return unlink(public_path($filePath));
            } catch (\Exception $e) {
                // Log error but generally return false or suppress so calling process continues
                \Illuminate\Support\Facades\Log::error("Failed to delete file at {$filePath}: " . $e->getMessage());
                return false;
            }
        }

        return false;
    }

    /**
     * Get pagination parameters from request
     * 
     * @param Request $request
     * @return array
     */
    protected function getPaginationParams(Request $request): array
    {
        return [
            'page' => $request->input('page', 1),
            'per_page' => $request->input('per_page', 10),
        ];
    }

    /**
     * Build query string from filters
     * 
     * @param array $filters
     * @return string
     */
    protected function buildQueryString(array $filters): string
    {
        $params = array_filter($filters, function ($value) {
            return !is_null($value) && $value !== '';
        });

        return http_build_query($params);
    }
}
