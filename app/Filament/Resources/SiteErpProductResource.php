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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
    public static function tableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        
        return Site::query()
            ->whereHas('erpProducts', function ($subQuery) {
                // Get the team IDs that the user belongs to
                $userTeamIds = auth()->user()->teams->pluck('id');
    
                // Ensure the products are only from sites associated with the user's teams
                $subQuery->whereHas('teams', function ($siteQuery) use ($userTeamIds) {
                    $siteQuery->whereIn('team_id', $userTeamIds);
                });
            });
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
