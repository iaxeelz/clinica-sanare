<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Services\NotificationService; // <--- NUEVO
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with('user')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('dashboard.announcements', compact('announcements'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:campaign,festivity,info,warning',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('announcements', 'public');
        }

        $announcement = Announcement::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'image' => $imagePath,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'is_active' => true,
            'user_id' => Auth::id(),
        ]);

        // ============================================
        // NOTIFICAR NUEVO ANUNCIO A TODOS LOS USUARIOS
        // ============================================
        $notificationService = new NotificationService();
        $notificationService->notifyNewAnnouncement($announcement);

        return redirect()->route('dashboard')
            ->with('success', 'Anuncio creado exitosamente.');
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:campaign,festivity,info,warning',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($request->hasFile('image')) {
            if ($announcement->image) {
                Storage::disk('public')->delete($announcement->image);
            }
            $imagePath = $request->file('image')->store('announcements', 'public');
            $announcement->image = $imagePath;
        }

        $announcement->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Anuncio actualizado exitosamente.');
    }

    public function destroy(Announcement $announcement)
    {
        if ($announcement->image) {
            Storage::disk('public')->delete($announcement->image);
        }
        $announcement->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Anuncio eliminado exitosamente.');
    }

    public function toggleStatus(Announcement $announcement)
    {
        $announcement->update(['is_active' => !$announcement->is_active]);

        return redirect()->route('dashboard')
            ->with('success', 'Estado del anuncio actualizado.');
    }
}