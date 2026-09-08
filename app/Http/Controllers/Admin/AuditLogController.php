<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.audit_logs.index', [
            'logs' => AdminAuditLog::with('user')
                ->latest('created_at')
                ->paginate(30),
        ]);
    }
}
