/** Small formatting helpers used across the app. */

/** Format a band score consistently, e.g. 6.5 -> "6.5", 7 -> "7.0". */
export function bandText(value) {
  if (value === null || value === undefined || isNaN(value)) return '—';
  return Number(value).toFixed(1);
}

/** Format a date string (ISO) in a readable local fashion. */
export function fmtDate(value) {
  if (!value) return '';
  try {
    return new Date(value).toLocaleString(undefined, {
      dateStyle: 'medium',
      timeStyle: 'short',
    });
  } catch {
    return String(value);
  }
}

/** Truncate a long string with an ellipsis. */
export function truncate(str, max = 120) {
  const s = String(str || '');
  return s.length > max ? s.slice(0, max).trimEnd() + '…' : s;
}

/** Map percentage for a score on the 1–9 IELTS scale. */
export function pctFor(score) {
  const n = Number(score);
  if (isNaN(n)) return 0;
  const clamped = Math.max(1, Math.min(9, n));
  return ((clamped - 1) / 8) * 100;
}
