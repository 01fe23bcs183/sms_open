{{-- Footer Component --}}
{{-- Displays copyright, quick links, version number, and social media links --}}
<footer class="footer bg-white border-top mt-auto py-4">
    <div class="container-fluid px-4">
        <div class="row">
            <!-- School Info -->
            <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-mortarboard-fill text-primary me-2 fs-4"></i>
                    <h5 class="mb-0 fw-bold">Smart School</h5>
                </div>
                <p class="text-muted small mb-3">
                    A comprehensive school management system designed to streamline 
                    administrative tasks, enhance communication, and improve educational outcomes.
                </p>
                <!-- Social Media Links -->
                <div class="social-links">
                    <a href="#" class="btn btn-sm btn-outline-primary me-1" title="Facebook">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a href="#" class="btn btn-sm btn-outline-info me-1" title="Twitter">
                        <i class="bi bi-twitter-x"></i>
                    </a>
                    <a href="#" class="btn btn-sm btn-outline-danger me-1" title="Instagram">
                        <i class="bi bi-instagram"></i>
                    </a>
                    <a href="#" class="btn btn-sm btn-outline-primary me-1" title="LinkedIn">
                        <i class="bi bi-linkedin"></i>
                    </a>
                    <a href="#" class="btn btn-sm btn-outline-danger" title="YouTube">
                        <i class="bi bi-youtube"></i>
                    </a>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
                <h6 class="fw-bold mb-3">Quick Links</h6>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <a href="{{ route('dashboard') }}" class="text-muted text-decoration-none small">
                            <i class="bi bi-chevron-right small"></i> Dashboard
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-muted text-decoration-none small">
                            <i class="bi bi-chevron-right small"></i> About Us
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-muted text-decoration-none small">
                            <i class="bi bi-chevron-right small"></i> Contact
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-muted text-decoration-none small">
                            <i class="bi bi-chevron-right small"></i> Help Center
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Legal Links -->
            <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
                <h6 class="fw-bold mb-3">Legal</h6>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <a href="#" class="text-muted text-decoration-none small">
                            <i class="bi bi-chevron-right small"></i> Privacy Policy
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-muted text-decoration-none small">
                            <i class="bi bi-chevron-right small"></i> Terms of Service
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-muted text-decoration-none small">
                            <i class="bi bi-chevron-right small"></i> Cookie Policy
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-muted text-decoration-none small">
                            <i class="bi bi-chevron-right small"></i> Disclaimer
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Contact Info -->
            <div class="col-lg-4 col-md-6">
                <h6 class="fw-bold mb-3">Contact Us</h6>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2 d-flex align-items-start">
                        <i class="bi bi-geo-alt text-primary me-2 mt-1"></i>
                        <span class="text-muted small">123 Education Street, Knowledge City, IN 560001</span>
                    </li>
                    <li class="mb-2 d-flex align-items-center">
                        <i class="bi bi-telephone text-primary me-2"></i>
                        <span class="text-muted small">+91 1234 567 890</span>
                    </li>
                    <li class="mb-2 d-flex align-items-center">
                        <i class="bi bi-envelope text-primary me-2"></i>
                        <span class="text-muted small">info@smartschool.com</span>
                    </li>
                    <li class="d-flex align-items-center">
                        <i class="bi bi-clock text-primary me-2"></i>
                        <span class="text-muted small">Mon - Sat: 8:00 AM - 5:00 PM</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Copyright Bar -->
        <hr class="my-4">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <p class="text-muted small mb-0">
                    &copy; {{ date('Y') }} <strong>Smart School Management System</strong>. All rights reserved.
                </p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <span class="badge bg-primary">
                    <i class="bi bi-code-slash me-1"></i> Version 1.0.0
                </span>
                <span class="text-muted small ms-2">
                    Built with <i class="bi bi-heart-fill text-danger"></i> using Laravel
                </span>
            </div>
        </div>
    </div>
</footer>

<style>
    .footer {
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }
    
    .footer a:hover {
        color: var(--primary-color, #4f46e5) !important;
    }
    
    .footer .social-links a:hover {
        transform: translateY(-2px);
        transition: transform 0.2s ease;
    }
    
    /* RTL Support */
    [dir="rtl"] .footer .bi-chevron-right {
        transform: rotate(180deg);
    }
    
    [dir="rtl"] .footer .me-1,
    [dir="rtl"] .footer .me-2 {
        margin-right: 0 !important;
        margin-left: 0.25rem !important;
    }
    
    [dir="rtl"] .footer .ms-2 {
        margin-left: 0 !important;
        margin-right: 0.5rem !important;
    }
</style>
