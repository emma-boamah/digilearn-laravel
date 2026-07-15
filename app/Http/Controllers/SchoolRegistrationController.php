<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\School;
use App\Models\User;
use App\Models\PricingPlan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;

class SchoolRegistrationController extends Controller
{
    /**
     * Show the school registration form.
     */
    public function showRegistrationForm(Request $request)
    {
        $plan = $request->query('plan', 'school-pro');
        $pricingPlan = PricingPlan::where('slug', $plan)->first();

        if (!$pricingPlan) {
            return redirect()->route('for-schools')->with('error', 'Invalid plan selected.');
        }

        $draft = auth()->user()->school_registration_draft ? json_decode(auth()->user()->school_registration_draft, true) : [];

        return view('schools.register', compact('pricingPlan', 'draft'));
    }

    public function saveDraft(Request $request)
    {
        $user = auth()->user();
        $draft = $user->school_registration_draft ? json_decode($user->school_registration_draft, true) : [];

        $newData = $request->except(['_token', 'plan_slug', 'ges_certificate', 'business_certificate']);
        $draft = array_merge($draft, $newData);

        $user->school_registration_draft = json_encode($draft);
        $user->save();

        return response()->json(['success' => true]);
    }

    /**
     * Handle the registration form submission.
     */
    public function register(Request $request)
    {
        $request->validate([
            // Step 1 — School Profile
            'school_name' => 'required|string|max:255',
            'school_type' => 'required|in:public,private,international,faith_based',
            'ges_registration_number' => 'required|string|max:50',
            'school_phone' => ['required', 'string', 'regex:/^(\+233|0)[0-9]{9}$/'],
            'school_email' => 'required|email|max:255',
            'subdomain' => ['required', 'string', 'max:50', 'unique:schools,subdomain', 'regex:/^[a-z0-9\-]+$/i'],

            // Step 2 — Location & Documents
            'gps_address' => ['required', 'string', 'regex:/^[A-Z]{2}-\d{3,4}-\d{4}$/i'],
            'region' => 'required|string|max:100',
            'district' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'ges_certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'business_certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'tin_number' => 'required|string|max:20',

            'plan_slug' => 'required|exists:pricing_plans,slug',
        ], [
            'subdomain.regex' => 'The subdomain may only contain letters, numbers, and dashes.',
            'school_phone.regex' => 'Please enter a valid Ghanaian phone number.',
            'gps_address.regex' => 'Please enter a valid Ghana Post GPS address (e.g. GA-123-4567).',
        ]);

        // Determine tier from plan slug
        $pricingPlan = PricingPlan::where('slug', $request->plan_slug)->firstOrFail();
        $tier = str_contains($request->plan_slug, 'enterprise') ? 'enterprise' : 'pro';
        $tierConfig = School::tierConfig()[$tier];

        // Handle File Uploads
        $gesPath = $request->file('ges_certificate')->store('school_documents/' . date('Y_m'), 'local');
        $businessPath = $request->file('business_certificate')->store('school_documents/' . date('Y_m'), 'local');

        // Create the school in pending status with licensing and profile data
        $school = School::create([
            'name' => $request->school_name,
            'subdomain' => strtolower($request->subdomain),
            'school_type' => $request->school_type,
            'ges_registration_number' => $request->ges_registration_number,
            'phone' => $request->school_phone,
            'school_email' => $request->school_email,
            'gps_address' => strtoupper($request->gps_address),
            'region' => $request->region,
            'district' => $request->district,
            'city' => $request->city,
            'ges_certificate_path' => $gesPath,
            'business_certificate_path' => $businessPath,
            'tin_number' => strtoupper($request->tin_number),
            'verification_status' => 'pending',

            'status' => 'pending',
            'plan_tier' => $tier,
            'max_seats' => $tierConfig['max_seats'],
            'price_per_seat' => $tierConfig['price_per_seat'],
            'billing_cycle' => 'term',
            'pricing_plan_id' => $pricingPlan->id,
        ]);

        // Upgrade the current user to a school admin
        $user = auth()->user();
        $user->school_id = $school->id;
        $user->designation = 'Administrator';
        $user->school_registration_draft = null; // Clear draft
        $user->save();

        if (!$user->hasRole('school-admin')) {
            $user->assignRole('school-admin');
        }

        // Redirect to checkout
        return redirect()->route('school.checkout', ['plan' => $request->plan_slug]);
    }

    /**
     * Show the checkout page for the school plan.
     */
    public function checkout(Request $request)
    {
        $planSlug = $request->query('plan', 'school-pro');
        $plan = PricingPlan::where('slug', $planSlug)->firstOrFail();

        $school = Auth::user()->school;

        if (!$school || $school->status === 'active') {
            return redirect()->route('dashboard.main');
        }

        return view('schools.checkout', compact('plan', 'school'));
    }
}
