<?php

namespace Database\Seeders;

use App\Models\CompanyProfile;
use App\Models\Template;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminPassword = config('agency.admin_password');
        if (! $adminPassword) {
            if (app()->environment('production')) {
                throw new \RuntimeException('ADMIN_PASSWORD wajib diisi sebelum seeding di production.');
            }
            $adminPassword = 'password'; // hanya untuk lokal
        }

        User::firstOrCreate(
            ['email' => config('agency.admin_email')],
            [
                'name' => 'Admin B!ng0.dev',
                'password' => $adminPassword, // auto-hash via cast
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        CompanyProfile::firstOrCreate([], [
            'agency_name' => 'B!ng0.dev',
            'about' => 'B!ng0.dev adalah tim pengembang web dan aplikasi custom yang membantu bisnis bertransformasi digital.',
            'services' => 'Company Profile, E-Commerce, Sistem Informasi, Custom Web Application, Dashboard & Automation.',
            'advantages' => 'Tim teknikal berpengalaman, proses kerja transparan, dukungan pasca-peluncuran.',
            'process' => 'Discovery → Requirement → Desain → Development → QA → Deployment → Maintenance.',
            'contact_email' => 'hello@bing0.dev',
            'contact_phone' => '+62 812-0000-0000',
            'footer_title' => 'B!ng0.dev',
            'footer_description' => 'Jasa pengembangan web dan aplikasi custom untuk membantu bisnis Anda bertumbuh.',
            'footer_whatsapp' => '+62 812-0000-0000',
            'footer_email' => 'hello@bing0.dev',
            'footer_address' => '',
        ]);

        $templates = [
            ['name' => 'Proposal Website Umum', 'type' => 'proposal', 'category' => 'Company Profile'],
            ['name' => 'Proposal E-Commerce', 'type' => 'proposal', 'category' => 'E-Commerce'],
            ['name' => 'Proposal Sistem Informasi', 'type' => 'proposal', 'category' => 'Sistem Informasi'],
            ['name' => 'Follow-up Setelah Konsultasi', 'type' => 'follow_up', 'category' => null],
            ['name' => 'Konfirmasi Request Diterima', 'type' => 'confirmation', 'category' => null],
        ];

        $teamMembers = [
            ['name' => 'Dimas Pratama', 'position' => 'Backend Developer', 'skills' => ['Laravel', 'Sistem Informasi', 'MySQL']],
            ['name' => 'Sari Wulandari', 'position' => 'UI/UX Designer', 'skills' => ['UI Design', 'Company Profile', 'Figma']],
            ['name' => 'Fajar Nugroho', 'position' => 'Fullstack Developer', 'skills' => ['E-Commerce', 'React', 'Laravel']],
        ];
        foreach ($teamMembers as $i => $t) {
            TeamMember::firstOrCreate(
                ['name' => $t['name']],
                ['position' => $t['position'], 'skills' => $t['skills'], 'is_active' => true, 'sort_order' => $i]
            );
        }

        foreach ($templates as $t) {
            Template::firstOrCreate(
                ['name' => $t['name']],
                [
                    'type' => $t['type'],
                    'category' => $t['category'],
                    'content' => "Halo {contact_person},\n\nTerima kasih telah menghubungi {nama_agency} untuk kebutuhan {jenis_website} \"{nama_proyek}\".\n\n[Isi proposal di sini]\n\nSalam,\n{nama_agency}",
                    'is_active' => true,
                ]
            );
        }
    }
}
