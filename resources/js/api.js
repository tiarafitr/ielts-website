import axios from 'axios';

const AUTH_STORAGE_KEY = 'ielts_speaking_auth';

export function readStoredSession() {
  try {
    return JSON.parse(localStorage.getItem(AUTH_STORAGE_KEY) || 'null');
  } catch {
    return null;
  }
}

export function storeSession(session) {
  if (session) localStorage.setItem(AUTH_STORAGE_KEY, JSON.stringify(session));
  else localStorage.removeItem(AUTH_STORAGE_KEY);
}

/**
 * Centralised API client.
 *
 * baseURL comes from the `VITE_API_URL` build-time variable when set
 * (e.g. .../api when frontend runs on a separate Vite dev origin). When unset
 * it defaults to `/api` — correct when Laravel serves the built app.
 */
const client = axios.create({
  baseURL: (import.meta.env.VITE_API_URL || '/api').replace(/\/$/, ''),
  headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  timeout: 30000, // includes the Gemini evaluation round-trip
});

// Attach the saved Sanctum bearer token to every request.
client.interceptors.request.use((config) => {
  const session = readStoredSession();
  if (session && session.token) {
    config.headers.Authorization = `Bearer ${session.token}`;
  }
  return config;
});

/** Map thrown axios/payload errors to short, human-friendly messages. */
export function apiErrorMessage(error, fallback) {
  if (!error) return fallback || 'Something went wrong. Please try again.';
  if (error.code === 'ECONNABORTED') {
    return 'The request timed out. The server may be busy - please try again.';
  }
  if (!error.response || error.code === 'ERR_NETWORK') {
    return 'Unable to reach the server. Make sure the Laravel backend is running (php artisan serve).';
  }
  const { status, data } = error.response;
  if (status === 422 && data && data.errors) {
    const first = Object.values(data.errors)[0];
    return Array.isArray(first) ? first[0] : String(first || '');
  }
  if (status === 401 && data && data.message) return data.message;
  if (data && data.message) return data.message;
  if (status === 429) return 'Too many requests. Please wait a moment and try again.';
  return fallback || 'Something went wrong on the server. Please try again.';
}

// ------------------------------- Auth -------------------------------------
export async function registerUser(payload) {
  const { data } = await client.post('/register', payload);
  return data.data; // { token, user }
}

export async function loginUser(payload) {
  const { data } = await client.post('/login', payload);
  return data.data; // { token, user }
}

export async function logoutUser() {
  await client.post('/logout');
}

// ------------------------------ Speaking ----------------------------------
export async function fetchAttempts() {
  const { data } = await client.get('/speaking/attempts');
  return data.data;
}

export async function fetchQuestions(part = null) {
  const { data } = await client.get('/speaking/questions', {
    params: part ? { part } : {},
  });
  return data.data;
}

export async function submitAnswer(payload) {
  const { data } = await client.post('/speaking/submit', payload);
  return data.data;
}

export default client;
