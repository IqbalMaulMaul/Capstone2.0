<?php

namespace App\Http\Middleware;

use App\Models\Room;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateRoomToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->route('token') ?? session('room_token');

        if (!$token) {
            abort(403, 'Akses ditolak. Silakan scan QR Code di kamar Anda.');
        }

        $room = Room::where('qr_token', $token)->active()->first();

        if (!$room) {
            abort(403, 'QR Code tidak valid atau kamar sedang tidak aktif.');
        }

        // Store room info in session for easy access
        session([
            'room_id' => $room->id,
            'room_number' => $room->room_number,
            'room_token' => $token
        ]);

        // Inject room into the request for controllers
        $request->merge(['room' => $room]);

        return $next($request);
    }
}
