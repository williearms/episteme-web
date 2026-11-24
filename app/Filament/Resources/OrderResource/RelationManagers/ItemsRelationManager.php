<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_title')
            ->columns([
                TextColumn::make('product_title')->label('Product (as purchased)'),
                TextColumn::make('product_type')->badge(),
                TextColumn::make('quantity'),
                TextColumn::make('unit_price')->formatStateUsing(fn ($state) => $state === 0 ? 'Free' : 'Rp'.number_format($state, 0, ',', '.')),
                TextColumn::make('subtotal')->formatStateUsing(fn ($state) => $state === 0 ? 'Free' : 'Rp'.number_format($state, 0, ',', '.')),
            ]);
    }
}
