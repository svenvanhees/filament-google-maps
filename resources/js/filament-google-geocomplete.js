// A module-level Promise to ensure the Google Maps script is loaded only once.
let googleMapsPromise = null;

// Symbols are constant, so they can be defined outside the class.
const SYMBOLS = {
    "%n": ["street_number"],
    "%z": ["postal_code"],
    "%S": ["street_address", "route"],
    "%A1": ["administrative_area_level_1"],
    "%A2": ["administrative_area_level_2"],
    "%A3": ["administrative_area_level_3"],
    "%A4": ["administrative_area_level_4"],
    "%A5": ["administrative_area_level_5"],
    "%a1": ["administrative_area_level_1"],
    "%a2": ["administrative_area_level_2"],
    "%a3": ["administrative_area_level_3"],
    "%a4": ["administrative_area_level_4"],
    "%a5": ["administrative_area_level_5"],
    "%L": ["locality", "postal_town"],
    "%D": ["sublocality"],
    "%C": ["country"],
    "%c": ["country"],
    "%p": ["premise"],
    "%P": ["premise"],
};

class GeocompleteManager {
    constructor({
                    setStateUsing,
                    debug,
                    autocompleteInputId,
                    statePath,
                    gmaps,
                    filterName,
                    reverseGeocodeFields,
                    latLngFields,
                    types,
                    countries,
                    isLocation,
                    placeField,
                    reverseGeocodeUsing,
                    hasReverseGeocodeUsing,
                }) {
        // Assign all properties to the instance
        Object.assign(this, {
            setStateUsing,
            debug,
            autocompleteInputId,
            statePath,
            gmaps,
            filterName,
            reverseGeocodeFields,
            latLngFields,
            types,
            countries,
            isLocation,
            placeField,
            reverseGeocodeUsing,
            hasReverseGeocodeUsing,
        });

        this.geocoder = null;
        this.autocompleteInput = document.getElementById(this.autocompleteInputId);
    }

    init() {
        if (!this.autocompleteInput) {
            if (this.debug) {
                console.error(`Geocomplete input with ID '${this.autocompleteInputId}' not found.`);
            }
            return;
        }

        this._loadGMaps()
            .then(() => this._createAutocomplete())
            .catch((error) => console.error("Could not load Google Maps.", error));
    }

    _loadGMaps() {
        if (!googleMapsPromise) {
            googleMapsPromise = new Promise((resolve, reject) => {
                if (window.google && window.google.maps) {
                    return resolve();
                }

                const scriptId = "filament-google-maps-google-maps-js";
                if (document.getElementById(scriptId)) {
                    return reject("Google Maps script tag found, but API not available.");
                }

                window.filamentGoogleMapsAsyncLoad = () => {
                    window.filamentGoogleMapsAPILoaded = true;
                    resolve();
                };

                const script = document.createElement("script");
                script.id = scriptId;
                script.src = `${this.gmaps}&callback=filamentGoogleMapsAsyncLoad`;
                script.async = true;
                script.defer = true;
                script.onerror = () => reject("Failed to load the Google Maps script.");
                document.head.appendChild(script);
            });
        }
        return googleMapsPromise;
    }

    async _createAutocomplete() {
        const {
            Place
        } = await google.maps.importLibrary("places");
        this.geocoder = new google.maps.Geocoder();

        const autocomplete = new Place.Autocomplete(
            this.autocompleteInput, {
                fields: ["address_components", "formatted_address", "geometry", "name", this.placeField],
                strictBounds: false,
                types: this.types,
            }
        );

        autocomplete.setComponentRestrictions({
            country: this.countries
        });

        this.autocompleteInput.addEventListener("keydown", (e) => {
            if (e.key === "Enter" && document.querySelector('.pac-item-selected')) {
                e.preventDefault();
            }
        });

        autocomplete.addListener("place_changed", () => {
            const place = autocomplete.getPlace();
            if (!place.geometry || !place.geometry.location) {
                window.alert(`No details available for input: '${place.name}'`);
                return;
            }
            this._handlePlaceSelected(place);
        });

        this._setupGeolocateButton();
    }

    _setupGeolocateButton() {
        const geolocateButton = document.getElementById(`${this.statePath}-geolocate`);
        if (!geolocateButton) return;

        geolocateButton.addEventListener("click", () => {
            if (!("geolocation" in navigator)) {
                alert("Geolocation is not supported by your browser.");
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const location = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                    };
                    this.geocoder.geocode({
                        location
                    })
                        .then(({
                                   results
                               }) => {
                            if (results[0]) {
                                this.autocompleteInput.value = results[0].formatted_address;
                                this._handlePlaceSelected(results[0]);
                            }
                        })
                        .catch(error => console.error("Geocoder failed:", error));
                },
                (error) => console.error("Geolocation failed:", error)
            );
        });
    }

    _handlePlaceSelected(place) {
        this.setLocation(place);
        this.updateReverseGeocode(place);
        this.updateLatLng(place);
    }

    async setLocation(place) {
        const locationData = this.isLocation ? {
                lat: place.geometry.location.lat(),
                lng: place.geometry.location.lng(),
                formatted_address: place[this.placeField] || place.formatted_address,
            } :
            place[this.placeField] || place.formatted_address;

        await this.setStateUsing(this.statePath, locationData);

        if (this.filterName) {
            const latPath = `${this.filterName}.latitude`;
            const lngPath = `${this.filterName}.longitude`;
            document.getElementById(latPath)?.setAttribute("value", place.geometry.location.lat());
            document.getElementById(lngPath)?.setAttribute("value", place.geometry.location.lng());
            await Promise.all([
                this.setStateUsing(latPath, place.geometry.location.lat().toString()),
                this.setStateUsing(lngPath, place.geometry.location.lng().toString())
            ]);
        }
    }

    async updateReverseGeocode(place) {
        if (!this._hasReverseGeocode() || !place.address_components) return;

        const replacements = this._getReplacements(place.address_components);
        const updatePromises = [];

        for (const [field, format] of Object.entries(this.reverseGeocodeFields)) {
            if (field === this.statePath) continue;

            let replacedValue = format;
            for (const [symbol, value] of Object.entries(replacements)) {
                replacedValue = replacedValue.replaceAll(symbol, value);
            }
            for (const symbol of Object.keys(SYMBOLS)) {
                replacedValue = replacedValue.replaceAll(symbol, "");
            }

            updatePromises.push(this.setStateUsing(field, replacedValue.trim()));
        }

        if (updatePromises.length > 0) {
            try {
                await Promise.all(updatePromises);
            } catch (error) {
                console.error('Error batch updating reverse geocode fields:', error);
            }
        }

        if (this.hasReverseGeocodeUsing) {
            this.reverseGeocodeUsing(place);
        }
    }

    async updateLatLng(place) {
        const {
            lat,
            lng
        } = this.latLngFields;
        if (!(lat && lng && place.geometry)) return;

        const latValue = place.geometry.location.lat().toFixed(7);
        const lngValue = place.geometry.location.lng().toFixed(7);

        try {
            await Promise.all([
                this.setStateUsing(lat, latValue),
                this.setStateUsing(lng, lngValue),
            ]);
        } catch (error) {
            console.error('Error updating lat/lng fields:', error);
        }
    }

    _getReplacements(addressComponents) {
        const replacements = {};

        for (const component of addressComponents) {
            const componentType = component.types[0];
            for (const [symbol, types] of Object.entries(SYMBOLS)) {
                if (types.includes(componentType)) {
                    const isLowerCase = symbol === symbol.toLowerCase();
                    replacements[symbol] = isLowerCase ? component.short_name : component.long_name;
                }
            }
        }

        if (this.debug) {
            console.log("Geocode Replacements:", replacements);
        }

        return replacements;
    }

    _hasReverseGeocode() {
        return Object.keys(this.reverseGeocodeFields).length > 0 || this.hasReverseGeocodeUsing;
    }
}

export default function filamentGoogleGeocomplete(options) {
    const manager = new GeocompleteManager(options);
    manager.init();
    return manager;
}
