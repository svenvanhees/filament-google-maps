<?php

namespace Cheesegrits\FilamentGoogleMaps\Tests\Fields\Fixtures\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Cheesegrits\FilamentGoogleMaps\Tests\Fields\Fixtures\Resources\LocationResource\Pages\ListLocations;
use Cheesegrits\FilamentGoogleMaps\Tests\Fields\Fixtures\Resources\LocationResource\Pages\CreateLocation;
use Cheesegrits\FilamentGoogleMaps\Tests\Fields\Fixtures\Resources\LocationResource\Pages\ViewLocation;
use Cheesegrits\FilamentGoogleMaps\Tests\Fields\Fixtures\Resources\LocationResource\Pages\EditLocation;
use Cheesegrits\FilamentGoogleMaps\Columns\MapColumn;
// use App\Filament\Resources\LocationResource\RelationManagers;
use Cheesegrits\FilamentGoogleMaps\Fields\Geocomplete;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Cheesegrits\FilamentGoogleMaps\Filters\RadiusFilter;
use Cheesegrits\FilamentGoogleMaps\Tests\Fields\Fixtures\Resources\LocationResource\Pages;
use Cheesegrits\FilamentGoogleMaps\Tests\Models\Location;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LocationResource extends Resource
{
    protected static ?string $model = Location::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-collection';

    protected static ?string $recordTitleAttribute = 'name';

    protected static int $globalSearchResultsLimit = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->maxLength(256),
                TextInput::make('lat')
                    ->maxLength(32),
                TextInput::make('lng')
                    ->maxLength(32),
                TextInput::make('street')
                    ->maxLength(255),
                TextInput::make('city')
                    ->maxLength(255),
                TextInput::make('state')
                    ->maxLength(255),
                TextInput::make('zip')
                    ->maxLength(255),
                Geocomplete::make('location')
                    ->isLocation(),
                //                    ->types(['airport'])
                //                    ->placeField('name')
                //                    ->isLocation()
                //                    ->reverseGeocode([
                //                        'city'   => '%L',
                //                        'zip'    => '%z',
                //                        'state'  => '%A1',
                //                        'street' => '%n %S',
                //                    ])
                //                    ->prefix('Choose:')
                //                    ->placeholder('Start typing and address ...')
                //                    ->maxLength(1024),
                //                Forms\Components\TextInput::make('formatted_address')
                //                    ->maxLength(1024),
                //                Map::make('location')
                //                    ->debug()
                //                    ->clickable()
                //                    ->layers([
                //                        'https://googlearchive.github.io/js-v2-samples/ggeoxml/cta.kml',
                //                    ])
                // //                    ->autocomplete('formatted_address')
                // //                    ->autocompleteReverse()
                //                    ->reverseGeocode([
                //                        'city' => '%L',
                //                        'zip' => '%z',
                //                        'state' => '%A1',
                //                        'street' => '%n %S',
                //                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('lat'),
                TextColumn::make('lng'),
                TextColumn::make('street'),
                TextColumn::make('city'),
                TextColumn::make('state'),
                TextColumn::make('zip'),
                TextColumn::make('formatted_address'),
                MapColumn::make('location'),
            ])
            ->filters([
                TernaryFilter::make('processed'),
                RadiusFilter::make('radius')
                    ->latitude('lat')
                    ->longitude('lng')
                    ->selectUnit(),
                //                    ->section('Radius Search'),
            ]
            )
            ->filtersLayout(FiltersLayout::Dropdown)
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
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
            'index'  => ListLocations::route('/'),
            'create' => CreateLocation::route('/create'),
            'view'   => ViewLocation::route('/{record}'),
            'edit'   => EditLocation::route('/{record}/edit'),
        ];
    }
}
