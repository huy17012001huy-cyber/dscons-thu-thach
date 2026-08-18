<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            [
                'id'               => 1,
                'name'             => 'The All In Plan',
                'slug'             => 'taip',
                'domain'           => 'app.jointaip.com',
                'tagline'          => 'B2C · Training & Community',
                'theme_primary'    => '#1d9e75',
                'theme_accent'     => '#1d9e75',
                'theme_bg'         => '#f0ede8',
                'has_expeditions'  => true,
                'has_academy'      => true,
                'has_marketplace'  => true,
                'has_qa'           => true,
                'is_invite_only'   => false,
                'registration_mode'=> 'open',
            ],
            [
                'id'               => 2,
                'name'             => 'The Covenant',
                'slug'             => 'covenant',
                'domain'           => 'app.thecovenant.network',
                'tagline'          => 'Brotherhood · Invite only',
                'theme_primary'    => '#8B5CF6',
                'theme_accent'     => '#fac775',
                'theme_bg'         => '#1a1a18',
                'has_expeditions'  => false,
                'has_academy'      => false,
                'has_marketplace'  => false,
                'has_qa'           => false,
                'is_invite_only'   => true,
                'registration_mode'=> 'invite',
            ],
            [
                'id'               => 3,
                'name'             => 'The AI Neighborhood',
                'slug'             => 'ain',
                'domain'           => 'app.theaineighborhood.com',
                'tagline'          => 'B2B · Enterprise',
                'theme_primary'    => '#2563EB',
                'theme_accent'     => '#185fa5',
                'theme_bg'         => '#f0f6fd',
                'has_expeditions'  => false,
                'has_academy'      => true,
                'has_marketplace'  => true,
                'has_qa'           => true,
                'is_invite_only'   => false,
                'registration_mode'=> 'open',
            ],
        ];

        foreach ($brands as $data) {
            Brand::updateOrCreate(['id' => $data['id']], $data);
        }
    }
}
