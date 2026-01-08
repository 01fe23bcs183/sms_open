<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Has DataTables Trait
 * 
 * Prompt 299: Add Server-Side Pagination, Search, and Filters
 * 
 * Provides DataTables-specific functionality for server-side processing.
 * Handles DataTables AJAX requests and returns properly formatted responses.
 */
trait HasDataTables
{
    use HasPagination;

    /**
     * Process a DataTables AJAX request.
     *
     * @param Builder $query
     * @param Request $request
     * @param array $columns Column definitions with searchable/orderable flags
     * @param callable|null $transformer Optional data transformer
     * @return JsonResponse
     */
    protected function processDataTablesRequest(
        Builder $query,
        Request $request,
        array $columns,
        ?callable $transformer = null
    ): JsonResponse {
        // Get total count before filtering
        $totalRecords = $query->count();

        // Apply global search
        $searchValue = $this->getDataTablesSearchValue($request);
        if ($searchValue) {
            $searchableColumns = $this->getSearchableColumns($columns);
            $this->applySearch($query, $request, $searchableColumns);
        }

        // Apply column-specific filters
        $this->applyColumnFilters($query, $request, $columns);

        // Get filtered count
        $filteredRecords = $query->count();

        // Apply ordering
        $this->applyDataTablesOrdering($query, $request, $columns);

        // Apply pagination
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        
        $data = $query->skip($start)->take($length)->get();

        // Transform data if transformer provided
        if ($transformer) {
            $data = $data->map($transformer);
        }

        return response()->json([
            'draw' => (int) $request->input('draw', 1),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data->values(),
        ]);
    }

    /**
     * Get DataTables search value.
     *
     * @param Request $request
     * @return string|null
     */
    protected function getDataTablesSearchValue(Request $request): ?string
    {
        $search = $request->input('search');
        
        if (is_array($search)) {
            return $search['value'] ?? null;
        }
        
        return $search;
    }

    /**
     * Get searchable columns from column definitions.
     *
     * @param array $columns
     * @return array
     */
    protected function getSearchableColumns(array $columns): array
    {
        $searchable = [];
        
        foreach ($columns as $column) {
            if (isset($column['searchable']) && $column['searchable'] && isset($column['data'])) {
                $searchable[] = $column['db'] ?? $column['data'];
            }
        }
        
        return $searchable;
    }

    /**
     * Apply column-specific filters from DataTables request.
     *
     * @param Builder $query
     * @param Request $request
     * @param array $columns
     * @return void
     */
    protected function applyColumnFilters(Builder $query, Request $request, array $columns): void
    {
        $requestColumns = $request->input('columns', []);
        
        foreach ($requestColumns as $index => $requestColumn) {
            $searchValue = $requestColumn['search']['value'] ?? null;
            
            if (empty($searchValue)) {
                continue;
            }
            
            $columnDef = $columns[$index] ?? null;
            
            if (!$columnDef || !($columnDef['searchable'] ?? false)) {
                continue;
            }
            
            $dbColumn = $columnDef['db'] ?? $columnDef['data'] ?? null;
            
            if (!$dbColumn) {
                continue;
            }
            
            // Apply filter based on column type
            $filterType = $columnDef['filter_type'] ?? 'like';
            
            switch ($filterType) {
                case 'exact':
                    $query->where($dbColumn, $searchValue);
                    break;
                    
                case 'like':
                    $query->where($dbColumn, 'like', "%{$searchValue}%");
                    break;
                    
                case 'date':
                    $query->whereDate($dbColumn, $searchValue);
                    break;
                    
                case 'date_range':
                    $dates = explode(' - ', $searchValue);
                    if (count($dates) === 2) {
                        $query->whereBetween($dbColumn, $dates);
                    }
                    break;
                    
                case 'boolean':
                    $query->where($dbColumn, filter_var($searchValue, FILTER_VALIDATE_BOOLEAN));
                    break;
                    
                case 'in':
                    $values = is_array($searchValue) ? $searchValue : explode(',', $searchValue);
                    $query->whereIn($dbColumn, $values);
                    break;
            }
        }
    }

    /**
     * Apply DataTables ordering.
     *
     * @param Builder $query
     * @param Request $request
     * @param array $columns
     * @return void
     */
    protected function applyDataTablesOrdering(Builder $query, Request $request, array $columns): void
    {
        $orders = $request->input('order', []);
        
        if (empty($orders)) {
            // Apply default ordering
            $query->orderBy('created_at', 'desc');
            return;
        }
        
        foreach ($orders as $order) {
            $columnIndex = $order['column'] ?? null;
            $direction = strtolower($order['dir'] ?? 'asc') === 'asc' ? 'asc' : 'desc';
            
            if ($columnIndex === null) {
                continue;
            }
            
            $columnDef = $columns[$columnIndex] ?? null;
            
            if (!$columnDef || !($columnDef['orderable'] ?? true)) {
                continue;
            }
            
            $dbColumn = $columnDef['db'] ?? $columnDef['data'] ?? null;
            
            if (!$dbColumn) {
                continue;
            }
            
            $query->orderBy($dbColumn, $direction);
        }
    }

    /**
     * Build column definitions for DataTables.
     *
     * @param array $columns Array of column configurations
     * @return array
     */
    protected function buildColumnDefinitions(array $columns): array
    {
        $definitions = [];
        
        foreach ($columns as $index => $column) {
            $definitions[$index] = [
                'data' => $column['data'] ?? $column['name'] ?? "column_{$index}",
                'name' => $column['name'] ?? $column['data'] ?? "column_{$index}",
                'db' => $column['db'] ?? $column['data'] ?? null,
                'searchable' => $column['searchable'] ?? true,
                'orderable' => $column['orderable'] ?? true,
                'filter_type' => $column['filter_type'] ?? 'like',
            ];
        }
        
        return $definitions;
    }

    /**
     * Create a simple DataTables response.
     *
     * @param Builder $query
     * @param Request $request
     * @param array $searchableColumns
     * @param array $sortableColumns
     * @param callable|null $transformer
     * @return JsonResponse
     */
    protected function simpleDataTablesResponse(
        Builder $query,
        Request $request,
        array $searchableColumns = [],
        array $sortableColumns = [],
        ?callable $transformer = null
    ): JsonResponse {
        // Build column definitions from searchable/sortable columns
        $allColumns = array_unique(array_merge($searchableColumns, $sortableColumns));
        $columns = [];
        
        foreach ($allColumns as $column) {
            $columns[] = [
                'data' => $column,
                'db' => $column,
                'searchable' => in_array($column, $searchableColumns),
                'orderable' => in_array($column, $sortableColumns),
            ];
        }
        
        return $this->processDataTablesRequest($query, $request, $columns, $transformer);
    }
}
