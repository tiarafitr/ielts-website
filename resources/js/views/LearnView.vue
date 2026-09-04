<template>
  <div class="page">
    <div class="card card--pad-lg course-head">
      <div>
        <span class="eyebrow">Your course</span>
        <h1 style="font-size:clamp(1.6rem,3vw,2.2rem);margin:8px 0 10px">IELTS Speaking Course</h1>
        <p class="lead" style="max-width:56ch">
          Work through each part of the speaking test. Every lesson is a real IELTS-style
          question — answer it and you'll get an examiner-style band report.
        </p>
        <div style="margin-top:22px;max-width:460px">
          <div class="row row--between" style="margin-bottom:8px">
            <strong style="font-size:.9rem">{{ progress.completed }} of {{ progress.total }} lessons completed</strong>
            <span class="muted">{{ progress.percent }}%</span>
          </div>
          <div class="progress"><div class="progress__fill" :style="{ width: barWidth }"></div></div>
        </div>
      </div>

      <ProgressRing :percent="progress.percent" :size="140" :stroke="11">
        <div class="ring__num" style="font-size:1.9rem">{{ progress.percent }}%</div>
        <div class="ring__cap">complete</div>
      </ProgressRing>
    </div>

    <div v-if="loading" class="stack" style="margin-top:30px">
      <div class="skeleton" style="height:76px" v-for="n in 4" :key="n"></div>
    </div>

    <div v-else-if="errorMessage" class="alert alert--error" style="margin-top:24px">
      <span>{{ errorMessage }}</span>
      <button class="btn btn--quiet btn--sm" type="button" @click="reload">Retry</button>
    </div>

    <section v-else class="module" v-for="section in curriculum" :key="section.key">
      <div class="module__bar">
        <span class="module__idx" :class="'module__idx--' + section.accent">
          {{ section.kind === 'part' ? section.part : '★' }}
        </span>
        <div>
          <div class="module__meta">{{ section.label }}</div>
          <h2 class="module__title">{{ section.title }}</h2>
        </div>
        <span class="module__count" v-if="section.kind === 'part'">
          {{ completedIn(section) }} / {{ section.lessons.length }}
        </span>
      </div>
      <p class="muted" style="margin:-6px 0 14px 56px">{{ section.blurb }}</p>

      <div class="lessons">
        <button
          v-for="lesson in section.lessons"
          :key="lesson.id"
          class="lesson"
          :class="{ 'is-done': lesson.state === 'done', 'is-current': lesson.state === 'current' }"
          type="button"
          @click="open(section, lesson)"
        >
          <span class="lesson__tick">
            <svg v-if="lesson.state === 'done'" width="16" height="16" viewBox="0 0 16 16" fill="none">
              <path d="M3 8.5l3.2 3.2L13 5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <template v-else>{{ lesson.n }}</template>
          </span>

          <span>
            <span class="lesson__title">{{ lesson.title }}</span>
            <span class="lesson__desc">{{ shorten(lesson.desc) }}</span>
            <span class="lesson__tags">
              <span class="chip">{{ lesson.minutes }} min</span>
              <span class="chip chip--now" v-if="lesson.state === 'current'">Continue here</span>
              <span class="chip chip--done" v-else-if="lesson.state === 'done'">Completed</span>
              <span class="chip chip--band" v-if="lesson.band != null">Best band {{ bandText(lesson.band) }}</span>
              <span class="chip" v-if="section.kind === 'guide'">Guide</span>
            </span>
          </span>

          <span class="lesson__go">{{ section.kind === 'guide' ? 'Read' : (lesson.state === 'done' ? 'Practise again' : 'Start') }} →</span>
        </button>
      </div>
    </section>

    <div v-if="openGuide" class="card" style="margin-top:24px;border-color:var(--lavender)">
      <div class="row row--between">
        <h3>{{ openGuide.title }}</h3>
        <button class="btn btn--quiet btn--sm" type="button" @click="openGuide = null">Close</button>
      </div>
      <p class="lead" style="margin-top:10px">{{ openGuide.body }}</p>
    </div>
  </div>
</template>

<script>
import { appState, setRoute, loadQuestions, loadAttempts } from '../store';
import { buildCurriculum, courseProgress } from '../course';
import { bandText, truncate } from '../format';
import ProgressRing from '../components/ProgressRing.vue';

export default {
  name: 'LearnView',
  components: { ProgressRing },
  data() {
    return { appState, openGuide: null, barWidth: '0%' };
  },
  computed: {
    loading() { return appState.questionsLoading && !appState.questions.length; },
    errorMessage() { return appState.questionsError; },
    curriculum() { return buildCurriculum(appState.questions, appState.attempts); },
    progress() { return courseProgress(appState.questions, appState.attempts); },
  },
  async mounted() {
    if (!appState.questions.length && !appState.questionsLoading) await loadQuestions();
    if (!appState.attempts.length && !appState.attemptsLoading) await loadAttempts();
    requestAnimationFrame(() => { this.barWidth = this.progress.percent + '%'; });
  },
  methods: {
    bandText,
    shorten(text) { return truncate(text, 110); },
    completedIn(section) { return section.lessons.filter((l) => l.state === 'done').length; },
    async reload() { await loadQuestions(); },
    open(section, lesson) {
      if (section.kind === 'guide') {
        this.openGuide = this.openGuide && this.openGuide.id === lesson.id ? null : lesson;
        return;
      }
      // Preserved behaviour: opening a lesson lands Practice on that part.
      appState.pendingPart = lesson.part;
      appState.pendingQuestionId = lesson.id;
      setRoute('practice');
    },
  },
};
</script>
