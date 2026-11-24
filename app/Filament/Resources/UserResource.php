<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Users are listed, not edited: names/emails/passwords belong to their owners
 * (Phase 4's account area). The one thing staff may change is the admin flag,
 * via an explicit, confirmed action — never a form field.
 */
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Users';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable()->sortable(),
                IconColumn::make('is_admin')->label('Admin')->boolean(),
                IconColumn::make('email_verified_at')->label('Verified')->boolean(),
                TextColumn::make('orders_count')->counts('orders')->label('Orders')->sortable(),
                TextColumn::make('enrollments_count')->counts('enrollments')->label('Enrollments')->sortable(),
                TextColumn::make('created_at')->label('Joined')->dateTime()->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_admin')->label('Admin'),
            ])
            ->actions([
                Action::make('makeAdmin')
                    ->label('Make admin')
                    ->icon('heroicon-o-shield-check')
                    ->requiresConfirmation()
                    ->modalDescription('This user will be able to sign in to the admin panel and manage products, orders, and access.')
                    ->visible(fn (User $record) => ! $record->is_admin)
                    ->action(function (User $record) {
                        $record->forceFill(['is_admin' => true])->save();
                        Notification::make()->title("{$record->email} is now an admin")->success()->send();
                    }),
                Action::make('removeAdmin')
                    ->label('Remove admin')
                    ->icon('heroicon-o-shield-exclamation')
                    ->color('danger')
                    ->requiresConfirmation()
                    // You can't demote yourself, so there is always at least one admin left.
                    ->visible(fn (User $record) => $record->is_admin && $record->id !== auth()->id())
                    ->action(function (User $record) {
                        $record->forceFill(['is_admin' => false])->save();
                        Notification::make()->title("{$record->email} is no longer an admin")->success()->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => ListUsers::route('/')];
    }
}
