<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoomController extends Controller
{
    private const TYPES = [
        'classroom' => 'Kelas',
        'laboratory' => 'Laboratorium',
        'hall' => 'Aula',
        'library' => 'Perpustakaan',
        'office' => 'Kantor',
        'other' => 'Lainnya',
    ];

    public function index(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $search = $request->input('search');
        $type = $request->input('type');
        $status = $request->input('status');
        $types = self::TYPES;

        $rooms = $school->rooms()
            ->when($search, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->when($type, fn ($query) => $query->where('type', $type))
            ->when($status !== null && $status !== '', fn ($query) => $query->where('is_active', $status === 'active'))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('rooms.index', compact('school', 'rooms', 'types', 'type', 'status'));
    }

    public function store(Request $request): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $validated = $request->validate($this->rules($school->id));

        Room::create([
            'school_id' => $school->id,
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'type' => $validated['type'],
            'capacity' => $validated['capacity'] ?? null,
            'location' => $validated['location'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('rooms.index')->with('success', 'Ruangan berhasil dibuat.');
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school || $room->school_id !== $school->id) {
            abort(403);
        }

        $validated = $request->validate($this->rules($school->id, $room->id));

        $room->update([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'type' => $validated['type'],
            'capacity' => $validated['capacity'] ?? null,
            'location' => $validated['location'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('rooms.index')->with('success', 'Ruangan berhasil diperbarui.');
    }

    public function destroy(Request $request, Room $room): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school || $room->school_id !== $school->id) {
            abort(403);
        }

        $room->delete();

        return redirect()->route('rooms.index')->with('success', 'Ruangan berhasil dihapus.');
    }

    private function rules(int $schoolId, ?int $roomId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('rooms', 'code')->where('school_id', $schoolId)->ignore($roomId),
            ],
            'type' => ['required', Rule::in(array_keys(self::TYPES))],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'location' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    private function activeSchool(Request $request)
    {
        return $request->user()
            ->schools()
            ->wherePivot('status', 'active')
            ->where('schools.status', 'active')
            ->first();
    }
}
