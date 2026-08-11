@push('head')
<style>
    .permission-toolbar { position: sticky; top: 0; z-index: 4; background: #fff; }
    .permission-group-card { border: 1px solid rgba(67, 89, 113, .12); border-radius: 12px; }
    .permission-item { border-radius: 8px; padding: .55rem .65rem; transition: background-color .15s ease; }
    .permission-item:hover { background: rgba(105, 108, 255, .06); }
    .permission-code { font-size: .68rem; color: #8592a3; }
</style>
@endpush

<div class="permission-toolbar border rounded-3 p-3 mb-3 shadow-sm">
    <div class="row g-2 align-items-center">
        <div class="col-lg-7">
            <div class="input-group">
                <span class="input-group-text"><i class="bx bx-search"></i></span>
                <input type="search" id="permissionSearch" class="form-control"
                    placeholder="Cari fitur atau kode permission...">
            </div>
        </div>
        <div class="col-lg-5 d-flex justify-content-lg-end align-items-center gap-2">
            <span class="badge bg-label-primary" id="permissionCounter">0 dipilih</span>
            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllPermissions">Pilih semua</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="clearAllPermissions">Kosongkan</button>
        </div>
    </div>
</div>

<div class="row g-3" id="permissionGroups">
    @foreach($permissionGroups as $group)
        <div class="col-xl-4 col-md-6 permission-group-wrapper">
            <div class="permission-group-card h-100" data-group="{{ $group['key'] }}">
                <div class="d-flex align-items-center justify-content-between border-bottom p-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="avatar-initial rounded bg-label-primary p-2">
                            <i class="bx {{ $group['icon'] }}"></i>
                        </span>
                        <div>
                            <h6 class="mb-0">{{ $group['label'] }}</h6>
                            <small class="text-muted">{{ count($group['permissions']) }} akses</small>
                        </div>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input group-toggle" type="checkbox"
                            aria-label="Pilih semua permission {{ $group['label'] }}">
                    </div>
                </div>
                <div class="p-2 permission-list">
                    @foreach($group['permissions'] as $item)
                        @php($permission = $item['model'])
                        <label class="permission-item d-flex align-items-start gap-2 mb-1"
                            data-search="{{ strtolower($item['label'].' '.$permission->name) }}">
                            <input class="form-check-input permission-checkbox mt-1" type="checkbox"
                                name="permission[]" value="{{ $permission->id }}"
                                @checked(in_array((int) $permission->id, $selectedPermissionIds, true))>
                            <span>
                                <span class="d-block fw-medium text-dark">{{ $item['label'] }}</span>
                                <code class="permission-code">{{ $permission->name }}</code>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>

@push('head')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = [...document.querySelectorAll('.permission-checkbox')];
    const wrappers = [...document.querySelectorAll('.permission-group-wrapper')];
    const counter = document.getElementById('permissionCounter');

    function updateState() {
        counter.textContent = checkboxes.filter(item => item.checked).length + ' dipilih';
        wrappers.forEach(wrapper => {
            const visible = [...wrapper.querySelectorAll('.permission-item')].filter(item => item.style.display !== 'none');
            const groupCheckboxes = visible.map(item => item.querySelector('.permission-checkbox'));
            const toggle = wrapper.querySelector('.group-toggle');
            toggle.checked = groupCheckboxes.length > 0 && groupCheckboxes.every(item => item.checked);
            toggle.indeterminate = groupCheckboxes.some(item => item.checked) && !toggle.checked;
        });
    }

    wrappers.forEach(wrapper => {
        wrapper.querySelector('.group-toggle').addEventListener('change', function () {
            wrapper.querySelectorAll('.permission-item').forEach(item => {
                if (item.style.display !== 'none') {
                    item.querySelector('.permission-checkbox').checked = this.checked;
                }
            });
            updateState();
        });
    });
    checkboxes.forEach(item => item.addEventListener('change', updateState));

    document.getElementById('permissionSearch').addEventListener('input', function () {
        const keyword = this.value.trim().toLowerCase();
        wrappers.forEach(wrapper => {
            let matches = 0;
            wrapper.querySelectorAll('.permission-item').forEach(item => {
                const match = item.dataset.search.includes(keyword);
                item.style.display = match ? '' : 'none';
                if (match) matches++;
            });
            wrapper.style.display = matches ? '' : 'none';
        });
        updateState();
    });

    document.getElementById('selectAllPermissions').addEventListener('click', function () {
        checkboxes.forEach(item => item.checked = true);
        updateState();
    });
    document.getElementById('clearAllPermissions').addEventListener('click', function () {
        checkboxes.forEach(item => item.checked = false);
        updateState();
    });
    updateState();
});
</script>
@endpush
