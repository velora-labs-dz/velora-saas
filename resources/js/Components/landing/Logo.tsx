/**
 * Velora logomark: two interlocked ribbon strokes forming a "V", same construction
 * logic as the Nexora "N" (two overlapping gradient strokes, taupe -> gold), so the
 * brand feels like it belongs to the same family without being a copy of the mark.
 */
export function LogoMark({ className = "" }: { className?: string }) {
  return (
    <svg viewBox="0 0 100 100" className={className} fill="none" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <linearGradient id="velora-stroke-a" x1="10" y1="10" x2="55" y2="90" gradientUnits="userSpaceOnUse">
          <stop offset="0" stopColor="oklch(0.62 0.02 70)" />
          <stop offset="1" stopColor="oklch(0.73 0.098 84)" />
        </linearGradient>
        <linearGradient id="velora-stroke-b" x1="90" y1="10" x2="45" y2="90" gradientUnits="userSpaceOnUse">
          <stop offset="0" stopColor="oklch(0.86 0.07 88)" />
          <stop offset="1" stopColor="oklch(0.66 0.09 78)" />
        </linearGradient>
      </defs>
      {/* left leg of the V */}
      <path
        d="M12 12 L34 12 L52 70 L44 92 L12 12 Z"
        fill="none"
        stroke="url(#velora-stroke-a)"
        strokeWidth="3.5"
        strokeLinejoin="round"
      />
      {/* right leg of the V, slightly offset to create the interlock at the base */}
      <path
        d="M88 12 L66 12 L48 70 L56 92 L88 12 Z"
        fill="none"
        stroke="url(#velora-stroke-b)"
        strokeWidth="3.5"
        strokeLinejoin="round"
      />
    </svg>
  );
}

export function VeloraLogo({
  className = "",
  markClassName = "h-10 w-10",
  wordmarkClassName = "text-sm uppercase tracking-[0.42em] text-foreground/80",
  showWordmark = true,
}: {
  className?: string;
  markClassName?: string;
  wordmarkClassName?: string;
  showWordmark?: boolean;
}) {
  return (
    <span className={`inline-flex items-center gap-3 ${className}`}>
      <LogoMark className={markClassName} />
      {showWordmark && <span className={wordmarkClassName}>Velora</span>}
    </span>
  );
}
