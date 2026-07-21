<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'original_name' => $this->original_name,
            'download_url' => route('api.provider.onboarding.documents.show', $this->id),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
