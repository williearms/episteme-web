<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuizResource\Pages\CreateQuiz;
use App\Filament\Resources\QuizResource\Pages\EditQuiz;
use App\Filament\Resources\QuizResource\Pages\ListQuizzes;
use App\Models\CourseModule;
use App\Models\Quiz;
use Closure;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class QuizResource extends Resource
{
    protected static ?string $model = Quiz::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Learning';

    protected static ?string $navigationLabel = 'Quizzes';

    protected static ?int $navigationSort = 2;

    /** @return array<int, string> module id => "Course — Module" */
    private static function moduleOptions(): array
    {
        return CourseModule::query()
            ->with('course.product')
            ->orderBy('course_id')->orderBy('position')
            ->get()
            ->mapWithKeys(fn (CourseModule $module) => [$module->id => $module->course->product->title.' — '.$module->title])
            ->all();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Quiz')->columns(2)->schema([
                Select::make('course_module_id')
                    ->label('Module')
                    ->options(fn () => static::moduleOptions())
                    ->searchable()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->validationMessages(['unique' => 'This module already has a quiz.'])
                    ->helperText('One quiz per module. For a final assessment, put it in the last module.'),
                TextInput::make('title')->required()->maxLength(255),
                Textarea::make('description')->rows(2)->columnSpanFull(),
                TextInput::make('pass_percentage')->numeric()->integer()->minValue(1)->maxValue(100)->default(70)->required()->suffix('%'),
            ]),

            Section::make('Questions')
                ->description('One correct option = students see radio buttons. More than one = checkboxes, and every correct option must be chosen (and no others) to score the question.')
                ->schema([
                    Repeater::make('questions')
                        ->relationship()
                        ->orderColumn('position')
                        ->minItems(1)
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['body'] ?? null)
                        ->addActionLabel('Add question')
                        ->schema([
                            Textarea::make('body')->label('Question')->required()->rows(2),
                            Repeater::make('options')
                                ->relationship()
                                ->orderColumn('position')
                                ->minItems(2)
                                ->addActionLabel('Add option')
                                ->schema([
                                    TextInput::make('body')->label('Option')->required()->maxLength(255),
                                    Toggle::make('is_correct')->label('Correct')->inline(false),
                                ])
                                ->columns(2)
                                ->rules([
                                    fn () => function (string $attribute, mixed $value, Closure $fail) {
                                        if (! collect($value)->contains(fn ($option) => ! empty($option['is_correct']))) {
                                            $fail('Mark at least one option as correct.');
                                        }
                                    },
                                ]),
                            Textarea::make('explanation')->rows(2)
                                ->helperText('Shown to the student after they submit — never before.'),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('module.course.product.title')->label('Course')->searchable()->limit(35),
                TextColumn::make('module.title')->label('Module'),
                TextColumn::make('title')->searchable(),
                TextColumn::make('questions_count')->counts('questions')->label('Questions'),
                TextColumn::make('pass_percentage')->label('Pass mark')->suffix('%'),
                TextColumn::make('attempts_count')->counts('attempts')->label('Attempts'),
            ])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->defaultSort('id', 'desc');
    }

    /** Deleting a quiz deletes every student's attempts and grades with it. */
    public static function canDelete(Model $record): bool
    {
        return ! $record->attempts()->exists();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQuizzes::route('/'),
            'create' => CreateQuiz::route('/create'),
            'edit' => EditQuiz::route('/{record}/edit'),
        ];
    }
}
