<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InstructorResource\Pages\ManageInstructors;
use App\Models\Instructor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class InstructorResource extends Resource
{
    protected static ?string $model = Instructor::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(fn (Set $set, Get $get, ?string $state) => $get('slug') ? null : $set('slug', Str::slug((string) $state))),
            TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true),
            TextInput::make('headline')->maxLength(255),
            Textarea::make('bio')->rows(4),
            TextInput::make('avatar')->url()->maxLength(255)->helperText('Image URL. Leave empty to show initials.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('headline')->limit(50),
                TextColumn::make('products_count')->counts('products')->label('Products')->sortable(),
            ])
            ->actions([
                EditAction::make(),
                // Deleting is safe (products keep existing, instructor becomes null) but easy to do by accident.
                DeleteAction::make()->requiresConfirmation(),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return ['index' => ManageInstructors::route('/')];
    }
}
