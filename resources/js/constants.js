/** Static product / domain constants shared across the UI. */

export const PARTS = {
  1: {
    num: 1,
    name: 'Part 1',
    title: 'Interview & Introduction',
    blurb: 'Short, familiar questions about yourself, home, work, studies and everyday life.',
    time: '4–5 min',
    prompt: 'Talk naturally about you — aim for 3–4 sentences per answer.',
  },
  2: {
    num: 2,
    name: 'Part 2',
    title: 'Individual Long Turn',
    blurb: 'A cue card with a 1–2 minute talk, covering who, what, where and why.',
    time: '1–2 min talk',
    prompt: 'Use your 1 minute of notes, then speak for 1–2 minutes on the topic.',
  },
  3: {
    num: 3,
    name: 'Part 3',
    title: 'Two-way Discussion',
    blurb: 'Abstract, opinion-style discussion questions extending the Part 2 topic.',
    time: '4–5 min',
    prompt: 'Develop your ideas with reasons, examples and balanced views.',
  },
};

/** Order the picker should present the parts. */
export const PART_ORDER = [1, 2, 3];

/** IELTS criteria breakdown shown on the report. Key maps to backend criteria. */
export const CRITERIA = [
  { key: 'fluency', label: 'Fluency' },
  { key: 'lexical_resource', label: 'Lexical Resource' },
  { key: 'grammar', label: 'Grammatical Range' },
  { key: 'coherence', label: 'Coherence' },
];

/** IELTS overall band descriptor bands. */
export function bandDescriptor(score) {
  if (score === null || score === undefined || isNaN(score)) return { text: 'Not yet scored', tier: 'none' };
  if (score >= 8) return { text: 'Very Good User', tier: 'upper' };
  if (score >= 6.5) return { text: 'Competent User', tier: 'upper' };
  if (score >= 5.5) return { text: 'Modest User', tier: 'mid' };
  if (score >= 4) return { text: 'Limited User', tier: 'lower' };
  if (score >= 2.5) return { text: 'Extremely Limited', tier: 'lower' };
  return { text: 'Non User', tier: 'lower' };
}

/** How the learning loop works (static copy, no fake scores). */
export const HOW_IT_WORKS = [
  {
    n: '01',
    title: 'Open a lesson',
    body: 'Pick a lesson from Part 1, 2 or 3 — each one is a real IELTS-style question.',
  },
  {
    n: '02',
    title: 'Answer as you would speak',
    body: 'Type what you would say in the real test. Aim for the kind of length you would actually speak.',
  },
  {
    n: '03',
    title: 'AI evaluates your answer',
    body: 'Gemini scores the whole range: fluency, vocabulary, range of grammar and coherence.',
  },
  {
    n: '04',
    title: 'Read your band report',
    body: 'See your estimated band, a criteria breakdown and what to practise next.',
  },
];
