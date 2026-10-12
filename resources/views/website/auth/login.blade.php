@extends('layouts.website')
@section('body')
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
                                    <h2 class="text-white fw-bold mb-2">Doctor Login</h2>
                                    <p class="text-white-50 small mb-0">Welcome back to DEPTH Microsite.</p>
                                </div>

                                <div class="p-4 p-md-5">
                                    <form id="doctorLoginForm" method="POST" action="{{ route('login.post') }}">
                                        @csrf
                                        <h6 class="form-section-title">Welcome Back</h6>

                                        <div class="mb-3">
                                            <label class="form-label custom-form-label">Email ID</label>
                                            <input type="email" name="email" class="form-control custom-form-control"
                                                   value="{{ old('email') }}" placeholder="doctor@example.com" required>
                                        </div>

                                        <button type="submit"
                                                class="btn btn-primary-custom w-100 py-3 mb-3 fw-bold fs-6"
                                                id="submitLoginBtn">
                                            Login
                                        </button>

                                        <div class="text-center mt-3">
                                            <p class="text-muted small">Don't have an account? <a
                                                    href="{{route('register')}}"
                                                    class="text-primary-green fw-semibold text-decoration-none">Register
                                                    here</a></p>
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
    <script type="application/javascript">
        $(document).ready(function () {

            $('.custom-form-control').focus(function () {
                $(this).parent().find('.custom-form-label').css('color', 'var(--primary-green)');
            }).blur(function () {
                $(this).parent().find('.custom-form-label').css('color', '#4a5568');
            });

            $('#doctorLoginForm').validate({
                rules: {
                    email: {required: true, email: true}
                },

                messages: {
                    email: "Please enter a valid email address"
                },
            });

        });
    </script>
@endpush
