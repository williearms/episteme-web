<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Filament\Resources\OrderResource\Pages\ViewOrder;
use App\Filament\Resources\OrderResource\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\PaymentsRelationManager;
use App\Models\Order;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Read-only on purpose. An order's status only ever changes through the payment
 * provider's verified webhook; letting an admin edit it here would create orders
 * that say "paid" with no matching payment (and no enrollment).
 */
class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $navigationGroup = 'Sales';

    private static function statusColor(OrderStatus $status): string
    {
        return match ($status) {
            OrderStatus::Paid => 'success',
            OrderStatus::Pending => 'warning',
            OrderStatus::Cancelled, OrderStatus::Expired => 'danger',
        };
    }

    // OrderPolicy only lets an order's owner view it (the storefront rule), so the
    // admin panel checks the admin flag directly instead of going through the Gate.
    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->is_admin;
    }

    public static function canView(Model $record): bool
    {
        return static::canViewAny();
    }

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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Order')->columns(3)->schema([
                TextEntry::make('order_number'),
                TextEntry::make('status')->badge()
                    ->formatStateUsing(fn (OrderStatus $state) => $state->label())
                    ->color(fn (OrderStatus $state) => static::statusColor($state)),
                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('user.name')->label('Customer'),
                TextEntry::make('user.email')->label('Email'),
                TextEntry::make('total')->formatStateUsing(fn ($state) => $state === 0 ? 'Free' : 'Rp'.number_format($state, 0, ',', '.')),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')->searchable()->sortable(),
                TextColumn::make('user.email')->label('Customer')->searchable(),
                TextColumn::make('status')->badge()
                    ->formatStateUsing(fn (OrderStatus $state) => $state->label())
                    ->color(fn (OrderStatus $state) => static::statusColor($state)),
                TextColumn::make('total')->sortable()->formatStateUsing(fn ($state) => $state === 0 ? 'Free' : 'Rp'.number_format($state, 0, ',', '.')),
                TextColumn::make('items_count')->counts('items')->label('Items'),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(collect(OrderStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])),
            ])
            ->actions([ViewAction::make()])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [ItemsRelationManager::class, PaymentsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'view' => ViewOrder::route('/{record}'),
        ];
    }
}
