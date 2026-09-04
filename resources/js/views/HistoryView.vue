<template>
  <div class="page">
    <div class="row row--between row--wrap" style="margin-bottom:26px;gap:18px">
      <div>
        <span class="eyebrow">Keep going</span>
        <h1 style="font-size:clamp(1.6rem,3vw,2.2rem);margin-top:8px">Your Learning Progress</h1>
        <p class="lead">{{ encouragement }}</p>
      </div>
      <button class="btn btn--primary" type="button" @click="setRoute('learn')">Continue course</button>
    </div>

    <div v-if="loading" class="stack">
      <div class="skeleton" style="height:86px" v-for="n in 3" :key="n"></div>
    </div>

    <div v-else-if="hasError" class="alert alert--error">
      <span>{{ errorMessage }}</span>
      <button class="btn btn--quiet btn--sm" type="button" @click="reload">Retry</button>
    </div>

    <template v-else-if="!attempts.length">
      <div class="card empty">
        <LearnerArt name="empty" width="260" />
        <h2>No attempts yet</h2>
        <p class="lead">Complete your first lesson and it will appear here as part of your journey.</p>
        <button class="btn btn--primary" type="button" @click="setRoute('practice')">Start practising</button>
      </div>
    </template>

    <template v-else>
      <div class="card card--pad-lg" style="margin-bottom:26px">
        <div class="row row--between" style="margin-bottom:10px">
          <strong style="font-size:.92rem">{{ progress.percent }}% of the course completed</strong>
          <span class="muted">{{ progress.completed }} / {{ progress.total }} lessons</span>
        </div>
        <div class="progress"><div class="progress__fill" :style="{ width: barWidth }"></div></div>

        <div class="milestones" style="margin-top:24px">
          <div class="milestone">
            <div class="milestone__n">{{ attempts.length }}</div>
            <div class="milestone__l">answers submitted</div>
          </div>
          <div class="milestone">
            <div class="milestone__n">{{ bandOrDash(latestBand) }}</div>
            <div class="milestone__l">latest band</div>
          </div>
          <div class="milestone">
            <div class="milestone__n">{{ bandOrDash(bestBand) }}</div>
            <div class="milestone__l">best band</div>
          </div>
          <div class="milestone">
            <div class="milestone__n">{{ bandOrDash(avgBand) }}</div>
            <div class="milestone__l">average band</div>
          </div>
        </div>

        <div class="row row--wrap" style="margin-top:22px;gap:10px">
          <span class="badge" :class="badgeClass(1)">🎤 First answer</span>
          <span class="badge" :class="badgeClass(3)">📚 Three lessons</span>
          <span class="badge" :class="badgeClass(5)">🔥 Five lessons</span>
          <span class="badge" :class="allPartsTouched ? 'is-earned' : 'is-locked'">🏆 All three parts</span>
        </div>
      </div>

      <div class="tl">
        <template v-for="group in grouped" :key="group.day">
          <div class="tl__day">{{ group.day }}</div>
          <div class="tl__node" v-for="att in group.items" :key="att.id" style="margin-bottom:12px">
            <button class="tl__card" type="button" @click="openDetail(att)">
              <span class="tl__score">{{ bandOrDash(att.feedback && att.feedback.band_score) }}</span>
              <span>
                <span class="lesson__title">{{ partLabel(att) }} · {{ topicOf(att) }}</span>
                <span class="lesson__desc">{{ shorten(att.answer_text) }}</span>
                <span class="lesson__tags">
                  <span class="chip chip--done">Completed</span>
                  <span class="chip">{{ time(att.created_at) }}</span>
                  <span class="chip" v-if="isOfflineAttempt(att)">offline estimate</span>
                </span>
              </span>
              <span class="lesson__go">Details →</span>
            </button>
          </div>
        </template>
      </div>

      <div v-if="detail" class="card" style="margin-top:24px;border-color:var(--lavender)">
        <div class="row row--between" style="margin-bottom:14px">
          <h3>{{ partLabel(detail) }} · {{ topicOf(detail) }}</h3>
          <button class="btn btn--quiet btn--sm" type="button" @click="detail = null">Close</button>
        </div>
        <p class="lead" style="white-space:pre-wrap;margin-bottom:20px">“{{ detail.answer_text }}”</p>
        <BandReport :feedback="detail.feedback" :offline="isOfflineAttempt(detail)" />
      </div>
    </template>
  </div>
</template>

<script>
import { appState, setRoute, loadAttempts, loadQuestions } from '../store';
import { PARTS } from '../constants';
import { bandText, truncate } from '../format';
import { courseProgress } from '../course';
import BandReport from '../components/BandReport.vue';
import LearnerArt from '../components/LearnerArt.vue';

export default {
  name: 'HistoryView',
  components: { BandReport, LearnerArt },
  data() { return { appState, detail: null, barWidth: '0%' }; },
  computed: {
    attempts() { return Array.isArray(appState.attempts) ? appState.attempts : []; },
    loading() { return appState.attemptsLoading && !this.attempts.length; },
    hasError() { return !!appState.attemptsError; },
    errorMessage() { return appState.attemptsError; },
    progress() { return courseProgress(appState.questions, appState.attempts); },
    bands() {
      return this.attempts
        .map((a) => (a.feedback ? Number(a.feedback.band_score) : NaN))
        .filter((n) => !isNaN(n));
    },
    latestBand() { return this.bands.length ? this.bands[0] : null; },
    bestBand() { return this.bands.length ? Math.max(...this.bands) : null; },
    avgBand() {
      if (!this.bands.length) return null;
      return this.bands.reduce((a, b) => a + b, 0) / this.bands.length;
    },
    allPartsTouched() {
      const parts = new Set(this.attempts.map((a) => a.question && Number(a.question.part)));
      return [1, 2, 3].every((p) => parts.has(p));
    },
    encouragement() {
      if (!this.attempts.length) return 'Your practice history will build up here as you complete lessons.';
      if (this.progress.percent >= 80) return "You're nearly through the whole course — outstanding consistency!";
      if (this.progress.percent >= 40) return "You're making great progress. Keep practising to reach your target band.";
      return 'A strong start. Every answer you submit sharpens your speaking.';
    },
    grouped() {
      const groups = [];
      const byDay = new Map();
      this.attempts.forEach((att) => {
        const label = this.dayLabel(att.created_at);
        if (!byDay.has(label)) { byDay.set(label, []); groups.push({ day: label, items: byDay.get(label) }); }
        byDay.get(label).push(att);
      });
      return groups;
    },
  },
  async mounted() {
    await loadAttempts();
    if (!appState.questions.length && !appState.questionsLoading) await loadQuestions();
    requestAnimationFrame(() => { this.barWidth = this.progress.percent + '%'; });
  },
  methods: {
    setRoute,
    shorten(text) { return truncate(text, 96); },
    bandOrDash(v) { return v == null ? '—' : bandText(v); },
    topicOf(att) { return att.question ? att.question.topic : 'Speaking practice'; },
    partLabel(att) {
      const p = att.question && PARTS[att.question.part];
      return p ? p.name : 'Practice';
    },
    isOfflineAttempt(att) { return !!att.feedback && !att.feedback.raw_response; },
    badgeClass(n) { return this.attempts.length >= n ? 'is-earned' : 'is-locked'; },
    dayLabel(value) {
      if (!value) return 'Earlier';
      const d = new Date(value);
      const today = new Date();
      const yest = new Date(); yest.setDate(today.getDate() - 1);
      const same = (a, b) => a.toDateString() === b.toDateString();
      if (same(d, today)) return 'Today';
      if (same(d, yest)) return 'Yesterday';
      return d.toLocaleDateString(undefined, { dateStyle: 'medium' });
    },
    time(value) {
      if (!value) return '';
      try { return new Date(value).toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' }); }
      catch { return ''; }
    },
    openDetail(att) { this.detail = this.detail && this.detail.id === att.id ? null : att; },
    async reload() { await loadAttempts(); },
  },
};
</script>
