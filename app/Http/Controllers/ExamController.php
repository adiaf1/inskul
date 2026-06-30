<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamOption;
use App\Models\ExamQuestion;
use App\Models\Student;
use App\Models\Teacher;
use App\Support\EffectiveAccess;
use App\Support\SchoolFileStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class ExamController extends Controller
{
    private const OPTION_LABELS = ['A', 'B', 'C', 'D'];
    private const QUESTION_IMPORT_HEADERS = [
        'question_text',
        'image_file',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_option',
        'points',
        'sort_order',
    ];
    private const QUESTION_IMPORT_REQUIRED_HEADERS = [
        'question_text',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_option',
    ];
    private const QUESTION_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    public function index(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $role = $this->effectiveRole($request);

        if ($role === 'student') {
            $student = $this->activeStudent($request, $school->id);
            $classroomIds = $student
                ? $student->classrooms()->wherePivot('status', 'active')->pluck('classrooms.id')
                : collect();

            $exams = $school->exams()
                ->with(['subject', 'classroom', 'teacher.user', 'attempts' => fn ($query) => $query->where('student_id', $student?->id)])
                ->where('status', Exam::STATUS_PUBLISHED)
                ->whereIn('classroom_id', $classroomIds)
                ->orderByDesc('starts_at')
                ->paginate(12)
                ->withQueryString();

            return view('exams.index', compact('school', 'exams', 'role', 'student'));
        }

        $teacher = $this->activeTeacher($request, $school->id);

        $exams = $school->exams()
            ->with(['subject', 'classroom', 'teacher.user'])
            ->withCount(['questions', 'attempts'])
            ->when($role === 'teacher', fn ($query) => $teacher
                ? $query->where('teacher_id', $teacher->id)
                : $query->whereRaw('1 = 0'))
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('exams.index', compact('school', 'exams', 'role', 'teacher'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        [$subjects, $classrooms, $teachers] = $this->formOptions($request, $school);

        return view('exams.create', compact('school', 'subjects', 'classrooms', 'teachers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $validated = $request->validate($this->examRules($school->id));
        $teacherId = $this->resolvedTeacherId($request, $school->id, $validated);

        Exam::create([
            'school_id' => $school->id,
            'subject_id' => $validated['subject_id'],
            'classroom_id' => $validated['classroom_id'],
            'teacher_id' => $teacherId,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'duration_minutes' => $validated['duration_minutes'],
            'status' => Exam::STATUS_DRAFT,
        ]);

        return redirect()->route('exams.index')->with('success', 'Ujian berhasil dibuat. Tambahkan soal sebelum dipublish.');
    }

    public function edit(Request $request, Exam $exam): View
    {
        $this->authorizeExam($request, $exam);

        $school = $this->activeSchool($request);
        [$subjects, $classrooms, $teachers] = $this->formOptions($request, $school);
        $exam->load(['questions.options', 'subject', 'classroom', 'teacher.user']);

        return view('exams.edit', compact('school', 'exam', 'subjects', 'classrooms', 'teachers'));
    }

    public function update(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorizeExam($request, $exam);

        $school = $this->activeSchool($request);
        $validated = $request->validate($this->examRules($school->id));
        $teacherId = $this->resolvedTeacherId($request, $school->id, $validated);

        $exam->update([
            'subject_id' => $validated['subject_id'],
            'classroom_id' => $validated['classroom_id'],
            'teacher_id' => $teacherId,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'duration_minutes' => $validated['duration_minutes'],
        ]);

        return redirect()->route('exams.edit', $exam)->with('success', 'Ujian berhasil diperbarui.');
    }

    public function destroy(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorizeExam($request, $exam);

        $exam->delete();

        return redirect()->route('exams.index')->with('success', 'Ujian berhasil dihapus.');
    }

    public function publish(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorizeExam($request, $exam);

        $exam->load('questions.options');

        if ($exam->questions->isEmpty()) {
            return back()->withErrors('Ujian belum memiliki soal.');
        }

        foreach ($exam->questions as $question) {
            if ($question->options->count() !== 4 || $question->options->where('is_correct', true)->count() !== 1) {
                return back()->withErrors('Setiap soal wajib memiliki 4 pilihan dan tepat 1 jawaban benar.');
            }
        }

        $exam->update(['status' => Exam::STATUS_PUBLISHED]);

        return back()->with('success', 'Ujian berhasil dipublish.');
    }

    public function close(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorizeExam($request, $exam);

        $exam->update(['status' => Exam::STATUS_CLOSED]);

        return back()->with('success', 'Ujian berhasil ditutup.');
    }

    public function storeQuestion(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorizeExam($request, $exam);

        $validated = $request->validate($this->questionRules());
        $school = $this->activeSchool($request);

        DB::transaction(function () use ($exam, $validated, $request, $school) {
            $question = $exam->questions()->create([
                'question_text' => $validated['question_text'],
                'image_path' => $request->hasFile('image') ? SchoolFileStorage::store($request->file('image'), $school, 'exams/questions', 'soal-ujian') : null,
                'points' => $validated['points'],
                'sort_order' => $validated['sort_order'],
            ]);

            foreach (self::OPTION_LABELS as $label) {
                $question->options()->create([
                    'label' => $label,
                    'option_text' => $validated['options'][$label],
                    'is_correct' => $validated['correct_option'] === $label,
                ]);
            }
        });

        return back()->with('success', 'Soal berhasil ditambahkan.');
    }

    public function updateQuestion(Request $request, Exam $exam, ExamQuestion $question): RedirectResponse
    {
        $this->authorizeExam($request, $exam);
        $this->authorizeQuestion($exam, $question);

        $validated = $request->validate($this->questionRules());
        $school = $this->activeSchool($request);

        DB::transaction(function () use ($question, $validated, $request, $school) {
            $imagePath = $question->image_path;

            if ($request->boolean('remove_image')) {
                SchoolFileStorage::delete($imagePath);
                $imagePath = null;
            }

            if ($request->hasFile('image')) {
                SchoolFileStorage::delete($imagePath);
                $imagePath = SchoolFileStorage::store($request->file('image'), $school, 'exams/questions', 'soal-ujian');
            }

            $question->update([
                'question_text' => $validated['question_text'],
                'image_path' => $imagePath,
                'points' => $validated['points'],
                'sort_order' => $validated['sort_order'],
            ]);

            foreach (self::OPTION_LABELS as $label) {
                $question->options()->updateOrCreate(
                    ['label' => $label],
                    [
                        'option_text' => $validated['options'][$label],
                        'is_correct' => $validated['correct_option'] === $label,
                    ]
                );
            }
        });

        return back()->with('success', 'Soal berhasil diperbarui.');
    }

    public function destroyQuestion(Request $request, Exam $exam, ExamQuestion $question): RedirectResponse
    {
        $this->authorizeExam($request, $exam);
        $this->authorizeQuestion($exam, $question);

        SchoolFileStorage::delete($question->image_path);
        $question->delete();

        return back()->with('success', 'Soal berhasil dihapus.');
    }

    public function downloadQuestionImportTemplate(Request $request, Exam $exam)
    {
        $this->authorizeExam($request, $exam);

        $filename = 'template-import-soal-'.$exam->id.'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, self::QUESTION_IMPORT_HEADERS);
            fputcsv($handle, [
                'Ibukota Indonesia adalah?',
                '',
                'Jakarta',
                'Bandung',
                'Surabaya',
                'Medan',
                'A',
                '1',
                '1',
            ]);
            fputcsv($handle, [
                'Perhatikan gambar pada soal.',
                'contoh-gambar.png',
                'Pilihan A',
                'Pilihan B',
                'Pilihan C',
                'Pilihan D',
                'B',
                '1',
                '2',
            ]);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function importQuestions(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorizeExam($request, $exam);

        $validated = $request->validate([
            'questions_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
            'images_zip' => ['nullable', 'file', 'mimes:zip', 'max:51200'],
        ]);

        $csvPath = $this->uploadedFilePath($request->file('questions_file'));

        if (! $csvPath) {
            return back()->withErrors('File CSV soal tidak bisa dibaca. Silakan upload ulang file template CSV.');
        }

        [$rows, $errors] = $this->parseQuestionImportRows($csvPath);

        if ($errors) {
            return back()->withErrors(implode(' ', $errors));
        }

        if (empty($rows)) {
            return back()->withErrors('File import tidak memiliki baris soal.');
        }

        [$zip, $zipEntries, $zipErrors] = $this->prepareQuestionImageZip($rows, $request->file('images_zip'));

        if ($zipErrors) {
            return back()->withErrors(implode(' ', $zipErrors));
        }

        $school = $this->activeSchool($request);
        $storedImagePaths = [];

        try {
            DB::transaction(function () use ($exam, $rows, $zip, $zipEntries, $school, &$storedImagePaths) {
                $nextSortOrder = ((int) $exam->questions()->max('sort_order')) + 1;

                foreach ($rows as $row) {
                    $question = $exam->questions()->create([
                        'question_text' => $row['question_text'],
                        'image_path' => $this->storeImportedQuestionImage($zip, $zipEntries, $row['image_file'], $school, $storedImagePaths),
                        'points' => $row['points'],
                        'sort_order' => $row['sort_order'] ?: $nextSortOrder,
                    ]);

                    foreach (self::OPTION_LABELS as $label) {
                        $question->options()->create([
                            'label' => $label,
                            'option_text' => $row['option_'.strtolower($label)],
                            'is_correct' => $row['correct_option'] === $label,
                        ]);
                    }

                    $nextSortOrder = max($nextSortOrder + 1, $question->sort_order + 1);
                }
            });
        } catch (Throwable $exception) {
            foreach ($storedImagePaths as $path) {
                SchoolFileStorage::delete($path);
            }

            throw $exception;
        } finally {
            $zip?->close();
        }

        return back()->with('success', count($rows).' soal berhasil diimport.');
    }

    public function take(Request $request, Exam $exam): View|RedirectResponse
    {
        $student = $this->authorizeStudentExam($request, $exam);

        if (! $exam->isOpen()) {
            return redirect()->route('exams.index')->withErrors('Ujian belum dibuka atau sudah ditutup.');
        }

        $attempt = $exam->attempts()->firstOrCreate(
            ['student_id' => $student->id],
            ['started_at' => now(), 'status' => ExamAttempt::STATUS_IN_PROGRESS]
        );

        if ($attempt->status === ExamAttempt::STATUS_SUBMITTED) {
            return redirect()->route('exams.result', $exam);
        }

        $exam->load(['subject', 'classroom']);
        $attempt->load('answers');
        $questions = $exam->questions()
            ->with('options')
            ->paginate(4)
            ->withQueryString();

        return view('exams.take', compact('exam', 'attempt', 'student', 'questions'));
    }

    public function saveAnswer(Request $request, Exam $exam): JsonResponse
    {
        $student = $this->authorizeStudentExam($request, $exam);

        if (! $exam->isOpen()) {
            return response()->json([
                'message' => 'Ujian belum dibuka atau sudah ditutup.',
            ], 422);
        }

        $attempt = $exam->attempts()->firstOrCreate(
            ['student_id' => $student->id],
            ['started_at' => now(), 'status' => ExamAttempt::STATUS_IN_PROGRESS]
        );

        if ($attempt->status === ExamAttempt::STATUS_SUBMITTED) {
            return response()->json([
                'message' => 'Ujian sudah selesai dikirim.',
            ], 422);
        }

        $validated = $request->validate([
            'question_id' => ['required', Rule::exists('exam_questions', 'id')->where('exam_id', $exam->id)],
            'option_id' => ['required', 'exists:exam_options,id'],
        ]);

        $question = $exam->questions()->with('options')->findOrFail($validated['question_id']);
        $selectedOption = $question->options->firstWhere('id', $validated['option_id']);

        if (! $selectedOption) {
            return response()->json([
                'message' => 'Pilihan jawaban tidak valid.',
            ], 422);
        }

        $isCorrect = (bool) $selectedOption->is_correct;

        $attempt->answers()->updateOrCreate(
            ['exam_question_id' => $question->id],
            [
                'exam_option_id' => $selectedOption->id,
                'is_correct' => $isCorrect,
                'points_awarded' => $isCorrect ? $question->points : 0,
            ]
        );

        return response()->json([
            'message' => 'Jawaban tersimpan sebagai draft.',
            'question_id' => $question->id,
            'option_id' => $selectedOption->id,
        ]);
    }

    public function submit(Request $request, Exam $exam): RedirectResponse
    {
        $student = $this->authorizeStudentExam($request, $exam);

        $attempt = $exam->attempts()
            ->where('student_id', $student->id)
            ->where('status', ExamAttempt::STATUS_IN_PROGRESS)
            ->firstOrFail();

        $exam->load('questions.options');
        $submittedAnswers = $request->input('answers', []);
        $savedAnswers = $attempt->answers()
            ->pluck('exam_option_id', 'exam_question_id')
            ->all();
        $answers = array_replace($savedAnswers, $submittedAnswers);
        $score = 0;
        $maxScore = $exam->questions->sum('points');

        DB::transaction(function () use ($attempt, $exam, $answers, &$score, $maxScore) {
            foreach ($exam->questions as $question) {
                $selectedOptionId = $answers[$question->id] ?? null;
                $selectedOption = $selectedOptionId
                    ? $question->options->firstWhere('id', $selectedOptionId)
                    : null;
                $isCorrect = (bool) $selectedOption?->is_correct;
                $points = $isCorrect ? $question->points : 0;

                $score += $points;

                $attempt->answers()->updateOrCreate(
                    ['exam_question_id' => $question->id],
                    [
                        'exam_option_id' => $selectedOption?->id,
                        'is_correct' => $isCorrect,
                        'points_awarded' => $points,
                    ]
                );
            }

            $attempt->update([
                'submitted_at' => now(),
                'score' => $score,
                'max_score' => $maxScore,
                'status' => ExamAttempt::STATUS_SUBMITTED,
            ]);
        });

        return redirect()->route('exams.result', $exam)->with('success', 'Jawaban berhasil dikirim.');
    }

    public function result(Request $request, Exam $exam): View
    {
        $student = $this->authorizeStudentExam($request, $exam);
        $attempt = $exam->attempts()
            ->with(['answers.question', 'answers.option'])
            ->where('student_id', $student->id)
            ->firstOrFail();

        $exam->load(['subject', 'classroom', 'questions.options']);

        return view('exams.result', compact('exam', 'attempt', 'student'));
    }

    public function results(Request $request, Exam $exam): View
    {
        $this->authorizeExam($request, $exam);

        $exam->load(['subject', 'classroom', 'teacher.user']);
        $attempts = $exam->attempts()
            ->with('student.user')
            ->orderByDesc('submitted_at')
            ->paginate(20)
            ->withQueryString();

        return view('exams.results', compact('exam', 'attempts'));
    }

    private function examRules(string $schoolId): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'subject_id' => ['required', Rule::exists('subjects', 'id')->where('school_id', $schoolId)],
            'classroom_id' => ['required', Rule::exists('classrooms', 'id')->where('school_id', $schoolId)],
            'teacher_id' => ['nullable', Rule::exists('teachers', 'id')->where('school_id', $schoolId)],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:600'],
        ];
    }

    private function questionRules(): array
    {
        return [
            'question_text' => ['required', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_image' => ['nullable', 'boolean'],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
            'sort_order' => ['required', 'integer', 'min:1', 'max:1000'],
            'correct_option' => ['required', Rule::in(self::OPTION_LABELS)],
            'options' => ['required', 'array'],
            'options.A' => ['required', 'string'],
            'options.B' => ['required', 'string'],
            'options.C' => ['required', 'string'],
            'options.D' => ['required', 'string'],
        ];
    }

    private function parseQuestionImportRows(?string $path): array
    {
        if (! $path || ! is_file($path)) {
            return [[], ['File import tidak bisa dibaca. Silakan upload ulang file CSV.']];
        }

        $handle = fopen($path, 'r');

        if (! $handle) {
            return [[], ['File import tidak bisa dibaca.']];
        }

        $headers = fgetcsv($handle);

        if (! $headers) {
            fclose($handle);

            return [[], ['File import kosong.']];
        }

        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
        $headers = array_map(fn ($header) => $this->normalizeImportHeader((string) $header), $headers);
        $headerMap = array_flip($headers);
        $missingHeaders = array_values(array_diff(self::QUESTION_IMPORT_REQUIRED_HEADERS, $headers));

        if ($missingHeaders) {
            fclose($handle);

            return [[], ['Format header belum lengkap. Kolom wajib yang belum ada: '.implode(', ', $missingHeaders).'.']];
        }

        $rows = [];
        $errors = [];
        $lineNumber = 1;

        while (($values = fgetcsv($handle)) !== false) {
            $lineNumber++;

            if ($values === [null] || collect($values)->filter(fn ($value) => trim((string) $value) !== '')->isEmpty()) {
                continue;
            }

            $row = [];

            foreach (self::QUESTION_IMPORT_HEADERS as $header) {
                $row[$header] = isset($headerMap[$header]) ? ($values[$headerMap[$header]] ?? '') : '';
            }

            $row = array_map(fn ($value) => trim((string) $value), $row);
            $row['image_file'] = basename(str_replace('\\', '/', $row['image_file']));
            $row['correct_option'] = strtoupper($row['correct_option']);
            $row['points'] = $row['points'] === '' ? 1 : (int) $row['points'];
            $row['sort_order'] = $row['sort_order'] === '' ? null : (int) $row['sort_order'];

            foreach (['question_text', 'option_a', 'option_b', 'option_c', 'option_d'] as $field) {
                if ($row[$field] === '') {
                    $errors[] = "Baris {$lineNumber}: kolom {$field} wajib diisi.";
                }
            }

            if (! in_array($row['correct_option'], self::OPTION_LABELS, true)) {
                $errors[] = "Baris {$lineNumber}: jawaban benar harus A, B, C, atau D.";
            }

            if ($row['points'] < 1 || $row['points'] > 100) {
                $errors[] = "Baris {$lineNumber}: poin harus antara 1 sampai 100.";
            }

            if ($row['sort_order'] !== null && ($row['sort_order'] < 1 || $row['sort_order'] > 1000)) {
                $errors[] = "Baris {$lineNumber}: urutan harus antara 1 sampai 1000.";
            }

            $rows[] = $row;
        }

        fclose($handle);

        return [$rows, array_slice($errors, 0, 10)];
    }

    private function prepareQuestionImageZip(array $rows, ?UploadedFile $zipFile): array
    {
        $imageNames = collect($rows)
            ->pluck('image_file')
            ->filter()
            ->map(fn ($name) => strtolower($name))
            ->unique()
            ->values();

        if ($imageNames->isEmpty()) {
            return [null, [], []];
        }

        if (! $zipFile) {
            return [null, [], []];
        }

        $zipPath = $this->uploadedFilePath($zipFile);

        if (! $zipPath) {
            return [null, [], []];
        }

        if (! class_exists(\ZipArchive::class)) {
            return [null, [], ['Server belum mendukung ZipArchive untuk membaca file ZIP gambar.']];
        }

        $zip = new \ZipArchive();

        if ($zip->open($zipPath) !== true) {
            return [null, [], ['File ZIP gambar tidak bisa dibuka.']];
        }

        $entries = [];

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $stat = $zip->statIndex($index);
            $entryName = $stat['name'] ?? '';

            if ($entryName === '' || str_ends_with($entryName, '/')) {
                continue;
            }

            $filename = basename(str_replace('\\', '/', $entryName));
            $normalizedFilename = strtolower($filename);
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (! in_array($extension, self::QUESTION_IMAGE_EXTENSIONS, true)) {
                continue;
            }

            if (($stat['size'] ?? 0) > 2 * 1024 * 1024) {
                continue;
            }

            if (isset($entries[$normalizedFilename])) {
                continue;
            }

            $entries[$normalizedFilename] = [
                'index' => $index,
                'filename' => $filename,
            ];
        }

        return [$zip, $entries, []];
    }

    private function storeImportedQuestionImage(?\ZipArchive $zip, array $zipEntries, ?string $imageFile, $school, array &$storedImagePaths): ?string
    {
        if (! $zip || ! $imageFile) {
            return null;
        }

        $entry = $zipEntries[strtolower($imageFile)] ?? null;

        if (! $entry) {
            return null;
        }

        $content = $zip->getFromIndex($entry['index']);

        if ($content === false) {
            return null;
        }

        $temporaryDirectory = storage_path('app/tmp/exam-question-import');
        File::ensureDirectoryExists($temporaryDirectory);

        $temporaryPath = $temporaryDirectory.'/'.uniqid('question-image-', true).'-'.$entry['filename'];
        file_put_contents($temporaryPath, $content);

        $uploadedFile = new UploadedFile($temporaryPath, $entry['filename'], null, null, true);
        $storedPath = SchoolFileStorage::store($uploadedFile, $school, 'exams/questions', 'soal-ujian');
        $storedImagePaths[] = $storedPath;

        return $storedPath;
    }

    private function normalizeImportHeader(string $header): string
    {
        $header = strtolower(trim($header));

        return str_replace([' ', '-'], '_', $header);
    }

    private function uploadedFilePath(?UploadedFile $file): ?string
    {
        if (! $file) {
            return null;
        }

        foreach ([$file->getRealPath(), $file->getPathname(), $file->path()] as $path) {
            if (is_string($path) && $path !== '' && is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    private function formOptions(Request $request, $school): array
    {
        $role = $this->effectiveRole($request);
        $teacher = $this->activeTeacher($request, $school->id);

        if ($role === 'teacher') {
            $scheduleQuery = $teacher
                ? $teacher->schedules()->where('school_id', $school->id)->where('is_active', true)
                : null;

            $subjectIds = $scheduleQuery ? (clone $scheduleQuery)->pluck('subject_id')->filter()->unique() : collect();
            $classroomIds = $scheduleQuery ? (clone $scheduleQuery)->pluck('classroom_id')->filter()->unique() : collect();

            return [
                $school->subjects()->whereIn('id', $subjectIds)->where('is_active', true)->orderBy('name')->get(),
                $school->classrooms()->whereIn('id', $classroomIds)->where('is_active', true)->orderBy('name')->get(),
                collect([$teacher])->filter(),
            ];
        }

        return [
            $school->subjects()->where('is_active', true)->orderBy('name')->get(),
            $school->classrooms()->where('is_active', true)->orderBy('name')->get(),
            $school->teachers()->with('user')->where('is_active', true)->get()->sortBy('user.name'),
        ];
    }

    private function resolvedTeacherId(Request $request, string $schoolId, array $validated): ?string
    {
        if ($this->effectiveRole($request) !== 'teacher') {
            return $validated['teacher_id'] ?? null;
        }

        $teacher = $this->activeTeacher($request, $schoolId);

        abort_if(! $teacher, 403);

        $hasSchedule = $teacher->schedules()
            ->where('school_id', $schoolId)
            ->where('classroom_id', $validated['classroom_id'])
            ->where('subject_id', $validated['subject_id'])
            ->where('is_active', true)
            ->exists();

        abort_if(! $hasSchedule, 403);

        return $teacher->id;
    }

    private function authorizeExam(Request $request, Exam $exam): void
    {
        $school = $this->activeSchool($request);

        abort_if(! $school || $exam->school_id !== $school->id, 403);

        if ($this->effectiveRole($request) === 'teacher') {
            $teacher = $this->activeTeacher($request, $school->id);

            abort_if(! $teacher || $exam->teacher_id !== $teacher->id, 403);
        }
    }

    private function authorizeQuestion(Exam $exam, ExamQuestion $question): void
    {
        abort_if($question->exam_id !== $exam->id, 403);
    }

    private function authorizeStudentExam(Request $request, Exam $exam): Student
    {
        $school = $this->activeSchool($request);
        $student = $school ? $this->activeStudent($request, $school->id) : null;

        abort_if(! $school || ! $student || $exam->school_id !== $school->id, 403);

        $eligible = $student->classrooms()
            ->wherePivot('status', 'active')
            ->where('classrooms.id', $exam->classroom_id)
            ->exists();

        abort_if(! $eligible, 403);

        return $student;
    }

    private function activeTeacher(Request $request, string $schoolId): ?Teacher
    {
        return EffectiveAccess::user($request)
            ?->teacher()
            ->where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();
    }

    private function activeStudent(Request $request, string $schoolId): ?Student
    {
        return EffectiveAccess::user($request)
            ?->student()
            ->where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();
    }

    private function activeSchool(Request $request)
    {
        return EffectiveAccess::school($request);
    }

    private function effectiveRole(Request $request): ?string
    {
        return EffectiveAccess::role($request);
    }
}
