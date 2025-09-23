<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class BaseApiController extends Controller
{
    /**
     * The model associated with the controller
     */
    protected string $model;

    /**
     * The resource class for transforming model data
     */
    protected ?string $resource = null;

    /**
     * Default pagination size
     */
    protected int $perPage = 15;

    /**
     * Maximum pagination size
     */
    protected int $maxPerPage = 100;

    /**
     * Fields that can be searched
     */
    protected array $searchableFields = [];

    /**
     * Fields that can be filtered
     */
    protected array $filterableFields = [];

    /**
     * Fields that can be sorted
     */
    protected array $sortableFields = [];

    /**
     * Default sort field
     */
    protected string $defaultSortField = 'created_at';

    /**
     * Default sort direction
     */
    protected string $defaultSortDirection = 'desc';

    /**
     * Get all resources with pagination, filtering, and sorting
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = $this->model::query();

            // Apply search
            $this->applySearch($query, $request);

            // Apply filters
            $this->applyFilters($query, $request);

            // Apply sorting
            $this->applySorting($query, $request);

            // Get pagination parameters
            $perPage = min(
                $request->get('per_page', $this->perPage),
                $this->maxPerPage
            );

            // Paginate results
            $results = $query->paginate($perPage);

            // Transform using resource if available
            if ($this->resource) {
                $results->getCollection()->transform(function ($item) {
                    return new $this->resource($item);
                });
            }

            return ApiResponse::paginated(
                $results,
                'Resources retrieved successfully'
            );

        } catch (\Exception $e) {
            Log::error('Error retrieving resources', [
                'controller' => static::class,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return ApiResponse::serverError('Failed to retrieve resources');
        }
    }

    /**
     * Show a specific resource
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $resource = $this->model::findOrFail($id);

            $data = $this->resource ? new $this->resource($resource) : $resource;

            return ApiResponse::success(
                $data,
                'Resource retrieved successfully'
            );

        } catch (NotFoundHttpException $e) {
            return ApiResponse::notFound('Resource not found');
        } catch (\Exception $e) {
            Log::error('Error retrieving resource', [
                'controller' => static::class,
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return ApiResponse::serverError('Failed to retrieve resource');
        }
    }

    /**
     * Store a new resource
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $this->validateStoreRequest($request);
            
            $resource = $this->model::create($validatedData);

            $data = $this->resource ? new $this->resource($resource) : $resource;

            return ApiResponse::created(
                $data,
                'Resource created successfully'
            );

        } catch (ValidationException $e) {
            return ApiResponse::validationError(
                $e->errors(),
                'Validation failed'
            );
        } catch (\Exception $e) {
            Log::error('Error creating resource', [
                'controller' => static::class,
                'data' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return ApiResponse::serverError('Failed to create resource');
        }
    }

    /**
     * Update a specific resource
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $resource = $this->model::findOrFail($id);
            
            $validatedData = $this->validateUpdateRequest($request, $resource);
            
            $resource->update($validatedData);

            $data = $this->resource ? new $this->resource($resource) : $resource;

            return ApiResponse::success(
                $data,
                'Resource updated successfully'
            );

        } catch (NotFoundHttpException $e) {
            return ApiResponse::notFound('Resource not found');
        } catch (ValidationException $e) {
            return ApiResponse::validationError(
                $e->errors(),
                'Validation failed'
            );
        } catch (\Exception $e) {
            Log::error('Error updating resource', [
                'controller' => static::class,
                'id' => $id,
                'data' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return ApiResponse::serverError('Failed to update resource');
        }
    }

    /**
     * Delete a specific resource
     */
    public function destroy($id): JsonResponse
    {
        try {
            $resource = $this->model::findOrFail($id);
            
            $resource->delete();

            return ApiResponse::success(
                null,
                'Resource deleted successfully'
            );

        } catch (NotFoundHttpException $e) {
            return ApiResponse::notFound('Resource not found');
        } catch (\Exception $e) {
            Log::error('Error deleting resource', [
                'controller' => static::class,
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return ApiResponse::serverError('Failed to delete resource');
        }
    }

    /**
     * Apply search to query
     */
    protected function applySearch($query, Request $request): void
    {
        $search = $request->get('search');
        
        if ($search && !empty($this->searchableFields)) {
            $query->where(function ($q) use ($search) {
                foreach ($this->searchableFields as $field) {
                    $q->orWhere($field, 'LIKE', "%{$search}%");
                }
            });
        }
    }

    /**
     * Apply filters to query
     */
    protected function applyFilters($query, Request $request): void
    {
        foreach ($this->filterableFields as $field) {
            $value = $request->get($field);
            
            if ($value !== null) {
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, $value);
                }
            }
        }
    }

    /**
     * Apply sorting to query
     */
    protected function applySorting($query, Request $request): void
    {
        $sortField = $request->get('sort_by', $this->defaultSortField);
        $sortDirection = $request->get('sort_direction', $this->defaultSortDirection);

        // Validate sort field
        if (!in_array($sortField, $this->sortableFields)) {
            $sortField = $this->defaultSortField;
        }

        // Validate sort direction
        if (!in_array(strtolower($sortDirection), ['asc', 'desc'])) {
            $sortDirection = $this->defaultSortDirection;
        }

        $query->orderBy($sortField, $sortDirection);
    }

    /**
     * Validate store request - to be implemented by child controllers
     */
    abstract protected function validateStoreRequest(Request $request): array;

    /**
     * Validate update request - to be implemented by child controllers
     */
    abstract protected function validateUpdateRequest(Request $request, Model $resource): array;
}
