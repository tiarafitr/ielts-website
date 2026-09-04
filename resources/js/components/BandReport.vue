<template>
  <div class="stack" style="gap:20px">
    <div class="card card--pad-lg band-hero">
      <ProgressRing :percent="ringPercent" :size="168">
        <div class="ring__cap">Overall band</div>
        <div class="ring__num">{{ bandText(band) }}</div>
        <div class="muted" style="font-size:.78rem">of 9.0</div>
      </ProgressRing>

      <div style="width:100%">
        <span class="chip" :class="'tag--' + desc.tier" v-if="desc.tier !== 'none'">{{ desc.text }}</span>
        <h2 style="margin:10px 0 6px">{{ headline }}</h2>
        <p class="lead">{{ tutorLine }}</p>

        <div class="crit" style="margin-top:22px" v-if="hasCriteria">
          <div class="crit__row" v-for="c in criteriaRows" :key="c.key">
            <span class="crit__name">{{ c.label }}</span>
            <span class="crit__val">{{ bandText(c.value) }}</span>
            <span class="crit__bar"><span class="crit__fill" :style="{ width: widths[c.key] }"></span></span>
          </div>
        </div>
        <p class="muted" style="margin-top:18px" v-else>
          No per-criterion breakdown was provided for this attempt.
        </p>
      </div>
    </div>

    <div class="feedback-col">
      <div class="fb fb--good">
        <div class="fb__head">
          <span style="color:#0d8f77">✓</span> What you did well
        </div>
        <ul v-if="strengths.length">
          <li v-for="(s, i) in strengths" :key="'s' + i">{{ s }}</li>
        </ul>
        <p class="muted" v-else>No strengths were recorded.</p>
      </div>

      <div class="fb fb--grow">
        <div class="fb__head">
          <span style="color:#c25a1d">→</span> What you can improve
        </div>
        <ul v-if="areas.length">
          <li v-for="(a, i) in areas" :key="'a' + i">{{ a }}</li>
        </ul>
        <p class="muted" v-else>No improvement areas were recorded.</p>
      </div>

      <div class="fb fb--next">
        <div class="fb__head">
          <span style="color:#2b7fd4">◎</span> Suggested next practice
        </div>
        <ul><li>{{ nextStep }}</li></ul>
      </div>
    </div>

    <div class="tutor" v-if="offline">
      <span style="font-size:1.2rem">💡</span>
      <p class="tutor__msg">
        This band was estimated offline because no Gemini API key is configured.
        Add <code>GEMINI_API_KEY</code> to <code>.env</code> for a full AI examiner report.
      </p>
    </div>
  </div>
</template>

<script>
import { CRITERIA, bandDescriptor } from '../constants';
import { bandText, pctFor } from '../format';
import ProgressRing from './ProgressRing.vue';

export default {
  name: 'BandReport',
  components: { ProgressRing },
  props: {
    feedback: { type: Object, default: null },
    offline: { type: Boolean, default: false },
  },
  data() {
    return { widths: {} };
  },
  computed: {
    band() { return this.feedback ? this.feedback.band_score : null; },
    ringPercent() { return pctFor(this.band); },
    desc() { return bandDescriptor(this.band); },
    criteria() { return (this.feedback && this.feedback.criteria) || null; },
    hasCriteria() { return !!this.criteria && Object.keys(this.criteria).length > 0; },
    criteriaRows() {
      if (!this.criteria) return [];
      return CRITERIA.map((c) => ({ ...c, value: this.criteria[c.key] })).filter((c) => c.value != null);
    },
    strengths() { return Array.isArray(this.feedback && this.feedback.strengths) ? this.feedback.strengths : []; },
    areas() { return Array.isArray(this.feedback && this.feedback.areas_to_improve) ? this.feedback.areas_to_improve : []; },
    headline() {
      if (this.band == null) return 'No score available yet.';
      if (this.band >= 7.5) return 'Excellent work!';
      if (this.band >= 6.5) return 'Great job — you are in competent territory.';
      if (this.band >= 5.5) return 'Solid effort. You are building real momentum.';
      return 'Good start — every answer moves you forward.';
    },
    tutorLine() {
      if (this.band == null) return 'Submit an answer to receive your band report.';
      if (this.band >= 7.5) return 'Your ideas are clear, well developed and expressed with range. Keep this consistency across all three parts.';
      if (this.band >= 6.5) return 'To reach Band 8, focus on more precise vocabulary and a wider mix of complex sentences.';
      if (this.band >= 5.5) return 'To reach Band 7, extend each answer with a reason and a concrete example.';
      return 'Focus on writing longer, fuller answers — aim for three to four sentences per question.';
    },
    nextStep() {
      if (this.band == null) return 'Try a Part 1 question to get your first band estimate.';
      if (this.band >= 7) return 'Move on to a Part 3 discussion question to stretch your abstract vocabulary.';
      if (this.band >= 6) return 'Try a Part 2 long turn — practise speaking for a full one to two minutes.';
      return 'Repeat a Part 1 question and aim to double the length of your answer.';
    },
  },
  watch: {
    feedback: { handler: 'animate', deep: true },
  },
  mounted() { this.animate(); },
  methods: {
    bandText,
    animate() {
      const target = {};
      this.criteriaRows.forEach((c) => { target[c.key] = pctFor(c.value) + '%'; });
      this.widths = {};
      requestAnimationFrame(() => { this.widths = target; });
    },
  },
};
</script>
