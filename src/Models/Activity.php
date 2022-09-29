<?php

namespace MFrouh\ActivityModel\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = ['title_ar', 'title_en', 'message_ar', 'message_en', 'user_id', 'activity_type', 'activity_id', 'data'];

    public function activity(): morphTo
    {
        return $this->morphTo();
    }
}
