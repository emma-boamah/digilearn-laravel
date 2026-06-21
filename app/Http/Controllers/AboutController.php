<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AboutController extends Controller
{
    public function index()
    {
        $partners = [1, 2, 3, 4, 5, 6]; // Partner image IDs
        
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

        $stats = [
            ['value' => '500+', 'label' => 'Courses'],
            ['value' => '206K+', 'label' => 'Students'],
            ['value' => '2M+', 'label' => 'Tutors'],
            ['value' => '4.8/5', 'label' => 'Rating']
        ];

        return view('about', compact('partners', 'testimonials', 'stats'));
    }
}
