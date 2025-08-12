<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ApiCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     */
    public $collects;

    /**
     * Additional meta data for the collection.
     */
    protected array $meta = [];

    /**
     * Create a new resource instance.
     */
    public function __construct($resource, string $collects = null, array $meta = [])
    {
        parent::__construct($resource);
        
        if ($collects) {
            $this->collects = $collects;
        }
        
        $this->meta = $meta;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => array_merge($this->getDefaultMeta(), $this->meta),
            'links' => $this->getLinks($request),
        ];
    }

    /**
     * Get default meta data.
     */
    protected function getDefaultMeta(): array
    {
        return [
            'total_items' => $this->collection->count(),
            'collection_type' => $this->collects ?? 'mixed',
            'generated_at' => now()->toISOString(),
        ];
    }

    /**
     * Get links for the collection.
     */
    protected function getLinks(Request $request): array
    {
        return [
            'self' => $request->url(),
            'first' => $request->url(),
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'success' => true,
            'message' => 'Data retrieved successfully',
            'timestamp' => now()->toISOString(),
            'request_id' => $request->header('X-Request-ID', uniqid()),
        ];
    }

    /**
     * Customize the pagination information for the resource.
     */
    public function paginationInformation(Request $request, array $paginated, array $default): array
    {
        return [
            'pagination' => [
                'current_page' => $paginated['current_page'],
                'last_page' => $paginated['last_page'],
                'per_page' => $paginated['per_page'],
                'total' => $paginated['total'],
                'from' => $paginated['from'],
                'to' => $paginated['to'],
                'has_more_pages' => $paginated['current_page'] < $paginated['last_page'],
                'links' => [
                    'first' => $paginated['first_page_url'],
                    'last' => $paginated['last_page_url'],
                    'prev' => $paginated['prev_page_url'],
                    'next' => $paginated['next_page_url'],
                ],
                'path' => $paginated['path'],
            ],
        ];
    }

    /**
     * Set additional meta data.
     */
    public function setMeta(array $meta): self
    {
        $this->meta = array_merge($this->meta, $meta);
        return $this;
    }

    /**
     * Add single meta item.
     */
    public function addMeta(string $key, $value): self
    {
        $this->meta[$key] = $value;
        return $this;
    }

    /**
     * Create collection with statistics.
     */
    public static function withStats($resource, string $collects = null, array $stats = []): self
    {
        return new self($resource, $collects, ['statistics' => $stats]);
    }

    /**
     * Create collection with filters applied.
     */
    public static function withFilters($resource, string $collects = null, array $filters = []): self
    {
        return new self($resource, $collects, ['applied_filters' => $filters]);
    }

    /**
     * Create collection with search results.
     */
    public static function withSearch($resource, string $collects = null, string $query = '', int $totalResults = 0): self
    {
        return new self($resource, $collects, [
            'search' => [
                'query' => $query,
                'total_results' => $totalResults,
                'results_count' => is_countable($resource) ? count($resource) : 0,
            ],
        ]);
    }
}
