@php
    $isLocation            = $getIsLocation();
    $datalistOptions       = [];
    $extraAlpineAttributes = $getExtraAlpineAttributes();
    $id                    = $getIsLocation() ? $getId() . '-fgm-address' : $getId();
    $isConcealed           = $isConcealed();
    $isDisabled            = $isDisabled();
    $isPrefixInline        = $isPrefixInline();
    $isSuffixInline        = $isSuffixInline();
    $statePath             = $getStatePath();
    $prefixActions         = $getPrefixActions();
    $prefixIcon            = $getPrefixIcon();
    $prefixLabel           = $getPrefixLabel();
    $suffixActions         = $getSuffixActions();
    $suffixIcon            = $getSuffixIcon();
    $suffixLabel           = $getSuffixLabel();
    $mask                  = null;
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <x-filament::input.wrapper
        :disabled="$isDisabled"
        :inline-prefix="$isPrefixInline"
        :inline-suffix="$isSuffixInline"
        :prefix="$prefixLabel"
        :prefix-actions="$prefixActions"
        :prefix-icon="$prefixIcon"
        :suffix="$suffixLabel"
        :suffix-actions="$suffixActions"
        :suffix-icon="$suffixIcon"
        :valid="! $errors->has($statePath)"
        class="fi-fo-text-input"
        :attributes="
            \Filament\Support\prepare_inherited_attributes($getExtraAttributeBag())
                ->class(['overflow-hidden'])
        "
    >
        <div
            class="w-full"
            x-ignore
            x-load
            x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('filament-google-maps-geocomplete', 'cheesegrits/filament-google-maps') }}"
            x-data="filamentGoogleGeocomplete({
                        setStateUsing: async (path, state) => {
                        console.log(path, state)
                            return await $wire.set(path, state)
                        },
                        reverseGeocodeUsing: (results) => {
                            $wire.reverseGeocodeUsing(@js($statePath), results)
                        },
                        filterName: @js($getFilterName()),
                        autocompleteInputId: @js($id),
                        statePath: @js($getStatePath()),
                        isLocation: @js($getIsLocation()),
                        reverseGeocodeFields: @js($getReverseGeocode()),
                        hasReverseGeocodeUsing: @js($getReverseGeocodeUsing()),
                        latLngFields: @js($getUpdateLatLngFields()),
                        types: @js($getTypes()),
                        placeField: @js($getPlaceField()),
                        countries: @js($getCountries()),
                        debug: @js($getDebug()),
                        gmaps: @js($getMapsUrl()),
                        minChars: @js($getMinChars()),
                    })"
            wire:ignore
        >

            <x-filament::input
                :attributes="
                    \Filament\Support\prepare_inherited_attributes($getExtraInputAttributeBag())
                        ->merge($extraAlpineAttributes, escape: false)
                        ->merge([
                            'class'                                                                 => 'filament-forms-input',
                            'x-ref'                                                                 => 'input',
                            'autocapitalize'                                                        => $getAutocapitalize(),
                            'autocomplete'                                                          => $getAutocomplete(),
                            'autofocus'                                                             => $isAutofocused(),
                            'disabled'                                                              => $isDisabled,
                            'id'                                                                    => $id,
                            'inlinePrefix'                                                          => $isPrefixInline && (count($prefixActions) || $prefixIcon || filled($prefixLabel)),
                            'inlineSuffix'                                                          => $isSuffixInline && (count($suffixActions) || $suffixIcon || filled($suffixLabel)),
                            'inputmode'                                                             => $getInputMode(),
                            'list'                                                                  => $datalistOptions ? $id . '-list' : null,
                            'max'                                                                   => null,
                            'maxlength'                                                             => null,
                            'min'                                                                   => null,
                            'minlength'                                                             => null,
                            'placeholder'                                                           => $getPlaceholder(),
                            'readonly'                                                              => $isReadOnly(),
                            'required'                                                              => $isRequired() && (! $isConcealed),
                            'step'                                                                  => null,
                            'type'                                                                  => 'text',
                            $applyStateBindingModifiers('wire:model')                               => (! $isLocation) ? $statePath : null,
                            'x-data'                                                                => (count($extraAlpineAttributes) || filled($mask)) ? '{}' : null,
                            'x-mask' . ($mask instanceof \Filament\Support\RawJs ? ':dynamic' : '') => filled($mask) ? $mask : null,
                            'value'                                                                 => $isLocation ? $getFormattedState() : null,
                        ], escape: false)
                "
            />

            @if ($getIsLocation())
                <input
                    {{ $applyStateBindingModifiers('wire:model') }}="{{ $getStatePath() }}"
                    type="hidden"
                    id="{{ $getStatePath() }}"
                />
            @endif
        </div>
    </x-filament::input.wrapper>
</x-dynamic-component>
