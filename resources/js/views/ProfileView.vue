<template>
  <div class="page wrap--narrow" style="width:100%;padding-inline:0">
    <div class="card card--pad-lg">
      <div class="row row--wrap" style="gap:20px">
        <span class="avatar" style="width:74px;height:74px;font-size:1.4rem">{{ initials }}</span>
        <div style="flex:1;min-width:200px">
          <h1 style="font-size:1.7rem">{{ user.name }}</h1>
          <p class="muted">{{ user.email }}</p>
          <span class="chip chip--now" style="margin-top:10px">IELTS Speaking learner</span>
        </div>
        <ProgressRing :percent="progress.percent" :size="104" :stroke="9">
          <div class="ring__num" style="font-size:1.35rem">{{ progress.percent }}%</div>
          <div class="ring__cap" style="font-size:.62rem">course</div>
        </ProgressRing>
      </div>
    </div>

    <section class="sec">
      <div class="sec__head">
        <h2>Your practice so far</h2>
        <p>Everything here comes from the lessons you have actually completed.</p>
      </div>
      <div class="milestones">
        <div class="milestone">
          <div class="milestone__n">{{ progress.completed }}</div>
          <div class="milestone__l">lessons completed</div>
        </div>
        <div class="milestone">
          <div class="milestone__n">{{ attempts.length }}</div>
          <div class="milestone__l">answers submitted</div>
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
    </section>

    <section class="sec">
      <div class="sec__head"><h2>Section progress</h2></div>
      <div class="stack">
        <div class="card" v-for="p in parts" :key="p.num" style="padding:20px">
          <div class="row row--between" style="margin-bottom:10px">
            <div>
              <strong style="font-family:var(--font-head)">{{ p.name }} — {{ p.title }}</strong>
              <div class="muted">{{ doneFor(p.num) }} of {{ countFor(p.num) }} lessons</div>
            </div>
            <span class="chip" :class="doneFor(p.num) === countFor(p.num) && countFor(p.num) ? 'chip--done' : ''">
              {{ pctFor(p.num) }}%
            </span>
          </div>
          <div class="progress progress--mint">
            <div class="progress__fill" :style="{ width: pctFor(p.num) + '%' }"></div>
          </div>
        </div>
      </div>
    </section>

    <section class="sec">
      <div class="card row row--between row--wrap" style="gap:16px">
        <div class="row" style="gap:14px">
          <LearnerArt name="trophy" width="66" />
          <div>
            <strong style="font-family:var(--font-head)">Keep your streak of effort going</strong>
            <p class="muted">Finish the remaining lessons to complete the course.</p>
          </div>
        </div>
        <button class="btn btn--primary" type="button" @click="setRoute('learn')">Continue course</button>
      </div>
    </section>
  </div>
</template>

<script>
import { appState, setRoute, loadAttempts, loadQuestions } from '../store';
import { PARTS, PART_ORDER } from '../constants';
import { bandText } from '../format';
import { courseProgress, practisedIds } from '../course';
import ProgressRing from '../components/ProgressRing.vue';
import LearnerArt from '../components/LearnerArt.vue';

export default {
  name: 'ProfileView',
  components: { ProgressRing, LearnerArt },
  data() { return { appState }; },
  computed: {
    user() { return appState.user || { name: '', email: '' }; },
    initials() {
      const name = this.user.name || '?';
      const parts = name.trim().split(/\s+/);
      return (parts[0][0] + (parts[1] ? parts[1][0] : '')).toUpperCase();
    },
    attempts() { return Array.isArray(appState.attempts) ? appState.attempts : []; },
    parts() { return PART_ORDER.map((n) => PARTS[n]); },
    progress() { return courseProgress(appState.questions, appState.attempts); },
    bands() {
      return this.attempts.map((a) => (a.feedback ? Number(a.feedback.band_score) : NaN)).filter((n) => !isNaN(n));
    },
    bestBand() { return this.bands.length ? Math.max(...this.bands) : null; },
    avgBand() { return this.bands.length ? this.bands.reduce((a, b) => a + b, 0) / this.bands.length : null; },
    doneIds() { return practisedIds(appState.attempts); },
  },
  async mounted() {
    if (!appState.questions.length && !appState.questionsLoading) await loadQuestions();
    if (!appState.attempts.length && !appState.attemptsLoading) await loadAttempts();
  },
  methods: {
    setRoute,
    bandOrDash(v) { return v == null ? '—' : bandText(v); },
    questionsFor(num) {
      const arr = Array.isArray(appState.questions) ? appState.questions : [];
      return arr.filter((q) => Number(q.part) === Number(num));
    },
    countFor(num) { return this.questionsFor(num).length; },
    doneFor(num) { return this.questionsFor(num).filter((q) => this.doneIds.has(Number(q.id))).length; },
    pctFor(num) {
      const total = this.countFor(num);
      return total ? Math.round((this.doneFor(num) / total) * 100) : 0;
    },
  },
};
</script>
