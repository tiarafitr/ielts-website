import { reactive, nextTick } from 'vue';
import client, {
  apiErrorMessage,
  fetchAttempts,
  fetchQuestions,
  loginUser,
  registerUser,
  logoutUser,
  readStoredSession,
  storeSession,
} from './api';

/** Simple shared, reactive app state (no external dependency needed). */
export const appState = reactive({
  route: 'home', // 'home' | 'learn' | 'practice' | 'result' | 'history' | 'profile'
  questions: [],
  attempts: [],
  questionsLoading: false,
  attemptsLoading: false,
  questionsError: '',
  attemptsError: '',

  // --- authentication ---
  user: null,
  token: null,
  authBootstrapped: false,

  latestResult: null,
  evaluating: false,

  sessionExpired: false,

  pendingPart: null,
  pendingQuestionId: null,
});

client.interceptors.response.use(
  (response) => response,
  (error) => {
    const url = String(error.config?.url || '');
    const isSignInAttempt = url.includes('/login') || url.includes('/register');
    if (error.response?.status === 401 && !isSignInAttempt) {
      clearSession();
      appState.sessionExpired = true;
    }
    return Promise.reject(error);
  }
);

export function isAuthed() {
  return !!appState.token;
}

export function bootAuth() {
  if (appState.authBootstrapped) return;
  appState.authBootstrapped = true;
  const session = readStoredSession();
  if (session) {
    appState.token = session.token || null;
    appState.user = session.user || null;
  }
}

export function setSession(session) {
  storeSession(session);
  appState.token = session.token || null;
  appState.user = session.user || null;
}

function clearSession() {
  storeSession(null);
  appState.token = null;
  appState.user = null;
  appState.attempts = [];
  appState.latestResult = null;
  setRoute('home');
}

export async function login(credentials) {
  const data = await loginUser(credentials);
  setSession(data);
  setRoute('home');
  return data;
}

export async function register(details) {
  const data = await registerUser(details);
  setSession(data);
  setRoute('home');
  return data;
}

export async function logout() {
  try {
    await logoutUser();
  } finally {
    clearSession();
  }
}

export function setRoute(name) {
  appState.route = name;
  nextTick(() => window.scrollTo({ top: 0, behavior: 'auto' }));
}

export async function loadQuestions() {
  if (!isAuthed()) return;
  appState.questionsLoading = true;
  appState.questionsError = '';
  try {
    appState.questions = await fetchQuestions();
  } catch (e) {
    appState.questionsError = apiErrorMessage(e);
  } finally {
    appState.questionsLoading = false;
  }
}

export async function loadAttempts() {
  if (!isAuthed()) return;
  appState.attemptsLoading = true;
  appState.attemptsError = '';
  try {
    appState.attempts = await fetchAttempts();
  } catch (e) {
    appState.attemptsError = apiErrorMessage(e);
  } finally {
    appState.attemptsLoading = false;
  }
}
