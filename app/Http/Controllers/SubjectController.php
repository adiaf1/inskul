<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Support\EffectiveAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $search = $request->input('search');
        $status = $request->input('status');

        $subjects = $school->subjects()
            ->when($search, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($status !== null && $status !== '', fn ($query) => $query->where('is_active', $status === 'active'))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('subjects.index', compact('school', 'subjects', 'status'));
    }

    public function store(Request $request): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $validated = $request->validate($this->rules());

        Subject::create([
            'school_id' => $school->id,
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('subjects.index')->with('success', 'Mata pelajaran berhasil dibuat.');
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school || $subject->school_id !== $school->id) {
            abort(403);
        }

        $validated = $request->validate($this->rules());

        $subject->update([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('subjects.index')->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(Request $request, Subject $subject): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school || $subject->school_id !== $school->id) {
            abort(403);
        }

        $subject->delete();

        return redirect()->route('subjects.index')->with('success', 'Mata pelajaran berhasil dihapus.');
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    private function activeSchool(Request $request)
    {
        return EffectiveAccess::school($request);
    }
}
