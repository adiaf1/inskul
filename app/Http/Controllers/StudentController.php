<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use App\Support\EffectiveAccess;
use App\Support\SchoolFileStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Models\Role;

class StudentController extends Controller
{
    private const IMPORT_HEADERS = [
        'name',
        'email',
        'password',
        'nis',
        'nisn',
        'entry_year',
        'gender',
        'phone',
        'birth_place',
        'birth_date',
        'address',
    ];

    public function index(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $search = $request->input('search');
        $status = $request->input('status');

        $students = $school->students()
            ->with('user')
            ->when($search, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('nis', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== null && $status !== '', fn ($query) => $query->where('is_active', $status === 'active'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('students.index', compact('school', 'students', 'status'));
    }

    public function store(Request $request): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ...$this->profileRules(),
        ]);

        DB::transaction(function () use ($school, $validated, $request) {
            $studentRole = Role::findByName('student');
            $status = $request->boolean('is_active', true) ? 'active' : 'inactive';
            $photoPath = $this->storeStudentPhoto($request, $school);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status' => $status,
            ]);

            $user->assignRole($studentRole);

            $school->users()->attach($user->id, [
                'role_id' => $studentRole->id,
                'status' => $status,
            ]);

            Student::create([
                'school_id' => $school->id,
                'user_id' => $user->id,
                'photo_path' => $photoPath,
                ...$this->profilePayload($validated, $request),
            ]);
        });

        return redirect()->route('students.index')->with('success', 'Data murid berhasil dibuat.');
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="format-import-murid.csv"',
        ];

        return Response::stream(function () {
            $output = fopen('php://output', 'w');

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, self::IMPORT_HEADERS);
            fputcsv($output, [
                'Andi Pratama',
                'andi.murid@example.com',
                'password123',
                '="20260001"',
                '="0012345678"',
                '2026',
                'L',
                '="08123456789"',
                'Bandung',
                '2010-01-01',
                'Jl. Pelajar No. 1',
            ]);
            fputcsv($output, [
                'Sari Melati',
                'sari.murid@example.com',
                'password123',
                '="20260002"',
                '="0012345679"',
                '2026',
                'P',
                '="08129876543"',
                'Jakarta',
                '2010-05-12',
                'Jl. Pelajar No. 2',
            ]);

            fclose($output);
        }, 200, $headers);
    }

    public function import(Request $request): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $file = $request->file('file');

        if (! $file->isValid() || empty($file->getPathname()) || ! is_file($file->getPathname())) {
            return back()->withErrors('File import gagal diunggah. Silakan pilih ulang file.');
        }

        $rows = $this->readCsvRows($file->getPathname());

        if (empty($rows)) {
            return back()->withErrors('File import tidak memiliki data murid atau header tidak sesuai format.');
        }

        $errors = [];
        $emails = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2;

            if (empty($row['name'])) {
                $errors[] = "Baris {$line}: nama murid wajib diisi.";
            }

            if (empty($row['email']) || ! filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Baris {$line}: email tidak valid.";
            }

            if (! empty($row['email']) && in_array(strtolower($row['email']), $emails, true)) {
                $errors[] = "Baris {$line}: email duplikat di file import.";
            }

            if (! empty($row['email'])) {
                $emails[] = strtolower($row['email']);
            }

            if (empty($row['password']) || strlen($row['password']) < 8) {
                $errors[] = "Baris {$line}: kata sandi wajib minimal 8 karakter.";
            }

            if (! empty($row['gender']) && ! in_array(strtoupper($row['gender']), ['L', 'P'], true)) {
                $errors[] = "Baris {$line}: gender hanya boleh L atau P.";
            }

            if (! empty($row['entry_year']) && ! preg_match('/^\d{4}$/', $row['entry_year'])) {
                $errors[] = "Baris {$line}: angkatan harus diisi tahun 4 digit.";
            }

            if (! empty($row['birth_date']) && ! strtotime($row['birth_date'])) {
                $errors[] = "Baris {$line}: tanggal lahir harus format YYYY-MM-DD.";
            }
        }

        $existingEmails = User::whereIn('email', $emails)->pluck('email')->map(fn ($email) => strtolower($email))->all();

        foreach ($existingEmails as $email) {
            $errors[] = "Email {$email} sudah terdaftar.";
        }

        if (! empty($errors)) {
            return back()->withErrors(implode(' ', array_slice($errors, 0, 8)));
        }

        DB::transaction(function () use ($school, $rows) {
            $studentRole = Role::findByName('student');

            foreach ($rows as $row) {
                $gender = match (strtoupper((string) ($row['gender'] ?? ''))) {
                    'L' => 'male',
                    'P' => 'female',
                    default => null,
                };

                $user = User::create([
                    'name' => $row['name'],
                    'email' => strtolower($row['email']),
                    'password' => Hash::make($row['password']),
                    'status' => 'active',
                ]);

                $user->assignRole($studentRole);

                $school->users()->attach($user->id, [
                    'role_id' => $studentRole->id,
                    'status' => 'active',
                ]);

                Student::create([
                    'school_id' => $school->id,
                    'user_id' => $user->id,
                    'nis' => $this->normalizeImportedText($row['nis'] ?? ''),
                    'nisn' => $this->normalizeImportedText($row['nisn'] ?? ''),
                    'entry_year' => $row['entry_year'] ?: null,
                    'gender' => $gender,
                    'phone' => $this->normalizeImportedText($row['phone'] ?? ''),
                    'birth_place' => $row['birth_place'] ?: null,
                    'birth_date' => $row['birth_date'] ?: null,
                    'address' => $row['address'] ?: null,
                    'is_active' => true,
                ]);
            }
        });

        return redirect()->route('students.index')->with('success', count($rows).' data murid berhasil diimport.');
    }

    public function printNametags(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $search = $request->input('search');
        $status = $request->input('status');

        $students = $school->students()
            ->with('user')
            ->when($search, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('nis', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== null && $status !== '', fn ($query) => $query->where('is_active', $status === 'active'))
            ->join('users', 'students.user_id', '=', 'users.id')
            ->orderBy('users.name')
            ->select('students.*')
            ->get();

        return view('students.nametags', compact('school', 'students'));
    }

    public function printNametag(Request $request, Student $student): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school || $student->school_id !== $school->id) {
            abort(403);
        }

        $students = collect([$student->load('user')]);

        return view('students.nametags', compact('school', 'students'));
    }


    public function update(Request $request, Student $student): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school || $student->school_id !== $school->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($student->user_id),
            ],
            ...$this->profileRules(),
        ]);

        DB::transaction(function () use ($school, $student, $validated, $request) {
            $studentRole = Role::findByName('student');
            $status = $request->boolean('is_active') ? 'active' : 'inactive';

            $student->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'status' => $status,
            ]);

            $student->user->assignRole($studentRole);

            $school->users()->syncWithoutDetaching([
                $student->user_id => [
                    'role_id' => $studentRole->id,
                    'status' => $status,
                ],
            ]);

            $payload = $this->profilePayload($validated, $request);

            if ($photoPath = $this->storeStudentPhoto($request, $school)) {
                if ($student->photo_path) {
                    SchoolFileStorage::delete($student->photo_path);
                }

                $payload['photo_path'] = $photoPath;
            }

            $student->update($payload);
        });

        return redirect()->route('students.index')->with('success', 'Data murid berhasil diperbarui.');
    }

    public function destroy(Request $request, Student $student): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school || $student->school_id !== $school->id) {
            abort(403);
        }

        DB::transaction(function () use ($school, $student) {
            $studentRole = Role::findByName('student');

            $student->update(['is_active' => false]);
            $student->user->update(['status' => 'inactive']);

            $school->users()->updateExistingPivot($student->user_id, [
                'role_id' => $studentRole->id,
                'status' => 'inactive',
            ]);
        });

        return redirect()->route('students.index')->with('success', 'Murid berhasil dinonaktifkan.');
    }

    private function profileRules(): array
    {
        return [
            'nis' => ['nullable', 'string', 'max:50'],
            'nisn' => ['nullable', 'string', 'max:50'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'entry_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'gender' => ['nullable', 'in:male,female'],
            'phone' => ['nullable', 'string', 'max:50'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    private function profilePayload(array $validated, Request $request): array
    {
        return [
            'nis' => $validated['nis'] ?? null,
            'nisn' => $validated['nisn'] ?? null,
            'entry_year' => $validated['entry_year'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'birth_place' => $validated['birth_place'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null,
            'address' => $validated['address'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ];
    }

    private function storeStudentPhoto(Request $request, $school): ?string
    {
        if (! $request->hasFile('photo')) {
            return null;
        }

        $photo = $request->file('photo');

        if (! $photo->isValid() || empty($photo->getPathname()) || ! is_file($photo->getPathname())) {
            throw ValidationException::withMessages([
                'photo' => 'File foto gagal diunggah. Silakan pilih ulang foto murid.',
            ]);
        }

        return SchoolFileStorage::store($photo, $school, 'students', 'foto-murid');
    }

    private function activeSchool(Request $request)
    {
        return EffectiveAccess::school($request);
    }

    private function readCsvRows(string $path): array
    {
        $handle = fopen($path, 'r');
        $headers = fgetcsv($handle);

        if (! $headers) {
            fclose($handle);
            return [];
        }

        $headers = array_map(function ($header) {
            return trim(str_replace("\xEF\xBB\xBF", '', $header));
        }, $headers);

        if ($headers !== self::IMPORT_HEADERS) {
            fclose($handle);
            return [];
        }

        $rows = [];

        while (($data = fgetcsv($handle)) !== false) {
            if ($data === [null] || count(array_filter($data, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            $data = array_pad($data, count(self::IMPORT_HEADERS), '');
            $rows[] = array_combine(self::IMPORT_HEADERS, array_map('trim', array_slice($data, 0, count(self::IMPORT_HEADERS))));
        }

        fclose($handle);

        return $rows;
    }

    private function normalizeImportedText(?string $value): string
    {
        $value = trim((string) $value);

        if (preg_match('/^="(.*)"$/', $value, $matches)) {
            return $matches[1];
        }

        return $value;
    }
}
