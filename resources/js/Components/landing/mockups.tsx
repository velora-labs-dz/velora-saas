/**
 * Browser-chrome wrapper used around the hero screenshot (dots + fake URL bar).
 * The dashboard/calendar/caisse content itself is real screenshots now
 * (public/images/landing/shot-*.jpg), not recreated markup — see OverviewSection.tsx.
 */
export function BrowserFrame({
  url,
  children,
}: {
  url: string;
  children: React.ReactNode;
}) {
  return (
    <div className="overflow-hidden rounded-[28px] border border-border/70 bg-card shadow-luxe">
      <div className="flex items-center gap-1.5 border-b border-border/60 bg-secondary/60 px-4 py-3">
        <span className="size-2.5 rounded-full bg-foreground/15" />
        <span className="size-2.5 rounded-full bg-foreground/15" />
        <span className="size-2.5 rounded-full bg-foreground/15" />
        <span className="ml-4 text-[11px] tracking-wide text-muted-foreground">{url}</span>
      </div>
      {children}
    </div>
  );
}
