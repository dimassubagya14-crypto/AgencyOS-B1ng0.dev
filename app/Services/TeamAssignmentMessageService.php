<?php

namespace App\Services;

use App\Models\CompanyProfile;
use App\Models\ServiceRequest;
use App\Models\TeamMember;

class TeamAssignmentMessageService
{
    public function render(ServiceRequest $request, TeamMember $member, string $task = '', ?string $deadline = null): array
    {
        $agency = CompanyProfile::current()->agency_name;
        $deadlineText = $deadline ? date('d M Y', strtotime($deadline)) : 'Belum ditentukan';
        $taskText = trim($task) !== '' ? trim($task) : 'Silakan cek detail request dan koordinasikan kebutuhan teknis dengan admin.';

        $whatsapp = "Halo {$member->name},\n\n";
        $whatsapp .= "Kamu mendapat tugas dari {$agency}.\n\n";
        $whatsapp .= "REQUEST: {$request->number}\n";
        $whatsapp .= "Client: {$request->company_name}\n";
        $whatsapp .= "Proyek: {$request->project_name}\n";
        $whatsapp .= "Jenis: {$request->website_type}\n\n";
        $whatsapp .= "Tugas:\n{$taskText}\n\n";
        $whatsapp .= "Deadline: {$deadlineText}\n\n";
        $whatsapp .= "Detail request dapat dilihat di panel admin. Mohon konfirmasi setelah menerima tugas.\n\nSalam,\n{$agency}";

        $email = "Halo {$member->name},\n\n";
        $email .= "Anda mendapat pembagian tugas dari {$agency}. Berikut detailnya:\n\n";
        $email .= "Nomor Request : {$request->number}\n";
        $email .= "Client        : {$request->company_name}\n";
        $email .= "Proyek        : {$request->project_name}\n";
        $email .= "Jenis Website : {$request->website_type}\n\n";
        $email .= "Tugas:\n{$taskText}\n\n";
        $email .= "Deadline: {$deadlineText}\n\n";
        $email .= "Deskripsi request:\n{$request->description}\n\n";
        $email .= "Fitur yang diminta:\n" . $this->features($request) . "\n\n";
        $email .= "Mohon konfirmasi setelah menerima tugas dan koordinasikan progres dengan admin.\n\nSalam,\n{$agency}";

        return ['whatsapp' => $whatsapp, 'email' => $email];
    }

    private function features(ServiceRequest $request): string
    {
        $features = $request->functional_requirements ?? [];
        if (!is_array($features) || count($features) === 0) {
            return '- Tidak ada fitur khusus yang dicantumkan.';
        }

        return collect($features)->map(fn ($feature) => '- ' . $feature)->implode("\n");
    }
}
