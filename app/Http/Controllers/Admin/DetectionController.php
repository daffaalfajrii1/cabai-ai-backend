<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Detection;
use Illuminate\View\View;

class DetectionController extends Controller
{
    public function index(): View
    {
        return view('admin.detections.index', [
            'detections' => Detection::with(['user', 'disease'])->latest()->paginate(15),
        ]);
    }

    public function show(Detection $detection): View
    {
        return view('admin.detections.show', [
            'detection' => $detection->load(['user', 'disease']),
        ]);
    }
}
