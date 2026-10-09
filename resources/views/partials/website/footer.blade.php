<footer class="custom-footer py-4 mt-auto">
    <div class="container">
        <div class="row align-items-center">

            <div class="col-lg-3 col-md-12 mb-4 mb-lg-0 d-flex justify-content-center justify-content-lg-start">
                <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
                    <img src="{{ siteSettingAsset('site_logo', 'web/image/ean-logo-official.png') }}" alt="European Academy of Neurology (EAN)" class="site-brand-logo" width="1603" height="646">
                </a>
            </div>

            <div class="col-lg-5 col-md-12 mb-4 mb-lg-0">
                <ul class="list-unstyled d-flex flex-wrap justify-content-center gap-4 mb-0">
                    <li><a href="#" class="footer-link">Support Portal</a></li>
                    <li><a href="#" class="footer-link">CME Guidelines</a></li>
                    <li><a href="#" class="footer-link">Privacy Policy</a></li>
                </ul>
            </div>

            <div class="col-lg-4 col-md-12 text-center text-lg-end">
                <span class="footer-text">{{ siteSetting('footer_text', '© ' . date('Y') . ' EAN Microsite.') }}</span>
            </div>

        </div>
    </div>
</footer>
