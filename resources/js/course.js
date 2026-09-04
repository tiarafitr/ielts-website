import { PARTS, PART_ORDER } from './constants';

/*
 * The curriculum is derived entirely from real data: every lesson is a real
 * question row, and a lesson counts as completed when the signed-in user
 * actually has an attempt for that question. Nothing here is simulated.
 */

export const GUIDE_LESSONS = [
  {
    id: 'guide-1',
    guide: true,
    title: 'Understanding IELTS Speaking',
    desc: 'The test has three parts and takes 11–14 minutes with a real examiner.',
    body: 'IELTS Speaking is a face-to-face conversation scored on four criteria: fluency and coherence, lexical resource, grammatical range and accuracy, and pronunciation. This course lets you rehearse the first three in writing before you speak.',
    minutes: 3,
  },
  {
    id: 'guide-2',
    guide: true,
    title: 'How you are scored',
    desc: 'Bands run from 1 to 9 in half steps, averaged across the criteria.',
    body: 'Examiners give a band for each criterion and average them. Band 6 is competent, band 7 is good, band 8 is very good. Small, consistent gains in vocabulary and linking usually move a band the fastest.',
    minutes: 3,
  },
  {
    id: 'guide-3',
    guide: true,
    title: 'How to use this course',
    desc: 'Answer in full sentences, exactly as you would speak aloud.',
    body: 'Type your answer the way you would actually say it, at the length you would actually speak. The AI examiner reads it as a transcript and returns a band estimate with specific next steps.',
    minutes: 2,
  },
];

export const IMPROVE_LESSONS = [
  {
    id: 'improve-1',
    guide: true,
    title: 'Extend every answer',
    desc: 'Add a reason and an example to move beyond one-line responses.',
    body: 'A band 5 answer states an opinion. A band 7 answer states it, explains why, and gives a concrete example. Practise the pattern: point, reason, example.',
    minutes: 4,
  },
  {
    id: 'improve-2',
    guide: true,
    title: 'Precise vocabulary',
    desc: 'Swap general words for topic-specific ones.',
    body: 'Instead of "a good place", try "a lively neighbourhood" or "a peaceful stretch of coast". Precision, not rarity, is what lifts lexical resource.',
    minutes: 4,
  },
  {
    id: 'improve-3',
    guide: true,
    title: 'Link your ideas',
    desc: 'Use connectors so your answer flows as one argument.',
    body: 'Coherence rewards signposting: "the main reason is", "on the other hand", "what I mean by that is". Use them sparingly and naturally.',
    minutes: 4,
  },
];

const PART_TIME = { 1: 4, 2: 6, 3: 5 };

/** Distinct question ids the user has already attempted. */
export function practisedIds(attempts) {
  const list = Array.isArray(attempts) ? attempts : [];
  return new Set(list.map((a) => Number(a.question && a.question.id ? a.question.id : a.question_id)));
}

/** Best band the user has achieved on a given question, or null. */
function bestBandFor(attempts, questionId) {
  const list = Array.isArray(attempts) ? attempts : [];
  const bands = list
    .filter((a) => Number(a.question && a.question.id ? a.question.id : a.question_id) === Number(questionId))
    .map((a) => (a.feedback ? Number(a.feedback.band_score) : NaN))
    .filter((n) => !isNaN(n));
  return bands.length ? Math.max(...bands) : null;
}

/**
 * Build the full curriculum: a guide section, one section per IELTS part
 * (filled with the real question bank), and a closing technique section.
 */
export function buildCurriculum(questions, attempts) {
  const bank = Array.isArray(questions) ? questions : [];
  const done = practisedIds(attempts);
  let counter = 0;

  const sections = [
    {
      key: 'start',
      kind: 'guide',
      label: 'Section 1',
      title: 'Getting Started',
      blurb: 'What the test looks like and how this course works.',
      accent: 'a',
      lessons: GUIDE_LESSONS.map((l) => ({ ...l, n: ++counter, state: 'available' })),
    },
  ];

  PART_ORDER.forEach((num, i) => {
    const meta = PARTS[num];
    const lessons = bank
      .filter((q) => Number(q.part) === num)
      .map((q) => {
        const isDone = done.has(Number(q.id));
        return {
          id: q.id,
          question: q,
          part: num,
          n: ++counter,
          title: q.topic,
          desc: q.prompt,
          minutes: PART_TIME[num] || 5,
          band: bestBandFor(attempts, q.id),
          state: isDone ? 'done' : 'available',
        };
      });

    sections.push({
      key: `part-${num}`,
      kind: 'part',
      part: num,
      label: `Section ${i + 2}`,
      title: `${meta.name} — ${meta.title}`,
      blurb: meta.blurb,
      accent: ['b', 'c', 'd'][i],
      lessons,
    });
  });

  sections.push({
    key: 'improve',
    kind: 'guide',
    label: 'Section 5',
    title: 'Improve Your Band Score',
    blurb: 'Three habits that move a band the fastest.',
    accent: 'a',
    lessons: IMPROVE_LESSONS.map((l) => ({ ...l, n: ++counter, state: 'available' })),
  });

  // The first unpractised question is what the learner should do next.
  const next = sections
    .filter((s) => s.kind === 'part')
    .flatMap((s) => s.lessons)
    .find((l) => l.state !== 'done');
  if (next) next.state = 'current';

  return sections;
}

/** Real progress: practised questions over the whole question bank. */
export function courseProgress(questions, attempts) {
  const bank = Array.isArray(questions) ? questions : [];
  const done = practisedIds(attempts);
  const completed = bank.filter((q) => done.has(Number(q.id))).length;
  const total = bank.length;
  return {
    completed,
    total,
    percent: total ? Math.round((completed / total) * 100) : 0,
  };
}

/** The lesson the learner should continue with, if any. */
export function nextLesson(questions, attempts) {
  const sections = buildCurriculum(questions, attempts);
  return sections.filter((s) => s.kind === 'part').flatMap((s) => s.lessons).find((l) => l.state === 'current') || null;
}
