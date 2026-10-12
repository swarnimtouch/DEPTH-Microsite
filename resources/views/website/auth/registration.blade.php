@extends('layouts.website')
@section('body')
    @php
        $oldMobile = old('mobile');
        $oldMobileCountryCode = old('mobile_country_code', '91');
        $oldMobileNumber = old('mobile_number');

        if ($oldMobileNumber === null && $oldMobile) {
            $oldMobileDigits = preg_replace('/\D+/', '', $oldMobile);
            $oldMobileNumber = str_starts_with((string) $oldMobile, '+') && str_starts_with($oldMobileDigits, $oldMobileCountryCode)
                ? substr($oldMobileDigits, strlen($oldMobileCountryCode))
                : $oldMobileDigits;
        }
    @endphp
    <div class="d-flex flex-column flex-lg-row min-vh-100">
        <div class="main-content flex-grow-1 d-flex flex-column">
            <section class="py-4 py-lg-5 flex-grow-1 d-flex align-items-center">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-9 col-xl-8">
                            <div class="form-card bg-white shadow-lg">
                                <div class="form-card-header text-center">
                                    <div class="d-flex justify-content-center align-items-center mb-3">
                                        <img src="{{ siteSettingAsset('site_logo', 'web/depth/assets/hetero.png') }}" alt="Hetero" class="site-brand-logo" width="254" height="211">
                                    </div>
                                    <h2 class="text-white fw-bold mb-2" id="formMainTitle">Doctor Registration Form</h2>
                                    <p class="text-white-50 small mb-0" id="formSubTitle">Join the elite community of
                                        heart failure care specialists.</p>
                                </div>
                                <div class="p-4 p-md-5">
                                    <form id="doctorRegistrationForm" method="POST" action="{{ route('register.store') }}">
                                        @csrf
                                        <h6 class="form-section-title">Section 1 — Personal Details</h6>
                                        <div class="mb-3">
                                            <label class="form-label custom-form-label">Name <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control custom-form-control"
                                                   value="{{ old('name') }}" placeholder="Name" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label custom-form-label">Hospital <span class="text-danger">*</span></label>
                                            <input type="text" name="hospital" class="form-control custom-form-control"
                                                   value="{{ old('hospital') }}" placeholder="Hospital" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label custom-form-label">Specialty <span class="text-danger">*</span></label>
                                            <select name="speciality_id" class="form-select custom-form-control" required>
                                                <option value="" disabled {{ old('speciality_id') ? '' : 'selected' }}>Choose specialty</option>
                                                @foreach($specialities as $speciality)
                                                    <option value="{{ $speciality->id }}" {{ (string) old('speciality_id') === (string) $speciality->id ? 'selected' : '' }}>{{ $speciality->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>


                                        <div class="mb-3">
                                            <label class="form-label custom-form-label">Country <span class="text-danger">*</span></label>
                                            <select name="country" id="registrationCountry" class="form-select custom-form-control" required>
                                                <option value="" disabled {{ old('country') ? '' : 'selected' }}>Choose Country</option>
                                                @foreach($country as $key=>$value)
                                                    <option value="{{$value->name}}" {{ old('country') == $value->name ? 'selected' : '' }}>{{$value->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>


                                        <h6 class="form-section-title">Section 2 — Contact & Credentials</h6>

                                        <div class="row g-3 mb-3">
                                             <div class="col-md-6">
                                                 <label class="form-label custom-form-label">Mobile Number</label>
                                                 <input type="hidden" name="mobile" id="mobileFullNumber" value="{{ old('mobile') }}">
                                                 <input type="hidden" name="mobile_country_code" id="mobileCountryCode" value="{{ $oldMobileCountryCode }}">
                                                 <input type="hidden" name="mobile_country_iso" id="mobileCountryIso" value="{{ old('mobile_country_iso', 'in') }}">
                                                 <div class="mobile-number-group">
                                                     <select id="mobileCountryPicker" class="form-select custom-form-control">
                                                         <option value="{{ old('mobile_country_iso', 'in') }}" data-dial="{{ $oldMobileCountryCode }}">+{{ $oldMobileCountryCode }}</option>
                                                     </select>
                                                     <input type="tel" name="mobile_number" id="mobileNumber" class="form-control custom-form-control"
                                                            value="{{ $oldMobileNumber }}" placeholder="0000000000">
                                                 </div>
                                             </div>
                                            <div class="col-md-6">
                                                <label class="form-label custom-form-label">Email ID <span class="text-danger">*</span></label>
                                                <input type="email" id="email" name="email" class="form-control custom-form-control"
                                                       value="{{ old('email') }}" placeholder="doctor@example.com" required>
                                            </div>
                                        </div>

                                        <button type="submit"
                                                class="btn btn-primary-custom w-100 py-3 mb-3 fw-bold fs-6"
                                                id="submitRegBtn">
                                            Register
                                        </button>

                                        <div class="text-center mt-4">
                                            <p class="text-muted small mb-4 privacy-footer-text">Your data is secured.
                                                By registering, you agree to our professional code of conduct and
                                                privacy policy.</p>

                                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                                <span
                                                    class="badge rounded-pill badge-light-green">6-MONTH WEBINAR PROGRAMME</span>
                                                <span class="badge rounded-pill badge-light-purple">CERTIFICATE ON COMPLETION</span>
                                            </div>
                                        </div>

                                    </form>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
@push('scripts')
    <script>window.module = {exports: {}}; window.exports = window.module.exports;</script>
    <script src="https://cdn.jsdelivr.net/npm/country-telephone-data@0.6.3/country_telephone_data.js"></script>
    <script>window.countryTelData = window.countryTelData || window.module.exports || {}; delete window.module; delete window.exports;</script>
    <script type="application/javascript">
        $(document).ready(function () {
            const telData = window.countryTelData || window.CountryTelephoneData || window.countryTelephoneData || {};
            const allTelephoneCountries = telData.allCountries || window.allCountries || [];
            const isoLookup = telData.iso2Lookup || telData.allCountriesIso2Lookup || allTelephoneCountries.reduce(function (countries, country) {
                const iso2 = country.iso2 || country[1];
                if (iso2) {
                    countries[iso2] = country;
                }
                return countries;
            }, {});
            const $countryPicker = $('#mobileCountryPicker');
            const $countryCode = $('#mobileCountryCode');
            const $countryIso = $('#mobileCountryIso');
            const $mobileNumber = $('#mobileNumber');
            const $mobileFullNumber = $('#mobileFullNumber');
            const $registrationCountry = $('#registrationCountry');

            function normalizeCountryName(countryName) {
                return (countryName || '').toLowerCase().replace(/[^a-z]/g, '');
            }

            function getCountryName(country) {
                return country.name || country[0] || '';
            }

            function flagEmoji(iso2) {
                return iso2.toUpperCase().replace(/./g, function (char) {
                    return String.fromCodePoint(127397 + char.charCodeAt(0));
                });
            }

            function populateCountryCodes() {
                const selectedIso = ($countryIso.val() || 'in').toLowerCase();
                const countries = allTelephoneCountries.length ? allTelephoneCountries : [
                    {name: 'India', iso2: 'in', dialCode: '91'},
                    {name: 'United States', iso2: 'us', dialCode: '1'},
                    {name: 'United Kingdom', iso2: 'gb', dialCode: '44'},
                ];

                $countryPicker.empty();
                countries.forEach(function (country) {
                    const iso2 = country.iso2 || country[1];
                    const dialCode = country.dialCode || country.dial_code || country[2];

                    if (!iso2 || !dialCode) {
                        return;
                    }

                    $('<option>')
                        .val(iso2)
                        .attr('data-dial', dialCode)
                        .text(flagEmoji(iso2) + ' +' + dialCode)
                        .prop('selected', iso2 === selectedIso)
                        .appendTo($countryPicker);
                });
            }

            function findTelephoneCountryByName(selectedCountryName) {
                const selectedName = normalizeCountryName(selectedCountryName);

                if (!selectedName) {
                    return null;
                }

                return allTelephoneCountries.find(function (country) {
                    return normalizeCountryName(getCountryName(country)) === selectedName;
                }) || null;
            }

            function syncCodeFromRegistrationCountry() {
                const telephoneCountry = findTelephoneCountryByName($registrationCountry.val());
                const iso2 = telephoneCountry ? (telephoneCountry.iso2 || telephoneCountry[1]) : null;

                if (iso2 && $countryPicker.find('option[value="' + iso2 + '"]').length) {
                    $countryPicker.val(iso2);
                    syncMobileValue();
                }
            }

            function selectedDialData() {
                const $selectedCountry = $countryPicker.find(':selected');
                const iso2 = ($selectedCountry.val() || $countryIso.val() || 'in').toLowerCase();
                const countryTelephoneData = isoLookup[iso2] || {};
                const dialCode = $selectedCountry.data('dial') || countryTelephoneData.dialCode || countryTelephoneData.dial_code || countryTelephoneData[2] || $countryCode.val() || '91';

                return {iso2, dialCode};
            }

            function syncMobileValue() {
                const dialData = selectedDialData();
                const localNumber = $mobileNumber.val().replace(/\D+/g, '');

                $countryIso.val(dialData.iso2);
                $countryCode.val(dialData.dialCode);
                $mobileFullNumber.val(localNumber ? '+' + dialData.dialCode + localNumber : '');

                return $mobileFullNumber.val();
            }

            populateCountryCodes();
            $countryPicker.on('change', syncMobileValue);
            $registrationCountry.on('change', syncCodeFromRegistrationCountry);
            syncCodeFromRegistrationCountry();

            $mobileNumber.on('input blur', function () {
                this.value = this.value.replace(/\D+/g, '');
                syncMobileValue();
            });
            syncMobileValue();

            $('.custom-form-control').focus(function () {
                $(this).parent().find('.custom-form-label').css('color', 'var(--primary-green)');
            }).blur(function () {
                $(this).parent().find('.custom-form-label').css('color', '#4a5568');
            });
            $.validator.addMethod('optionalMobileDigits', function (value, element) {
                return this.optional(element) || /^\d{7,15}$/.test(value);
            }, 'Please enter a valid mobile number');
            $('#doctorRegistrationForm').validate({
                ignore: [],
                rules: {
                    name: {required: true},
                    hospital: {required: true},
                    country: {required: true},
                    mobile_number: {
                        required: false,
                        optionalMobileDigits: true,
                        remote: {
                            url: "{{ route('check_mobile') }}",
                            type: "post",
                            data: {
                                _token: "{{ csrf_token() }}",
                                mobile: function () {
                                    return syncMobileValue();
                                }
                            }
                        }
                    },
                    email: {
                        required: true,
                        email: true,
                        remote: {
                            url: "{{ route('check_email') }}",
                            type: "post",
                            data: {
                                _token: "{{ csrf_token() }}",
                                email: function () {
                                    return $('[name="email"]').val();
                                }
                            }
                        }
                    },
                },
                messages: {
                    name: "Please enter your name",
                    hospital: "Please enter your hospital",
                    country: "Please select your country",
                    mobile_number: {
                        optionalMobileDigits: "Please enter 7 to 15 digits",
                        remote: "Mobile number already exists",
                    },
                    email: {
                        required: "Please enter your email address",
                        email: "Please enter a valid email address",
                        remote: "Email already exists",
                    },
                },
            });
            $('#doctorRegistrationForm').on('submit', syncMobileValue);
        })
    </script>
@endpush
