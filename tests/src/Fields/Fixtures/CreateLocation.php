<?php

namespace Cheesegrits\FilamentGoogleMaps\Tests\Fields\Fixtures;

use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Cheesegrits\FilamentGoogleMaps\Fields\Geocomplete;
use Cheesegrits\FilamentGoogleMaps\Tests\Models\Location;
use Filament\Forms;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CreateLocation extends Component implements HasForms
{
    use InteractsWithForms;

    public $location;

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            Geocomplete::make('location'),
        ];
    }

    public function create(): void
    {
        $location = Location::create($this->form->getState());
    }

    protected function getFormModel(): string
    {
        return Location::class;
    }

    public function render(): View
    {
        return view('forms.fixtures.form');
    }
}
