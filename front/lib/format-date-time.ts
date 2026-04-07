/** UK-style display: dd/mm/yyyy HH:mm:ss (e.g. 06/04/2026 20:39:06) */

function pad2(n: number): string {
  return String(n).padStart(2, "0");
}

function formatFromDate(d: Date): string {
  return `${pad2(d.getDate())}/${pad2(d.getMonth() + 1)}/${d.getFullYear()} ${pad2(d.getHours())}:${pad2(d.getMinutes())}:${pad2(d.getSeconds())}`;
}

/**
 * Normalise API / legacy strings to dd/mm/yyyy HH:mm:ss.
 * Accepts ISO-8601, `d/m/yyyy H:i` / `H:i:s`, and `d/m/yyyy` (time 00:00:00).
 */
export function formatDisplayDateTime(
  value: string | Date | null | undefined,
  empty = "—"
): string {
  if (value === null || value === undefined || value === "") return empty;
  if (value instanceof Date) {
    return Number.isNaN(value.getTime()) ? empty : formatFromDate(value);
  }

  const s = String(value).trim();
  if (!s) return empty;

  const legacy = s.match(
    /^(\d{1,2}):(\d{2})(?::(\d{2}))?\s+(\d{1,2})\/(\d{1,2})\/(\d{4})$/
  );
  if (legacy) {
    const [, hh, mm, ss = "00", dd, mo, yyyy] = legacy;
    return `${pad2(+dd)}/${pad2(+mo)}/${yyyy} ${pad2(+hh)}:${pad2(+mm)}:${pad2(+ss)}`;
  }

  const withTime = s.match(
    /^(\d{1,2})\/(\d{1,2})\/(\d{4})\s+(\d{1,2}):(\d{2})(?::(\d{2}))?$/
  );
  if (withTime) {
    const [, d, m, y, H, M, S] = withTime;
    return `${pad2(+d)}/${pad2(+m)}/${y} ${pad2(+H)}:${pad2(+M)}:${pad2(+(S ?? "0"))}`;
  }

  const dateOnly = s.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
  if (dateOnly) {
    const [, d, m, y] = dateOnly;
    return `${pad2(+d)}/${pad2(+m)}/${y} 00:00:00`;
  }

  const parsed = new Date(s);
  if (!Number.isNaN(parsed.getTime())) return formatFromDate(parsed);

  return s;
}
