<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => $this->when($this->user, [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
            ]),
            'action' => $this->action,
            'description' => $this->description,
            'url' => $this->url,
            'method' => $this->method,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'payload' => $this->when(
                $request->user()?->hasRole('super_admin'),
                $this->payload
            ),
            'response_code' => $this->response_code,
            'duration_ms' => $this->duration_ms,
            'api_version' => $this->api_version,
            'metadata' => $this->when(
                $request->user()?->hasRole('super_admin'),
                $this->metadata
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Computed fields
            'is_error' => $this->response_code >= 400,
            'response_type' => $this->getResponseType(),
            'duration_category' => $this->getDurationCategory(),
            'formatted_duration' => $this->getFormattedDuration(),
            'time_ago' => $this->created_at?->diffForHumans(),
        ];
    }

    /**
     * Get response type based on status code
     */
    protected function getResponseType(): string
    {
        return match (true) {
            $this->response_code >= 500 => 'server_error',
            $this->response_code >= 400 => 'client_error',
            $this->response_code >= 300 => 'redirect',
            $this->response_code >= 200 => 'success',
            default => 'unknown'
        };
    }

    /**
     * Get duration category
     */
    protected function getDurationCategory(): string
    {
        return match (true) {
            $this->duration_ms >= 5000 => 'very_slow',
            $this->duration_ms >= 2000 => 'slow',
            $this->duration_ms >= 1000 => 'moderate',
            $this->duration_ms >= 500 => 'fast',
            default => 'very_fast'
        };
    }

    /**
     * Get formatted duration
     */
    protected function getFormattedDuration(): string
    {
        if ($this->duration_ms >= 1000) {
            return round($this->duration_ms / 1000, 2) . 's';
        }
        
        return $this->duration_ms . 'ms';
    }
}
