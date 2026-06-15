<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::orderBy('floor')->orderBy('room_number')->get();
        return view('admin.rooms.index', compact('rooms'));
    }

    public function create()
    {
        return view('admin.rooms.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_number' => 'required|string|max:50|unique:rooms,room_number',
            'floor' => 'required|integer|min:1',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['qr_token'] = Str::uuid()->toString();

        Room::create($validated);

        return redirect()->route('admin.rooms.index')->with('success', 'Kamar berhasil ditambahkan!');
    }

    public function edit(Room $room)
    {
        return view('admin.rooms.form', compact('room'));
    }

    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'room_number' => 'required|string|max:50|unique:rooms,room_number,' . $room->id,
            'floor' => 'required|integer|min:1',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        // Optional: Regenerate token
        if ($request->has('regenerate_token')) {
            $validated['qr_token'] = Str::uuid()->toString();
        }

        $room->update($validated);

        return redirect()->route('admin.rooms.index')->with('success', 'Kamar berhasil diperbarui!');
    }

    public function destroy(Room $room)
    {
        $room->delete();
        return back()->with('success', 'Kamar berhasil dihapus!');
    }

    public function print(Room $room)
    {
        return view('admin.rooms.print', compact('room'));
    }
}
