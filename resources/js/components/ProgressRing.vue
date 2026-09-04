<template>
  <div class="ring" :style="{ width: size + 'px', height: size + 'px' }">
    <svg :width="size" :height="size">
      <defs>
        <linearGradient id="bandgrad" x1="0" y1="0" x2="1" y2="1">
          <stop offset="0%" stop-color="#6c4df6" />
          <stop offset="100%" stop-color="#4a9df8" />
        </linearGradient>
      </defs>
      <circle class="ring__track" :cx="c" :cy="c" :r="r" :stroke-width="stroke" fill="none" />
      <circle
        class="ring__bar"
        :cx="c" :cy="c" :r="r" :stroke-width="stroke" fill="none"
        :stroke-dasharray="circumference"
        :stroke-dashoffset="offset"
      />
    </svg>
    <div class="ring__mid">
      <slot />
    </div>
  </div>
</template>

<script>
export default {
  name: 'ProgressRing',
  props: {
    percent: { type: Number, default: 0 },
    size: { type: Number, default: 168 },
    stroke: { type: Number, default: 12 },
  },
  data() {
    return { shown: 0 };
  },
  computed: {
    c() { return this.size / 2; },
    r() { return this.size / 2 - this.stroke; },
    circumference() { return 2 * Math.PI * this.r; },
    offset() {
      const pct = Math.max(0, Math.min(100, this.shown));
      return this.circumference * (1 - pct / 100);
    },
  },
  watch: {
    percent: 'animate',
  },
  mounted() { this.animate(); },
  methods: {
    animate() {
      // One frame at zero so the stroke visibly sweeps in on load.
      requestAnimationFrame(() => { this.shown = this.percent; });
    },
  },
};
</script>
