@props(['type' => 'default'])

<div id="global-skeleton-loader" class="skeleton-wrapper" style="position: fixed; inset: 0; z-index: 99999; background: var(--bg-main, #f9fafb); width: 100vw; height: 100vh; overflow: hidden; overflow-y: auto; display: flex; flex-direction: column;">
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        /* Global Skeleton CSS */
        .skeleton-wrapper {
            transition: opacity 0.5s ease-out, visibility 0.5s ease-out;
        }

        .skeleton-wrapper.hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .skeleton-screen {
            background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%);
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s infinite linear;
            border-radius: 8px;
        }

        [data-theme="dark"] .skeleton-wrapper {
            background: var(--bg-main, #000000);
        }

        [data-theme="dark"] .skeleton-screen {
            background: linear-gradient(90deg, #1e293b 25%, #334155 50%, #1e293b 75%);
            background-size: 200% 100%;
        }

        @keyframes skeleton-loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Generic utilities */
        .sk-container { max-width: 1200px; margin: 0 auto; width: 100%; padding: 0 2rem; }

        /* Headers */
        .sk-header {
            height: 70px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            border-bottom: 1px solid var(--border-color, #e5e7eb);
            background: var(--bg-surface, #ffffff);
            flex-shrink: 0;
        }

        .sk-header-dash {
            height: 60px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1rem;
            border-bottom: 1px solid var(--border-color, #e5e7eb);
            background: var(--bg-surface, #ffffff);
            flex-shrink: 0;
        }

        .sk-logo { width: 150px; height: 35px; }
        .sk-nav { display: flex; gap: 1.5rem; }
        .sk-nav-item { width: 60px; height: 20px; }
        .sk-btn { width: 100px; height: 35px; border-radius: 9999px; }
        .sk-avatar { width: 40px; height: 40px; border-radius: 50%; }

        /* Home Specific */
        .sk-hero-full {
            height: calc(100vh - 70px);
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1.5rem;
            padding: 2rem;
        }
        .sk-hero-title { width: 60%; height: 60px; border-radius: 8px; }
        .sk-hero-subtitle { width: 40%; height: 30px; border-radius: 8px; }

        /* About Specific */
        .sk-about-hero {
            padding: 8rem 0 4rem;
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 2rem;
            align-items: center;
        }
        .sk-text-block { display: flex; flex-direction: column; gap: 1rem; }
        .sk-line { height: 20px; width: 100%; border-radius: 4px; }
        .sk-line-short { height: 20px; width: 70%; border-radius: 4px; }
        .sk-line-title { height: 40px; width: 80%; border-radius: 8px; margin-bottom: 1rem; }
        .sk-img-box { width: 100%; height: 400px; border-radius: 16px; }
        
        .sk-about-intro {
            padding: 6rem 0;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        /* Level Selection Specific */
        .sk-back { width: 120px; height: 20px; margin: 2rem 0; }
        .sk-level-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            padding: 1rem 0;
        }
        .sk-level-card {
            height: 380px;
            width: 100%;
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            background: var(--bg-surface, #ffffff);
            border: 1px solid var(--border-color, #e5e7eb);
        }
        .sk-card-img { width: 100%; height: 160px; border-radius: 8px; }
        .sk-card-title { width: 50%; height: 24px; }
        .sk-card-desc { width: 100%; height: 16px; }
        .sk-card-btn { width: 100%; height: 40px; border-radius: 8px; margin-top: auto; }

        @media (max-width: 768px) {
            .sk-nav { display: none; }
            .sk-about-hero { grid-template-columns: 1fr; text-align: center; }
            .sk-about-intro { grid-template-columns: 1fr; }
            .sk-hero-title { width: 90%; }
            .sk-hero-subtitle { width: 70%; }
        }

        .sk-digilearn-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem 1rem;
            padding: 2rem 1.5rem 3rem;
            align-content: flex-start;
        }
        
        @media (min-width: 768px) {
            .sk-digilearn-grid { grid-template-columns: repeat(2, 1fr); }
        }
        
        @media (min-width: 1024px) {
            .sk-digilearn-grid { grid-template-columns: repeat(3, 1fr); }
        }
    </style>

    @if($type === 'home')
        <div class="sk-header">
            <div class="skeleton-screen sk-logo"></div>
            <div class="sk-nav">
                <div class="skeleton-screen sk-nav-item"></div>
                <div class="skeleton-screen sk-nav-item"></div>
                <div class="skeleton-screen sk-nav-item"></div>
                <div class="skeleton-screen sk-nav-item"></div>
            </div>
            <div class="skeleton-screen sk-btn"></div>
        </div>
        <div class="sk-hero-full">
            <div class="skeleton-screen sk-hero-title"></div>
            <div class="skeleton-screen sk-hero-subtitle"></div>
            <div class="skeleton-screen sk-btn" style="margin-top: 2rem; width: 150px; height: 50px;"></div>
        </div>
    @elseif($type === 'about')
        <div class="sk-header">
            <div class="skeleton-screen sk-logo"></div>
            <div class="sk-nav">
                <div class="skeleton-screen sk-nav-item"></div>
                <div class="skeleton-screen sk-nav-item"></div>
                <div class="skeleton-screen sk-nav-item"></div>
                <div class="skeleton-screen sk-nav-item"></div>
            </div>
            <div class="skeleton-screen sk-btn"></div>
        </div>
        <div class="sk-container">
            <div class="sk-about-hero">
                <div class="sk-text-block">
                    <div class="skeleton-screen sk-line-title"></div>
                    <div class="skeleton-screen sk-line"></div>
                    <div class="skeleton-screen sk-line"></div>
                    <div class="skeleton-screen sk-line-short"></div>
                </div>
                <div class="skeleton-screen sk-img-box"></div>
            </div>
            <div class="sk-about-intro">
                <div class="sk-text-block">
                    <div class="skeleton-screen sk-line-title"></div>
                    <div class="skeleton-screen sk-line"></div>
                    <div class="skeleton-screen sk-line"></div>
                    <div class="skeleton-screen sk-line"></div>
                    <div class="skeleton-screen sk-line"></div>
                    <div class="skeleton-screen sk-line-short"></div>
                </div>
                <div class="skeleton-screen sk-img-box" style="height: 350px;"></div>
            </div>
        </div>
    @elseif($type === 'level-selection')
        <div class="sk-header-dash">
            <div class="skeleton-screen sk-logo" style="width: 120px;"></div>
            <div class="skeleton-screen sk-avatar"></div>
        </div>
        <div class="sk-container">
            <div class="skeleton-screen sk-back"></div>
            <div class="sk-level-grid">
                @for($i = 0; $i < 4; $i++)
                <div class="sk-level-card">
                    <div class="skeleton-screen sk-card-title"></div>
                    <div class="skeleton-screen sk-card-img"></div>
                    <div class="skeleton-screen sk-card-desc"></div>
                    <div class="skeleton-screen sk-card-desc" style="width: 80%"></div>
                    <div class="skeleton-screen sk-card-btn"></div>
                </div>
                @endfor
            </div>
        </div>
    @elseif($type === 'pricing')
        <div class="sk-header">
            <div class="skeleton-screen sk-logo"></div>
            <div class="sk-nav">
                <div class="skeleton-screen sk-nav-item"></div>
                <div class="skeleton-screen sk-nav-item"></div>
                <div class="skeleton-screen sk-nav-item"></div>
                <div class="skeleton-screen sk-nav-item"></div>
            </div>
            <div class="skeleton-screen sk-btn"></div>
        </div>
        <div class="skeleton-screen" style="width: 100%; height: 300px;"></div>
        <div class="sk-container" style="padding-top: 4rem;">
            <div style="display: flex; flex-direction: column; align-items: center; gap: 1rem; margin-bottom: 3rem;">
                <div class="skeleton-screen sk-line-title"></div>
                <div class="skeleton-screen sk-line-short"></div>
            </div>
            <div class="sk-level-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
                @for($i = 0; $i < 3; $i++)
                <div class="sk-level-card" style="height: 450px;">
                    <div class="skeleton-screen sk-card-title" style="margin: 0 auto 2rem;"></div>
                    <div class="skeleton-screen sk-line" style="margin-bottom: 0.5rem;"></div>
                    <div class="skeleton-screen sk-line" style="margin-bottom: 2rem;"></div>
                    <div class="skeleton-screen sk-img-box" style="height: 60px; margin-bottom: 2rem;"></div>
                    <div class="skeleton-screen sk-line" style="margin-bottom: 1rem;"></div>
                    <div class="skeleton-screen sk-line" style="margin-bottom: 1rem;"></div>
                    <div class="skeleton-screen sk-line" style="margin-bottom: 1rem;"></div>
                    <div class="skeleton-screen sk-card-btn" style="margin-top: auto;"></div>
                </div>
                @endfor
            </div>
        </div>
    @elseif($type === 'digilearn')
        <div style="display: flex; height: 100vh; overflow: hidden; width: 100%;">
            <!-- Sidebar -->
            <div style="width: 240px; border-right: 1px solid var(--border-color, #e5e7eb); display: flex; flex-direction: column; flex-shrink: 0; background: var(--bg-surface, #ffffff);">
                <div style="height: 60px; padding: 0 1.5rem; display: flex; align-items: center; border-bottom: 1px solid transparent;">
                    <!-- Brand Space left empty as it's in the top header in the screenshot -->
                </div>
                <div style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1.5rem;">
                    <!-- MAIN -->
                    <div class="skeleton-screen sk-line" style="width: 40px; height: 10px;"></div>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <div style="display: flex; gap: 1rem; align-items: center;"><div class="skeleton-screen" style="width: 20px; height: 20px; border-radius: 4px;"></div><div class="skeleton-screen sk-line" style="width: 60px; height: 14px;"></div></div>
                        <div style="display: flex; gap: 1rem; align-items: center;"><div class="skeleton-screen" style="width: 20px; height: 20px; border-radius: 4px;"></div><div class="skeleton-screen sk-line" style="width: 80px; height: 14px;"></div></div>
                        <div style="display: flex; gap: 1rem; align-items: center;"><div class="skeleton-screen" style="width: 20px; height: 20px; border-radius: 4px;"></div><div class="skeleton-screen sk-line" style="width: 70px; height: 14px;"></div></div>
                    </div>
                    
                    <!-- LEARNING -->
                    <div class="skeleton-screen sk-line" style="width: 60px; height: 10px; margin-top: 1rem;"></div>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <div style="display: flex; gap: 1rem; align-items: center;"><div class="skeleton-screen" style="width: 20px; height: 20px; border-radius: 4px;"></div><div class="skeleton-screen sk-line" style="width: 90px; height: 14px;"></div></div>
                        <div style="display: flex; gap: 1rem; align-items: center;"><div class="skeleton-screen" style="width: 20px; height: 20px; border-radius: 4px;"></div><div class="skeleton-screen sk-line" style="width: 100px; height: 14px;"></div></div>
                        <div style="display: flex; gap: 1rem; align-items: center;"><div class="skeleton-screen" style="width: 20px; height: 20px; border-radius: 4px;"></div><div class="skeleton-screen sk-line" style="width: 80px; height: 14px;"></div></div>
                        <div style="display: flex; gap: 1rem; align-items: center;"><div class="skeleton-screen" style="width: 20px; height: 20px; border-radius: 4px;"></div><div class="skeleton-screen sk-line" style="width: 70px; height: 14px;"></div></div>
                        <div style="display: flex; gap: 1rem; align-items: center;"><div class="skeleton-screen" style="width: 20px; height: 20px; border-radius: 4px;"></div><div class="skeleton-screen sk-line" style="width: 85px; height: 14px;"></div></div>
                    </div>

                    <!-- ACCOUNT -->
                    <div class="skeleton-screen sk-line" style="width: 55px; height: 10px; margin-top: 1rem;"></div>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <div style="display: flex; gap: 1rem; align-items: center;"><div class="skeleton-screen" style="width: 20px; height: 20px; border-radius: 4px;"></div><div class="skeleton-screen sk-line" style="width: 80px; height: 14px;"></div></div>
                        <div style="display: flex; gap: 1rem; align-items: center;"><div class="skeleton-screen" style="width: 20px; height: 20px; border-radius: 4px;"></div><div class="skeleton-screen sk-line" style="width: 120px; height: 14px;"></div></div>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div style="flex: 1; display: flex; flex-direction: column; width: calc(100% - 240px); position: relative;">
                <!-- Top Header -->
                <div style="height: 60px; padding: 0 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color, #e5e7eb); background: var(--bg-surface, #ffffff);">
                    <div style="display: flex; gap: 1.5rem; align-items: center;">
                        <div class="skeleton-screen" style="width: 24px; height: 24px; border-radius: 4px;"></div>
                        <div class="skeleton-screen sk-logo" style="width: 140px; height: 32px;"></div>
                    </div>
                    <div style="display: flex; gap: 1.5rem; align-items: center;">
                        <div class="skeleton-screen" style="width: 24px; height: 24px; border-radius: 50%;"></div>
                        <div class="skeleton-screen" style="width: 24px; height: 24px; border-radius: 50%;"></div>
                        <div class="skeleton-screen sk-avatar" style="width: 36px; height: 36px;"></div>
                    </div>
                </div>
                
                <!-- Filter Bar -->
                <div style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--border-color, #e5e7eb); display: flex; flex-direction: column; gap: 1rem; background: var(--bg-surface, #ffffff);">
                    <!-- Row 1 -->
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <div class="skeleton-screen sk-line" style="width: 100px; height: 14px;"></div>
                        <div class="skeleton-screen" style="width: 70px; height: 32px; border-radius: 16px;"></div>
                        <div class="skeleton-screen" style="width: 70px; height: 32px; border-radius: 16px;"></div>
                        <div class="skeleton-screen" style="width: 70px; height: 32px; border-radius: 16px;"></div>
                        <div class="skeleton-screen" style="width: 300px; height: 36px; border-radius: 4px;"></div>
                        <div class="skeleton-screen" style="width: 80px; height: 36px; border-radius: 4px; margin-left: auto;"></div>
                    </div>
                    <!-- Row 2 -->
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <div class="skeleton-screen sk-line" style="width: 80px; height: 14px;"></div>
                        <div class="skeleton-screen" style="width: 70px; height: 32px; border-radius: 16px;"></div>
                        <div class="skeleton-screen" style="width: 80px; height: 32px; border-radius: 16px;"></div>
                    </div>
                    <!-- Row 3 -->
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <div class="skeleton-screen sk-line" style="width: 80px; height: 14px;"></div>
                        <div class="skeleton-screen" style="width: 70px; height: 32px; border-radius: 16px;"></div>
                        <div class="skeleton-screen" style="width: 120px; height: 32px; border-radius: 16px;"></div>
                        <div class="skeleton-screen" style="width: 90px; height: 32px; border-radius: 16px;"></div>
                    </div>
                </div>
                
                <div style="flex: 1; overflow-y: auto; display: flex; flex-direction: column;">
                    <!-- Hero Banner -->
                    <div class="skeleton-screen" style="width: 100%; height: 240px; border-radius: 0; display: flex; flex-direction: column; justify-content: center; padding: 0 4rem; gap: 1rem; flex-shrink: 0;">
                        <div class="skeleton-screen" style="width: 300px; height: 36px; background: rgba(255,255,255,0.2);"></div>
                        <div class="skeleton-screen" style="width: 200px; height: 20px; background: rgba(255,255,255,0.2);"></div>
                    </div>
                    
                    <!-- Video Grid -->
                    <div class="sk-digilearn-grid">
                        @for($i = 0; $i < 6; $i++)
                        <div style="display: flex; flex-direction: column; border-radius: 0.75rem; overflow: hidden; border: 1px solid var(--border-color, #e5e7eb); background: var(--bg-surface, #ffffff);">
                            <div class="skeleton-screen" style="width: 100%; aspect-ratio: 16/9; position: relative;">
                                <!-- Level Badge -->
                                <div class="skeleton-screen" style="position: absolute; top: 1rem; left: 1rem; width: 60px; height: 24px; border-radius: 12px; background: rgba(255,255,255,0.3);"></div>
                                <!-- Play Button -->
                                <div class="skeleton-screen" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 48px; height: 48px; border-radius: 50%; background: rgba(255,255,255,0.4);"></div>
                                <!-- Duration -->
                                <div class="skeleton-screen" style="position: absolute; bottom: 0.5rem; right: 0.5rem; width: 40px; height: 20px; border-radius: 4px; background: rgba(0,0,0,0.3);"></div>
                            </div>
                            <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
                                <div class="skeleton-screen sk-line" style="width: 80%; height: 20px;"></div>
                                
                                <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 0.5rem;">
                                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                        <div class="skeleton-screen sk-line" style="width: 100px; height: 14px;"></div>
                                        <div class="skeleton-screen sk-line" style="width: 160px; height: 12px;"></div>
                                    </div>
                                    <div class="skeleton-screen" style="width: 28px; height: 28px; border-radius: 4px;"></div>
                                </div>
                            </div>
                        </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Default Fallback -->
        <div class="sk-header">
            <div class="skeleton-screen sk-logo"></div>
            <div class="sk-nav">
                <div class="skeleton-screen sk-nav-item"></div>
                <div class="skeleton-screen sk-nav-item"></div>
                <div class="skeleton-screen sk-nav-item"></div>
                <div class="skeleton-screen sk-nav-item"></div>
            </div>
            <div class="skeleton-screen sk-btn"></div>
        </div>
        <div class="sk-container" style="padding-top: 2rem;">
            <div class="skeleton-screen sk-img-box" style="margin-bottom: 2rem;"></div>
            <div class="sk-level-grid">
                <div class="skeleton-screen sk-img-box" style="height: 200px;"></div>
                <div class="skeleton-screen sk-img-box" style="height: 200px;"></div>
                <div class="skeleton-screen sk-img-box" style="height: 200px;"></div>
            </div>
        </div>
    @endif

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        (function() {
            const removeSkeleton = () => {
                const skeleton = document.getElementById('global-skeleton-loader');
                if (skeleton && !skeleton.classList.contains('hidden')) {
                    skeleton.classList.add('hidden');
                    setTimeout(() => skeleton.remove(), 500); // Remove from DOM after fade out
                }
            };

            // Remove on load
            window.addEventListener('load', removeSkeleton);
            
            // Fallback: Remove after 3 seconds maximum to ensure the page doesn't get stuck
            setTimeout(removeSkeleton, 3000);
        })();
    </script>
</div>
