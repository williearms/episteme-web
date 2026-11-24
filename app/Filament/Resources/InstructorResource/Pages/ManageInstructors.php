<?php

namespace App\Filament\Resources\InstructorResource\Pages;

use App\Filament\Resources\InstructorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageInstructors extends ManageRecords
{
    protected static string $resource = InstructorResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
