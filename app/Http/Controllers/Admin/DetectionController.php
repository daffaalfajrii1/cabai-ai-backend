<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateDetectionReviewRequest;
use App\Models\Detection;
use App\Models\Disease;
use App\Support\AdminAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DetectionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Detection::query()
            ->with(['user', 'disease', 'correctedDisease'])
            ->latest();

        $this->applyDetectionFilter($query, (string) $request->query('filter', 'all'));

        return view('admin.detections.index', [
            'detections' => $query->paginate(15)->withQueryString(),
            'filter' => (string) $request->query('filter', 'all'),
            'isReviewQueue' => false,
        ]);
    }

    public function reviewQueue(Request $request): View
    {
        $query = Detection::query()
            ->needsAdminReview()
            ->with(['user', 'disease', 'correctedDisease'])
            ->latest();

        $this->applyDetectionFilter($query, (string) $request->query('filter', 'pending'));

        return view('admin.detections.index', [
            'detections' => $query->paginate(15)->withQueryString(),
            'filter' => (string) $request->query('filter', 'pending'),
            'isReviewQueue' => true,
        ]);
    }

    public function show(Detection $detection): View
    {
        return view('admin.detections.show', [
            'detection' => $detection->load(['user', 'disease', 'correctedDisease', 'reviewer']),
            'diseases' => Disease::where('is_active', true)->orderBy('name')->get(),
            'reviewStatuses' => Detection::reviewStatusLabels(),
        ]);
    }

    public function updateReview(UpdateDetectionReviewRequest $request, Detection $detection): RedirectResponse
    {
        $validated = $request->validated();
        $status = $validated['review_status'];

        $detection->update([
            'review_status' => $status,
            'corrected_disease_id' => $status === Detection::REVIEW_CORRECTED
                ? $validated['corrected_disease_id']
                : null,
            'review_notes' => $validated['review_notes'] ?? null,
            'reviewed_by' => $status === Detection::REVIEW_PENDING ? null : $request->user()->id,
            'reviewed_at' => $status === Detection::REVIEW_PENDING ? null : now(),
        ]);

        AdminAudit::record(
            $request->user(),
            $status === Detection::REVIEW_CORRECTED ? 'admin.detection.corrected' : 'admin.detection.reviewed',
            $detection,
            'Review detection #'.$detection->id.' diubah menjadi '.$status.'.'
        );

        return redirect()
            ->route('admin.detections.show', $detection)
            ->with('status', 'Review detection berhasil disimpan.');
    }

    private function applyDetectionFilter($query, string $filter): void
    {
        match ($filter) {
            Detection::REVIEW_PENDING => $query->where(function ($query): void {
                $query->where('review_status', Detection::REVIEW_PENDING)->orWhereNull('review_status');
            }),
            Detection::REVIEW_VERIFIED,
            Detection::REVIEW_CORRECTED,
            Detection::REVIEW_REJECTED => $query->where('review_status', $filter),
            'invalid' => $query->where('valid_input', false),
            'needs-retake' => $query->where('needs_retake', true),
            'low-confidence' => $query->where(function ($query): void {
                $query->where('confidence', '<', 0.70)
                    ->orWhere('confidence_percent', '<', 70);
            }),
            default => null,
        };
    }
}
