<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Stores images attached to lessons in the rich editor.
 *
 * Filament's RichEditor has no type or size limit on attachments, and these land on
 * the *public* disk. So the type is decided here from the file's actual content (never
 * the client's filename or declared type) and the stored name uses that detected
 * extension — an `.php`/`.html`/`.svg` upload, or a script renamed to `.png`, is rejected.
 */
class LessonAttachment
{
    public const DISK = 'public';

    public const DIRECTORY = 'lesson-attachments';

    public const MAX_KILOBYTES = 2048;

    private const ALLOWED = [
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    /** @return string path on the public disk */
    public static function store(UploadedFile $file): string
    {
        $extension = self::ALLOWED[$file->getMimeType() ?? ''] ?? null;

        if ($extension === null) {
            throw ValidationException::withMessages(['attachment' => 'Only PNG, JPG, WebP or GIF images can be attached.']);
        }

        if ($file->getSize() > self::MAX_KILOBYTES * 1024) {
            throw ValidationException::withMessages(['attachment' => 'Images can be at most '.(self::MAX_KILOBYTES / 1024).' MB.']);
        }

        return $file->storePubliclyAs(self::DIRECTORY, Str::uuid().'.'.$extension, self::DISK);
    }
}
