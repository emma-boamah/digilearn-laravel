<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PricingPlan;

class HomeController extends Controller
{
    public function index()
    {
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
                'name' => 'Sarah Johnson',
                'role' => 'Web Development Graduate',
                'image' => 'images/testimonial-1.png',
                'quote' => 'DigiLearn helped me acquire the skills I needed to advance my career. The courses are comprehensive and the instructors are experts in their fields.'
            ],
            [
                'name' => 'Michael Chen',
                'role' => 'Data Science Student',
                'image' => 'images/testimonial-2.png',
                'quote' => 'The flexibility of DigiLearn\'s platform allowed me to study while working full-time. I\'ve already applied what I\'ve learned to real-world projects.'
            ],
            [
                'name' => 'Emily Rodriguez',
                'role' => 'Digital Marketing Professional',
                'image' => 'images/testimonial-3.png',
                'quote' => 'As someone who needed to update my skills, DigiLearn provided exactly what I needed. The courses are practical and up-to-date with industry trends.'
            ],
            [
                'name' => 'David Kim',
                'role' => 'Mobile App Developer',
                'image' => 'images/testimonial-4.png',
                'quote' => 'The community support and instructor feedback make DigiLearn stand out. I feel confident in my abilities after completing their courses.'
            ]
        ];

        $faqs = [
            [
                'question' => 'How do I sign up for a course?',
                'answer' => 'To sign up for a course, simply create an account, browse our catalog, and click on the "Enroll" button for the course you\'re interested in. Follow the prompts to complete your enrollment.'
            ],
            [
                'question' => 'Can I access DigiLearn courses on mobile devices?',
                'answer' => 'Yes, DigiLearn is fully responsive and can be accessed on smartphones, tablets, and computers. We also offer a mobile app for iOS and Android devices.'
            ],
            [
                'question' => 'Are certificates provided upon course completion?',
                'answer' => 'Yes, we provide certificates of completion for most of our courses. These certificates can be added to your resume or LinkedIn profile to showcase your new skills.'
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
