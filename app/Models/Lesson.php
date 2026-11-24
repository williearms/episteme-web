<?php

namespace App\Models;

use App\Support\RichText;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lesson extends Model
{
    protected $fillable = ['course_module_id', 'title', 'content', 'position'];

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }

    /** The only form of `content` that may be printed unescaped. */
    public function getContentHtmlAttribute(): string
    {
        return RichText::sanitize($this->content);
    }
}
