<template>
  <!-- Original flat illustration set, one consistent style across the app. -->
  <svg :viewBox="box" :width="width" role="img" :aria-label="label" fill="none" xmlns="http://www.w3.org/2000/svg">
    <g v-if="name === 'hero'">
      <ellipse cx="230" cy="330" rx="185" ry="22" fill="#efeaff" />
      <rect x="96" y="120" width="268" height="172" rx="16" fill="#fff" stroke="#ddd8f0" stroke-width="3" />
      <rect x="96" y="120" width="268" height="30" rx="16" fill="#efeaff" />
      <circle cx="115" cy="135" r="4" fill="#b8a6ff" /><circle cx="130" cy="135" r="4" fill="#9ed8ff" /><circle cx="145" cy="135" r="4" fill="#9fe6d4" />
      <rect x="120" y="176" width="120" height="11" rx="5.5" fill="#ddd8f0" />
      <rect x="120" y="198" width="180" height="11" rx="5.5" fill="#efeaff" />
      <rect x="120" y="220" width="150" height="11" rx="5.5" fill="#efeaff" />
      <rect x="120" y="248" width="86" height="24" rx="12" fill="#6c4df6" />
      <rect x="80" y="292" width="300" height="14" rx="7" fill="#ddd8f0" />

      <circle cx="230" cy="72" r="34" fill="#ffd9c2" />
      <path d="M196 70a34 34 0 0 1 68 0v-6a34 34 0 0 0-68 0v6Z" fill="#3b2f5c" />
      <path d="M214 78a4 4 0 1 1 0-8m32 8a4 4 0 1 0 0-8" stroke="#3b2f5c" stroke-width="3" stroke-linecap="round" />
      <path d="M222 88q8 7 16 0" stroke="#3b2f5c" stroke-width="3" stroke-linecap="round" />
      <path d="M190 74v-6a40 40 0 0 1 80 0v6" stroke="#6c4df6" stroke-width="7" stroke-linecap="round" />
      <rect x="182" y="70" width="16" height="26" rx="8" fill="#6c4df6" />
      <rect x="262" y="70" width="16" height="26" rx="8" fill="#6c4df6" />
      <path d="M266 92c14 4 22 12 22 22" stroke="#6c4df6" stroke-width="5" stroke-linecap="round" />
      <circle cx="292" cy="120" r="9" fill="#ff9159" />

      <g>
        <path d="M40 138a12 12 0 0 1 12-12h44a12 12 0 0 1 12 12v22a12 12 0 0 1-12 12H70l-14 12v-12h-4a12 12 0 0 1-12-12v-22Z" fill="#fff" stroke="#9ed8ff" stroke-width="3" />
        <circle cx="62" cy="149" r="3.4" fill="#9ed8ff" /><circle cx="74" cy="149" r="3.4" fill="#9ed8ff" /><circle cx="86" cy="149" r="3.4" fill="#9ed8ff" />
      </g>
      <g>
        <path d="M348 196a12 12 0 0 1 12-12h44a12 12 0 0 1 12 12v20a12 12 0 0 1-12 12h-30l-14 11v-11h-.5a12 12 0 0 1-11.5-12v-20Z" fill="#fff" stroke="#9fe6d4" stroke-width="3" />
        <path d="M366 206h32M366 216h20" stroke="#9fe6d4" stroke-width="3.5" stroke-linecap="round" />
      </g>
      <path d="M60 246c0-4 3-7 7-7h30c4 0 7 3 7 7s-3 7-7 7H67c-4 0-7-3-7-7Z" fill="#fff0e7" />
      <text x="70" y="251" font-family="Poppins, sans-serif" font-size="11" font-weight="700" fill="#ff9159">Band 7</text>
    </g>

    <g v-else-if="name === 'auth'">
      <ellipse cx="200" cy="300" rx="150" ry="20" fill="#e2ddf7" />
      <rect x="76" y="96" width="248" height="164" rx="18" fill="#fff" stroke="#ddd8f0" stroke-width="3" />
      <path d="M76 130h248" stroke="#ddd8f0" stroke-width="3" />
      <circle cx="94" cy="113" r="4" fill="#b8a6ff" /><circle cx="109" cy="113" r="4" fill="#9ed8ff" />
      <rect x="102" y="156" width="80" height="10" rx="5" fill="#efeaff" />
      <rect x="102" y="176" width="140" height="10" rx="5" fill="#efeaff" />
      <rect x="102" y="206" width="96" height="26" rx="13" fill="#6c4df6" />
      <circle cx="272" cy="196" r="30" fill="#e0f8f3" />
      <path d="M258 196l10 10 18-20" stroke="#22c3a6" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" />
      <rect x="184" y="34" width="32" height="52" rx="16" fill="#fff" stroke="#6c4df6" stroke-width="4" />
      <path d="M170 74a30 30 0 0 0 60 0M200 104v18" stroke="#6c4df6" stroke-width="4" stroke-linecap="round" />
      <path d="M176 122h48" stroke="#6c4df6" stroke-width="4" stroke-linecap="round" />
    </g>

    <g v-else-if="name === 'empty'">
      <ellipse cx="150" cy="196" rx="96" ry="14" fill="#efeaff" />
      <path d="M60 74a14 14 0 0 1 14-14h152a14 14 0 0 1 14 14v76a14 14 0 0 1-14 14h-92l-30 24v-24H74a14 14 0 0 1-14-14V74Z" fill="#fff" stroke="#ddd8f0" stroke-width="3" />
      <path d="M96 100h108M96 124h72" stroke="#e3def5" stroke-width="8" stroke-linecap="round" />
      <circle cx="232" cy="62" r="18" fill="#fff0e7" />
      <path d="M232 54v10m0 8v1" stroke="#ff9159" stroke-width="4" stroke-linecap="round" />
    </g>

    <g v-else-if="name === 'trophy'">
      <path d="M52 34h60v22a30 30 0 0 1-60 0V34Z" fill="#ffd9a8" stroke="#f0a83c" stroke-width="4" />
      <path d="M52 40H38a14 14 0 0 0 14 14M112 40h14a14 14 0 0 1-14 14" stroke="#f0a83c" stroke-width="4" stroke-linecap="round" />
      <path d="M74 86h16v14H74z" fill="#f0a83c" />
      <rect x="58" y="100" width="48" height="12" rx="6" fill="#f0a83c" />
      <path d="M82 46l4.5 9 10 1.5-7 7 1.5 10-9-4.5-9 4.5 1.5-10-7-7 10-1.5L82 46Z" fill="#fff" />
    </g>
  </svg>
</template>

<script>
const BOXES = { hero: '0 0 460 360', auth: '0 0 400 330', empty: '0 0 300 220', trophy: '0 0 164 130' };
const LABELS = {
  hero: 'A student wearing headphones practising IELTS speaking at a laptop',
  auth: 'A microphone beside a sign-in screen with a completed check mark',
  empty: 'An empty speech bubble waiting for a first practice answer',
  trophy: 'A trophy marking a practice milestone',
};

export default {
  name: 'LearnerArt',
  props: {
    name: { type: String, default: 'hero' },
    width: { type: [Number, String], default: 460 },
  },
  computed: {
    box() { return BOXES[this.name] || BOXES.hero; },
    label() { return LABELS[this.name] || ''; },
  },
};
</script>
