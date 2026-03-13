<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\PricingPlan;

class PricingStructuredDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_pricing_page_contains_structured_data_with_image()
    {
        // Setup: Create a pricing plan
        PricingPlan::create([
            'name' => 'Essential Plus Plan',
            'slug' => 'essential-plus',
            'price' => 19.99,
            'currency' => 'GHS',
            'description' => 'All the benefits of Essential, extended to SHS( Grade 10-12)',
            'features' => ['Access to lessons', 'Quizzes'],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->get('/pricing');

        $response->assertStatus(200);
        
        // Assert JSON-LD is present
        $response->assertSee('application/ld+json');
        
        // Assert Schema type and content (unescaped)
        $response->assertSee('"@type": "Product"', false);
        $response->assertSee('"name": "Essential Plus Plan"', false);
        $response->assertSee('"image"', false);
        $response->assertSee('shoutoutgh-logo.png', false);
        $response->assertSee('"price": "19.99"', false);
        $response->assertSee('"priceCurrency": "GHS"', false);
        $response->assertSee('"availability": "https://schema.org/InStock"', false);
    }
}
