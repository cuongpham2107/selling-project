<?php

namespace App\Filament\Pages;

use App\Filament\Actions\BuyProductAction;
use App\Models\ShopProduct;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Pages\Page;
use Filament\QueryBuilder\Constraints\NumberConstraint;
use Filament\QueryBuilder\Constraints\RelationshipConstraint;
use Filament\QueryBuilder\Constraints\RelationshipConstraint\Operators\IsRelatedToOperator;
use Filament\QueryBuilder\Constraints\SelectConstraint;
use Filament\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Columns\Layout\View;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Table;
use UnitEnum;

class Market extends Page implements HasActions, HasTable
{
    use HasPageShield;
    use InteractsWithActions;
    use InteractsWithTable;

    protected string $view = 'filament.pages.market';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static string|UnitEnum|null $navigationGroup = 'Mua bán';

    protected static ?string $navigationLabel = 'Chợ';

    protected static ?string $title = '';

    protected ?string $heading = '';

    public ?int $selectedCategoryId = null;

    public function filterByCategory(?int $categoryId): void
    {
        $this->selectedCategoryId = $categoryId;
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ShopProduct::query()
                    ->with(['seller', 'categories'])
                    ->where('status', 'active')
                    ->whereJsonLength('stock', '>', 0)
                    ->when($this->selectedCategoryId, function ($query) {
                        $query->whereHas('categories', function ($q) {
                            $q->where('shop_categories.id', $this->selectedCategoryId);
                        });
                    })
                    // Explicitly select only safe columns, loading 'stock' for the accessor but keeping it hidden from JSON serialization
                    ->select([
                        'id',
                        'user_id',
                        'name',
                        'description',
                        'image_url',
                        'price',
                        'stock',
                        'status',
                        'created_at',
                        'updated_at',
                    ])
            )
            ->columns([
                TextColumn::make('name')
                    ->hidden(),
                View::make('markets.table.custom-row-content'),
            ])
            ->contentGrid([
                '' => 2,
                'sm' => 2,
                'md' => 3,
                'lg' => 4,
                'xl' => 6,
            ])
            ->filters([
                \Filament\Tables\Filters\QueryBuilder::make()
                    ->constraints([
                        TextConstraint::make('name')
                            ->label('Tên sản phẩm'),
                        NumberConstraint::make('price')
                            ->label('Giá'),
                        SelectConstraint::make('status')
                            ->label('Trạng thái')
                            ->options([
                                // active', 'sold', 'deleted', 'banned'
                                'active' => 'Đang hoạt động',
                                'sold' => 'Đã bán',
                                'deleted' => 'Đã xóa',
                                'banned' => 'Đã khóa',
                            ]),
                        RelationshipConstraint::make('categories')
                            ->label('Danh mục')
                            ->selectable(
                                IsRelatedToOperator::make()
                                    ->titleAttribute('name')
                                    ->searchable()
                                    ->multiple(),
                            ),
                        DateConstraint::make('created_at')
                            ->label('Ngày tạo'),
                    ])
                    ->constraintPickerColumns(2),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->deferFilters(false)
            ->defaultPaginationPageOption(25)
            ->actions([
                BuyProductAction::make(),
            ])
            ->headerActions([
                // ...
            ]);
    }
}
