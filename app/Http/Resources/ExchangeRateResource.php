<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExchangeRateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'referenceId'   => $this->referenceId,
            'service_type'  => $this->service_type,
            'currency'      => $this->currency,
            'buy_rate'      => $this->buy_rate,
            'sell_rate'     => $this->sell_rate,
            'created_at'    => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at'    => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
