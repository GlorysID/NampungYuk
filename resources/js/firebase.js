import { getApps, initializeApp } from 'firebase/app';
import {
    getAuth,
    getRedirectResult,
    GoogleAuthProvider,
    signInWithPopup,
    signInWithRedirect,
} from 'firebase/auth';

let redirectResultPromise = null;

/**
 * Resolve Firebase Web SDK configuration from injected server config or Vite env.
 */
function resolveFirebaseConfig(serverConfig = {}) {
    const config = {
        apiKey: serverConfig.api_key || import.meta.env.VITE_FIREBASE_API_KEY || '',
        authDomain: serverConfig.auth_domain || import.meta.env.VITE_FIREBASE_AUTH_DOMAIN || '',
        projectId: serverConfig.project_id || import.meta.env.VITE_FIREBASE_PROJECT_ID || '',
        storageBucket: serverConfig.storage_bucket || import.meta.env.VITE_FIREBASE_STORAGE_BUCKET || '',
        messagingSenderId: serverConfig.messaging_sender_id || import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID || '',
        appId: serverConfig.app_id || import.meta.env.VITE_FIREBASE_APP_ID || '',
    };

    if (!config.apiKey || !config.authDomain || !config.projectId) {
        return null;
    }

    return config;
}

function getFirebaseAuthInstance(serverConfig = {}) {
    const config = resolveFirebaseConfig(serverConfig);
    if (!config) {
        throw new Error('Konfigurasi Firebase Web SDK belum lengkap.');
    }

    const app = getApps().length > 0 ? getApps()[0] : initializeApp(config);
    const auth = getAuth(app);
    auth.languageCode = 'id';

    return auth;
}

function createGoogleProvider() {
    const provider = new GoogleAuthProvider();
    provider.setCustomParameters({ prompt: 'select_account' });
    return provider;
}

function mapFirebaseErrorMessage(err) {
    const code = err?.code || '';

    switch (code) {
        case 'auth/popup-closed-by-user':
        case 'auth/cancelled-popup-request':
            return 'Login Google dibatalkan.';
        case 'auth/unauthorized-domain':
            return `Domain "${window.location.hostname}" belum diizinkan di Firebase Console (Authentication -> Settings -> Authorized domains). Coba akses lewat http://localhost:8000 atau tambahkan domain ini.`;
        case 'auth/operation-not-allowed':
            return 'Provider Google belum diaktifkan di Firebase Console (Authentication -> Sign-in method).';
        case 'auth/network-request-failed':
            return 'Koneksi ke Firebase gagal. Periksa koneksi internet kamu lalu coba lagi.';
        case 'auth/account-exists-with-different-credential':
            return 'Email ini sudah terdaftar dengan metode login lain.';
        default:
            return err?.message || 'Gagal menghubungkan ke akun Google lewat Firebase.';
    }
}

async function exchangeIdTokenForSession(endpoint, idToken) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    const response = await fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        credentials: 'same-origin',
        body: JSON.stringify({ id_token: idToken }),
    });

    if (response.status === 419) {
        throw new Error('Sesi halaman kedaluwarsa. Muat ulang halaman lalu coba lagi.');
    }

    const payload = await response.json().catch(() => ({}));

    if (!response.ok) {
        throw new Error(payload.message || 'Gagal memverifikasi login Google di server.');
    }

    if (payload.redirect) {
        window.location.assign(payload.redirect);
    }

    return payload;
}

/**
 * Complete any pending redirect-based Google sign-in once per page load.
 */
export async function completeFirebaseRedirectIfNeeded({ endpoint, config } = {}) {
    if (!endpoint || !resolveFirebaseConfig(config)) {
        return null;
    }

    if (!redirectResultPromise) {
        redirectResultPromise = (async () => {
            const auth = getFirebaseAuthInstance(config);
            const result = await getRedirectResult(auth);
            if (!result?.user) {
                return null;
            }

            const idToken = await result.user.getIdToken();
            return exchangeIdTokenForSession(endpoint, idToken);
        })().catch((err) => {
            redirectResultPromise = null;
            throw new Error(mapFirebaseErrorMessage(err));
        });
    }

    return redirectResultPromise;
}

/**
 * Trigger Google Sign-In via Firebase popup (falling back to redirect if popups are blocked).
 */
export async function signInWithFirebaseGoogle({ endpoint, config } = {}) {
    const auth = getFirebaseAuthInstance(config);
    const provider = createGoogleProvider();

    try {
        const result = await signInWithPopup(auth, provider);
        const idToken = await result.user.getIdToken();

        return await exchangeIdTokenForSession(endpoint, idToken);
    } catch (err) {
        if (err?.code === 'auth/popup-blocked') {
            await signInWithRedirect(auth, provider);
            return null;
        }

        if (err?.code && err.code.startsWith('auth/')) {
            throw new Error(mapFirebaseErrorMessage(err));
        }

        throw err;
    }
}
