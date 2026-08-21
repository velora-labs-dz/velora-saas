import type { ReactNode } from "react";
import { useReveal } from "./useReveal";

/**
 * Wraps a single element with the scroll-reveal effect. Exists so that lists rendered
 * via .map() can each reveal independently without calling useReveal() directly inside
 * the loop (which would break the rules of hooks).
 */
export function Reveal({
  children,
  delay = 0,
  className = "",
}: {
  children: ReactNode;
  delay?: number;
  className?: string;
}) {
  const { ref, className: revealClass } = useReveal<HTMLDivElement>(delay);
  return (
    <div ref={ref} className={`${revealClass} ${className}`}>
      {children}
    </div>
  );
}
