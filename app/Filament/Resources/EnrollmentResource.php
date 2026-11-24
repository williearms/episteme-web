<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnrollmentResource\Pages\ManageEnrollments;
use App\Models\Enrollment;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

class EnrollmentResource extends Resource
{
    protected static ?string $model = Enrollment::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationGroup = 'Learning';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('user_id')->label('User')->relationship('user', 'email')->searchable()->preload()->required()->live(),
            Select::make('product_id')->label('Product')->relationship('product', 'title')->searchable()->preload()->required()
                ->unique(
                    ignoreRecord: true,
                    modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('user_id', $get('user_id')),
                )
                ->validationMessages(['unique' => 'This user already has access to this product.']),
            DateTimePicker::make('enrolled_at')->default(now())->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.email')->label('User')->searchable()->sortable(),
                TextColumn::make('product.title')->label('Product')->searchable()->limit(40),
                TextColumn::make('order.order_number')->label('Source')->placeholder('Manual grant'),
                TextColumn::make('enrolled_at')->dateTime()->sortable(),
                IconColumn::make('completed_at')->label('Completed')->boolean(),
            ])
            ->actions([
                DeleteAction::make()
                    ->label('Revoke')
                    ->modalHeading('Revoke access')
                    ->modalDescription('The user loses access immediately, and their lesson progress is deleted. The order itself is not touched.'),
            ])
            ->defaultSort('enrolled_at', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => ManageEnrollments::route('/')];
    }
}
