<?php

namespace App\Filament\Resources\CourseModuleResource\RelationManagers;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LessonsRelationManager extends RelationManager
{
    protected static string $relationship = 'lessons';

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('title')->required()->maxLength(255),
            TextInput::make('position')->numeric()->integer()->minValue(1)->required()->default(1),
            Textarea::make('content')->required()->rows(10)->columnSpanFull()
                ->helperText('Plain text. Separate paragraphs with a blank line.'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('position')->sortable(),
                TextColumn::make('title')->searchable(),
            ])
            ->headerActions([CreateAction::make()])
            ->actions([
                EditAction::make(),
                DeleteAction::make()->modalDescription('Students\' progress on this lesson is deleted too.'),
            ])
            ->defaultSort('position');
    }
}
