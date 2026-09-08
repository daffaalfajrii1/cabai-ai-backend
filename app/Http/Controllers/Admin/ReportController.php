<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Detection;
use App\Models\Disease;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $query = $this->filteredQuery($request);

        return response()->view('admin.reports.index', [
            'detections' => $query->paginate(20)->withQueryString(),
            'summary' => $this->summary($this->filteredQuery($request)),
            'diseases' => Disease::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
            'classificationSources' => Detection::query()
                ->whereNotNull('classification_source')
                ->distinct()
                ->orderBy('classification_source')
                ->pluck('classification_source'),
            'reviewStatuses' => Detection::reviewStatusLabels(),
            'filters' => $request->only([
                'date_from',
                'date_to',
                'disease_id',
                'user_id',
                'validity',
                'review_status',
                'classification_source',
            ]),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $query = $this->filteredQuery($request);
        $filename = 'cabai-ai-detections-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $output = fopen('php://output', 'w');

            fputcsv($output, [
                'ID',
                'Tanggal',
                'User',
                'Email',
                'AI Class',
                'Penyakit AI',
                'Penyakit Koreksi',
                'Confidence',
                'Valid Input',
                'Needs Retake',
                'Review Status',
                'Classification Source',
                'Catatan Review',
            ]);

            $query->chunk(500, function ($detections) use ($output): void {
                foreach ($detections as $detection) {
                    fputcsv($output, [
                        $detection->id,
                        $detection->created_at?->format('Y-m-d H:i:s'),
                        $detection->user?->name,
                        $detection->user?->email,
                        $detection->ai_class_name,
                        $detection->disease?->name,
                        $detection->correctedDisease?->name,
                        $detection->confidence_percent === null ? null : number_format((float) $detection->confidence_percent, 2).'%',
                        $detection->valid_input ? 'valid' : 'invalid',
                        $detection->needs_retake ? 'yes' : 'no',
                        $detection->reviewStatus(),
                        $detection->classification_source,
                        $detection->review_notes,
                    ]);
                }
            });

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function filteredQuery(Request $request): Builder
    {
        $query = Detection::query()
            ->with(['user', 'disease', 'correctedDisease'])
            ->latest();

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from')?->toDateString());
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to')?->toDateString());
        }

        if ($request->filled('disease_id')) {
            $query->where('disease_id', $request->integer('disease_id'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->query('validity') === 'valid') {
            $query->where('valid_input', true);
        }

        if ($request->query('validity') === 'invalid') {
            $query->where('valid_input', false);
        }

        if (in_array($request->query('review_status'), Detection::reviewStatuses(), true)) {
            $query->where('review_status', $request->query('review_status'));
        }

        if ($request->filled('classification_source')) {
            $query->where('classification_source', $request->query('classification_source'));
        }

        return $query;
    }

    private function summary(Builder $query): array
    {
        return [
            'total' => (clone $query)->count(),
            'valid' => (clone $query)->where('valid_input', true)->count(),
            'invalid' => (clone $query)->where('valid_input', false)->count(),
            'needs_retake' => (clone $query)->where('needs_retake', true)->count(),
            'healthy' => (clone $query)->where('valid_input', true)->whereHas('disease', fn (Builder $query) => $query->where('is_healthy', true))->count(),
            'disease' => (clone $query)->where('valid_input', true)->whereHas('disease', fn (Builder $query) => $query->where('is_healthy', false))->count(),
            'average_confidence' => (clone $query)->whereNotNull('confidence_percent')->avg('confidence_percent'),
        ];
    }
}
