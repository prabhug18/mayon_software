<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\DateHelper;

class UserResource extends BaseApiResource
{
    use DateHelper;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return array_merge(parent::toArray($request), [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'role' => $this->roles->pluck('name'),
            'status' => $this->status,
            'created_at' => $this->formatDate($this->created_at),
            'updated_at' => $this->formatDate($this->updated_at),
        ]);
    }
}
