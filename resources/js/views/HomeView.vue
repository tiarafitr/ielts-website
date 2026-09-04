<template>
  <div class="page">
    <section class="hero">
      <div>
        <span class="pill">
          <span style="width:8px;height:8px;border-radius:50%;background:#22c3a6;display:inline-block"></span>
          IELTS Speaking · Parts 1, 2 &amp; 3
        </span>
        <h1>Master IELTS Speaking <span class="hero__grad">with Confidence</span></h1>
        <p class="lead">
          Build fluency, expand your vocabulary, and practice real IELTS-style speaking
          questions with instant feedback.
        </p>

        <div class="hero__cta">
          <button class="btn btn--primary" type="button" @click="continueLearning">
            {{ progress.completed ? 'Continue Learning' : 'Start Learning' }}
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
              <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </button>
          <button class="btn btn--ghost" type="button" @click="setRoute('learn')">Explore Course</button>
        </div>

        <div class="row row--wrap" style="margin-top:28px;gap:22px">
          <div>
            <div style="font-family:var(--font-head);font-weight:700;font-size:1.3rem">{{ progress.total }}</div>
            <div class="muted">practice questions</div>
          </div>
          <div>
            <div style="font-family:var(--font-head);font-weight:700;font-size:1.3rem">3</div>
            <div class="muted">test parts covered</div>
          </div>
          <div>
            <div style="font-family:var(--font-head);font-weight:700;font-size:1.3rem">{{ progress.percent }}%</div>
            <div class="muted">of course completed</div>
          </div>
        </div>
      </div>

      <div class="hero__art">
        <LearnerArt name="hero" width="100%" />
      </div>
    </section>

    <section class="sec" v-if="nextUp">
      <div class="card card--pad-lg" style="background:linear-gradient(140deg,#efeaff,#fff 55%,#e6f2ff)">
        <div class="row row--between row--wrap" style="gap:20px">
          <div>
            <span class="eyebrow">Pick up where you left off</span>
            <h2 style="margin:8px 0 6px">Lesson {{ nextUp.n }} · {{ nextUp.title }}</h2>
            <p class="lead" style="max-width:52ch">{{ nextUp.desc }}</p>
          </div>
          <button class="btn btn--primary" type="button" @click="openLesson(nextUp)">Continue</button>
        </div>
        <div style="margin-top:22px">
          <div class="row row--between" style="margin-bottom:8px">
            <span class="muted">{{ progress.completed }} of {{ progress.total }} lessons completed</span>
            <span class="muted">{{ progress.percent }}%</span>
          </div>
          <div class="progress"><div class="progress__fill" :style="{ width: barWidth }"></div></div>
        </div>
      </div>
    </section>

    <section class="sec">
      <div class="sec__head">
        <span class="eyebrow">The course</span>
        <h2>Three parts, one speaking journey</h2>
        <p>Each part of the real test has its own section of lessons and question bank.</p>
      </div>

      <div class="lessons">
        <button
          v-for="part in parts"
          :key="part.num"
          class="lesson"
          type="button"
          :disabled="isPartEmpty(part.num)"
          @click="pickPart(part.num)"
        >
          <span class="module__idx" :class="'module__idx--' + accentFor(part.num)">{{ part.num }}</span>
          <span>
            <span class="lesson__title">{{ part.name }} — {{ part.title }}</span>
            <span class="lesson__desc">{{ part.blurb }}</span>
            <span class="lesson__tags">
              <span class="chip">{{ part.time }}</span>
              <span class="chip">{{ countFor(part.num) }} questions</span>
              <span class="chip chip--done" v-if="doneFor(part.num)">{{ doneFor(part.num) }} completed</span>
            </span>
          </span>
          <span class="lesson__go">Practice →</span>
        </button>
      </div>
    </section>

    <section class="sec">
      <div class="sec__head">
        <span class="eyebrow">How it works</span>
        <h2>From question to band report in four steps</h2>
      </div>
      <div class="milestones">
        <div class="milestone" v-for="s in steps" :key="s.n" style="text-align:left">
          <div class="eyebrow">{{ s.n }}</div>
          <div style="font-family:var(--font-head);font-weight:600;margin:6px 0 4px">{{ s.title }}</div>
          <p class="muted" style="line-height:1.5">{{ s.body }}</p>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import { appState, setRoute, loadQuestions, loadAttempts } from '../store';
import { PARTS, PART_ORDER, HOW_IT_WORKS } from '../constants';
import { courseProgress, nextLesson } from '../course';
import LearnerArt from '../components/LearnerArt.vue';

export default {
  name: 'HomeView',
  components: { LearnerArt },
  data() {
    return { appState, steps: HOW_IT_WORKS, barWidth: '0%' };
  },
  computed: {
    parts() { return PART_ORDER.map((n) => PARTS[n]); },
    progress() { return courseProgress(appState.questions, appState.attempts); },
    nextUp() { return nextLesson(appState.questions, appState.attempts); },
  },
  async mounted() {
    if (!appState.questions.length && !appState.questionsLoading) await loadQuestions();
    if (!appState.attempts.length && !appState.attemptsLoading) await loadAttempts();
    requestAnimationFrame(() => { this.barWidth = this.progress.percent + '%'; });
  },
  methods: {
    setRoute,
    accentFor(num) { return ['b', 'c', 'd'][num - 1] || 'a'; },
    questionsFor(num) {
      const arr = Array.isArray(appState.questions) ? appState.questions : [];
      return arr.filter((q) => Number(q.part) === Number(num));
    },
    countFor(num) { return this.questionsFor(num).length; },
    doneFor(num) {
      const done = new Set((appState.attempts || []).map((a) => Number(a.question && a.question.id)));
      return this.questionsFor(num).filter((q) => done.has(Number(q.id))).length;
    },
    isPartEmpty(num) { return this.countFor(num) === 0; },
    // Preserved behaviour: choosing a part opens Practice on that part.
    pickPart(num) {
      if (this.isPartEmpty(num)) return;
      appState.pendingPart = num;
      setRoute('practice');
    },
    openLesson(lesson) {
      appState.pendingPart = lesson.part;
      appState.pendingQuestionId = lesson.id;
      setRoute('practice');
    },
    continueLearning() {
      if (this.nextUp) return this.openLesson(this.nextUp);
      setRoute('learn');
    },
  },
};
</script>
