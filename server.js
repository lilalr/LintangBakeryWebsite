import express from 'express';
import midtransClient from 'midtrans-client';
import cors from 'cors';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
const PORT = 3000;

app.use(express.json());
app.use(cors({ origin: '*' }));

// Serve file statis langsung dari folder utama
app.use(express.static(__dirname));

// Access Keys Dashboard Midtrans Sandbox
const SERVER_KEY = 'Mid-server-doEf5XIK4fHiyq1FxHd8cKXe';
const CLIENT_KEY = 'Mid-client-LVKdYZ6KsBOhBLah';

let coreApi = new midtransClient.CoreApi({
    isProduction: false,
    serverKey: SERVER_KEY,
    clientKey: CLIENT_KEY
});

// Endpoint POST Generate QRIS (Mendukung panggilan dari Kasir maupun Checkout Web)
app.post(['/api/generate-qris', '/api/generate_qris'], async (req, res) => {
    try {
        // Menerima 'gross_amount' dari checkout.blade.php atau 'totalHarga' dari Kasir
        const amount = req.body.gross_amount || req.body.totalHarga || req.body.amount;

        if (!amount || isNaN(amount) || amount <= 0) {
            return res.status(400).json({ 
                status: 'error', 
                message: 'Nominal harga tidak valid' 
            });
        }

        const orderId = req.body.order_id || ('LB-' + Date.now());
        console.log(`[REQUEST] Order ID: ${orderId} | Total: Rp ${amount}`);

        let parameter = {
            "payment_type": "qris",
            "qris": {
                "acquirer": "gopay"
            },
            "transaction_details": {
                "order_id": orderId,
                "gross_amount": Math.round(Number(amount))
            }
        };

        const chargeResponse = await coreApi.charge(parameter);
        console.log("[MIDTRANS SUCCESS]:", chargeResponse.order_id);

        const qrAction = chargeResponse.actions && chargeResponse.actions.find(action => action.name === 'generate-qr-code');

        if (qrAction) {
            res.json({
                status: 'success',
                order_id: orderId,
                qr_url: qrAction.url
            });
        } else {
            res.status(400).json({ 
                status: 'error', 
                message: 'QR Code action tidak ditemukan dari respon Midtrans' 
            });
        }

    } catch (error) {
        console.error("=== DETAIL ERROR MIDTRANS ===");
        const msg = error.ApiResponse ? error.ApiResponse.status_message : error.message;
        console.error(msg);

        res.status(500).json({ status: 'error', message: msg });
    }
});

// Endpoint GET Cek Status Transaksi Midtrans
app.get('/api/check-status/:orderId', async (req, res) => {
    try {
        const statusResponse = await coreApi.transaction.status(req.params.orderId);
        res.json({ 
            status: 'success',
            order_id: req.params.orderId,
            transaction_status: statusResponse.transaction_status 
        });
    } catch (error) {
        console.error("Error check status:", error.message);
        res.status(500).json({ status: 'error', message: error.message });
    }
});

app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'index.html'));
});

app.listen(PORT, () => {
    console.log(`==================================================`);
    console.log(` Server Kasir & Integration Berhasil Berjalan!`);
    console.log(` Akses aplikasi di browser: http://localhost:${PORT}`);
    console.log(`==================================================`);
});