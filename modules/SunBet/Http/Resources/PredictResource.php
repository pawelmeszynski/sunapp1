<?php

namespace SunAppModules\SunBet\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PredictResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'data' => $this->resource->toArray(),

        ];
    }
}
