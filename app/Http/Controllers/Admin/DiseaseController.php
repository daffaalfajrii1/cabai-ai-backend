<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDiseaseRequest;
use App\Http\Requests\Admin\UpdateDiseaseRequest;
use App\Models\Disease;
use App\Support\AdminAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DiseaseController extends Controller
{
    public function index(): View
    {
        return view('admin.diseases.index', [
            'diseases' => Disease::withCount([
                'detections' => fn ($query) => $query->where('valid_input', true),
            ])
                ->orderBy('ai_class_name')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.diseases.create', [
            'disease' => new Disease(['is_active' => true]),
        ]);
    }

    public function store(StoreDiseaseRequest $request): RedirectResponse
    {
        $data = $this->diseaseData($request->validated());
        $data['ai_class_name'] = trim((string) $request->input('ai_class_name'));
        $data['slug'] = $this->uniqueSlug($data['name']);

        $disease = Disease::create($data);

        AdminAudit::record($request->user(), 'admin.disease.created', $disease, 'Disease dibuat: '.$disease->name);

        return redirect()
            ->route('admin.diseases.show', $disease)
            ->with('status', 'Data penyakit berhasil dibuat.');
    }

    public function show(Disease $disease): View
    {
        return view('admin.diseases.show', [
            'disease' => $disease->loadCount('detections'),
        ]);
    }

    public function edit(Disease $disease): View
    {
        return view('admin.diseases.edit', [
            'disease' => $disease,
        ]);
    }

    public function update(UpdateDiseaseRequest $request, Disease $disease): RedirectResponse
    {
        $disease->update($this->diseaseData($request->validated()));

        AdminAudit::record($request->user(), 'admin.disease.updated', $disease, 'Disease diperbarui: '.$disease->name);

        return redirect()
            ->route('admin.diseases.show', $disease)
            ->with('status', 'Data penyakit berhasil diperbarui.');
    }

    private function diseaseData(array $validated): array
    {
        return [
            'name' => trim((string) $validated['name']),
            'scientific_name' => $validated['scientific_name'] ?? null,
            'description' => $validated['description'] ?? null,
            'symptoms' => $validated['symptoms'] ?? null,
            'cause' => $validated['cause'] ?? null,
            'treatment' => $validated['treatment'] ?? null,
            'prevention' => $validated['prevention'] ?? null,
            'is_healthy' => (bool) ($validated['is_healthy'] ?? false),
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ];
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: Str::random(8);
        $slug = $base;
        $counter = 2;

        while (Disease::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
