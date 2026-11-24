<?php

namespace Database\Seeders;

use App\Models\PricePackage;
use App\Models\Service;
use App\Models\SiteSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $catalog = [
            ['Moving Services', 'moving-services', 'A smoother move, from the first box to the final arrival.', 'Moving', [['Bedsitter / Studio', 17000], ['One Bedroom', 18000], ['Two Bedrooms', 20000], ['Three Bedrooms', 25000], ['Four Bedrooms', 34000]]],
            ['Fumigation Services', 'fumigation-services', 'Thoughtful fumigation services for a more comfortable home.', 'Fumigation', [['Bedsitter / Studio', 5000], ['One Bedroom', 5000], ['Two Bedrooms', 6500], ['Three Bedrooms', 7500], ['Four Bedrooms', 9000], ['Five Bedrooms', 10000]]],
            ['Sofa & Mattress Cleaning', 'sofa-mattress-cleaning', 'Refresh the furniture and mattresses you use every day.', 'Cleaning', [['Sofa · 1–3 seaters', 4000], ['Sofa · 4–5 seaters', 6000], ['Sofa · 6–7 seaters', 7000], ['Sofa · 8–10 seaters', 9000], ['Mattress · 4×6', 3000], ['Mattress · 5×6', 4000], ['Mattress · 6×6', 4500]]],
            ['House Deep Cleaning', 'house-deep-cleaning', 'A detailed clean to help your space feel fresh again.', 'Home', [['Bedsitter / Studio', 10000], ['One Bedroom', 12000], ['Two Bedrooms', 15000], ['Three Bedrooms', 18000], ['Four Bedrooms', 22000], ['Five Bedrooms', 30000]]],
        ];

        foreach ($catalog as $index => [$name, $slug, $summary, $icon, $packages]) {
            $service = Service::updateOrCreate(['slug' => $slug], [
                'name' => $name, 'summary' => $summary, 'icon' => $icon,
                'description' => $summary.' Request a booking and our team will follow up to confirm the details.',
                'is_active' => true, 'sort_order' => $index,
            ]);

            foreach ($packages as $order => [$package, $amount]) {
                PricePackage::updateOrCreate(['service_id' => $service->id, 'name' => $package], [
                    'amount' => $amount, 'pricing_type' => 'starting', 'is_active' => true, 'sort_order' => $order,
                ]);
            }
            if ($slug === 'moving-services') {
                PricePackage::updateOrCreate(['service_id' => $service->id, 'name' => 'Five Bedroom'], [
                    'amount' => 0, 'pricing_type' => 'quote', 'is_active' => true, 'sort_order' => count($packages),
                    'description' => 'Contact us to discuss this move.',
                ]);
            }
        }

        foreach ([
            'company_name' => 'HOME MOVERS AND CLEANERS',
            'tagline' => 'Wherever You Go, We Take Home With You.',
            'hero_heading' => 'Professional Moving & Cleaning Services',
            'hero_description' => 'From moving to deep cleaning, we provide convenient solutions to help you maintain a clean, comfortable home and enjoy a smoother moving experience.',
            'about_preview' => 'Life moves. Rooms need a reset. Our moving and cleaning services are here to make those moments feel a little lighter.',
            'phone' => '07999 48 186',
            'whatsapp' => '25479948186',
            'booking_recipient' => 'bookings@homemoversandcleaners.co.ke',
            'enquiry_recipient' => 'bookings@homemoversandcleaners.co.ke',
            'notification_cc' => 'albertmuhatia@gmail.com, homemoversandcleaners@gmail.com',
            'facebook' => 'https://www.facebook.com/share/1EvzboeHp3/',
            'instagram' => 'https://www.instagram.com/home_movers_and_cleaners',
            'tiktok' => 'https://www.tiktok.com/@homemoversandcleaners',
        ] as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
