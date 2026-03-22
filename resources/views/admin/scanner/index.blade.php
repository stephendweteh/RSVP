@extends('layouts.admin')

@section('title', 'Scanner — '.config('app.name'))

@section('content')
    <h1 class="h3 mb-2">Check-in scanner</h1>
    <p class="text-muted small mb-4">
        Scan the QR code from the guest’s <strong>RSVP approved</strong> email, or paste the check-in link / code below.
        Each successful scan records <strong>admitted</strong> once (repeat scans show as already admitted).
    </p>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <h2 class="h6 text-uppercase text-muted mb-3">Camera</h2>
                    <div id="qr-reader" class="rounded overflow-hidden bg-dark" style="min-height: 240px;"></div>
                    <p class="small text-muted mt-2 mb-0">Allow camera access when prompted. Use a device with a camera for best results.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <h2 class="h6 text-uppercase text-muted mb-3">Manual entry</h2>
                    <label for="manual-payload" class="form-label small">Paste full URL or code</label>
                    <textarea id="manual-payload" class="form-control font-monospace small mb-2" rows="3" placeholder="https://…/rsvp/admission/…"></textarea>
                    <button type="button" id="manual-submit" class="btn btn-primary rounded-pill px-4">Admit</button>
                </div>
            </div>
            <div id="scan-result" class="card border-0 shadow-sm mt-3 d-none">
                <div class="card-body p-3">
                    <h2 class="h6 text-uppercase text-muted mb-2">Result</h2>
                    <p id="scan-result-message" class="mb-0 fw-medium"></p>
                    <p id="scan-result-detail" class="small text-muted mb-0 mt-1"></p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js" crossorigin="anonymous"></script>
    <script>
        (function () {
            const admitUrl = @json(route('admin.scanner.admit'));
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const resultCard = document.getElementById('scan-result');
            const resultMsg = document.getElementById('scan-result-message');
            const resultDetail = document.getElementById('scan-result-detail');
            let busy = false;
            let html5Qr = null;

            function showResult(ok, message, detail) {
                resultCard.classList.remove('d-none');
                resultMsg.className = 'mb-0 fw-medium ' + (ok ? 'text-success' : 'text-danger');
                resultMsg.textContent = message;
                resultDetail.textContent = detail || '';
            }

            async function admitPayload(payload) {
                const text = (payload || '').trim();
                if (!text || busy) return;
                busy = true;
                try {
                    const res = await fetch(admitUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify({ payload: text }),
                    });
                    const data = await res.json().catch(() => ({}));
                    if (res.ok && data.ok) {
                        const extra = [data.guests_count ? data.guests_count + ' guest(s)' : null, data.table].filter(Boolean).join(' · ');
                        showResult(true, data.message, extra);
                    } else {
                        showResult(false, data.message || 'Something went wrong.', '');
                    }
                } catch (e) {
                    showResult(false, 'Network error. Try again.', '');
                } finally {
                    busy = false;
                }
            }

            document.getElementById('manual-submit')?.addEventListener('click', function () {
                admitPayload(document.getElementById('manual-payload').value);
            });

            const readerElId = 'qr-reader';
            if (typeof Html5Qrcode !== 'undefined') {
                html5Qr = new Html5Qrcode(readerElId);
                const config = { fps: 8, qrbox: { width: 220, height: 220 } };
                Html5Qrcode.getCameras().then(cameras => {
                    if (!cameras.length) return;
                    const id = cameras.find(c => /back|rear|environment/i.test(c.label))?.id || cameras[0].id;
                    html5Qr.start(
                        { deviceId: { exact: id } },
                        config,
                        (decodedText) => { admitPayload(decodedText); },
                        () => {}
                    ).catch(() => {
                        html5Qr.start(
                            { facingMode: 'environment' },
                            config,
                            (decodedText) => { admitPayload(decodedText); },
                            () => {}
                        ).catch(() => {});
                    });
                }).catch(() => {});
            }
        })();
    </script>
@endpush
