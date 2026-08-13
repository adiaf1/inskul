<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use App\Support\EffectiveAccess;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Models\Role;

class TeacherController extends Controller
{
    private const IMPORT_HEADERS = [
        'name',
        'email',
        'password',
        'nip',
        'nuptk',
        'employee_number',
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

        $teachers = $school->teachers()
            ->with('user')
            ->when($search, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('nip', 'like', "%{$search}%")
                        ->orWhere('nuptk', 'like', "%{$search}%")
                        ->orWhere('employee_number', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== null && $status !== '', fn ($query) => $query->where('is_active', $status === 'active'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('teachers.index', compact('school', 'teachers', 'status'));
    }

    public function export(Request $request)
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $search = $request->input('search');
        $status = $request->input('status');

        $teachers = $school->teachers()
            ->with('user')
            ->when($search, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('nip', 'like', "%{$search}%")
                        ->orWhere('nuptk', 'like', "%{$search}%")
                        ->orWhere('employee_number', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== null && $status !== '', fn ($query) => $query->where('is_active', $status === 'active'))
            ->latest()
            ->get();

        $filename = 'data-guru-'.Str::slug($school->name).'-'.now()->format('Ymd-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        return Response::stream(function () use ($teachers) {
            $output = fopen('php://output', 'w');

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                'No',
                'Nama Guru',
                'Email',
                'Username',
                'NIP',
                'NUPTK',
                'Nomor Pegawai',
                'Jenis Kelamin',
                'Telepon',
                'Tempat Lahir',
                'Tanggal Lahir',
                'Alamat',
                'Status',
                'Tanggal Dibuat',
            ]);

            foreach ($teachers as $index => $teacher) {
                fputcsv($output, [
                    $index + 1,
                    $teacher->user?->name,
                    $teacher->user?->email,
                    $teacher->user?->username,
                    $this->excelText($teacher->nip),
                    $this->excelText($teacher->nuptk),
                    $this->excelText($teacher->employee_number),
                    $teacher->gender === 'male' ? 'Laki-laki' : ($teacher->gender === 'female' ? 'Perempuan' : ''),
                    $this->excelText($teacher->phone),
                    $teacher->birth_place,
                    $teacher->birth_date?->format('d-m-Y'),
                    $teacher->address,
                    $teacher->is_active ? 'Aktif' : 'Tidak Aktif',
                    $teacher->created_at?->format('d-m-Y H:i'),
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
            $teacherRole = Role::findByName('teacher');

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status' => $request->boolean('is_active', true) ? 'active' : 'inactive',
            ]);

            $user->assignRole($teacherRole);

            $school->users()->attach($user->id, [
                'role_id' => $teacherRole->id,
                'status' => $request->boolean('is_active', true) ? 'active' : 'inactive',
            ]);

            Teacher::create([
                'school_id' => $school->id,
                'user_id' => $user->id,
                ...$this->profilePayload($validated, $request),
            ]);
        });

        return redirect()->route('teachers.index')->with('success', 'Data guru berhasil dibuat.');
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="format-import-guru.csv"',
        ];

        return Response::stream(function () {
            $output = fopen('php://output', 'w');

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, self::IMPORT_HEADERS);
            fputcsv($output, [
                'Budi Santoso',
                'budi.guru@example.com',
                'password123',
                '="198801012020121001"',
                '="1234567890123456"',
                'G001',
                'L',
                '="08123456789"',
                'Bandung',
                '01-01-1988',
                'Jl. Pendidikan No. 1',
            ]);
            fputcsv($output, [
                'Siti Aminah',
                'siti.guru@example.com',
                'password123',
                '',
                '',
                'G002',
                'P',
                '="08129876543"',
                'Jakarta',
                '12-05-1990',
                'Jl. Sekolah No. 2',
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
            return back()->withErrors('File import tidak memiliki data guru.');
        }

        $errors = [];
        $emails = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2;

            if (empty($row['name'])) {
                $errors[] = "Baris {$line}: nama guru wajib diisi.";
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
            $teacherRole = Role::findByName('teacher');

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

                $user->assignRole($teacherRole);

                $school->users()->attach($user->id, [
                    'role_id' => $teacherRole->id,
                    'status' => 'active',
                ]);

                Teacher::create([
                    'school_id' => $school->id,
                    'user_id' => $user->id,
                    'nip' => $this->normalizeImportedText($row['nip'] ?? ''),
                    'nuptk' => $this->normalizeImportedText($row['nuptk'] ?? ''),
                    'employee_number' => $row['employee_number'] ?: null,
                    'gender' => $gender,
                    'phone' => $this->normalizeImportedText($row['phone'] ?? ''),
                    'birth_place' => $row['birth_place'] ?: null,
                    'birth_date' => $this->parseImportedBirthDate($row['birth_date'] ?? ''),
                    'address' => $row['address'] ?: null,
                    'is_active' => true,
                ]);
            }
        });

        return redirect()->route('teachers.index')->with('success', count($rows).' data guru berhasil diimport.');
    }

    public function update(Request $request, Teacher $teacher): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school || $teacher->school_id !== $school->id) {
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
                Rule::unique('users', 'email')->ignore($teacher->user_id),
            ],
            ...$this->profileRules(),
        ]);

        DB::transaction(function () use ($school, $teacher, $validated, $request) {
            $teacherRole = Role::findByName('teacher');
            $status = $request->boolean('is_active') ? 'active' : 'inactive';

            $teacher->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'status' => $status,
            ]);

            $teacher->user->assignRole($teacherRole);

            $school->users()->syncWithoutDetaching([
                $teacher->user_id => [
                    'role_id' => $teacherRole->id,
                    'status' => $status,
                ],
            ]);

            $teacher->update($this->profilePayload($validated, $request));
        });

        return redirect()->route('teachers.index')->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy(Request $request, Teacher $teacher): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school || $teacher->school_id !== $school->id) {
            abort(403);
        }

        DB::transaction(function () use ($school, $teacher) {
            $teacherRole = Role::findByName('teacher');

            $teacher->update(['is_active' => false]);
            $teacher->user->update(['status' => 'inactive']);

            $school->users()->updateExistingPivot($teacher->user_id, [
                'role_id' => $teacherRole->id,
                'status' => 'inactive',
            ]);
        });

        return redirect()->route('teachers.index')->with('success', 'Guru berhasil dinonaktifkan.');
    }

    private function profileRules(): array
    {
        return [
            'nip' => ['nullable', 'string', 'max:50'],
            'nuptk' => ['nullable', 'string', 'max:50'],
            'employee_number' => ['nullable', 'string', 'max:50'],
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
            'nip' => $validated['nip'] ?? null,
            'nuptk' => $validated['nuptk'] ?? null,
            'employee_number' => $validated['employee_number'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'birth_place' => $validated['birth_place'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null,
            'address' => $validated['address'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ];
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
                $reference = (string) $cell['r'];
                $column = $this->xlsxColumnIndex($reference);
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
