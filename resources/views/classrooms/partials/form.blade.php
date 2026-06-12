@php
    $selectedStudentValues = array_map('strval', $selectedStudents);
@endphp

<style>
    .form-check-input.border-danger:checked {
        background-color: var(--bs-danger);
        border-color: var(--bs-danger);
    }
</style>

<div class="border-bottom pb-3 mb-4">
    <h6 class="mb-1">Informasi Rombel</h6>
    <div class="text-muted small">Tentukan periode akademik, kelas, dan identitas rombel.</div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <label class="form-label" for="{{ $mode }}_academic_year_id">Tahun Ajaran</label>
        <select class="form-select" id="{{ $mode }}_academic_year_id" name="academic_year_id" required>
            <option value="">Pilih tahun ajaran</option>
            @foreach($academicYears as $academicYear)
                <option value="{{ $academicYear->id }}" @selected((string) old('academic_year_id', $classroom?->academic_year_id) === (string) $academicYear->id)>
                    {{ $academicYear->name }}{{ $academicYear->is_active ? ' - Aktif' : '' }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6 mb-4">
        <label class="form-label" for="{{ $mode }}_semester_id">Semester</label>
        <select class="form-select" id="{{ $mode }}_semester_id" name="semester_id" required>
            <option value="">Pilih semester</option>
            @foreach($semesters as $semester)
                <option value="{{ $semester->id }}" @selected((string) old('semester_id', $classroom?->semester_id) === (string) $semester->id)>
                    {{ $semester->name }} - {{ $semester->academicYear?->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6 mb-4">
        <label class="form-label" for="{{ $mode }}_school_class_id">Kelas</label>
        <select class="form-select" id="{{ $mode }}_school_class_id" name="school_class_id" required>
            <option value="">Pilih kelas</option>
            @foreach($schoolClasses as $class)
                <option value="{{ $class->id }}" @selected((string) old('school_class_id', $classroom?->school_class_id) === (string) $class->id)>
                    {{ $class->name }}{{ $class->level ? ' - '.$class->level : '' }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6 mb-4">
        <label class="form-label" for="{{ $mode }}_name">Nama Rombel</label>
        <input class="form-control" id="{{ $mode }}_name" name="name" value="{{ old('name', $classroom?->name) }}" placeholder="Contoh: VII A, X IPA 1" required>
    </div>

    <div class="col-md-6 mb-4">
        <label class="form-label" for="{{ $mode }}_homeroom_teacher_id">Wali Kelas</label>
        <select class="form-select" id="{{ $mode }}_homeroom_teacher_id" name="homeroom_teacher_id">
            <option value="">Belum ditentukan</option>
            @foreach($teachers as $teacher)
                <option value="{{ $teacher->id }}" @selected((string) old('homeroom_teacher_id', $classroom?->homeroom_teacher_id) === (string) $teacher->id)>
                    {{ $teacher->user?->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6 mb-4">
        <label class="form-label" for="{{ $mode }}_capacity">Kapasitas</label>
        <input type="number" min="1" max="999" class="form-control" id="{{ $mode }}_capacity" name="capacity" value="{{ old('capacity', $classroom?->capacity) }}" placeholder="Contoh: 36">
    </div>
</div>

<div class="border-top border-bottom py-3 mb-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <h6 class="mb-1">Anggota Murid</h6>
            <div class="text-muted small">Pilih murid dari tabel, lalu gunakan filter angkatan untuk mempercepat pencarian.</div>
        </div>
        <button class="btn btn-label-primary" type="button" data-bs-toggle="modal" data-bs-target="#{{ $mode }}_student_modal" data-student-modal-trigger="{{ $mode }}">
            <i class="bx bx-user-plus me-1"></i> Pilih Murid
        </button>
    </div>
</div>

<div id="{{ $mode }}_student_hidden_inputs"></div>

<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
    <div class="input-group">
        <input type="search" class="form-control" placeholder="Cari anggota berdasarkan nama, NIS, NISN" data-selected-student-search="{{ $mode }}">
        <span class="input-group-text"><i class="bx bx-search"></i></span>
    </div>
    <button type="button" class="btn btn-outline-danger" data-remove-checked-selected-students="{{ $mode }}">
        <i class="bx bx-trash me-1"></i> Hapus Terpilih
    </button>
</div>

<div class="table-responsive mb-4">
    <table class="table table-sm table-hover align-middle">
        <thead>
            <tr>
                <th style="width: 48px;">
                    <input class="form-check-input border-danger" type="checkbox" data-check-all-selected-students="{{ $mode }}" title="Pilih semua anggota yang tampil" aria-label="Pilih semua anggota yang tampil">
                </th>
                <th>Murid Terpilih</th>
                <th>NIS</th>
                <th>Angkatan</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody id="{{ $mode }}_selected_student_rows">
            <tr data-empty-row="true">
                <td colspan="5" class="text-center text-muted py-4">Belum ada murid dipilih.</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="form-check mb-4">
    <input class="form-check-input" type="checkbox" id="{{ $mode }}_is_active" name="is_active" value="1" @checked(old('is_active', $classroom?->is_active ?? true))>
    <label class="form-check-label" for="{{ $mode }}_is_active">Rombel aktif</label>
</div>

<div class="modal fade" id="{{ $mode }}_student_modal" tabindex="-1" aria-labelledby="{{ $mode }}_student_modal_label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="{{ $mode }}_student_modal_label">Pilih Anggota Murid</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-column flex-md-row gap-3 mb-4">
                    <select class="form-select" data-student-cohort-filter="{{ $mode }}">
                        <option value="">Semua angkatan</option>
                        @foreach($studentCohorts as $cohort)
                            <option value="{{ $cohort }}">Angkatan {{ $cohort }}</option>
                        @endforeach
                    </select>
                    <div class="input-group">
                        <input type="search" class="form-control" placeholder="Cari nama, NIS, NISN" data-student-search="{{ $mode }}">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                    </div>
                </div>

                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
                    <div class="text-muted small" data-visible-student-count="{{ $mode }}">0 murid tampil</div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-label-primary" data-select-visible-students="{{ $mode }}">
                            Pilih Semua yang Tampil
                        </button>
                        <button type="button" class="btn btn-sm btn-label-secondary" data-unselect-visible-students="{{ $mode }}">
                            Batal Pilih yang Tampil
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width: 48px;">
                                    <input class="form-check-input" type="checkbox" data-check-all-modal-students="{{ $mode }}" title="Pilih semua murid yang tampil" aria-label="Pilih semua murid yang tampil">
                                </th>
                                <th>Murid</th>
                                <th>NIS/NISN</th>
                                <th>Angkatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $student)
                                @php
                                    $studentId = (string) $student->id;
                                    $studentSearchText = strtolower($student->user?->name.' '.$student->nis.' '.$student->nisn);
                                @endphp
                                <tr data-student-row="{{ $mode }}" data-cohort="{{ $student->entry_year }}" data-search="{{ $studentSearchText }}">
                                    <td>
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            value="{{ $student->id }}"
                                            data-student-checkbox="{{ $mode }}"
                                            data-name="{{ $student->user?->name }}"
                                            data-email="{{ $student->user?->email }}"
                                            data-nis="{{ $student->nis }}"
                                            data-nisn="{{ $student->nisn }}"
                                            data-cohort="{{ $student->entry_year }}"
                                            @checked(in_array($studentId, $selectedStudentValues, true))
                                        >
                                    </td>
                                    <td>
                                        <strong>{{ $student->user?->name }}</strong>
                                        <div class="text-muted small">{{ $student->user?->email }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $student->nis ?: '-' }}</div>
                                        <div class="text-muted small">{{ $student->nisn ? 'NISN: '.$student->nisn : '' }}</div>
                                    </td>
                                    <td>{{ $student->entry_year ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Belum ada data murid aktif.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mt-4">
                    <div class="text-muted small" data-student-page-info="{{ $mode }}">
                        Menampilkan 0 sampai 0 dari 0 murid
                    </div>
                    <nav aria-label="Pagination anggota murid">
                        <ul class="pagination pagination-sm mb-0" data-student-pagination="{{ $mode }}"></ul>
                    </nav>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Selesai</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const mode = @json($mode);
    const hiddenContainer = document.getElementById(mode + '_student_hidden_inputs');
    const selectedRows = document.getElementById(mode + '_selected_student_rows');
    const selectedSearch = document.querySelector('[data-selected-student-search="' + mode + '"]');
    const checkAllSelected = document.querySelector('[data-check-all-selected-students="' + mode + '"]');
    const removeCheckedSelectedButton = document.querySelector('[data-remove-checked-selected-students="' + mode + '"]');
    const filter = document.querySelector('[data-student-cohort-filter="' + mode + '"]');
    const search = document.querySelector('[data-student-search="' + mode + '"]');
    const rows = Array.from(document.querySelectorAll('[data-student-row="' + mode + '"]'));
    const checkboxes = Array.from(document.querySelectorAll('[data-student-checkbox="' + mode + '"]'));
    const visibleCount = document.querySelector('[data-visible-student-count="' + mode + '"]');
    const selectVisibleButton = document.querySelector('[data-select-visible-students="' + mode + '"]');
    const unselectVisibleButton = document.querySelector('[data-unselect-visible-students="' + mode + '"]');
    const checkAllModal = document.querySelector('[data-check-all-modal-students="' + mode + '"]');
    const pageInfo = document.querySelector('[data-student-page-info="' + mode + '"]');
    const pagination = document.querySelector('[data-student-pagination="' + mode + '"]');
    const modalElement = document.getElementById(mode + '_student_modal');
    const modalTrigger = document.querySelector('[data-student-modal-trigger="' + mode + '"]');

    if (!hiddenContainer || !selectedRows) {
        return;
    }

    if (modalElement && modalElement.parentElement !== document.body) {
        document.body.appendChild(modalElement);
    }

    if (modalTrigger && modalElement && window.bootstrap) {
        modalTrigger.addEventListener('click', function (event) {
            event.preventDefault();
            bootstrap.Modal.getOrCreateInstance(modalElement).show();
        });
    }

    const selected = new Map();
    const perPage = 10;
    let currentPage = 1;
    let filteredRows = rows;

    const normalize = (value) => (value || '').toString().toLowerCase();

    const applySelectedSearch = () => {
        const keyword = normalize(selectedSearch ? selectedSearch.value : '');
        const selectedTableRows = Array.from(selectedRows.querySelectorAll('[data-selected-student-row]'));

        selectedTableRows.forEach((row) => {
            row.classList.toggle('d-none', keyword && !normalize(row.dataset.search).includes(keyword));
        });

        if (checkAllSelected) {
            checkAllSelected.checked = false;
        }
    };

    const renderSelected = () => {
        hiddenContainer.innerHTML = '';
        selectedRows.innerHTML = '';

        if (selected.size === 0) {
            selectedRows.innerHTML = '<tr data-empty-row="true"><td colspan="5" class="text-center text-muted py-4">Belum ada murid dipilih.</td></tr>';
            if (checkAllSelected) {
                checkAllSelected.checked = false;
            }
            return;
        }

        selected.forEach((student, id) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'student_ids[]';
            input.value = id;
            hiddenContainer.appendChild(input);

            const tr = document.createElement('tr');
            tr.dataset.selectedStudentRow = id;
            tr.dataset.search = [student.name, student.nis, student.nisn].join(' ');

            const checkCell = document.createElement('td');
            const rowCheckbox = document.createElement('input');
            rowCheckbox.type = 'checkbox';
            rowCheckbox.className = 'form-check-input border-danger';
            rowCheckbox.dataset.selectedStudentCheckbox = id;
            rowCheckbox.setAttribute('aria-label', 'Pilih anggota untuk dihapus');
            checkCell.appendChild(rowCheckbox);

            const nameCell = document.createElement('td');
            const name = document.createElement('strong');
            name.textContent = student.name || '-';
            const email = document.createElement('div');
            email.className = 'text-muted small';
            email.textContent = student.email || '';
            nameCell.append(name, email);

            const nisCell = document.createElement('td');
            const nis = document.createElement('div');
            nis.textContent = student.nis || '-';
            const nisn = document.createElement('div');
            nisn.className = 'text-muted small';
            nisn.textContent = student.nisn ? 'NISN: ' + student.nisn : '';
            nisCell.append(nis, nisn);

            const cohortCell = document.createElement('td');
            cohortCell.textContent = student.cohort || '-';

            const actionCell = document.createElement('td');
            actionCell.className = 'text-end';
            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'btn btn-sm btn-icon btn-outline-danger';
            removeButton.dataset.removeStudent = id;
            removeButton.title = 'Hapus';
            removeButton.setAttribute('aria-label', 'Hapus');
            const removeIcon = document.createElement('i');
            removeIcon.className = 'bx bx-trash';
            removeButton.appendChild(removeIcon);
            actionCell.appendChild(removeButton);

            tr.append(checkCell, nameCell, nisCell, cohortCell, actionCell);
            selectedRows.appendChild(tr);
        });

        applySelectedSearch();
    };

    const totalPages = () => Math.max(1, Math.ceil(filteredRows.length / perPage));

    const visibleCheckboxes = () => checkboxes.filter((checkbox) => !checkbox.closest('tr').classList.contains('d-none'));

    const createPageItem = ({ label, page, active = false, disabled = false, icon = null, ariaLabel = null }) => {
        const item = document.createElement('li');
        item.className = 'page-item';
        item.classList.toggle('active', active);
        item.classList.toggle('disabled', disabled);

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'page-link';
        button.disabled = disabled;
        button.dataset.studentPage = page;

        if (ariaLabel) {
            button.setAttribute('aria-label', ariaLabel);
        }

        if (icon) {
            const iconElement = document.createElement('i');
            iconElement.className = icon;
            button.appendChild(iconElement);
        } else {
            button.textContent = label;
        }

        item.appendChild(button);
        return item;
    };

    const renderPagination = () => {
        if (!pagination) {
            return;
        }

        const pageCount = totalPages();
        pagination.innerHTML = '';

        pagination.appendChild(createPageItem({
            page: currentPage - 1,
            disabled: currentPage <= 1,
            icon: 'bx bx-chevron-left',
            ariaLabel: 'Sebelumnya',
        }));

        for (let page = 1; page <= pageCount; page++) {
            if (pageCount > 7 && page > 2 && page < pageCount - 1 && Math.abs(page - currentPage) > 1) {
                if (!pagination.querySelector('[data-pagination-ellipsis="true"]')) {
                    const ellipsis = document.createElement('li');
                    ellipsis.className = 'page-item disabled';
                    ellipsis.dataset.paginationEllipsis = 'true';
                    ellipsis.innerHTML = '<span class="page-link">...</span>';
                    pagination.appendChild(ellipsis);
                }
                continue;
            }

            pagination.appendChild(createPageItem({
                label: page.toString(),
                page,
                active: page === currentPage,
            }));
        }

        pagination.appendChild(createPageItem({
            page: currentPage + 1,
            disabled: currentPage >= pageCount,
            icon: 'bx bx-chevron-right',
            ariaLabel: 'Berikutnya',
        }));
    };

    const syncModalCheckAll = () => {
        if (!checkAllModal) {
            return;
        }

        const visible = visibleCheckboxes();
        checkAllModal.checked = visible.length > 0 && visible.every((checkbox) => checkbox.checked);
        checkAllModal.indeterminate = visible.some((checkbox) => checkbox.checked) && !checkAllModal.checked;
    };

    const renderStudentPage = () => {
        const pageCount = totalPages();
        currentPage = Math.min(Math.max(currentPage, 1), pageCount);
        const start = (currentPage - 1) * perPage;
        const visibleRows = filteredRows.slice(start, start + perPage);

        rows.forEach((row) => {
            row.classList.toggle('d-none', !visibleRows.includes(row));
        });

        if (visibleCount) {
            visibleCount.textContent = filteredRows.length + ' murid sesuai filter';
        }

        if (pageInfo) {
            const firstItem = filteredRows.length === 0 ? 0 : start + 1;
            const lastItem = Math.min(start + perPage, filteredRows.length);
            pageInfo.textContent = 'Menampilkan ' + firstItem + ' sampai ' + lastItem + ' dari ' + filteredRows.length + ' murid';
        }

        renderPagination();
        syncModalCheckAll();
    };

    const applyFilters = () => {
        const cohort = filter ? filter.value : '';
        const keyword = normalize(search ? search.value : '');

        filteredRows = rows.filter((row) => {
            const matchCohort = !cohort || row.dataset.cohort === cohort;
            const matchSearch = !keyword || normalize(row.dataset.search).includes(keyword);
            return matchCohort && matchSearch;
        });

        currentPage = 1;
        renderStudentPage();
    };

    checkboxes.forEach((checkbox) => {
        if (checkbox.checked) {
            selected.set(checkbox.value, checkbox.dataset);
        }

        checkbox.addEventListener('change', () => {
            if (checkbox.checked) {
                selected.set(checkbox.value, checkbox.dataset);
            } else {
                selected.delete(checkbox.value);
            }

            renderSelected();
            syncModalCheckAll();
        });
    });

    selectedRows.addEventListener('click', function (event) {
        const button = event.target.closest('[data-remove-student]');

        if (!button) {
            return;
        }

        const id = button.dataset.removeStudent;
        selected.delete(id);

        const checkbox = checkboxes.find((item) => item.value === id);
        if (checkbox) {
            checkbox.checked = false;
        }

        renderSelected();
    });

    if (selectedSearch) {
        selectedSearch.addEventListener('input', applySelectedSearch);
    }

    if (checkAllSelected) {
        checkAllSelected.addEventListener('change', () => {
            const visibleSelectedCheckboxes = Array.from(selectedRows.querySelectorAll('[data-selected-student-checkbox]'))
                .filter((checkbox) => !checkbox.closest('tr').classList.contains('d-none'));

            visibleSelectedCheckboxes.forEach((checkbox) => {
                checkbox.checked = checkAllSelected.checked;
            });
        });
    }

    if (removeCheckedSelectedButton) {
        removeCheckedSelectedButton.addEventListener('click', () => {
            const checkedIds = Array.from(selectedRows.querySelectorAll('[data-selected-student-checkbox]:checked'))
                .map((checkbox) => checkbox.dataset.selectedStudentCheckbox);

            checkedIds.forEach((id) => {
                selected.delete(id);

                const checkbox = checkboxes.find((item) => item.value === id);
                if (checkbox) {
                    checkbox.checked = false;
                }
            });

            renderSelected();
        });
    }

    if (filter) {
        filter.addEventListener('change', applyFilters);
    }

    if (search) {
        search.addEventListener('input', applyFilters);
    }

    if (pagination) {
        pagination.addEventListener('click', (event) => {
            const button = event.target.closest('[data-student-page]');

            if (!button || button.disabled) {
                return;
            }

            currentPage = Number(button.dataset.studentPage);
            renderStudentPage();
        });
    }

    if (selectVisibleButton) {
        selectVisibleButton.addEventListener('click', () => {
            visibleCheckboxes().forEach((checkbox) => {
                checkbox.checked = true;
                selected.set(checkbox.value, checkbox.dataset);
            });

            renderSelected();
            syncModalCheckAll();
        });
    }

    if (unselectVisibleButton) {
        unselectVisibleButton.addEventListener('click', () => {
            visibleCheckboxes().forEach((checkbox) => {
                checkbox.checked = false;
                selected.delete(checkbox.value);
            });

            renderSelected();
            syncModalCheckAll();
        });
    }

    if (checkAllModal) {
        checkAllModal.addEventListener('change', () => {
            visibleCheckboxes().forEach((checkbox) => {
                checkbox.checked = checkAllModal.checked;

                if (checkbox.checked) {
                    selected.set(checkbox.value, checkbox.dataset);
                } else {
                    selected.delete(checkbox.value);
                }
            });

            renderSelected();
            syncModalCheckAll();
        });
    }

    renderSelected();
    applyFilters();
});
</script>
