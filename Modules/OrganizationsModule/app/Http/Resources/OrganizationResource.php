<?php
namespace Modules\OrganizationsModule\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,

            'media' => [
                'logo' => [
                    'original' => $this->getFirstMediaUrl('logo'),
                    'thumb' => $this->getFirstMediaUrl('logo', 'thumb'),
                    'optimized' => $this->getFirstMediaUrl('logo', 'optimized'),
                ],
            ],

            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
