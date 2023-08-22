<?php

namespace Northplay\NorthplayApi\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Northplay\NorthplayApi\Models\SoftswissGameTagModel;
use Northplay\NorthplayApi\Resources\GameTagResource;

class GameRowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "slug" => $this->slug,
            "title" => $this->title,
            "provider" => $this->provider,
            "active" => $this->active,
            "tags" => GameTagResource::collection(SoftswissGameTagModel::where('game_id', $this->id)->get()),
        ];
    }
}
