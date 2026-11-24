<?php

namespace App\Filament\Resources\CourseModuleResource\Pages;

use App\Filament\Resources\CourseModuleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCourseModule extends EditRecord
{
    protected static string $resource = CourseModuleResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
