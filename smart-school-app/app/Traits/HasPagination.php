<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Has Pagination Trait
 * 
 * Prompt 299: Add Server-Side Pagination, Search, and Filters
 * 
 * Provides reusable pagination, search, and filter functionality
 * for controllers. Supports both standard pagination and DataTables format.
 */
trait HasPagination
{
    /**
     * Default items per page.
     *
     * @var int
     */
    protected int $defaultPerPage = 15;

    /**
     * Maximum items per page.
     *
     * @var int
     */
    protected int $maxPerPage = 100;

    /**
     * Apply pagination to a query.
     *
     * @param Builder $query
     * @param Request $request
     * @return LengthAwarePaginator
     */
    protected function paginate(Builder $query, Request $request): LengthAwarePaginator
    {
        $perPage = $this->getPerPage($request);
        $page = $request->input('page', 1);

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get items per page from request.
     *
     * @param Request $request
     * @return int
     */
    protected function getPerPage(Request $request): int
    {
        $perPage = $request->input('per_page', $this->defaultPerPage);
        
        // DataTables uses 'length' parameter
        if ($request->has('length')) {
            $perPage = $request->input('length');
        }

        return min(max(1, (int) $perPage), $this->maxPerPage);
    }

    /**
     * Apply search to a query.
     *
     * @param Builder $query
     * @param Request $request
     * @param array $searchableColumns
     * @return Builder
     */
    protected function applySearch(Builder $query, Request $request, array $searchableColumns): Builder
    {
        $search = $request->input('search');
        
        // DataTables sends search as array with 'value' key
        if (is_array($search)) {
            $search = $search['value'] ?? '';
        }

        if (empty($search) || empty($searchableColumns)) {
            return $query;
        }

        return $query->where(function ($q) use ($search, $searchableColumns) {
            foreach ($searchableColumns as $column) {
                if (str_contains($column, '.')) {
                    // Handle relationship columns (e.g., 'user.name')
                    [$relation, $field] = explode('.', $column, 2);
                    $q->orWhereHas($relation, function ($subQuery) use ($field, $search) {
                        $subQuery->where($field, 'like', "%{$search}%");
                    });
                } else {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            }
        });
    }

    /**
     * Apply filters to a query.
     *
     * @param Builder $query
     * @param Request $request
     * @param array $filterableColumns
     * @return Builder
     */
    protected function applyFilters(Builder $query, Request $request, array $filterableColumns): Builder
    {
        foreach ($filterableColumns as $column => $type) {
            $value = $request->input($column);
            
            if ($value === null || $value === '') {
                continue;
            }

            switch ($type) {
                case 'exact':
                    $query->where($column, $value);
                    break;
                    
                case 'like':
                    $query->where($column, 'like', "%{$value}%");
                    break;
                    
                case 'boolean':
                    $query->where($column, filter_var($value, FILTER_VALIDATE_BOOLEAN));
                    break;
                    
                case 'date':
                    $query->whereDate($column, $value);
                    break;
                    
                case 'date_from':
                    $query->whereDate($column, '>=', $value);
                    break;
                    
                case 'date_to':
                    $query->whereDate($column, '<=', $value);
                    break;
                    
                case 'in':
                    if (is_array($value)) {
                        $query->whereIn($column, $value);
                    }
                    break;
                    
                case 'between':
                    if (is_array($value) && count($value) === 2) {
                        $query->whereBetween($column, $value);
                    }
                    break;
                    
                case 'null':
                    if ($value === 'true' || $value === true) {
                        $query->whereNull($column);
                    } else {
                        $query->whereNotNull($column);
                    }
                    break;
                    
                case 'relation':
                    // Handle relationship filters (e.g., 'class_id' => 'relation')
                    $query->where($column, $value);
                    break;
            }
        }

        return $query;
    }

    /**
     * Apply date range filter to a query.
     *
     * @param Builder $query
     * @param Request $request
     * @param string $column
     * @param string $fromParam
     * @param string $toParam
     * @return Builder
     */
    protected function applyDateRange(
        Builder $query,
        Request $request,
        string $column,
        string $fromParam = 'date_from',
        string $toParam = 'date_to'
    ): Builder {
        $from = $request->input($fromParam);
        $to = $request->input($toParam);

        if ($from) {
            $query->whereDate($column, '>=', $from);
        }

        if ($to) {
            $query->whereDate($column, '<=', $to);
        }

        return $query;
    }

    /**
     * Apply sorting to a query.
     *
     * @param Builder $query
     * @param Request $request
     * @param array $sortableColumns
     * @param string $defaultSort
     * @param string $defaultDirection
     * @return Builder
     */
    protected function applySorting(
        Builder $query,
        Request $request,
        array $sortableColumns,
        string $defaultSort = 'created_at',
        string $defaultDirection = 'desc'
    ): Builder {
        $sortColumn = $request->input('sort', $defaultSort);
        $sortDirection = $request->input('direction', $defaultDirection);
        
        // DataTables sorting
        if ($request->has('order') && is_array($request->input('order'))) {
            $order = $request->input('order')[0] ?? [];
            $columns = $request->input('columns', []);
            
            if (isset($order['column']) && isset($columns[$order['column']])) {
                $sortColumn = $columns[$order['column']]['data'] ?? $defaultSort;
                $sortDirection = $order['dir'] ?? $defaultDirection;
            }
        }

        // Validate sort column
        if (!in_array($sortColumn, $sortableColumns)) {
            $sortColumn = $defaultSort;
        }

        // Validate sort direction
        $sortDirection = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortColumn, $sortDirection);
    }

    /**
     * Build pagination metadata.
     *
     * @param LengthAwarePaginator $paginator
     * @return array
     */
    protected function buildPaginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'has_more_pages' => $paginator->hasMorePages(),
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ];
    }

    /**
     * Format response for DataTables.
     *
     * @param LengthAwarePaginator $paginator
     * @param Request $request
     * @param callable|null $transformer
     * @return array
     */
    protected function formatDataTablesResponse(
        LengthAwarePaginator $paginator,
        Request $request,
        ?callable $transformer = null
    ): array {
        $data = $paginator->items();
        
        if ($transformer) {
            $data = array_map($transformer, $data);
        }

        return [
            'draw' => (int) $request->input('draw', 1),
            'recordsTotal' => $paginator->total(),
            'recordsFiltered' => $paginator->total(),
            'data' => $data,
        ];
    }

    /**
     * Apply all query modifications (search, filter, sort, paginate).
     *
     * @param Builder $query
     * @param Request $request
     * @param array $options
     * @return LengthAwarePaginator
     */
    protected function applyQueryModifications(Builder $query, Request $request, array $options = []): LengthAwarePaginator
    {
        $searchableColumns = $options['searchable'] ?? [];
        $filterableColumns = $options['filterable'] ?? [];
        $sortableColumns = $options['sortable'] ?? ['created_at'];
        $defaultSort = $options['default_sort'] ?? 'created_at';
        $defaultDirection = $options['default_direction'] ?? 'desc';

        // Apply search
        if (!empty($searchableColumns)) {
            $this->applySearch($query, $request, $searchableColumns);
        }

        // Apply filters
        if (!empty($filterableColumns)) {
            $this->applyFilters($query, $request, $filterableColumns);
        }

        // Apply sorting
        $this->applySorting($query, $request, $sortableColumns, $defaultSort, $defaultDirection);

        // Paginate
        return $this->paginate($query, $request);
    }
}
