<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteErpProductResource\Pages;
use App\Filament\Resources\SiteErpProductResource\RelationManagers;
use App\Models\SiteErpProduct;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Faxt\Invenbin\Models\ErpProduct;
use App\Models\Site;

class SiteErpProductResource extends Resource
{
    protected static ?string $model = SiteErpProduct::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'My Site';
    
    protected static ?string $navigationLabel = 'My Products';
    
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Dropdown to select the site
                Forms\Components\Select::make('site_id')
                    ->label('Site')
                    ->options(Site::all()->pluck('site_name', 'id'))  // Assuming `name` is the field you want to display
                    ->required(),

                // Dropdown to select the product
                Forms\Components\Select::make('erp_product_id')
                    ->label('Product')
                    ->options(function ($get) {
                        $siteId = $get('site_id');
                        if ($siteId) {
                            // Find the site and retrieve the associated products through the site_erp_products pivot table
                            $site = Site::find($siteId);
                            
                            // Get products related to the site via the erpProducts relationship
                            return $site->erpProducts()->pluck('product_name', 'erp_product_id'); 
                        }
                        return ErpProduct::pluck('product_name', 'id'); // Return all products if no site is selected (optional fallback)
                    })
                    ->required(),
            ]);


    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            // Display the Site name
            Tables\Columns\TextColumn::make('site.site_name')->label('Site'),
            
            // Display the Product name through the pivot relationship
            Tables\Columns\TextColumn::make('erpProduct.product_name')
                ->label('Product')
                ->sortable()
                ->searchable(),
        ])
        ->filters([
            //
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
    
    }
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();
        
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }
        
        // If user is super admin, show all products
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return $query;
        }
        
        // For site admins (instructors), only show products for their site
        try {
            $siteId = $user->getUserOwnerSiteId();
            if ($siteId) {
                return $query->where('site_id', $siteId);
            }
        } catch (\Throwable $e) {}
        
        // If we can't determine the site, don't show any products
        return $query->whereRaw('1 = 0');
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSiteErpProducts::route('/'),
            'create' => Pages\CreateSiteErpProduct::route('/create'),
            'edit' => Pages\EditSiteErpProduct::route('/{record}/edit'),
        ];
    }
}
