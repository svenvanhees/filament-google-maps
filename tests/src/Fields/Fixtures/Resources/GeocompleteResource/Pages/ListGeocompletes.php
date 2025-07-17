<?php

namespace Cheesegrits\FilamentGoogleMaps\Tests\Fields\Fixtures\Resources\GeocompleteResource\Pages;

use Filament\Actions\CreateAction;
use Cheesegrits\FilamentGoogleMaps\Tests\Fields\Fixtures\Resources\GeocompleteResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGeocompletes extends ListRecords
{
    protected static string $resource = GeocompleteResource::class;

    protected function getActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    //    protected function getTableFiltersFormWidth(): string
    //    {
    //        return '4xl';
    //    }
}
