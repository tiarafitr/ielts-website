<template>
  <div class="page">
    <div v-if="loading" class="stack">
      <div class="skeleton" style="height:120px"></div>
      <div class="skeleton" style="height:220px"></div>
    </div>

    <div v-else-if="fetchError" class="alert alert--error">
      <span>{{ fetchErrorMessage }}</span>
      <button class="btn btn--quiet btn--sm" type="button" @click="reloadQuestions">Retry</button>
    </div>

    <div v-else-if="noQuestions" class="card empty">
      <LearnerArt name="empty" width="260" />
      <h2>No lessons available yet</h2>
      <p class="lead">The question bank is empty. Seed it with <code>php artisan db:seed</code>.</p>
      <button class="btn btn--ghost" type="button" @click="reloadQuestions">Reload</button>
    </div>

    <div v-else class="player">
      <!-- course navigation sidebar (drawer on mobile) -->
      <aside class="sidebar">
        <div class="sidebar__title">IELTS Speaking</div>
        <div class="muted" style="font-size:.82rem">{{ progress.completed }} of {{ progress.total }} done</div>
        <div class="progress progress--mint" style="margin-top:10px">
          <div class="progress__fill" :style="{ width: progress.percent + '%' }"></div>
        </div>

        <div class="sidebar__group" v-for="p in parts" :key="p.num">
          <div class="sidebar__label">{{ p.name }}</div>
          <button
            v-for="q in questionsOf(p.num)"
            :key="q.id"
            class="sidebar__item"
            :class="{ 'is-active': selectedQuestion && selectedQuestion.id === q.id, 'is-done': isDone(q.id) }"
            type="button"
            @click="jumpTo(p.num, q)"
          >
            <span class="sidebar__dot">
              <svg v-if="isDone(q.id)" width="9" height="9" viewBox="0 0 16 16" fill="none">
                <path d="M3 8.5l3.2 3.2L13 5" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </span>
            <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ q.topic }}</span>
          </button>
        </div>
      </aside>

      <div>
        <!-- lesson header -->
        <div class="lessonbar">
          <div>
            <span class="eyebrow">{{ currentPart.name }} — {{ currentPart.title }}</span>
            <h1 style="font-size:clamp(1.3rem,2.6vw,1.8rem);margin-top:6px">
              {{ selectedQuestion ? selectedQuestion.topic : 'Choose your lesson' }}
            </h1>
            <p class="muted" v-if="selectedQuestion">
              Lesson {{ lessonIndex }} of {{ questionsForPart.length }} in this section
            </p>
          </div>
          <div class="lessonbar__steps">
            <span v-for="(s, i) in stepsMeta" :key="s.label" class="lessonbar__step" :class="{ 'is-on': i <= stepIndex }"></span>
          </div>
        </div>

        <!-- step 1: choose the part -->
        <div v-if="step === 1" class="lessons">
          <button
            v-for="p in parts"
            :key="p.num"
            class="lesson"
            :class="{ 'is-current': chosenPart === p.num }"
            type="button"
            :disabled="isPartEmpty(p.num)"
            @click="choosePart(p.num)"
          >
            <span class="module__idx" :class="'module__idx--' + accentFor(p.num)">{{ p.num }}</span>
            <span>
              <span class="lesson__title">{{ p.name }} — {{ p.title }}</span>
              <span class="lesson__desc">{{ p.blurb }}</span>
              <span class="lesson__tags">
                <span class="chip">{{ p.time }}</span>
                <span class="chip">{{ questionsOf(p.num).length }} lessons</span>
              </span>
            </span>
            <span class="lesson__go">Open →</span>
          </button>
        </div>

        <!-- step 2: choose the question -->
        <div v-else-if="step === 2">
          <div class="row row--between" style="margin-bottom:14px">
            <p class="muted">{{ currentPart.prompt }}</p>
            <button class="btn btn--quiet btn--sm" type="button" @click="back()">← Change part</button>
          </div>
          <div class="lessons">
            <button
              v-for="(q, i) in questionsForPart"
              :key="q.id"
              class="lesson"
              :class="{ 'is-done': isDone(q.id) }"
              type="button"
              @click="chooseQuestion(q)"
            >
              <span class="lesson__tick">
                <svg v-if="isDone(q.id)" width="16" height="16" viewBox="0 0 16 16" fill="none">
                  <path d="M3 8.5l3.2 3.2L13 5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <template v-else>{{ i + 1 }}</template>
              </span>
              <span>
                <span class="lesson__title">{{ q.topic }}</span>
                <span class="lesson__desc">{{ shorten(q.prompt) }}</span>
              </span>
              <span class="lesson__go">{{ isDone(q.id) ? 'Practise again' : 'Start' }} →</span>
            </button>
          </div>
        </div>

        <!-- step 3: the lesson itself -->
        <div v-else>
          <div v-if="submitting" class="card evaluating">
            <div class="evaluating__dots"><i></i><i></i><i></i></div>
            <h2>Your examiner is reading your answer…</h2>
            <p class="lead">Scoring fluency, vocabulary, grammar and coherence.</p>
          </div>

          <template v-else>
            <div class="prompt-card">
              <span class="eyebrow">Your question</span>
              <p class="prompt-card__q">{{ selectedQuestion.prompt }}</p>
              <div class="row row--wrap">
                <span class="chip">{{ currentPart.name }}</span>
                <span class="chip">{{ selectedQuestion.topic }}</span>
                <span class="chip">{{ currentPart.time }}</span>
              </div>

              <ul class="tips">
                <li><strong>How to answer:</strong> {{ currentPart.prompt }}</li>
                <li>Write it exactly as you would say it out loud — full sentences, natural phrasing.</li>
                <li>Add a reason and an example; that is what separates band 6 from band 7.</li>
              </ul>
            </div>

            <div class="card" style="margin-top:18px">
              <div class="row row--between" style="margin-bottom:10px">
                <label for="answer" style="font-family:var(--font-head);font-weight:600">Your answer</label>
                <span class="counter">{{ wordCount }} words · {{ answerText.trim().length }} characters</span>
              </div>
              <textarea
                id="answer"
                class="answer-area"
                v-model="answerText"
                placeholder="Start speaking on paper… write your full answer here."
              ></textarea>

              <div v-if="runtimeError" class="alert alert--error" style="margin-top:14px">{{ runtimeError }}</div>

              <div class="row row--between row--wrap" style="margin-top:18px">
                <button class="btn btn--ghost" type="button" @click="back()">← Back</button>
                <button class="btn btn--primary" type="button" :disabled="submitting" @click="submit">
                  <span v-if="submitting" class="spinner"></span>
                  {{ submitting ? 'Evaluating…' : 'Submit for feedback' }}
                </button>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { appState, setRoute, loadQuestions, loadAttempts } from '../store';
import { PARTS, PART_ORDER } from '../constants';
import { submitAnswer, apiErrorMessage } from '../api';
import { courseProgress, practisedIds } from '../course';
import { truncate } from '../format';
import LearnerArt from '../components/LearnerArt.vue';

const STEPS = ['Part', 'Question', 'Answer', 'Report'];

export default {
  name: 'PracticeView',
  components: { LearnerArt },
  data() {
    return {
      appState,
      step: 1,
      chosenPart: 1,
      selectedQuestion: null,

      answerText: '',
      submitting: false,
      runtimeError: '',
    };
  },
  computed: {
    parts() { return PART_ORDER.map((n) => PARTS[n]); },
    currentPart() { return this.chosenPart ? PARTS[this.chosenPart] : PARTS[1]; },
    stepsMeta() { return STEPS.map((label) => ({ label })); },
    stepIndex() {
      if (this.step === 1) return 0;
      if (this.step === 2) return 1;
      if (this.step === 3) return 2;
      return 3;
    },
    loading() { return appState.questionsLoading; },
    noQuestions() { return !appState.questionsLoading && this.totalQuestions === 0; },
    fetchError() { return !!appState.questionsError; },
    fetchErrorMessage() { return appState.questionsError; },
    totalQuestions() { return Array.isArray(appState.questions) ? appState.questions.length : 0; },
    questionsForPart() { return this.questionsOf(this.chosenPart); },
    lessonIndex() {
      if (!this.selectedQuestion) return 0;
      return this.questionsForPart.findIndex((q) => q.id === this.selectedQuestion.id) + 1;
    },
    progress() { return courseProgress(appState.questions, appState.attempts); },
    doneIds() { return practisedIds(appState.attempts); },
    wordCount() {
      const t = this.answerText.trim();
      return t ? t.split(/\s+/).length : 0;
    },
  },
  async mounted() {
    if (appState.questions.length === 0 && !appState.questionsLoading) {
      await loadQuestions();
    }
    if (!appState.attempts.length && !appState.attemptsLoading) await loadAttempts();

    if (appState.pendingPart) {
      this.chosenPart = appState.pendingPart;
      appState.pendingPart = null;
      this.step = 2;
    }
    // Deep link from a lesson card: open that exact question.
    if (appState.pendingQuestionId) {
      const target = (appState.questions || []).find((q) => Number(q.id) === Number(appState.pendingQuestionId));
      appState.pendingQuestionId = null;
      if (target) {
        this.chosenPart = Number(target.part);
        this.selectedQuestion = target;
        this.step = 3;
      }
    }
  },
  methods: {
    shorten(text) { return truncate(text, 110); },
    accentFor(num) { return ['b', 'c', 'd'][num - 1] || 'a'; },
    questionsOf(num) {
      const arr = Array.isArray(appState.questions) ? appState.questions : [];
      return arr.filter((q) => Number(q.part) === Number(num));
    },
    isDone(id) { return this.doneIds.has(Number(id)); },
    isPartEmpty(num) { return this.questionsOf(num).length === 0; },
    choosePart(num) { if (this.isPartEmpty(num)) return; this.chosenPart = num; this.step = 2; },
    chooseQuestion(q) { this.selectedQuestion = q; this.step = 3; },
    jumpTo(part, q) {
      this.chosenPart = Number(part);
      this.selectedQuestion = q;
      this.answerText = '';
      this.runtimeError = '';
      this.step = 3;
    },
    back() {
      if (this.step === 3) { this.step = 2; return; }
      if (this.step === 2) { this.step = 1; this.selectedQuestion = null; return; }
      this.step = 1;
    },
    resetAll() {
      this.step = 1;
      this.chosenPart = 1;
      this.selectedQuestion = null;
      this.answerText = '';
      this.runtimeError = '';
    },
    async reloadQuestions() { await loadQuestions(); },
    async submit() {
      if (this.submitting) return;
      this.runtimeError = '';
      if (!this.selectedQuestion) {
        this.runtimeError = 'No question selected. Please go back and choose one.';
        return;
      }
      const text = this.answerText.trim();
      if (text.length < 10) {
        this.runtimeError = 'Please write at least 10 characters before submitting.';
        return;
      }
      this.submitting = true;
      appState.evaluating = true;
      try {
        const data = await submitAnswer({
          question_id: this.selectedQuestion.id,
          answer_text: text,
        });
        this.resetAll();
        appState.latestResult = data;
        setRoute('result');
        await loadAttempts();
      } catch (e) {
        this.runtimeError = apiErrorMessage(e);
      } finally {
        this.submitting = false;
        appState.evaluating = false;
      }
    },
  },
};
</script>
