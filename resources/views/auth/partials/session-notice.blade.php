@if (session('auth_notice'))
    <div role="alert"
        style="margin-bottom: 1rem; padding: .85rem 1rem; border: 1px solid #f6c76e; border-radius: .75rem; background: #fff8e6; color: #7a4b00; line-height: 1.45;">
        <div style="display: flex; gap: .6rem; align-items: flex-start;">
            <span aria-hidden="true" style="font-size: 1.1rem;">&#9432;</span>
            <div>
                <strong>Sesi berakhir</strong>
                <div>{{ session('auth_notice') }}</div>
            </div>
        </div>
    </div>
@endif
