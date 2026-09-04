<template>
  <div class="page">
    <div v-if="!result" class="card empty">
      <LearnerArt name="empty" width="260" />
      <h2>No report to show yet</h2>
      <p class="lead">Complete a lesson and your band report will appear here.</p>
      <button class="btn btn--primary" type="button" @click="setRoute('practice')">Go to Practice</button>
    </div>

    <template v-else>
      <div class="row row--between row--wrap" style="margin-bottom:22px">
        <div>
          <span class="eyebrow">Lesson complete</span>
          <h1 style="font-size:clamp(1.5rem,3vw,2.1rem);margin-top:8px">Your band report</h1>
          <p class="muted">{{ displayPart }} · {{ topic }}</p>
        </div>
        <div class="row">
          <button class="btn btn--ghost" type="button" @click="setRoute('history')">View progress</button>
          <button class="btn btn--primary" type="button" @click="practiseAgain">Next lesson</button>
        </div>
      </div>

      <div class="card" style="margin-bottom:20px" v-if="question">
        <span class="eyebrow">The question you answered</span>
        <p style="font-family:var(--font-head);font-size:1.05rem;margin-top:8px">{{ question.prompt }}</p>
        <p class="lead" style="margin-top:14px;white-space:pre-wrap">“{{ answerText }}”</p>
      </div>

      <BandReport :feedback="feedback" :offline="isOffline" />
    </template>
  </div>
</template>

<script>
import { appState, setRoute } from '../store';
import { PARTS } from '../constants';
import BandReport from '../components/BandReport.vue';
import LearnerArt from '../components/LearnerArt.vue';

export default {
  name: 'ResultView',
  components: { BandReport, LearnerArt },
  data() { return { appState }; },
  computed: {
    result() { return appState.latestResult; },
    attempt() { return this.result ? this.result.attempt : null; },
    question() { return this.result ? this.result.question : null; },
    feedback() { return this.result ? this.result.feedback : null; },
    answerText() { return this.attempt ? this.attempt.answer_text : ''; },
    topic() { return this.question ? this.question.topic : 'a speaking question'; },
    displayPart() {
      const p = this.question && PARTS[this.question.part];
      return p ? `${p.name} — ${p.title}` : 'Speaking practice';
    },
    isOffline() { return !!this.feedback && !this.feedback.raw_response; },
  },
  methods: {
    setRoute,
    practiseAgain() { setRoute('learn'); },
  },
};
</script>
