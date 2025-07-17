<?php

namespace Cheesegrits\FilamentGoogleMaps\Tests\Fields\Fixtures\Resources\MapResource\Pages;

use Filament\Actions\CreateAction;
use Cheesegrits\FilamentGoogleMaps\Tests\Fields\Fixtures\Resources\MapResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMap extends ListRecords
{
    protected static string $resource = MapResource::class;

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
