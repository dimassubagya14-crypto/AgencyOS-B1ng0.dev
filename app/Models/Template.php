<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'category', 'content', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /**
     * Isi placeholder {nama_client}, {jenis_website}, {kebutuhan}, {nama_agency}
     * dengan data dari sebuah ServiceRequest (PRD bab 7).
     */
    public function renderFor(ServiceRequest $request): string
    {
        $agencyName = CompanyProfile::current()->agency_name;

        return strtr($this->content, [
            '{nama_client}' => $request->company_name,
            '{jenis_website}' => $request->website_type,
            '{kebutuhan}' => $request->description,
            '{nama_agency}' => $agencyName,
            '{nama_proyek}' => $request->project_name,
            '{contact_person}' => $request->contact_name,
        ]);
    }
}
