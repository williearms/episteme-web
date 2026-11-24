<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Enums\PaymentStatus;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** Every payment attempt for the order. Deliberately does not show raw_response. */
class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('provider_payment_id')
            ->columns([
                TextColumn::make('provider_payment_id')->label('Reference')->placeholder('—'),
                TextColumn::make('provider'),
                TextColumn::make('status')->badge()
                    ->formatStateUsing(fn (PaymentStatus $state) => $state->label())
                    ->color(fn (PaymentStatus $state) => match ($state) {
                        PaymentStatus::Paid => 'success',
                        PaymentStatus::Pending => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('amount')->formatStateUsing(fn ($state) => $state === 0 ? 'Free' : 'Rp'.number_format($state, 0, ',', '.')),
                TextColumn::make('paid_at')->dateTime()->placeholder('—'),
                TextColumn::make('created_at')->dateTime(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
