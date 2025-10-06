<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    /* Footer styles */
    .footer {
        background-color: var(--white);
        border-top: 1px solid #e5e7eb;
        padding: 3rem 0 2rem;
    }

    .footer-content {
        display: none;
    }

    .footer-brand {
        display: flex;
        flex-direction: column;
        align-items: flex-end; /* Align logo and text to right */
        margin-left: auto; /* Push to right side */
        text-align: right;
        order: 2; /* Logo below copyright */
    }

    .image-logo-height {
        height: 40px;
    }

    .brand-link {
        display: flex;
        align-items: center;
        text-decoration: none;
        color: inherit;
        margin-bottom: 1rem;
    }

    .brand-icon {
        width: 2rem;
        height: 2rem;
        background-color: var(--primary-red);
        margin-right: 0.5rem;
    }

    .footer-brand p {
        font-size: 0.875rem;
        color: var(--gray-500);
        margin-top: 0.5rem;
        font-style: italic;
    }

    /* Our Associates Section */
    .associates-section {
        text-align: center;
        margin-bottom: 2rem;
    }

    .associates-title {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--gray-600);
        margin-bottom: 1.5rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .associates-icons {
        display: flex;
        justify-content: center;
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .associates-group {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .associate-icon {
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .associate-icon.black {
        background-color: #1f2937;
    }

    .associate-icon.blue {
        background-color: #3b82f6;
    }

    .associate-icon.gray {
        border: 1px solid #d1d5db;
        background-color: transparent;
    }

    .associate-icon:hover {
        transform: scale(1.1);
    }

    /* Horizontal divider */
    .footer-divider {
        width: 100%;
        height: 1px;
        background-color: #e5e7eb;
        margin: 2rem 0;
    }

    /* Footer links section */
    .footer-links-section {
        margin-bottom: 2rem;
    }

    .footer-links {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
        max-width: 600px;
        margin: 0 auto;
    }

    /* For larger screens - side by side layout */
    @media (min-width: 768px) {
        .footer-bottom {
            flex-direction: row; /* Horizontal layout */
            justify-content: space-between;
            align-items: flex-end; /* Align to bottom */
        }
        
        .copyright {
            order: 1; /* Copyright on left */
        }
        
        .footer-brand {
            order: 2; /* Logo on right */
            text-align: right;
        }
    }

    @media (min-width: 48rem) {
        .footer-links {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    .link-group {
        text-align: center;
    }

    .link-group h3 {
        font-weight: 500;
        margin-bottom: 1rem;
        color: var(--gray-900);
    }

    .link-group ul {
        list-style: none;
        padding: 0;
    }

    .link-group li {
        margin-bottom: 0.5rem;
    }

    .link-group a {
        font-size: 0.875rem;
        color: var(--gray-500);
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .link-group a:hover {
        color: var(--gray-900);
    }

    /* Footer bottom */
    .footer-bottom {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        border-top: 1px solid #e5e7eb;
        padding-top: 2rem;
    }

    .copyright {
        font-size: 0.875rem;
        color: var(--gray-500);
        order: 1; /* Copyright above logo */
    }

    .social-links {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .social-links a {
        color: var(--gray-500);
        transition: color 0.2s ease;
    }

    .social-links a:hover {
        color: var(--gray-900);
    }

    .social-links svg {
        width: 1.25rem;
        height: 1.25rem;
    }

    /* Responsive adjustments */
    @media (max-width: 48rem) {
        .associates-icons {
            gap: 1rem;
        }
        
        .associates-group {
            gap: 0.25rem;
        }
        
        .footer-content {
            flex-direction: column;
            text-align: center;
        }
        
        .footer-bottom {
            flex-direction: column;
            text-align: center;
            align-items: center;
        }

        .footer-links {
            grid-template-columns: 1fr; /* Stack links on small screens */
        }

        .footer-brand {
            margin: 1rem auto 0;
            align-items: center; /* Center logo and text */
        }

        .copyright {
            margin-bottom: 1rem;
        }
    }
</style>

<!-- Updated Footer Section -->
<footer class="footer">
    <div class="container">
        <!-- Our Associates Section -->
        <div class="associates-section">
            <h3 class="associates-title">OUR ASSOCIATES</h3>
            <div class="associates-icons">
                <div class="associates-group">
                    <div class="associate-icon black"></div>
                    <div class="associate-icon blue"></div>
                    <div class="associate-icon gray"></div>
                </div>
                <div class="associates-group">
                    <div class="associate-icon black"></div>
                    <div class="associate-icon blue"></div>
                    <div class="associate-icon gray"></div>
                </div>
            </div>
        </div>

        <!-- Horizontal Divider -->
        <div class="footer-divider"></div>

        <!-- Footer Links Section -->
        <div class="footer-links-section">
            <div class="footer-links">
                <div class="link-group">
                    <ul>
                        <li>
                            <a href="{{ route('about') }}">About</a>
                        </li>
                        <li>
                            <a href="{{ route('contact') }}">Contact</a>
                        </li>
                        <li>
                            <a href="#">Pricing</a>
                        </li>
                        <li>
                            <a href="#">Features</a>
                        </li>
                    </ul>
                </div>
                <div class="link-group">
                    <ul>
                        <li>
                            <a href="#">FAQ</a>
                        </li>
                        <li>
                            <a href="#">T & C</a>
                        </li>
                        <li>
                            <a href="#">Support</a>
                        </li>
                        <li>
                            <a href="#">Docs</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Footer Bottom with Logo on Right -->
        <div class="footer-bottom">
            <div class="copyright">© {{ date('Y') }} ShoutoutGh All rights reserved.</div>
            <div class="footer-brand">
                <a href="{{ route('home') }}" class="brand-link">
                    <img src="{{ secure_asset('images/shoutoutgh-logo.png') }}" alt="ShoutOutGh" class="logo-image image-logo-height">
                </a>
                <p>Educating through Entertainment</p>
            </div>
        </div>
    </div>
</footer>