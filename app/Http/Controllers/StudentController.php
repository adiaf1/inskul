<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use App\Support\EffectiveAccess;
use App\Support\SchoolFileStorage;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
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
        $entryYear = $request->input('entry_year');

        $entryYears = $school->students()
            ->whereNotNull('entry_year')
            ->select('entry_year')
            ->distinct()
            ->orderByDesc('entry_year')
            ->pluck('entry_year');

        $students = $school->students()
            ->with('user')
            ->when($search, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('nis', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== null && $status !== '', fn ($query) => $query->where('is_active', $status === 'active'))
            ->when($entryYear !== null && $entryYear !== '', fn ($query) => $query->where('entry_year', $entryYear))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('students.index', compact('school', 'students', 'status', 'entryYear', 'entryYears'));
    }

    public function export(Request $request)
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $search = $request->input('search');
        $status = $request->input('status');
        $entryYear = $request->input('entry_year');

        $students = $school->students()
            ->with('user')
            ->when($search, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('nis', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== null && $status !== '', fn ($query) => $query->where('is_active', $status === 'active'))
            ->when($entryYear !== null && $entryYear !== '', fn ($query) => $query->where('entry_year', $entryYear))
            ->latest()
            ->get();

        $filename = 'data-murid-'.Str::slug($school->name).'-'.now()->format('Ymd-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        return Response::stream(function () use ($students) {
            $output = fopen('php://output', 'w');

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                'No',
                'Nama Murid',
                'Email',
                'Username',
                'NIS',
                'NISN',
                'Angkatan',
                'Jenis Kelamin',
                'Telepon',
                'Tempat Lahir',
                'Tanggal Lahir',
                'Alamat',
                'Status',
                'Tanggal Dibuat',
            ]);

            foreach ($students as $index => $student) {
                fputcsv($output, [
                    $index + 1,
                    $student->user?->name,
                    $student->user?->email,
                    $student->user?->username,
                    $this->excelText($student->nis),
                    $this->excelText($student->nisn),
                    $student->entry_year,
                    $student->gender === 'male' ? 'Laki-laki' : ($student->gender === 'female' ? 'Perempuan' : ''),
                    $this->excelText($student->phone),
                    $student->birth_place,
                    $student->birth_date?->format('d-m-Y'),
                    $student->address,
                    $student->is_active ? 'Aktif' : 'Tidak Aktif',
                    $student->created_at?->format('d-m-Y H:i'),
                ]);
            }

            fclose($output);
        }, 200, $headers);
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
                '01-01-2010',
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
                '12-05-2010',
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
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:2048'],
        ]);

        $file = $request->file('file');

        if (! $file->isValid() || empty($file->getPathname()) || ! is_file($file->getPathname())) {
            return back()->withErrors('File import gagal diunggah. Silakan pilih ulang file.');
        }

        $rows = $this->readImportRows($file->getPathname(), strtolower((string) $file->getClientOriginalExtension()));

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

            if (! empty($row['birth_date']) && $this->parseImportedBirthDate($row['birth_date']) === null) {
                $errors[] = "Baris {$line}: tanggal lahir harus format dd-mm-yyyy.";
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
                $email = strtolower($row['email']);
                $gender = match (strtoupper((string) ($row['gender'] ?? ''))) {
                    'L' => 'male',
                    'P' => 'female',
                    default => null,
                };

                $user = User::create([
                    'name' => $row['name'],
                    'username' => User::uniqueUsername(Str::before($email, '@')),
                    'email' => $email,
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
                    'birth_date' => $this->parseImportedBirthDate($row['birth_date'] ?? ''),
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
        $entryYear = $request->input('entry_year');
        $selectedStudentIds = collect((array) $request->input('student_ids', []))
            ->filter()
            ->unique()
            ->values();

        $students = $school->students()
            ->with('user')
            ->when($selectedStudentIds->isNotEmpty(), fn ($query) => $query->whereIn('students.id', $selectedStudentIds))
            ->when($selectedStudentIds->isEmpty() && $search, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('nis', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($selectedStudentIds->isEmpty() && $status !== null && $status !== '', fn ($query) => $query->where('is_active', $status === 'active'))
            ->when($selectedStudentIds->isEmpty() && $entryYear !== null && $entryYear !== '', fn ($query) => $query->where('entry_year', $entryYear))
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

    public function printOwnNametag(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);
        $user = EffectiveAccess::user($request);

        if (! $school || ! $user) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $student = Student::query()
            ->with('user')
            ->where('school_id', $school->id)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (! $student) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke data murid aktif.');
        }

        $students = collect([$student]);

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

    private function readImportRows(string $path, string $extension): array
    {
        return $extension === 'xlsx'
            ? $this->readXlsxRows($path)
            : $this->readCsvRows($path);
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

    private function readXlsxRows(string $path): array
    {
        if (! class_exists(\ZipArchive::class)) {
            return [];
        }

        $zip = new \ZipArchive();

        if ($zip->open($path) !== true) {
            return [];
        }

        $worksheetPath = $this->firstXlsxWorksheetPath($zip);
        $worksheetXml = $worksheetPath ? $zip->getFromName($worksheetPath) : false;

        if ($worksheetXml === false) {
            $zip->close();
            return [];
        }

        $sharedStrings = $this->xlsxSharedStrings($zip);
        $zip->close();

        $sheet = simplexml_load_string($worksheetXml);

        if (! $sheet) {
            return [];
        }

        $table = [];

        foreach ($sheet->sheetData->row as $row) {
            $cells = [];

            foreach ($row->c as $cell) {
                $column = $this->xlsxColumnIndex((string) $cell['r']);
                $type = (string) $cell['t'];
                $value = '';

                if ($type === 's') {
                    $value = $sharedStrings[(int) $cell->v] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = trim((string) ($cell->is->t ?? ''));
                } else {
                    $value = trim((string) ($cell->v ?? ''));
                }

                if ($column !== null) {
                    $cells[$column] = $value;
                }
            }

            if ($cells !== []) {
                ksort($cells);
                $table[] = $cells;
            }
        }

        if ($table === []) {
            return [];
        }

        $headers = array_map(function ($header) {
            return trim(str_replace("\xEF\xBB\xBF", '', (string) $header));
        }, array_values($table[0]));

        if ($headers !== self::IMPORT_HEADERS) {
            return [];
        }

        $rows = [];

        foreach (array_slice($table, 1) as $data) {
            $values = [];

            for ($index = 0; $index < count(self::IMPORT_HEADERS); $index++) {
                $values[] = trim((string) ($data[$index] ?? ''));
            }

            if (count(array_filter($values, fn ($value) => $value !== '')) === 0) {
                continue;
            }

            $rows[] = array_combine(self::IMPORT_HEADERS, $values);
        }

        return $rows;
    }

    private function firstXlsxWorksheetPath(\ZipArchive $zip): ?string
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($workbookXml === false || $relsXml === false) {
            return $zip->locateName('xl/worksheets/sheet1.xml') !== false ? 'xl/worksheets/sheet1.xml' : null;
        }

        $workbook = simplexml_load_string($workbookXml);
        $rels = simplexml_load_string($relsXml);

        if (! $workbook || ! $rels) {
            return null;
        }

        $workbook->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
        $firstSheet = $workbook->sheets->sheet[0] ?? null;

        if (! $firstSheet) {
            return null;
        }

        $relationshipId = (string) $firstSheet->attributes('r', true)->id;

        foreach ($rels->Relationship as $relationship) {
            if ((string) $relationship['Id'] === $relationshipId) {
                $target = ltrim((string) $relationship['Target'], '/');

                return str_starts_with($target, 'xl/') ? $target : 'xl/'.$target;
            }
        }

        return null;
    }

    private function xlsxSharedStrings(\ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if ($xml === false) {
            return [];
        }

        $sharedStrings = simplexml_load_string($xml);

        if (! $sharedStrings) {
            return [];
        }

        $values = [];

        foreach ($sharedStrings->si as $item) {
            if (isset($item->t)) {
                $values[] = (string) $item->t;
                continue;
            }

            $parts = [];

            foreach ($item->r as $run) {
                $parts[] = (string) $run->t;
            }

            $values[] = implode('', $parts);
        }

        return $values;
    }

    private function xlsxColumnIndex(string $cellReference): ?int
    {
        if (! preg_match('/^([A-Z]+)/i', $cellReference, $matches)) {
            return null;
        }

        $index = 0;

        foreach (str_split(strtoupper($matches[1])) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return $index - 1;
    }

    private function normalizeImportedText(?string $value): string
    {
        $value = trim((string) $value);

        if (preg_match('/^="(.*)"$/', $value, $matches)) {
            return $matches[1];
        }

        return $value;
    }

    private function excelText(?string $value): string
    {
        $value = trim((string) $value);

        return $value === '' ? '' : '="'.$value.'"';
    }

    private function parseImportedBirthDate(?string $value): ?string
    {
        $value = $this->normalizeImportedText($value);

        if ($value === '') {
            return null;
        }

        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $value)) {
            try {
                $date = CarbonImmutable::createFromFormat('!d-m-Y', $value);
            } catch (\Throwable) {
                return null;
            }

            return $date && $date->format('d-m-Y') === $value
                ? $date->format('Y-m-d')
                : null;
        }

        if (is_numeric($value) && (int) $value >= 15000 && (int) $value <= 60000) {
            return CarbonImmutable::create(1899, 12, 30)
                ->addDays((int) $value)
                ->format('Y-m-d');
        }

        return null;
    }
}
