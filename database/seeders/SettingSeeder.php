<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::create([
            'company_name' => 'Simply CPW',
            'company_address' => '123 Main St, Anytown, USA',
            'company_phone' => '123-456-7890',
            'company_email' => 'info@simplycpw.com',
            'company_logo' => 'https://via.placeholder.com/150',
            'company_description' => 'Simply CPW is a company that provides services to the public.',
            'company_facebook' => 'https://www.facebook.com/simplycpw',
            'company_instagram' => 'https://www.instagram.com/simplycpw',
            'company_twitter' => 'https://www.twitter.com/simplycpw',
            'company_linkedin' => 'https://www.linkedin.com/company/simplycpw',
            'company_youtube' => 'https://www.youtube.com/channel/UC_x5XG1OV2P6BVIhjj9pi-g',
            'company_tiktok' => 'https://www.tiktok.com/@simplycpw',
            'company_pinterest' => 'https://www.pinterest.com/simplycpw',
            'company_story' => 'Simply CPW is a company that provides services to the public.',
            'company_mission' => 'Simply CPW is a company that provides services to the public.',
            'company_vision' => 'Simply CPW is a company that provides services to the public.',
        ]);
    }
}
