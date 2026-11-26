// A minimal stand-in for Midtrans Snap: accepts the charge request, "hosts" a payment page, and
// remembers what it was sent so a test can post the matching webhook afterwards.
import { createServer } from 'node:http';
import { SERVER_KEY, STUB_PORT, STUB_URL } from '../env.mjs';

const transactions = [];

const send = (res, status, body, type = 'application/json') => {
    res.writeHead(status, { 'Content-Type': type });
    res.end(typeof body === 'string' ? body : JSON.stringify(body));
};

createServer((req, res) => {
    const url = new URL(req.url, STUB_URL);

    if (req.method === 'GET' && url.pathname === '/health') {
        return send(res, 200, { ok: true });
    }

    if (req.method === 'GET' && url.pathname === '/transactions') {
        return send(res, 200, transactions);
    }

    if (req.method === 'POST' && url.pathname === '/snap/v1/transactions') {
        const expected = 'Basic ' + Buffer.from(`${SERVER_KEY}:`).toString('base64');

        if (req.headers.authorization !== expected) {
            return send(res, 401, { error_messages: ['Unauthorized'] });
        }

        let raw = '';
        req.on('data', (chunk) => (raw += chunk));
        req.on('end', () => {
            const payload = JSON.parse(raw);
            const orderId = payload.transaction_details.order_id;
            transactions.push(payload);
            send(res, 201, { token: `stub-${orderId}`, redirect_url: `${STUB_URL}/pay/${encodeURIComponent(orderId)}` });
        });

        return;
    }

    if (req.method === 'GET' && url.pathname.startsWith('/pay/')) {
        const orderId = decodeURIComponent(url.pathname.slice('/pay/'.length)).replace(/[^A-Za-z0-9-]/g, '');

        return send(res, 200, `<!doctype html><title>Fake Midtrans</title><h1>Fake payment page</h1><p id="order">${orderId}</p>`, 'text/html');
    }

    return send(res, 404, { error: 'not found' });
}).listen(STUB_PORT, '127.0.0.1');
