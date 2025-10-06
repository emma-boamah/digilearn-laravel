<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PricingPlan;

class PricingController extends Controller
{
    public function index()
    {
        $pricingPlans = PricingPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        return view('pricing', compact('pricingPlans'));
    }

    public function show()
    {
        $pricingPlans = [
            'essential' => [
                'name' => 'Essential',
                'price' => 'Ghs 50.00',
                'period' => '7 days free trial',
                'features' => [
                    'DigiLearn',
                    'Access to unlimited learning materials such as demonstration videos, interactive videos presenting various subjects, learning objectives, study guides'
                ]
            ],
            'extra-tuition' => [
                'name' => 'Extra Tuition',
                'price' => 'Ghs 200.00',
                'period' => '7 days free trial',
                'features' => [
                    'DigiLearn',
                    'Access to unlimited learning materials such as demonstration videos, interactive videos presenting various subjects, learning objectives, study guides',
                    'Join a class',
                    'Access to unlimited learning materials such as demonstration videos, interactive videos presenting various subjects, learning objectives, study guides',
                    '24hr support service',
                    'Access to unlimited learning materials such as demonstration videos, interactive videos presenting various subjects, learning objectives, study guides',
                    'Personalised tuition',
                    'Access to unlimited learning materials such as demonstration videos, interactive videos presenting various subjects, learning objectives, study guides',
                    'Learning Resources',
                    'Access to unlimited learning materials such as demonstration videos, interactive videos presenting various subjects, learning objectives, study guides',
                ]
            ],
            'home-sch' => [
                'name' => 'Home Sch',
                'price' => 'Ghs 200.00',
                'period' => '7 days free trial',
                'features' => [
                    'DigiLearn',
                    'Access to unlimited learning materials such as demonstration videos, interactive videos presenting various subjects, learning objectives, study guides',
                    'Join a class',
                    'Access to unlimited learning materials such as demonstration videos, interactive videos presenting various subjects, learning objectives, study guides',
                    'PPT support service',
                    'Access to unlimited learning materials such as demonstration videos, interactive videos presenting various subjects, learning objectives, study guides',
                    'Personalized Tuition (1 session)',
                    'Access to unlimited learning materials such as demonstration videos, interactive videos presenting various subjects, learning objectives, study guides',
                    'Learning Resources',
                    'Access to unlimited learning materials such as demonstration videos, interactive videos presenting various subjects, learning objectives, study guides'
                ]
            ]
        ];

        return view ('pricing-details', compact('pricingPlans'));
    }
}
