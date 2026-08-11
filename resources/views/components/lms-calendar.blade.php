@props([
    'events' => [],
    'storeRoute',
    'updateRouteTemplate',
    'destroyRouteTemplate',
    'calendarId' => 'lmsCalendar',
    'focusUpcoming' => false,
])

<div class="card border-0 shadow-sm mb-4 lms-calendar" id="{{ $calendarId }}">
    <div class="card-header bg-white border-0 p-4 pb-2">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="avatar-initial rounded bg-label-primary p-2"><i class="bx bx-calendar fs-4"></i></span>
                    <h5 class="mb-0 fw-bold">Kalender LMS</h5>
                </div>
                <p class="text-muted small mb-0">Jadwal materi, tugas, ujian, dan catatan pribadi Anda.</p>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
            <button
                type="button"
                class="btn btn-sm btn-light border calendar-collapse-toggle"
                data-bs-toggle="collapse"
                data-bs-target="#{{ $calendarId }}Collapse"
                aria-expanded="false"
                aria-controls="{{ $calendarId }}Collapse"
            >
                <i class="bx bx-calendar me-1 text-primary"></i>
                <span class="calendar-collapse-label">Kalender</span>
                <i class="bx bx-chevron-down ms-2 calendar-collapse-icon text-muted"></i>
            </button>
                <button type="button" class="btn btn-sm btn-outline-secondary calendar-prev" title="Bulan sebelumnya"><i class="bx bx-chevron-left"></i></button>
                <button type="button" class="btn btn-sm btn-label-primary calendar-today">Hari ini</button>
                <button type="button" class="btn btn-sm btn-outline-secondary calendar-next" title="Bulan berikutnya"><i class="bx bx-chevron-right"></i></button>
                <button type="button" class="btn btn-sm btn-primary calendar-add-note"><i class="bx bx-plus me-1"></i>Catatan Pribadi</button>
            </div>
        </div>
    </div>

    <div class="collapse" id="{{ $calendarId }}Collapse">
    <div class="card-body p-4 pt-3">
        @if($errors->hasAny(['title', 'description', 'note_date', 'note_time', 'color']))
            <div class="alert alert-danger py-2 small">
                <i class="bx bx-error-circle me-1"></i>{{ $errors->first() }}
            </div>
        @endif

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
            <h4 class="calendar-month-title fw-bold mb-0 text-capitalize"></h4>
            <div class="d-flex flex-wrap gap-2 calendar-legends small">
                <span><i class="legend-dot bg-info"></i>Materi</span>
                <span><i class="legend-dot bg-warning"></i>Tugas</span>
                <span><i class="legend-dot bg-primary"></i>Ujian</span>
                <span><i class="legend-dot bg-danger"></i>Deadline ujian</span>
                <span><i class="legend-dot bg-success"></i>Catatan pribadi</span>
            </div>
        </div>

        <div class="calendar-scroll border rounded-3 overflow-auto">
            <div class="calendar-board">
                <div class="calendar-weekdays">
                    @foreach(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $day)
                        <div>{{ $day }}</div>
                    @endforeach
                </div>
                <div class="calendar-grid"></div>
            </div>
        </div>

        <div class="calendar-agenda mt-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold mb-0"><i class="bx bx-list-ul me-1 text-primary"></i>Agenda <span class="agenda-date"></span></h6>
                <span class="badge bg-label-secondary agenda-count">0 agenda</span>
            </div>
            <div class="agenda-list"></div>
        </div>
    </div>
    </div>
</div>

<div class="modal fade" id="{{ $calendarId }}NoteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ $storeRoute }}" class="modal-content calendar-note-form">
            @csrf
            <div class="modal-header">
                <div><h5 class="modal-title fw-bold mb-1">Catatan Pribadi</h5><small class="text-muted">Catatan ini hanya dapat dilihat oleh akun Anda.</small></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Judul <span class="text-danger">*</span></label><input type="text" name="title" class="form-control" maxlength="255" required></div>
                <div class="row g-3 mb-3">
                    <div class="col-sm-7"><label class="form-label">Tanggal <span class="text-danger">*</span></label><input type="date" name="note_date" class="form-control" required></div>
                    <div class="col-sm-5"><label class="form-label">Jam</label><input type="time" name="note_time" class="form-control"></div>
                </div>
                <div class="mb-3"><label class="form-label">Warna</label><select name="color" class="form-select"><option value="success">Hijau</option><option value="primary">Biru</option><option value="warning">Kuning</option><option value="danger">Merah</option><option value="info">Biru muda</option></select></div>
                <div><label class="form-label">Keterangan</label><textarea name="description" class="form-control" rows="3" maxlength="3000" placeholder="Contoh: Kerjakan bagian analisis Tugas A"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary"><i class="bx bx-save me-1"></i>Simpan</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="{{ $calendarId }}DetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" class="calendar-edit-form">
                @csrf @method('PUT')
                <div class="modal-header"><div><span class="badge bg-label-success mb-2"><i class="bx bx-lock-alt me-1"></i>Pribadi</span><h5 class="modal-title fw-bold mb-0">Edit Catatan</h5></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Judul</label><input type="text" name="title" class="form-control edit-title" maxlength="255" required></div>
                    <div class="row g-3 mb-3"><div class="col-sm-7"><label class="form-label">Tanggal</label><input type="date" name="note_date" class="form-control edit-date" required></div><div class="col-sm-5"><label class="form-label">Jam</label><input type="time" name="note_time" class="form-control edit-time"></div></div>
                    <div class="mb-3"><label class="form-label">Warna</label><select name="color" class="form-select edit-color"><option value="success">Hijau</option><option value="primary">Biru</option><option value="warning">Kuning</option><option value="danger">Merah</option><option value="info">Biru muda</option></select></div>
                    <div><label class="form-label">Keterangan</label><textarea name="description" class="form-control edit-description" rows="3" maxlength="3000"></textarea></div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-label-danger calendar-delete-note"><i class="bx bx-trash me-1"></i>Hapus</button>
                    <div><button type="button" class="btn btn-label-secondary me-1" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan Perubahan</button></div>
                </div>
            </form>
            <form method="POST" class="calendar-delete-form d-none">@csrf @method('DELETE')</form>
        </div>
    </div>
</div>

<script type="application/json" id="{{ $calendarId }}Data">@json($events)</script>

@once
<style>
    .lms-calendar .legend-dot{display:inline-block;width:.55rem;height:.55rem;border-radius:50%;margin-right:.3rem}.lms-calendar .calendar-board{min-width:790px}.lms-calendar .calendar-weekdays,.lms-calendar .calendar-grid{display:grid;grid-template-columns:repeat(7,minmax(0,1fr))}.lms-calendar .calendar-weekdays{background:#f5f5f9;border-bottom:1px solid #e6e6eb}.lms-calendar .calendar-weekdays div{padding:.7rem;text-align:center;font-size:.75rem;font-weight:700;color:#697a8d;text-transform:uppercase}.lms-calendar .calendar-day{min-height:126px;padding:.55rem;border-right:1px solid #ececf1;border-bottom:1px solid #ececf1;background:#fff;cursor:pointer;transition:.15s}.lms-calendar .calendar-day:nth-child(7n){border-right:0}.lms-calendar .calendar-day:hover{background:#f8f8ff}.lms-calendar .calendar-day.outside{background:#fafafa;color:#b4b4bd}.lms-calendar .calendar-day.today{box-shadow:inset 0 0 0 2px #696cff}.lms-calendar .day-number{display:flex;align-items:center;justify-content:center;width:1.75rem;height:1.75rem;border-radius:50%;font-size:.8rem;font-weight:700}.lms-calendar .today .day-number{background:#696cff;color:#fff}.lms-calendar .day-events{display:flex;flex-direction:column;gap:.25rem;margin-top:.35rem}.lms-calendar .calendar-event{display:block;border:0;border-left:3px solid;padding:.28rem .4rem;border-radius:.3rem;text-align:left;font-size:.68rem;line-height:1.25;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;width:100%;cursor:pointer}.lms-calendar .event-info{background:#e7f5ff;border-color:#03c3ec;color:#087990}.lms-calendar .event-warning{background:#fff4e5;border-color:#ffab00;color:#9a6700}.lms-calendar .event-primary{background:#eeeeff;border-color:#696cff;color:#4d50c7}.lms-calendar .event-danger{background:#ffe8e8;border-color:#ff3e1d;color:#b42318}.lms-calendar .event-success{background:#e8fadf;border-color:#71dd37;color:#277d08}.lms-calendar .more-events{font-size:.68rem;color:#696cff;font-weight:600;padding-left:.35rem}.lms-calendar .agenda-item{display:flex;align-items-start;gap:.75rem;border:1px solid #ececf1;border-radius:.65rem;padding:.75rem;margin-bottom:.5rem;background:#fff}.lms-calendar .agenda-icon{width:2.25rem;height:2.25rem;display:flex;align-items:center;justify-content:center;border-radius:.5rem;flex:0 0 auto}.lms-calendar .empty-agenda{text-align:center;padding:1.25rem;border:1px dashed #d9dee3;border-radius:.65rem;color:#8592a3}@media(max-width:767.98px){.lms-calendar .card-body,.lms-calendar .card-header{padding-left:1rem!important;padding-right:1rem!important}.lms-calendar .calendar-day{min-height:105px}}
</style>
@endonce

<script>
(() => {
    function initializeCalendar() {
    const root = document.getElementById(@json($calendarId));
    if (!root) return;
    const events = JSON.parse(document.getElementById(@json($calendarId.'Data')).textContent || '[]');
    const focusUpcoming = @json((bool) $focusUpcoming);
    const monthTitle = root.querySelector('.calendar-month-title');
    const grid = root.querySelector('.calendar-grid');
    const agendaDate = root.querySelector('.agenda-date');
    const agendaCount = root.querySelector('.agenda-count');
    const agendaList = root.querySelector('.agenda-list');
    const collapseElement = document.getElementById(@json($calendarId.'Collapse'));
    const collapseIcon = root.querySelector('.calendar-collapse-icon');
    const collapseLabel = root.querySelector('.calendar-collapse-label');
    const addModalElement = document.getElementById(@json($calendarId.'NoteModal'));
    const detailModalElement = document.getElementById(@json($calendarId.'DetailModal'));
    const addModal = bootstrap.Modal.getOrCreateInstance(addModalElement);
    const detailModal = bootstrap.Modal.getOrCreateInstance(detailModalElement);
    const today = new Date();
    let cursor = new Date(today.getFullYear(), today.getMonth(), 1);
    let selectedDate = formatDate(today);

    function formatDate(date) {
        return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
    }
    function parseDate(value) {
        const [year, month, day] = value.split('-').map(Number);
        return new Date(year, month - 1, day);
    }
    function escapeHtml(value) {
        const node = document.createElement('div'); node.textContent = value || ''; return node.innerHTML;
    }
    function dateLabel(value, long = false) {
        return parseDate(value).toLocaleDateString('id-ID', long ? {weekday:'long',day:'numeric',month:'long',year:'numeric'} : {day:'numeric',month:'long',year:'numeric'});
    }
    function dayEvents(value) {
        return events.filter(event => event.date === value).sort((a,b) => (a.time || '').localeCompare(b.time || ''));
    }
    function focusNearestUpcomingEvent() {
        if (!focusUpcoming) return;

        const todayValue = formatDate(today);
        const upcoming = events
            .filter(event => !event.private && event.date >= todayValue)
            .sort((a, b) => `${a.date} ${a.time || '00:00'}`.localeCompare(`${b.date} ${b.time || '00:00'}`))[0];

        if (!upcoming) return;

        const eventDate = parseDate(upcoming.date);
        cursor = new Date(eventDate.getFullYear(), eventDate.getMonth(), 1);
        selectedDate = upcoming.date;
    }
    function eventButton(event) {
        return `<button type="button" class="calendar-event event-${escapeHtml(event.color)}" data-event-id="${escapeHtml(event.id)}" title="${escapeHtml(event.title)}">${event.time ? escapeHtml(event.time) + ' ' : ''}${event.private ? '🔒 ' : ''}${escapeHtml(event.title)}</button>`;
    }
    function bindEvents(scope) {
        scope.querySelectorAll('[data-event-id]').forEach(button => button.addEventListener('click', event => {
            event.stopPropagation();
            openEvent(events.find(item => item.id === button.dataset.eventId));
        }));
    }
    function render() {
        monthTitle.textContent = cursor.toLocaleDateString('id-ID', {month:'long',year:'numeric'});
        const first = new Date(cursor.getFullYear(), cursor.getMonth(), 1);
        const start = new Date(first);
        start.setDate(first.getDate() - ((first.getDay() + 6) % 7));
        let html = '';
        for (let index = 0; index < 42; index++) {
            const date = new Date(start); date.setDate(start.getDate() + index);
            const value = formatDate(date); const items = dayEvents(value);
            const classes = ['calendar-day'];
            if (date.getMonth() !== cursor.getMonth()) classes.push('outside');
            if (value === formatDate(today)) classes.push('today');
            html += `<div class="${classes.join(' ')}" data-date="${value}"><span class="day-number">${date.getDate()}</span><div class="day-events">${items.slice(0,3).map(eventButton).join('')}${items.length > 3 ? `<span class="more-events">+${items.length - 3} agenda lainnya</span>` : ''}</div></div>`;
        }
        grid.innerHTML = html;
        grid.querySelectorAll('.calendar-day').forEach(day => day.addEventListener('click', () => { selectedDate = day.dataset.date; renderAgenda(); }));
        bindEvents(grid);
        renderAgenda();
    }
    function renderAgenda() {
        const items = dayEvents(selectedDate);
        agendaDate.textContent = `— ${dateLabel(selectedDate, true)}`;
        agendaCount.textContent = `${items.length} agenda`;
        if (!items.length) {
            agendaList.innerHTML = '<div class="empty-agenda"><i class="bx bx-calendar-x fs-3 d-block mb-1"></i>Tidak ada agenda pada tanggal ini.</div>';
            return;
        }
        agendaList.innerHTML = items.map(event => `<div class="agenda-item"><span class="agenda-icon bg-label-${escapeHtml(event.color)}"><i class="bx ${escapeHtml(event.icon)}"></i></span><div class="flex-grow-1 overflow-hidden"><div class="fw-semibold text-dark">${escapeHtml(event.title)}</div><div class="small text-muted">${event.time ? `<i class="bx bx-time me-1"></i>${escapeHtml(event.time)} · ` : ''}${escapeHtml(event.subtitle || (event.private ? 'Catatan pribadi' : ''))}</div></div><button type="button" class="btn btn-sm btn-icon btn-label-${escapeHtml(event.color)}" data-event-id="${escapeHtml(event.id)}"><i class="bx ${event.private ? 'bx-edit' : 'bx-right-arrow-alt'}"></i></button></div>`).join('');
        bindEvents(agendaList);
    }
    function openEvent(event) {
        if (!event) return;
        if (!event.private) { if (event.url) window.location.href = event.url; return; }
        const form = detailModalElement.querySelector('.calendar-edit-form');
        form.action = @json($updateRouteTemplate).replace('__NOTE__', event.note_id);
        detailModalElement.querySelector('.calendar-delete-form').action = @json($destroyRouteTemplate).replace('__NOTE__', event.note_id);
        detailModalElement.querySelector('.edit-title').value = event.title || '';
        detailModalElement.querySelector('.edit-date').value = event.date || '';
        detailModalElement.querySelector('.edit-time').value = event.time || '';
        detailModalElement.querySelector('.edit-color').value = event.color || 'success';
        detailModalElement.querySelector('.edit-description').value = event.subtitle || '';
        detailModal.show();
    }
    function openAdd(date = selectedDate) {
        const form = addModalElement.querySelector('form'); form.reset();
        form.querySelector('[name="note_date"]').value = date || formatDate(today);
        form.querySelector('[name="color"]').value = 'success'; addModal.show();
    }
    root.querySelector('.calendar-prev').addEventListener('click', () => { cursor.setMonth(cursor.getMonth() - 1); render(); });
    root.querySelector('.calendar-next').addEventListener('click', () => { cursor.setMonth(cursor.getMonth() + 1); render(); });
    root.querySelector('.calendar-today').addEventListener('click', () => { cursor = new Date(today.getFullYear(), today.getMonth(), 1); selectedDate = formatDate(today); render(); });
    root.querySelector('.calendar-add-note').addEventListener('click', () => openAdd());
    collapseElement.addEventListener('shown.bs.collapse', () => {
        collapseIcon.classList.replace('bx-chevron-down', 'bx-chevron-up');
        collapseLabel.textContent = 'Tutup Kalender';
    });
    collapseElement.addEventListener('hidden.bs.collapse', () => {
        collapseIcon.classList.replace('bx-chevron-up', 'bx-chevron-down');
        collapseLabel.textContent = 'Buka Kalender';
    });
    detailModalElement.querySelector('.calendar-delete-note').addEventListener('click', () => { if (confirm('Hapus catatan pribadi ini?')) detailModalElement.querySelector('.calendar-delete-form').submit(); });
    focusNearestUpcomingEvent();
    render();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeCalendar, { once: true });
    } else {
        initializeCalendar();
    }
})();
</script>
