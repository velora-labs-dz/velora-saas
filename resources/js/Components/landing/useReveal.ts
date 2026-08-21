import { useEffect, useRef, useState } from "react";

/**
 * Reveals an element with a fade-up as it scrolls into view.
 * Usage: const { ref, className } = useReveal();  <div ref={ref} className={className}>
 *
 * Mirrors the `fadeUp` whileInView behaviour from the Nexora prototype (motion/react)
 * without pulling in a new dependency — one IntersectionObserver, triggers once.
 */
export function useReveal<T extends HTMLElement = HTMLDivElement>(delayMs = 0) {
  const ref = useRef<T | null>(null);
  const [visible, setVisible] = useState(false);

  useEffect(() => {
    const el = ref.current;
    if (!el) return;

    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          if (delayMs > 0) {
            const t = setTimeout(() => setVisible(true), delayMs);
            return () => clearTimeout(t);
          }
          setVisible(true);
          observer.unobserve(el);
        }
      },
      { threshold: 0.15, rootMargin: "-80px 0px" },
    );

    observer.observe(el);
    return () => observer.disconnect();
  }, [delayMs]);

  return {
    ref,
    className: `reveal ${visible ? "reveal-in" : ""}`,
  };
}
