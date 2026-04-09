<div class="header">
    <div class="kop-container">
        <div class="company-logo-section">
            <img src="{{ asset('images/logokopsurat.png') }}" alt="Logo" class="logo">
        </div>
        <div class="company-details-section">
            <h1 class="company-name">MR LUX INDONESIA</h1>
            <p class="company-address">Jln. Wr Supratman NO.31 Gisikdrono, Semarang Barat</p>
            <p class="company-contact">Telp. (024) 7624836 | WhatsApp: +62 818 4520 14</p>
            <p class="company-web">www.mrluxindonesia.com</p>
        </div>
        <div class="document-section">
            <h2 class="document-title">{{ $title ?? 'DOKUMEN' }}</h2>
            <div class="document-meta">
                <table>
                    <tr>
                        <td>Nomor</td>
                        <td>: {{ $number }}</td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td>: {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</td>
                    </tr>
                    @if(isset($ref))
                    <tr>
                        <td>Referensi</td>
                        <td>: {{ $ref }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <div class="customer-section">
        <div class="customer-info">
            <p class="kepada-label">KEPADA YTH:</p>
            <h3 class="customer-name-display">{{ $customerName }}</h3>
            <p class="customer-address-display">{{ $customerAddress ?? 'Semarang' }}</p>
        </div>
    </div>
</div>

<style>
    .header {
        margin-bottom: 25px;
    }

    .kop-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid #d32f2f;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }

    .company-logo-section {
        flex: 1;
    }

    .logo {
        height: 70px;
        width: auto;
    }

    .company-details-section {
        flex: 2;
        text-align: center;
    }

    .company-name {
        margin: 0;
        font-size: 20px;
        font-weight: 800;
        color: #d32f2f;
        letter-spacing: 0.5px;
    }

    .company-address, .company-contact, .company-web {
        margin: 1px 0;
        font-size: 10.5px;
        color: #000;
        font-weight: 500;
    }

    .document-section {
        flex: 1.5;
        text-align: right;
    }

    .document-title {
        margin: 0 0 5px 0;
        font-size: 18px;
        font-weight: 800;
        color: #000;
        text-transform: uppercase;
    }

    .document-meta table {
        border-collapse: collapse;
        float: right;
    }

    .document-meta td {
        border: none !important;
        padding: 0 4px !important;
        font-size: 10.5px;
        text-align: left;
        color: #000;
        font-weight: 500;
    }

    .customer-section {
        margin-bottom: 15px;
    }

    .kepada-label {
        margin: 0;
        font-size: 10px;
        font-weight: 700;
        color: #000;
    }

    .customer-name-display {
        margin: 2px 0 0 0;
        font-size: 13px;
        font-weight: 800;
        color: #000;
    }

    .customer-address-display {
        margin: 1px 0 0 0;
        font-size: 11px;
        color: #333;
        max-width: 400px;
        line-height: 1.3;
    }
</style>
