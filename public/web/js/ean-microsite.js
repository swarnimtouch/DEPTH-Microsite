(function ($) {
    'use strict';

    $(function () {
        const telData = window.countryTelData || window.CountryTelephoneData || window.countryTelephoneData || {};
        const telephoneCountries = telData.allCountries || window.allCountries || [];
        const isoLookup = telData.iso2Lookup || telData.allCountriesIso2Lookup || telephoneCountries.reduce(function (result, country) {
            const iso2 = country.iso2 || country[1];
            if (iso2) result[iso2] = country;
            return result;
        }, {});
        const $form = $('#homepageRegistrationForm');
        if (!$form.length) return;

        const $countryPicker = $('#homepage-mobile-country');
        const $localNumber = $('#homepage-mobile-number');
        const $fullNumber = $('#homepage-mobile-full');
        const $dialCode = $('#homepage-mobile-country-code');
        const $countryIso = $('#homepage-mobile-country-iso');
        const $registrationCountry = $('#homepage-country');
        const csrfToken = $form.data('csrf-token');

        function normalizeCountryName(countryName) {
            return String(countryName || '').toLowerCase().replace(/[^a-z]/g, '');
        }

        function telephoneCountryName(country) {
            return country.name || country[0] || '';
        }

        function flag(iso2) {
            return iso2.toUpperCase().replace(/./g, function (letter) {
                return String.fromCodePoint(127397 + letter.charCodeAt(0));
            });
        }

        function syncMobile() {
            const $selected = $countryPicker.find(':selected');
            const iso2 = String($selected.val() || 'in').toLowerCase();
            const country = isoLookup[iso2] || {};
            const dial = String($selected.data('dial') || country.dialCode || country.dial_code || country[2] || $dialCode.val() || '91');
            const local = $localNumber.val().replace(/\D+/g, '');
            $countryIso.val(iso2);
            $dialCode.val(dial);
            $fullNumber.val(local ? '+' + dial + local : '');
            return $fullNumber.val();
        }

        function syncCodeFromRegistrationCountry() {
            const selectedName = normalizeCountryName($registrationCountry.val());
            if (!selectedName) return;

            const country = telephoneCountries.find(function (telephoneCountry) {
                return normalizeCountryName(telephoneCountryName(telephoneCountry)) === selectedName;
            });
            const iso2 = country ? String(country.iso2 || country[1] || '').toLowerCase() : '';

            if (iso2 && $countryPicker.find('option[value="' + iso2 + '"]').length) {
                $countryPicker.val(iso2);
                syncMobile();
            }
        }

        const initialIso = String($countryIso.val() || 'in').toLowerCase();
        if (telephoneCountries.length) {
            $countryPicker.empty();
            telephoneCountries.forEach(function (country) {
                const iso2 = String(country.iso2 || country[1] || '').toLowerCase();
                const dial = country.dialCode || country.dial_code || country[2];
                if (!iso2 || !dial) return;
                const option = new Option(flag(iso2) + ' +' + dial, iso2, false, iso2 === initialIso);
                option.dataset.dial = dial;
                $countryPicker.append(option);
            });
        }

        $.validator.addMethod('requiredMobileDigits', function (value) {
            return /^\d{7,15}$/.test(value);
        }, 'Enter 7 to 15 digits');

        $countryPicker.on('change', syncMobile);
        $registrationCountry.on('change', syncCodeFromRegistrationCountry);
        $localNumber.on('input blur', function () {
            this.value = this.value.replace(/\D+/g, '');
            syncMobile();
        });
        syncCodeFromRegistrationCountry();
        syncMobile();

        $form.validate({
            ignore: [],
            rules: {
                event: { required: true },
                consent: { required: true },
                name: { required: true, maxlength: 255 },
                city: { required: true, maxlength: 255 },
                email: {
                    required: true,
                    email: true,
                    remote: {
                        url: $form.data('check-email-url'),
                        type: 'post',
                        data: { _token: csrfToken, email: function () { return $form.find('[name="email"]').val(); } }
                    }
                },
                speciality_id: { required: true },
                country: { required: true },
                hospital: { required: true, maxlength: 255 },
                mobile_number: {
                    required: true,
                    requiredMobileDigits: true,
                    remote: {
                        url: $form.data('check-mobile-url'),
                        type: 'post',
                        data: { _token: csrfToken, mobile: syncMobile }
                    }
                }
            },
            messages: {
                event: { required: 'Please select a webinar' },
                consent: { required: 'Please confirm your consent to continue' },
                name: { required: 'Please enter your name' },
                city: { required: 'Please enter your city' },
                email: { required: 'Please enter your email address', email: 'Please enter a valid email address', remote: 'Email already exists' },
                speciality_id: { required: 'Please select your speciality' },
                country: { required: 'Please select your country' },
                hospital: { required: 'Please enter your institution' },
                mobile_number: { required: 'Please enter your mobile number', requiredMobileDigits: 'Enter 7 to 15 digits', remote: 'Mobile number already exists' }
            },
            errorElement: 'span',
            errorClass: 'micro-error',
            onkeyup: function (element) { $(element).valid(); },
            errorPlacement: function (error, element) {
                if (element.attr('name') === 'event') {
                    error.addClass('epic-span');
                    element.closest('.epic-events').after(error);
                } else if (element.attr('name') === 'consent') {
                    error.addClass('epic-span');
                    element.closest('.epic-consent').after(error);
                } else {
                    (element.closest('.micro-mobile-group').length ? element.closest('.micro-mobile-group') : element).after(error);
                }
            },
            highlight: function (element) { $(element).addClass('is-invalid'); },
            unhighlight: function (element) { $(element).removeClass('is-invalid'); }
        });

        function updateCountdowns() {
            document.querySelectorAll('[data-countdown]').forEach(function (element) {
                const difference = new Date(element.dataset.countdown).getTime() - Date.now();
                if (difference <= 0) {
                    element.classList.add('is-finished');
                    element.querySelector('div').innerHTML = '<span class="epic-countdown-status">Webinar access opens soon</span>';
                    return;
                }
                element.querySelector('[data-days]').textContent = String(Math.floor(difference / 86400000)).padStart(2, '0');
                element.querySelector('[data-hours]').textContent = String(Math.floor(difference / 3600000) % 24).padStart(2, '0');
                element.querySelector('[data-minutes]').textContent = String(Math.floor(difference / 60000) % 60).padStart(2, '0');
                element.querySelector('[data-seconds]').textContent = String(Math.floor(difference / 1000) % 60).padStart(2, '0');
            });
        }

        updateCountdowns();
        window.setInterval(updateCountdowns, 1000);
        $form.on('submit', syncMobile);
    });
})(jQuery);
