<?php

namespace App\Filament\Resources\CourseModuleResource\RelationManagers;

use App\Support\LessonAttachment;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class LessonsRelationManager extends RelationManager
{
    protected static string $relationship = 'lessons';

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('title')->required()->maxLength(255),
            TextInput::make('position')->numeric()->integer()->minValue(1)->maxValue(65535)->required()->default(1),
            RichEditor::make('content')
                ->required()
                ->columnSpanFull()
                ->toolbarButtons([
                    'h2', 'h3', 'bold', 'italic', 'strike', 'link', 'attachFiles',
                    'bulletList', 'orderedList', 'blockquote', 'codeBlock', 'undo', 'redo',
                ])
                // Public disk so students' browsers can load the images (run `php artisan
                // storage:link` once). Filament has no type/size limit for attachments, so
                // the upload goes through LessonAttachment, which allows only real images.
                ->fileAttachmentsDisk(LessonAttachment::DISK)
                ->fileAttachmentsDirectory(LessonAttachment::DIRECTORY)
                ->fileAttachmentsVisibility('public')
                ->saveUploadedFileAttachmentsUsing(fn (TemporaryUploadedFile $file) => LessonAttachment::store($file))
                ->helperText('Formatted text and images (PNG, JPG, WebP, GIF, up to 2 MB). Scripts and unsafe markup are stripped when students view the lesson.'),
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
