<?php

namespace Modules\AssesmentModule\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Media extends Model
{
     use HasFactory;
    protected $fillable = [
        'name',
        'path',
        'type',
        'size',
        'collection_name',
        'mediable_id','mediable_type'
    ];
    protected $casts =[
        'size' => 'integer'
    ];
    public function mediable(){
        return $this->morphTo();
    }
    public function url():Attribute{
        return Attribute::make(
            get: fn () => str_starts_with($this->path,'http') ? $this->path : Storage::disk('public')->url($this->path)
        );
    }
}
