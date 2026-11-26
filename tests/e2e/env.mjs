// Everything the E2E app server and artisan calls run with. These override .env, so the suite
// uses its own database and a local Midtrans stand-in and never reaches the internet to pay.
export const APP_PORT = 8011;
export const STUB_PORT = 8099;
export const BASE_URL = `http://127.0.0.1:${APP_PORT}`;
export const STUB_URL = `http://127.0.0.1:${STUB_PORT}`;
export const SERVER_KEY = 'e2e-server-key';
export const PASSWORD = 'e2e-password-123';
export const ADMIN_EMAIL = 'admin@e2e.test';
export const ADMIN_STATE = 'storage/e2e/admin-state.json';

export const E2E_ENV = {
    APP_ENV: 'e2e',
    APP_URL: BASE_URL,
    DB_DATABASE: 'e2e',
    CACHE_STORE: 'database',
    SESSION_DRIVER: 'database',
    SESSION_COOKIE: 'episteme_e2e',
    QUEUE_CONNECTION: 'sync',
    MAIL_MAILER: 'array',
    PAYMENT_PROVIDER: 'midtrans',
    MIDTRANS_SERVER_KEY: SERVER_KEY,
    MIDTRANS_CLIENT_KEY: 'e2e-client-key',
    MIDTRANS_IS_PRODUCTION: 'false',
    MIDTRANS_SNAP_URL: `${STUB_URL}/snap/v1`,
};
