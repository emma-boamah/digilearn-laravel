<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PricingPlan;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Check if user is authenticated and redirect to dashboard
        // UNLESS they explicitly requested the home page via query param
        if (Auth::check() && !$request->has('show_home')) {
            $user = Auth::user();

            // Check if user is admin and redirect appropriately
            if ($user->is_admin || $user->is_superuser) {
                return redirect()->route('admin.dashboard');
            }

            // For regular users, redirect to their dashboard
            $selectedLevelGroup = $user->current_level_group ?? session('selected_level_group');
            
            if ($selectedLevelGroup) {
                if (!session('selected_level_group')) {
                    session(['selected_level_group' => $selectedLevelGroup]);
                }
                return redirect()->route('dashboard.main');
            } else {
                return redirect()->route('dashboard.level-selection');
            }
        }

        $courseCategories = [
            [
                'title' => 'Web Development',
                'img' => 'images/web-dev-course.png',
                'video' => 'videos/courses/web-development.mp4',
                'preview_video' => 'videos/samples/sample-1.mp4'
            ],
            [
                'title' => 'Data Science',
                'img' => 'images/data-science-course.png',
                'video' => 'videos/courses/data-science.mp4',
                'preview_video' => 'videos/samples/sample-2.mp4'
            ],
            [
                'title' => 'Mobile Development',
                'img' => 'images/mobile-dev-course.png',
                'video' => 'videos/courses/mobile-development.mp4',
                'preview_video' => 'videos/samples/sample-3.mp4'
            ],
            [
                'title' => 'Digital Marketing',
                'img' => 'images/digital-marketing-course.png',
                'video' => 'videos/courses/digital-marketing.mp4',
                'preview_video' => 'videos/samples/sample-4.mp4'
            ]
        ];

        $pricingPlans = PricingPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        $testimonials = [
            [
                'name' => 'Bonaventure Williamson',
                'role' => 'Parent',
                'image' => 'images/testimonial-1.png',
                'quote' => 'ShoutOutGh is an important tool for my kids. As a parent, I don’t have to pay for extra tuition anymore. This is where my kids learn every day. '
            ],
            [
                'name' => 'Travis Owusu Kelvin',
                'role' => 'Student - Mfantsipim School',
                'image' => 'images/testimonial-2.png',
                'quote' => 'This platform makes it easy to understand difficult topics. The video lessons are short, concise and self-explanatory. Also aligns with our curriculum. '
            ],
            [
                'name' => 'Princess Ivy',
                'role' => 'Student - University of Ghana',
                'image' => 'images/testimonial-3.png',
                'quote' => 'I love the resources on entrepreneurship and other non-traditional courses. This has given me the needed skills to start a business whilst in school.'
            ],
            [
                'name' => 'Nana Akua Nhyira',
                'role' => 'Student - University of Ghana',
                'image' => 'images/testimonial-4.png',
                'quote' => 'I like the fact that subscription isn’t expensive.  To pay less for such comprehensive resources on all subjects is just so considerate.'
            ]
        ];

        $faqs = [
            [
                'question' => 'How do I Watch a Video Lesson?',
                'answer' => 'To Watch a Video Lesson, simply create an account, browse our catalog, and click on the "Watch" button for the course you\'re interested in.'
            ],
            [
                'question' => 'Can I access ShoutOutGH Video Lessons on mobile devices?',
                'answer' => 'Yes, ShoutOutGH Video Lessons are fully responsive and can be accessed on smartphones, tablets, and computers. We are also working to offer a mobile app for iOS and Android devices.'
            ],
            [
                'question' => 'Are certificates provided upon course completion?',
                'answer' => 'No, we do not provide certificates of completion for most of our courses. However, we are considering adding this feature in the future.'
            ],
            [
                'question' => 'What happens if my account gets suspended?',
                'answer' => 'Account suspension occurs when community guidelines are violated. Learn more about our suspension policies, appeal process, and prevention tips in our <a href="' . route('guidelines.account-suspension') . '" style="color: #3b82f6; text-decoration: underline;">Account Suspension Guidelines</a>.'
            ]
        ];

        $stats = [
            ['value' => '500+', 'label' => 'Courses'],
            ['value' => '50k+', 'label' => 'Students'],
            ['value' => '100+', 'label' => 'Countries'],
            ['value' => '4.8/5', 'label' => 'Rating']
        ];

        return view('home', compact('courseCategories', 'pricingPlans', 'testimonials', 'faqs', 'stats'));
    }

    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        // Here you would typically save the email to your newsletter database
        // For now, we'll just redirect with a success message

        return redirect()->route('home')->with('success', 'Thank you for subscribing to our newsletter!');
    }
}
