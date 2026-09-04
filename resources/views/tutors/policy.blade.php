@extends('layouts.tutors-layout')

@section('sidebar')
    @include('components.tutors-sidebar')
@endsection

@section('filter-bar')
    <div class="policy-header-bar">
        <div class="policy-header-left">
            <h2 class="policy-header-title">
                <i class="fa-solid fa-file-lines" style="color: var(--secondary-blue);"></i>
                <span>Settlement & Earnings Policy</span>
            </h2>
            <p class="policy-header-sub">
                Official guide to platform commission, escrow security, payouts, and dispute rules
            </p>
        </div>

        <div class="policy-header-right">
            <a href="{{ route('tutors.earnings.transactions') }}" class="header-action-btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to Transactions</span>
            </a>
            <a href="{{ route('tutors.earnings.index') }}" class="header-action-btn btn-primary">
                <i class="fa-solid fa-chart-line"></i>
                <span>Insights Dashboard</span>
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="policy-doc-wrapper">
        <article class="policy-doc-article">
            <!-- Document Header -->
            <header class="doc-main-header">
                <span class="doc-category-badge">Tutor Financial Guidelines</span>
                <h1 class="doc-main-title">Earnings, Escrow & Settlement Policy</h1>
                <p class="doc-lead">
                    This document outlines how lesson payments are processed, protected in escrow, and disbursed to your registered Ghanaian Mobile Money or Bank account.
                </p>
                <div class="doc-meta-row">
                    <span><i class="fa-regular fa-clock"></i> Effective & Updated: 2026</span>
                    <span>•</span>
                    <span><i class="fa-solid fa-shield-halved"></i> Platform Escrow Protection</span>
                </div>
            </header>

            <div class="doc-divider"></div>

            <!-- Section 1: Platform Commission Structure -->
            <section class="doc-section">
                <h2 class="doc-section-title">1. Platform Commission & Fee Structure</h2>
                <p class="doc-paragraph">
                    DigiLearn operates on a simple, transparent revenue-sharing model. When a lesson is booked and successfully delivered, a flat <strong>10% platform commission</strong> is deducted from the total booking amount. Tutors retain exactly <strong>90%</strong> of their listed hourly rate.
                </p>
                
                <h3 class="doc-sub-title">1.1 Calculation Example</h3>
                <p class="doc-paragraph">
                    For a one-hour tutoring lesson listed at <strong>GHS 100.00</strong>:
                </p>

                <div class="doc-calculation-table">
                    <div class="calc-row">
                        <span class="calc-label">Gross Student Booking Fee</span>
                        <span class="calc-val">GHS 100.00</span>
                    </div>
                    <div class="calc-row">
                        <span class="calc-label">Platform Service & Escrow Fee (10%)</span>
                        <span class="calc-val text-red">- GHS 10.00</span>
                    </div>
                    <div class="calc-row calc-total">
                        <span class="calc-label">Net Credit Released to Tutor Wallet</span>
                        <span class="calc-val text-green">+ GHS 90.00</span>
                    </div>
                </div>

                <h3 class="doc-sub-title">1.2 What the 10% Fee Covers</h3>
                <p class="doc-paragraph">
                    The platform commission directly finances the infrastructure and guarantees provided to tutors, including:
                </p>
                <ul class="doc-bullet-list">
                    <li><strong>Guaranteed Escrow Insurance:</strong> Eliminates non-payment risks by securing funds prior to class start.</li>
                    <li><strong>Integrated Classroom Technology:</strong> High-definition video, interactive whiteboards, and real-time screen sharing.</li>
                    <li><strong>Payment Gateway Fees:</strong> Absorption of Paystack and telecom partner processing overheads.</li>
                    <li><strong>Dedicated Tutor Support:</strong> Dispute investigation and dedicated account resolution support.</li>
                </ul>
            </section>

            <div class="doc-divider"></div>

            <!-- Section 2: Automated Escrow Security -->
            <section class="doc-section">
                <h2 class="doc-section-title">2. Automated Escrow Security & Release Mechanisms</h2>
                <p class="doc-paragraph">
                    To protect tutors from unfulfilled bookings and payment defaults, all student lesson credits are charged and locked in DigiLearn Escrow at the moment a lesson is scheduled.
                </p>

                <h3 class="doc-sub-title">2.1 Instant Credit Release</h3>
                <p class="doc-paragraph">
                    Once a session concludes and is marked completed, the 90% net earnings are immediately credited to your Available Balance. These funds are instantly available to be withdrawn or held in your platform wallet.
                </p>

                <h3 class="doc-sub-title">2.2 Cancellation Protection</h3>
                <p class="doc-paragraph">
                    If a student cancels a session within 24 hours of the scheduled time, or fails to attend without prior notice, platform cancellation rules ensure the tutor receives compensation for their reserved time.
                </p>
            </section>

            <div class="doc-divider"></div>

            <!-- Section 3: Withdrawal Timelines & Channels -->
            <section class="doc-section">
                <h2 class="doc-section-title">3. Payouts, Withdrawal Timelines & Supported Channels</h2>
                <p class="doc-paragraph">
                    Tutors can request a withdrawal of their available balance at any time directly through the <strong>Wallet & Earnings</strong> dashboard.
                </p>

                <h3 class="doc-sub-title">3.1 Supported Payment Methods</h3>
                <p class="doc-paragraph">
                    Withdrawals are disbursed via direct Paystack transfers to all recognized Ghanaian networks and financial institutions:
                </p>
                <ul class="doc-bullet-list">
                    <li><strong>Mobile Money:</strong> MTN MoMo, Telecel Cash, and AT Money.</li>
                    <li><strong>Direct Bank Transfer:</strong> All commercial banks licensed by the Bank of Ghana.</li>
                </ul>

                <h3 class="doc-sub-title">3.2 Processing Schedules</h3>
                <ul class="doc-bullet-list">
                    <li><strong>Standard Delivery:</strong> Transferred to your mobile money wallet or bank account within <strong>1 to 2 business days</strong> (24–48 hours).</li>
                    <li><strong>Business Hours:</strong> Requests submitted before 3:00 PM GMT on regular working days (Monday to Friday) are queued and initiated on the same day.</li>
                </ul>
            </section>

            <div class="doc-divider"></div>

            <!-- Section 4: Minimum Thresholds & Account Security -->
            <section class="doc-section">
                <h2 class="doc-section-title">4. Minimum Thresholds & Verification Rules</h2>
                <p class="doc-paragraph">
                    To maintain financial compliance and prevent automated fraudulent transactions, the following rules apply to all payout requests:
                </p>

                <h3 class="doc-sub-title">4.1 Minimum Payout Limit</h3>
                <p class="doc-paragraph">
                    The minimum withdrawal threshold is <strong>GHS {{ number_format($minPayoutAmount, 2) }}</strong>. Balances lower than this amount remain securely stored in your wallet until subsequent lessons bring the total above the threshold.
                </p>

                <h3 class="doc-sub-title">4.2 Name & Identity Verification</h3>
                <p class="doc-paragraph">
                    The registered mobile money subscriber name or bank account name must match your verified identity on DigiLearn. Payouts to unverified third-party accounts may be temporarily paused for security verification.
                </p>
            </section>

            <div class="doc-divider"></div>

            <!-- Section 5: Disputes & Support -->
            <section class="doc-section">
                <h2 class="doc-section-title">5. Dispute Resolution & Assistance</h2>
                <p class="doc-paragraph">
                    If an unforeseen technical disruption or attendance disagreement occurs during a scheduled session, either party may flag the booking for review within 24 hours.
                </p>
                <p class="doc-paragraph">
                    Our compliance team reviews session logs and connectivity reports to ensure equitable distribution of funds. For questions or assistance regarding any transaction, contact tutor operations at <strong>contact@shoutoutgh.com</strong>.
                </p>
            </section>

            <!-- Document Footer -->
            <footer class="doc-footer">
                <p class="doc-footer-note">
                    By providing tutoring services on DigiLearn, you agree to these settlement rules. Updates to policy will be communicated via your tutor notification center.
                </p>
                <div class="doc-footer-links">
                    <a href="{{ route('tutors.earnings.transactions') }}" class="footer-link">
                        <i class="fa-solid fa-arrow-left"></i> Return to Transactions
                    </a>
                    <a href="{{ route('tutors.earnings.index') }}" class="footer-link">
                        Go to Insights Dashboard <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </footer>
        </article>
    </div>

    <!-- Scoped Clean Vertical Document Styles (Anti-Glare & Neurodivergent-Friendly) -->
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        .policy-doc-wrapper {
            padding: 2rem 2rem 5rem;
            max-width: 860px;
            margin: 0 auto;
            color: #1e293b;
            font-family: inherit;
        }

        /* Top Header Bar */
        .policy-header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            width: 100%;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .policy-header-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .policy-header-sub {
            margin: 0.15rem 0 0 0;
            font-size: 0.8rem;
            color: #64748b;
        }

        .policy-header-right {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .header-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.48rem 1rem;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-primary {
            background: var(--secondary-blue);
            color: #ffffff;
            border: 1px solid transparent;
            box-shadow: 0 2px 4px rgba(38, 119, 184, 0.2);
        }

        .btn-primary:hover {
            background: var(--secondary-blue-hover);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: #f8fafc;
            color: #0f172a;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        }

        .btn-secondary:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            transform: translateY(-1px);
        }

        /* Article Layout (Borderless, Clean Reading Flow) */
        .policy-doc-article {
            background: transparent;
            border: none;
            padding: 1rem 0;
        }

        .doc-category-badge {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--secondary-blue);
            background: rgba(38, 119, 184, 0.08);
            padding: 0.25rem 0.65rem;
            border-radius: 6px;
            margin-bottom: 0.85rem;
        }

        .doc-main-title {
            font-size: 2.1rem;
            font-weight: 800;
            line-height: 1.25;
            color: #0f172a;
            letter-spacing: -0.03em;
            margin: 0 0 1rem 0;
        }

        .doc-lead {
            font-size: 1.05rem;
            line-height: 1.7;
            color: #475569;
            margin: 0 0 1.25rem 0;
            font-weight: 500;
        }

        .doc-meta-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.82rem;
            color: #64748b;
            font-weight: 600;
        }

        .doc-divider {
            height: 1px;
            background: #e2e8f0;
            margin: 2.25rem 0;
            border: none;
        }

        /* Section Headings & Paragraphs */
        .doc-section {
            margin-bottom: 1.5rem;
        }

        .doc-section-title {
            font-size: 1.35rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.015em;
            margin: 0 0 1rem 0;
            line-height: 1.3;
        }

        .doc-sub-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #1e293b;
            margin: 1.5rem 0 0.65rem 0;
            line-height: 1.4;
        }

        .doc-paragraph {
            font-size: 0.95rem;
            line-height: 1.75;
            color: #334155;
            margin: 0 0 1rem 0;
        }

        .doc-paragraph strong {
            color: #0f172a;
            font-weight: 700;
        }

        /* Bullet Points */
        .doc-bullet-list {
            margin: 0.75rem 0 1.25rem 1.25rem;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
        }

        .doc-bullet-list li {
            font-size: 0.92rem;
            line-height: 1.65;
            color: #334155;
        }

        .doc-bullet-list li strong {
            color: #0f172a;
        }

        /* Clean Calculation Table (Soft, Borderless Tone) */
        .doc-calculation-table {
            background: #f8fafc;
            border-radius: 10px;
            padding: 1rem 1.25rem;
            margin: 1rem 0 1.25rem 0;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
            max-width: 520px;
        }

        .calc-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.88rem;
            color: #475569;
        }

        .calc-row.calc-total {
            border-top: 1px dashed #cbd5e1;
            padding-top: 0.65rem;
            margin-top: 0.25rem;
            font-weight: 800;
            font-size: 0.95rem;
            color: #0f172a;
        }

        .calc-val {
            font-weight: 700;
            font-family: monospace;
            font-size: 0.9rem;
        }

        .text-red { color: var(--primary-red); }
        .text-green { color: #10b981; }

        /* Footer */
        .doc-footer {
            margin-top: 3rem;
            padding-top: 1.75rem;
            border-top: 1px solid #e2e8f0;
        }

        .doc-footer-note {
            font-size: 0.82rem;
            color: #64748b;
            line-height: 1.6;
            margin: 0 0 1.5rem 0;
        }

        .doc-footer-links {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .footer-link {
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--secondary-blue);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: color 0.15s ease;
        }

        .footer-link:hover {
            color: var(--secondary-blue-hover);
            text-decoration: underline;
        }

        @media (max-width: 640px) {
            .policy-doc-wrapper {
                padding: 1.25rem 1rem 3.5rem;
            }

            .doc-main-title {
                font-size: 1.65rem;
            }

            .policy-header-bar {
                flex-direction: column;
                align-items: flex-start;
            }

            .doc-footer-links {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
@endsection
