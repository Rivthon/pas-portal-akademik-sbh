@props([
    'messages' => collect(),
    'action',
    'viewer' => 'mahasiswa',
    'title' => 'Diskusi KRS',
    'submitLabel' => 'Kirim Pesan',
])

<div>
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h6 class="fw-bold mb-0"><i class="bx bx-conversation text-primary me-1"></i>{{ $title }}</h6>
        <span class="badge bg-label-primary">{{ $messages->count() }} pesan</span>
    </div>

    <div class="rounded border bg-light p-3 mb-3" style="max-height: 340px; overflow-y: auto;">
        @forelse($messages as $message)
            @php
                $isMine = $message->sender_type === $viewer;
                $senderLabel = $isMine ? 'Anda' : ($message->sender_type === 'dosen' ? 'Dosen Pembimbing' : 'Mahasiswa');
            @endphp
            <div class="d-flex {{ $isMine ? 'justify-content-end' : 'justify-content-start' }} mb-3">
                <div style="max-width: 82%;">
                    <small class="d-block mb-1 {{ $isMine ? 'text-end' : '' }} text-muted">{{ $senderLabel }}</small>
                    <div class="rounded-3 px-3 py-2 {{ $isMine ? 'bg-primary text-white' : 'bg-white border text-dark' }}">
                        {!! nl2br(e($message->message)) !!}
                    </div>
                    <small class="d-block mt-1 {{ $isMine ? 'text-end' : '' }} text-muted">{{ $message->created_at?->translatedFormat('d M Y, H:i') }}</small>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-4">
                <i class="bx bx-message-rounded fs-2 d-block mb-2"></i>
                Belum ada percakapan mengenai KRS.
            </div>
        @endforelse
    </div>

    <form method="POST" action="{{ $action }}">
        @csrf
        <label class="form-label fw-semibold">Tulis {{ $viewer === 'dosen' ? 'komentar' : 'umpan balik' }}</label>
        <textarea name="message" class="form-control mb-2" rows="3" maxlength="2000" required
            placeholder="{{ $viewer === 'dosen' ? 'Contoh: Jumlah SKS masih kurang, silakan diperbaiki.' : 'Contoh: Sudah diperbaiki Pak/Bu, mohon diperiksa kembali.' }}"></textarea>
        <div class="d-flex justify-content-between align-items-center gap-2">
            <small class="text-muted">Maksimal 2.000 karakter</small>
            <button type="submit" class="btn btn-primary"><i class="bx bx-send me-1"></i>{{ $submitLabel }}</button>
        </div>
    </form>
</div>
