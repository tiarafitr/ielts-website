<template>
  <div>
    <AmbientBackdrop />

    <div class="shell">
      <header class="topbar">
        <div class="wrap topbar__inner">
          <button class="brand" type="button" @click="go('home')">
            <span class="brand__mark">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <rect x="9" y="3" width="6" height="11" rx="3" fill="#fff" />
                <path d="M6 11a6 6 0 0 0 12 0M12 17v4M9 21h6" stroke="#fff" stroke-width="2" stroke-linecap="round" />
              </svg>
            </span>
            <span>
              <span class="brand__name">IELTS Speaking</span>
              <span class="brand__sub">Course</span>
            </span>
          </button>

          <nav class="nav" v-if="isAuthed">
            <button
              v-for="item in navItems"
              :key="item.key"
              class="nav__link"
              :class="{ 'is-active': appState.route === item.key }"
              @click="go(item.key)"
            >{{ item.label }}</button>
          </nav>

          <div class="row" style="margin-left:auto" v-if="isAuthed">
            <button class="avatar" type="button" :title="userName" @click="go('profile')">{{ initials }}</button>
          </div>

          <button class="navtoggle" type="button" v-if="isAuthed" @click="menuOpen = !menuOpen" aria-label="Menu">
            <svg width="20" height="14" viewBox="0 0 20 14" fill="none">
              <path d="M1 1h18M1 7h18M1 13h18" stroke="#56507a" stroke-width="2" stroke-linecap="round" />
            </svg>
          </button>
        </div>

        <div class="wrap drawer" :class="{ 'is-closed': !menuOpen }" v-if="isAuthed">
          <button
            v-for="item in navItems"
            :key="item.key"
            class="nav__link"
            :class="{ 'is-active': appState.route === item.key }"
            @click="go(item.key)"
          >{{ item.label }}</button>
          <button class="nav__link" @click="go('profile')">Profile</button>
        </div>
      </header>

      <main class="wrap" style="flex:1">
        <AuthView v-if="!isAuthed" />
        <template v-else>
          <HomeView v-if="appState.route === 'home'" />
          <LearnView v-else-if="appState.route === 'learn'" />
          <PracticeView v-else-if="appState.route === 'practice'" />
          <ResultView v-else-if="appState.route === 'result'" />
          <HistoryView v-else-if="appState.route === 'history'" />
          <ProfileView v-else-if="appState.route === 'profile'" />
          <HomeView v-else />
        </template>
      </main>

      <footer class="foot wrap" v-if="isAuthed">
        <div class="row row--between row--wrap">
          <span>IELTS Speaking Mini — a practice course with AI band feedback.</span>
          <button class="btn btn--quiet btn--sm" type="button" @click="signOut">Sign out</button>
        </div>
      </footer>
    </div>

    <ToastRegion :items="toasts" @dismiss="dismissToast" />
  </div>
</template>

<script>
import HomeView from './views/HomeView.vue';
import LearnView from './views/LearnView.vue';
import PracticeView from './views/PracticeView.vue';
import ResultView from './views/ResultView.vue';
import HistoryView from './views/HistoryView.vue';
import ProfileView from './views/ProfileView.vue';
import AuthView from './views/AuthView.vue';
import ToastRegion from './components/ToastRegion.vue';
import AmbientBackdrop from './components/AmbientBackdrop.vue';
import { appState, setRoute, isAuthed, bootAuth, logout as doLogout } from './store';

export default {
  name: 'AppShell',
  components: {
    HomeView, LearnView, PracticeView, ResultView, HistoryView, ProfileView,
    AuthView, ToastRegion, AmbientBackdrop,
  },
  data() {
    return {
      appState,
      toasts: [],
      menuOpen: false,
      _toastId: 0,
    };
  },
  computed: {
    navItems() {
      return [
        { key: 'home', label: 'Home' },
        { key: 'learn', label: 'My Learning' },
        { key: 'practice', label: 'Practice' },
        { key: 'history', label: 'Progress' },
      ];
    },
    isAuthed() { return isAuthed(); },
    userName() { return (appState.user && appState.user.name) || ''; },
    initials() {
      const name = this.userName || '?';
      const parts = name.trim().split(/\s+/);
      return (parts[0][0] + (parts[1] ? parts[1][0] : '')).toUpperCase();
    },
  },
  watch: {
    'appState.sessionExpired'(expired) {
      if (!expired) return;
      appState.sessionExpired = false;
      this.notify('Your session has expired. Please sign in again.');
    },
  },
  mounted() {
    bootAuth();
  },
  methods: {
    go(route) {
      if (route === 'result' && !appState.latestResult) route = 'practice';
      this.menuOpen = false;
      setRoute(route);
    },
    async signOut() {
      await doLogout();
      this.notify('You signed out. See you soon!');
    },
    notify(message) {
      const id = ++this._toastId;
      this.toasts.unshift({ id, message });
      setTimeout(() => this.dismissToast(id), 4600);
    },
    dismissToast(id) {
      this.toasts = this.toasts.filter((t) => t.id !== id);
    },
  },
};
</script>
