<?php

namespace App\Filament\Resources;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Models\Product;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 3;

    private const MODES = ['online' => 'Online', 'offline' => 'Offline', 'hybrid' => 'Hybrid'];

    /** Select state can be the raw value or the enum itself depending on how it was hydrated. */
    private static function typeIs(Get $get, ProductType ...$types): bool
    {
        $current = $get('product_type');
        $current = $current instanceof BackedEnum ? $current->value : $current;

        return in_array($current, array_map(fn (ProductType $type) => $type->value, $types), true);
    }

    public static function form(Form $form): Form
    {
        $live = [ProductType::Webinar, ProductType::Workshop, ProductType::Bootcamp, ProductType::Mentoring];

        return $form->schema([
            Section::make('Basics')->columns(2)->schema([
                Select::make('category_id')->relationship('category', 'name')->required()->searchable()->preload(),
                Select::make('instructor_id')->relationship('instructor', 'name')->searchable()->preload()
                    ->helperText('Optional — the product page shows "Episteme" when empty.'),
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, Get $get, ?string $state) => $get('slug') ? null : $set('slug', Str::slug((string) $state))),
                TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true)
                    ->helperText('Part of the public URL — changing it breaks existing links.'),
                Textarea::make('short_description')->required()->maxLength(255)->rows(2)->columnSpanFull(),
                Textarea::make('description')->required()->rows(8)->columnSpanFull()
                    ->helperText('Separate paragraphs with a blank line.'),
            ]),

            Section::make('Classification & pricing')->columns(3)->schema([
                Select::make('product_type')
                    ->options(ProductType::options())
                    ->required()
                    ->live()
                    // Switching type would orphan the old subtype row, so it's fixed once created.
                    ->disabledOn('edit'),
                Select::make('level')->options(Product::LEVELS),
                TextInput::make('price')->required()->numeric()->integer()->minValue(0)->maxValue(100_000_000)->prefix('Rp')
                    ->helperText('0 = free. Maximum Rp100.000.000.'),
                Hidden::make('currency')->default('IDR'),
                Select::make('status')->options(collect(ProductStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()]))
                    ->default(ProductStatus::Draft->value)->required()
                    ->helperText('Only "Published" products are visible and purchasable.'),
                Toggle::make('featured'),
                DateTimePicker::make('published_at')->helperText('Filled automatically when first published.'),
                TextInput::make('thumbnail')->url()->maxLength(255)->columnSpanFull()
                    ->helperText('Image URL. Leave empty for the generated placeholder.'),
            ]),

            Section::make('Marketing copy')->schema([
                Repeater::make('outcomes')->simple(TextInput::make('outcome')->required())->defaultItems(0)->addActionLabel('Add outcome'),
                Repeater::make('requirements')->simple(TextInput::make('requirement')->required())->defaultItems(0)->addActionLabel('Add requirement'),
                Textarea::make('audience')->rows(2),
            ]),

            Section::make('Course details')->visible(fn (Get $get) => static::typeIs($get, ProductType::Course))->schema([
                Group::make()->relationship('course')->columns(2)->schema([
                    TextInput::make('duration_minutes')->numeric()->minValue(0)->maxValue(100000)->suffix('minutes'),
                    Select::make('delivery_type')->options(['self_paced' => 'Self-paced', 'cohort_based' => 'Cohort-based']),
                    TextInput::make('total_modules')->numeric()->minValue(0)->maxValue(1000)->helperText('Public count on the product page. Actual modules/lessons are managed under Learning → Course modules.'),
                    TextInput::make('total_lessons')->numeric()->minValue(0)->maxValue(10000),
                ]),
            ]),

            Section::make('Webinar details')->visible(fn (Get $get) => static::typeIs($get, ProductType::Webinar))->schema([
                Group::make()->relationship('webinar')->columns(2)->schema([
                    DatePicker::make('event_date'),
                    TextInput::make('timezone')->default('WIB')->required()->maxLength(20),
                    TimePicker::make('start_time')->seconds(false),
                    TimePicker::make('end_time')->seconds(false),
                    TextInput::make('platform')->maxLength(255)->helperText('Platform name only (e.g. Zoom). The join link goes under "Member access".'),
                ]),
            ]),

            Section::make('Workshop details')->visible(fn (Get $get) => static::typeIs($get, ProductType::Workshop))->schema([
                Group::make()->relationship('workshop')->columns(2)->schema([
                    DatePicker::make('event_date'),
                    TextInput::make('duration_minutes')->numeric()->minValue(0)->maxValue(100000)->suffix('minutes'),
                    Select::make('mode')->options(self::MODES)->default('online')->required(),
                    TextInput::make('location')->maxLength(255),
                ]),
            ]),

            Section::make('Bootcamp details')->visible(fn (Get $get) => static::typeIs($get, ProductType::Bootcamp))->schema([
                Group::make()->relationship('bootcamp')->columns(2)->schema([
                    TextInput::make('duration')->maxLength(255)->placeholder('12 minggu'),
                    TextInput::make('schedule')->maxLength(255),
                    TextInput::make('cohort')->maxLength(255),
                    Select::make('delivery_mode')->options(self::MODES)->default('online')->required(),
                ]),
            ]),

            Section::make('Mentoring details')->visible(fn (Get $get) => static::typeIs($get, ProductType::Mentoring))->schema([
                Group::make()->relationship('mentoring')->columns(2)->schema([
                    TextInput::make('session_duration_minutes')->numeric()->minValue(0)->maxValue(100000)->suffix('minutes'),
                    TextInput::make('number_of_sessions')->numeric()->minValue(1)->maxValue(1000)->default(1)->required(),
                    Select::make('session_type')->options(['1_on_1' => '1-on-1', 'group' => 'Group'])->default('1_on_1')->required(),
                    Select::make('delivery_mode')->options(self::MODES)->default('online')->required(),
                ]),
            ]),

            Section::make('Digital product details')->visible(fn (Get $get) => static::typeIs($get, ProductType::DigitalProduct))->schema([
                Group::make()->relationship('digitalProduct')->columns(2)->schema([
                    TextInput::make('file_type')->maxLength(50)->placeholder('PDF'),
                    TextInput::make('file_size')->maxLength(50)->placeholder('12 MB'),
                    TextInput::make('access_type')->maxLength(255)->placeholder('Lifetime download'),
                    FileUpload::make('file_path')
                        ->label('File')
                        ->disk('local')
                        ->directory('digital-products')
                        ->visibility('private')
                        ->helperText('Stored on the private disk; only enrolled buyers can download it.'),
                ]),
            ]),

            Section::make('Member access')
                ->description('Shown only to enrolled users on their learning page — never on the public product page.')
                ->visible(fn (Get $get) => static::typeIs($get, ...$live))
                ->schema([
                    Group::make()->relationship('access')->schema([
                        TextInput::make('join_url')->url()->maxLength(255),
                        Textarea::make('instructions')->rows(4),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable()->limit(45),
                TextColumn::make('product_type')->badge()->formatStateUsing(fn ($state) => $state->label()),
                TextColumn::make('category.name')->sortable(),
                TextColumn::make('status')->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state) => match ($state) {
                        ProductStatus::Published => 'success',
                        ProductStatus::Draft => 'warning',
                        ProductStatus::Archived => 'gray',
                    }),
                TextColumn::make('price')->sortable()->formatStateUsing(fn ($state) => $state === 0 ? 'Free' : 'Rp'.number_format($state, 0, ',', '.')),
                IconColumn::make('featured')->boolean(),
                TextColumn::make('enrollments_count')->counts('enrollments')->label('Enrolled')->sortable(),
            ])
            ->filters([
                SelectFilter::make('product_type')->options(ProductType::options()),
                SelectFilter::make('status')->options(collect(ProductStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])),
                SelectFilter::make('category')->relationship('category', 'name'),
                TernaryFilter::make('featured'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * A product that has been bought can't be deleted: enrollments cascade with it, which
     * would silently take access away from paying customers. Archive it instead.
     */
    public static function canDelete(Model $record): bool
    {
        return ! $record->enrollments()->exists() && ! $record->orderItems()->exists();
    }

    /** First time a product goes live, record when. */
    public static function stampPublishedAt(array $data): array
    {
        $status = $data['status'] ?? null;
        $status = $status instanceof BackedEnum ? $status->value : $status;

        if ($status === ProductStatus::Published->value && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
