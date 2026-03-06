<div class="header">
    <div class="kop-container">
        <div class="company-section">
            <img src="{{ asset('images/logokopsurat.png') }}" alt="Logo" class="logo">
            <div class="company-details">
                <h1 class="company-name">MR LUX INDONESIA</h1>
                <p class="company-address">Jln. Wr Supratman NO.31 Gisikdrono, Semarang Barat</p>
                <p class="company-contact">Telp. (024) 7624836 | WhatsApp: +62 818 4520 14</p>
                <p class="company-web">www.mrluxindonesia.com</p>
            </div>
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
        <div class="customer-box">
            <p class="box-label">Kepada Yth:</p>
            <h3 class="customer-name">{{ $customerName }}</h3>
            <p class="customer-address">{{ $customerAddress ?? 'Semarang, Jawa Tengah' }}</p>
        </div>
    </div>
</div>

<style>
    :root {
        --primary-color: #d32f2f;
        --text-color: #333;
        --border-color: #ddd;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: var(--text-color);
        margin: 0;
        padding: 0;
        line-height: 1.4;
    }

    .header {
        margin-bottom: 20px;
    }

    .kop-container {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        border-bottom: 3px solid var(--primary-color);
        padding-bottom: 15px;
        margin-bottom: 20px;
    }

    .company-section {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .logo {
        height: 80px;
        width: auto;
    }

    .company-name {
        margin: 0;
        font-size: 24px;
        font-weight: 800;
        color: var(--primary-color);
        letter-spacing: 1px;
    }

    .company-details p {
        margin: 2px 0;
        font-size: 11px;
        color: #666;
    }

    .document-section {
        text-align: right;
    }

    .document-title {
        margin: 0 0 10px 0;
        font-size: 20px;
        font-weight: 700;
        text-transform: uppercase;
        color: #222;
        border-bottom: 1px solid var(--border-color);
        display: inline-block;
        padding-bottom: 5px;
    }

    .document-meta table {
        border-collapse: collapse;
        display: inline-block;
    }

    .document-meta td {
        border: none !important;
        padding: 1px 5px !important;
        font-size: 11px;
        text-align: left;
    }

    .customer-section {
        margin-bottom: 20px;
        display: flex;
        justify-content: flex-start;
    }

    .customer-box {
        border: 1px solid var(--border-color);
        padding: 10px 15px;
        min-width: 250px;
        border-radius: 4px;
        background-color: #fafafa;
    }

    .box-label {
        margin: 0 0 5px 0;
        font-size: 10px;
        text-transform: uppercase;
        color: #888;
        font-weight: 600;
    }

    .customer-name {
        margin: 0;
        font-size: 14px;
        font-weight: 700;
        color: #222;
    }

    .customer-address {
        margin: 3px 0 0 0;
        font-size: 11px;
        color: #555;
    }
</style>
