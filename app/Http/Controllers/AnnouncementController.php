<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    // Post new announcement (English and Sinhala)
    public function store(Request $request)
    {
        $request->validate([
            'title_en' => 'required|string|max:255',
            'title_si' => 'required|string|max:255',
            'content_en' => 'required|string',
            'content_si' => 'required|string',
        ]);

        $announcement = Announcement::create([
            'admin_id' => Auth::id(),
            'title_en' => $request->title_en,
            'title_si' => $request->title_si,
            'content_en' => $request->content_en,
            'content_si' => $request->content_si,
            'published_at' => now(),
        ]);

        // Audit log action
        SystemLog::create([
            'user_id' => Auth::id(),
            'action' => 'Create Announcement',
            'entity_type' => 'Announcement',
            'entity_id' => $announcement->id,
            'new_values' => $announcement->toArray(),
        ]);

        return redirect()->back()->with('success', 'Announcement published successfully.');
    }
}
