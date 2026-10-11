@extends('layouts.app')

@section('content')
<div class="container py-4 farmer-scan-page">
    <div class="mb-3">
        <a href="{{ route('farmer.dashboard') }}" class="btn btn-secondary">
            &larr; Go Back to Dashboard
        </a>
    </div>

    <div class="card mx-auto farmer-scan-card" style="max-width: 800px;">
        <div class="card-header farmer-scan-header">
            <span class="checkout-kicker"><i class="bi bi-qr-code-scan"></i> FARMER CHECKOUT</span>
            <h1 class="mb-0">Scan merchant QR</h1>
            <p class="mb-0">Review the order, then choose to pay or cancel.</p>
        </div>

        <div class="card-body">
            <p class="text-muted">
                Scan the QR code provided by the accredited merchant,
                or upload a photo of the QR code.
            </p>

            <div id="reader" class="mb-3"></div>

            <div class="mb-3 qr-upload-field">
                <label for="qr-file" class="form-label">
                    Or upload a QR code image
                </label>
                <input
                    type="file"
                    id="qr-file"
                    class="form-control"
                    accept="image/*"
                >
            </div>

            <div id="message" class="alert d-none" role="status"></div>

            @if ($errors->has('payment'))
                <div class="alert alert-danger" role="alert">
                    {{ $errors->first('payment') }}
                </div>
            @endif

            <div id="review" class="d-none scan-order-review">
                <hr>
                <h5>Review order</h5>

                <p><strong>Merchant:</strong> <span id="merchant"></span></p>
                <p><strong>Reference:</strong> <span id="reference"></span></p>
                <p><strong>Status:</strong> <span id="status"></span></p>
                <p><strong>QR Expiry:</strong> <span id="expiry"></span></p>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="items"></tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total</th>
                                <th id="total"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="checkout-review-note">
                    <i class="bi bi-info-circle"></i><span>
                    Review the details carefully. Payment is not yet processed,
                    and no subsidy or stock has been deducted.
                    </span>
                </div>

                <div class="checkout-action-grid">
                <form id="payment-form" method="POST">
                    @csrf
                    <button type="submit" class="btn checkout-pay-button">
                        <i class="bi bi-check-circle"></i> Pay now
                    </button>
                </form>

                <form id="cancel-form" method="POST">
                    @csrf
                    <button type="submit" class="btn checkout-cancel-button">
                        <i class="bi bi-x-circle"></i> Cancel order
                    </button>
                </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const reader = document.getElementById('reader');
    const fileInput = document.getElementById('qr-file');
    const message = document.getElementById('message');
    const review = document.getElementById('review');

    let scanner = null;
    let processing = false;

    function showMessage(text, type = 'info') {
        message.textContent = text;
        message.className = 'alert alert-' + type;
    }

    function money(value) {
        return '₱' + Number(value).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    async function stopScanner() {
        if (scanner && scanner.isScanning) {
            try {
                await scanner.stop();
            } catch (error) {
                console.warn('Could not stop scanner:', error);
            }
        }
    }

    async function verifyQr(qrContent) {
        if (processing) return;
        processing = true;

        showMessage('Verifying QR code...', 'info');
        review.classList.add('d-none');

        await stopScanner();

        try {
            const response = await fetch(
                @json(route('farmer.scan-qr')),
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content
                    },
                    body: JSON.stringify({ qr_content: qrContent })
                }
            );

            const data = await response.json();

            if (!response.ok || !data.valid) {
                throw new Error(data.message || 'QR verification failed.');
            }

            const order = data.transaction;

            const paymentForm = document.getElementById('payment-form');
            const paymentUrl = @json(route('farmer.checkout.payment', ['uuid' => '__TRANSACTION_UUID__']));
            paymentForm.action = paymentUrl.replace(
                '__TRANSACTION_UUID__',
                encodeURIComponent(order.uuid)
            );

            const cancelForm = document.getElementById('cancel-form');
            const cancelUrl = @json(route('farmer.checkout.cancel', ['uuid' => '__TRANSACTION_UUID__']));
            cancelForm.action = cancelUrl.replace(
                '__TRANSACTION_UUID__',
                encodeURIComponent(order.uuid)
            );

            document.getElementById('merchant').textContent = order.merchant;
            document.getElementById('reference').textContent = order.uuid;
            document.getElementById('status').textContent = order.status;
            document.getElementById('expiry').textContent = order.expires_at;
            document.getElementById('total').textContent = money(order.total_amount);

            const tbody = document.getElementById('items');
            tbody.replaceChildren();

            order.items.forEach(function (item) {
                const row = document.createElement('tr');

                [
                    item.product_name,
                    item.quantity,
                    money(item.unit_price),
                    money(item.subtotal)
                ].forEach(function (value) {
                    const cell = document.createElement('td');
                    cell.textContent = value;
                    row.appendChild(cell);
                });

                tbody.appendChild(row);
            });

            review.classList.remove('d-none');
            showMessage(data.message, 'success');
        } catch (error) {
            showMessage(error.message || 'Unable to verify QR code.', 'danger');
        } finally {
            processing = false;
        }
    }

    if (typeof Html5Qrcode === 'undefined') {
        showMessage(
            'QR scanner library could not load. Check your internet connection.',
            'danger'
        );
        return;
    }

    scanner = new Html5Qrcode('reader');

    Html5Qrcode.getCameras()
        .then(function (cameras) {
            if (!cameras.length) {
                showMessage(
                    'No camera detected. You can upload a QR image instead.',
                    'warning'
                );
                return;
            }

            return scanner.start(
                cameras[0].id,
                { fps: 10, qrbox: { width: 250, height: 250 } },
                function (decodedText) {
                    verifyQr(decodedText);
                },
                function () {
                    // Ignore frames without a QR code.
                }
            );
        })
        .catch(function () {
            showMessage(
                'Camera unavailable or permission denied. Try uploading a QR image.',
                'warning'
            );
        });

    fileInput.addEventListener('change', async function (event) {
        const file = event.target.files[0];
        if (!file || processing) return;

        if (scanner && scanner.isScanning) {
            await stopScanner();
        }

        try {
            const decodedText = await scanner.scanFile(file, true);
            await verifyQr(decodedText);
        } catch (error) {
            showMessage(
                'Could not read a QR code from that image. Please try another image.',
                'warning'
            );
        } finally {
            fileInput.value = '';
        }
    });
});
</script>
@endsection
