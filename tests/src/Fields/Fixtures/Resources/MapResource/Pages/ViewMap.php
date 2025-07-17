<?php

namespace Cheesegrits\FilamentGoogleMaps\Tests\Fields\Fixtures\Resources\MapResource\Pages;

use Filament\Actions\EditAction;
use Cheesegrits\FilamentGoogleMaps\Tests\Fields\Fixtures\Resources\MapResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMap extends ViewRecord
{
    protected static string $resource = MapResource::class;

    protected function getActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
