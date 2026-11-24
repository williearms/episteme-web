<?php

namespace App\Filament\Resources;

use App\Enums\ProductType;
use App\Filament\Resources\CourseModuleResource\Pages\CreateCourseModule;
use App\Filament\Resources\CourseModuleResource\Pages\EditCourseModule;
use App\Filament\Resources\CourseModuleResource\Pages\ListCourseModules;
use App\Filament\Resources\CourseModuleResource\RelationManagers\LessonsRelationManager;
use App\Models\Course;
use App\Models\CourseModule;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CourseModuleResource extends Resource
{
    protected static ?string $model = CourseModule::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationGroup = 'Learning';

    protected static ?string $navigationLabel = 'Course modules';

    protected static ?string $modelLabel = 'module';

    protected static ?int $navigationSort = 1;

    /** @return array<int, string> course id => product title, for courses only */
    private static function courseOptions(): array
    {
        return Course::query()
            ->whereHas('product', fn ($query) => $query->where('product_type', ProductType::Course))
            ->with('product')
            ->get()
            ->mapWithKeys(fn (Course $course) => [$course->id => $course->product->title])
            ->all();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('course_id')
                ->label('Course')
                ->options(fn () => static::courseOptions())
                ->searchable()
                ->required(),
            TextInput::make('title')->required()->maxLength(255),
            TextInput::make('position')->numeric()->integer()->minValue(1)->required()
                ->default(fn () => 1)
                ->helperText('Order within the course (1 = first).'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('course.product.title')->label('Course')->searchable()->sortable(),
                TextColumn::make('position')->sortable(),
                TextColumn::make('title')->searchable(),
                TextColumn::make('lessons_count')->counts('lessons')->label('Lessons'),
            ])
            ->filters([
                SelectFilter::make('course_id')->label('Course')->options(fn () => static::courseOptions()),
            ])
            ->actions([EditAction::make(), DeleteAction::make()->requiresConfirmation()
                ->modalDescription('This also deletes the module\'s lessons and every student\'s progress on them.')])
            ->defaultSort('position');
    }

    public static function getRelations(): array
    {
        return [LessonsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourseModules::route('/'),
            'create' => CreateCourseModule::route('/create'),
            'edit' => EditCourseModule::route('/{record}/edit'),
        ];
    }
}
