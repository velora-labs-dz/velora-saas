import { Fragment } from "react";

/**
 * These three components are hand-built stand-ins for real product screenshots.
 * The Nexora reference used actual screenshots of its working app — Velora doesn't
 * have a dashboard UI yet, so rather than ship images with the wrong brand baked in,
 * these recreate the same layout/data-shape as live markup. Swap for real
 * screenshots (same aspect ratio, ~1600x1008) once Step 6/7/8 of FOUNDATION.md ship.
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

const NAV_GROUPS = [
  { title: "Clientèle", items: ["Clients", "Registre clients", "Clients passagers", "Abonnements"] },
  { title: "Opérations", items: ["Présences", "Rendez-vous", "Services"] },
  { title: "Commerce", items: ["Caisse", "Paiements"] },
];

const STATS = [
  { label: "Chiffre d'affaires", value: "2 450 000", suffix: "DZD", delta: "+12,5 % vs avr.", up: true },
  { label: "Nouveaux clients", value: "128", delta: "+8,4 % vs avr.", up: true },
  { label: "Abonnements actifs", value: "512", delta: "+5,1 % vs avr.", up: true },
  { label: "Rendez-vous", value: "96", delta: "−3,2 % vs avr.", up: false },
];

const ACTIVITY = [
  { title: "Nouveau client", sub: "Yassine B. · Gym 6 mois", time: "10:24" },
  { title: "Rendez-vous SPA", sub: "Sarah K. · Cabine 3", time: "09:15" },
  { title: "Paiement encaissé", sub: "Facture #F-2025-0512", time: "Hier 18:42" },
  { title: "Badge attribué", sub: "Amine T. · #A-0481", time: "Hier 15:02" },
];

const DUE = [
  { name: "Nadia Bencheikh", plan: "Beauté & Relax — 3 mois", due: "28 mai", left: "0 DZD", status: "À jour" },
  { name: "Karim Haddad", plan: "Gym avec coach — 12 mois", due: "31 mai", left: "12 000 DZD", status: "Solde" },
  { name: "Lina Meziane", plan: "SPA — 10 séances", due: "02 juin", left: "0 DZD", status: "À jour" },
];

const CHART_MONTHS = ["Nov.", "Déc.", "Jan.", "Fév.", "Mars", "Avr.", "Mai"];

export function DashboardMockup() {
  return (
    <div className="flex min-w-[720px] text-[13px]">
      {/* sidebar */}
      <div className="w-[190px] shrink-0 border-r border-border/60 bg-secondary/30 p-5">
        <p className="text-[11px] font-medium uppercase tracking-[0.32em] text-foreground/70">Velora</p>
        <p className="mt-4 rounded-lg bg-card px-3 py-2 text-xs font-medium shadow-sm">Tableau de bord</p>
        <div className="mt-5 space-y-5">
          {NAV_GROUPS.map((group) => (
            <div key={group.title}>
              <p className="text-[10px] uppercase tracking-[0.24em] text-muted-foreground/70">{group.title}</p>
              <ul className="mt-2 space-y-1.5">
                {group.items.map((item) => (
                  <li key={item} className="flex items-center gap-2 text-xs text-muted-foreground">
                    <span className="size-1 rounded-full bg-muted-foreground/50" />
                    {item}
                  </li>
                ))}
              </ul>
            </div>
          ))}
        </div>
      </div>

      {/* main */}
      <div className="flex-1 p-6">
        <div className="flex items-center justify-between">
          <h4 className="font-serif text-2xl">Tableau de bord</h4>
          <div className="flex gap-2">
            <span className="rounded-full border border-border/70 px-3 py-1.5 text-[11px] text-muted-foreground">
              1–31 mai
            </span>
            <span className="rounded-full bg-primary px-3 py-1.5 text-[11px] font-medium text-primary-foreground">
              Nouveau client
            </span>
          </div>
        </div>

        <div className="mt-5 grid grid-cols-4 gap-3">
          {STATS.map((s) => (
            <div key={s.label} className="rounded-2xl border border-border/60 bg-card p-4">
              <p className="text-[10px] text-muted-foreground">{s.label}</p>
              <p className="mt-1.5 font-serif text-lg">
                {s.value}
                {s.suffix && <span className="ml-1 text-[10px] font-sans text-muted-foreground">{s.suffix}</span>}
              </p>
              <p className={`mt-0.5 text-[10px] ${s.up ? "text-emerald-600/80" : "text-destructive/80"}`}>
                {s.delta}
              </p>
            </div>
          ))}
        </div>

        <div className="mt-3 grid grid-cols-[1.5fr_1fr] gap-3">
          <div className="rounded-2xl border border-border/60 bg-card p-4">
            <p className="text-xs font-medium">Évolution du chiffre d'affaires (DZD)</p>
            <svg viewBox="0 0 340 110" className="mt-3 w-full">
              <defs>
                <linearGradient id="dash-area" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%" stopColor="oklch(0.73 0.098 84)" stopOpacity="0.35" />
                  <stop offset="100%" stopColor="oklch(0.73 0.098 84)" stopOpacity="0" />
                </linearGradient>
              </defs>
              <path
                d="M0,70 L48,76 L96,68 L144,42 L192,30 L240,18 L288,10 L340,6 L340,110 L0,110 Z"
                fill="url(#dash-area)"
              />
              <path
                d="M0,70 L48,76 L96,68 L144,42 L192,30 L240,18 L288,10 L340,6"
                fill="none"
                stroke="var(--color-gold)"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
              />
            </svg>
            <div className="mt-1 flex justify-between text-[10px] text-muted-foreground">
              {CHART_MONTHS.map((m) => (
                <span key={m}>{m}</span>
              ))}
            </div>
          </div>
          <div className="rounded-2xl border border-border/60 bg-card p-4">
            <p className="text-xs font-medium">Activité récente</p>
            <ul className="mt-2.5 space-y-2.5">
              {ACTIVITY.map((a) => (
                <li key={a.title} className="flex items-start justify-between gap-2">
                  <span>
                    <span className="block text-[11px] font-medium">{a.title}</span>
                    <span className="block text-[10px] text-muted-foreground">{a.sub}</span>
                  </span>
                  <span className="shrink-0 text-[10px] text-muted-foreground">{a.time}</span>
                </li>
              ))}
            </ul>
          </div>
        </div>

        <div className="mt-3 rounded-2xl border border-border/60 bg-card p-4">
          <p className="text-xs font-medium">Abonnements arrivant à échéance</p>
          <table className="mt-2.5 w-full text-left text-[11px]">
            <thead className="text-muted-foreground">
              <tr>
                <th className="pb-1.5 font-normal">Client</th>
                <th className="pb-1.5 font-normal">Formule</th>
                <th className="pb-1.5 font-normal">Échéance</th>
                <th className="pb-1.5 font-normal">Reste à payer</th>
                <th className="pb-1.5 font-normal">Statut</th>
              </tr>
            </thead>
            <tbody>
              {DUE.map((row) => (
                <tr key={row.name} className="border-t border-border/50">
                  <td className="py-1.5">{row.name}</td>
                  <td className="py-1.5 text-muted-foreground">{row.plan}</td>
                  <td className="py-1.5 text-muted-foreground">{row.due}</td>
                  <td className="py-1.5 text-muted-foreground">{row.left}</td>
                  <td className="py-1.5">
                    <span
                      className={
                        "rounded-full px-2 py-0.5 text-[10px] " +
                        (row.status === "À jour"
                          ? "bg-emerald-100 text-emerald-700"
                          : "bg-amber-100 text-amber-700")
                      }
                    >
                      {row.status}
                    </span>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

const DAYS = ["Lun 18", "Mar 19", "Mer 20", "Jeu 21", "Ven 22", "Sam 23", "Dim 24"];
const HOURS = ["09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00"];
type Appt = { day: number; start: number; span: number; label: string; sub: string; tone: string };
const APPTS: Appt[] = [
  { day: 0, start: 0, span: 1, label: "Coaching perso", sub: "Yassine", tone: "bg-amber-100 text-amber-900" },
  { day: 0, start: 3, span: 2, label: "Cours collectif", sub: "Salle 2", tone: "bg-sky-100 text-sky-900" },
  { day: 1, start: 1, span: 1, label: "Séance musculation", sub: "Sarah", tone: "bg-rose-100 text-rose-900" },
  { day: 1, start: 4, span: 1, label: "Massage relaxant", sub: "Cabine 3", tone: "bg-violet-100 text-violet-900" },
  { day: 2, start: 2, span: 2, label: "Cross training", sub: "Salle de sport", tone: "bg-emerald-100 text-emerald-900" },
  { day: 3, start: 0, span: 1, label: "Bilan forme", sub: "Alexandre", tone: "bg-amber-100 text-amber-900" },
  { day: 4, start: 3, span: 1, label: "Coaching perso", sub: "Sophie", tone: "bg-sky-100 text-sky-900" },
  { day: 5, start: 5, span: 1, label: "Soin visage", sub: "Institut", tone: "bg-rose-100 text-rose-900" },
];

export function CalendarMockup() {
  return (
    <div className="min-w-[720px] p-5 text-[12px]">
      <div className="flex items-center justify-between">
        <h4 className="font-serif text-xl">Rendez-vous</h4>
        <span className="rounded-full bg-primary px-3 py-1.5 text-[11px] font-medium text-primary-foreground">
          Nouveau rendez-vous
        </span>
      </div>
      <div className="mt-4 grid grid-cols-[64px_repeat(7,1fr)] overflow-hidden rounded-2xl border border-border/60">
        <div className="border-b border-r border-border/50 bg-secondary/40" />
        {DAYS.map((d) => (
          <div
            key={d}
            className="border-b border-r border-border/50 bg-secondary/40 px-2 py-2 text-center text-[10px] font-medium last:border-r-0"
          >
            {d}
          </div>
        ))}
        {HOURS.map((h, hi) => (
          <Fragment key={h}>
            <div className="border-r border-b border-border/40 px-2 py-2.5 text-[10px] text-muted-foreground">
              {h}
            </div>
            {DAYS.map((_, di) => {
              const appt = APPTS.find((a) => a.day === di && a.start === hi);
              return (
                <div key={di} className="relative border-r border-b border-border/40 last:border-r-0" style={{ minHeight: "2.1rem" }}>
                  {appt && (
                    <div
                      className={`absolute inset-x-0.5 top-0.5 rounded-md px-1.5 py-1 ${appt.tone}`}
                      style={{ height: `calc(${appt.span * 2.1}rem - 4px)` }}
                    >
                      <p className="truncate text-[10px] font-medium leading-tight">{appt.label}</p>
                      <p className="truncate text-[9px] leading-tight opacity-80">{appt.sub}</p>
                    </div>
                  )}
                </div>
              );
            })}
          </Fragment>
        ))}
      </div>
    </div>
  );
}

const PRODUCTS = [
  { name: "Serviette club", price: "900" },
  { name: "Bouteille d'eau", price: "60" },
  { name: "Gel douche", price: "800" },
  { name: "Créatine", price: "3 500" },
  { name: "Thé vert", price: "120" },
  { name: "Shampoing pro", price: "1 200" },
  { name: "Boisson protéinée", price: "600" },
  { name: "Barre énergétique", price: "250" },
];
const TICKET = [
  { name: "Shampoing pro", qty: 1, total: "1 200" },
  { name: "Créatine", qty: 1, total: "3 500" },
  { name: "Bouteille d'eau", qty: 2, total: "120" },
  { name: "Thé vert", qty: 1, total: "120" },
];

export function CaisseMockup() {
  return (
    <div className="grid min-w-[720px] grid-cols-[1fr_260px] text-[12px]">
      <div className="p-5">
        <h4 className="font-serif text-xl">Caisse</h4>
        <div className="mt-4 grid grid-cols-4 gap-2.5">
          {PRODUCTS.map((p) => (
            <div key={p.name} className="rounded-xl border border-border/60 bg-card p-3">
              <div className="mb-2 aspect-square rounded-lg bg-secondary/50" />
              <p className="truncate text-[11px] font-medium">{p.name}</p>
              <p className="text-[10px] text-muted-foreground">{p.price} DA</p>
            </div>
          ))}
        </div>
      </div>
      <div className="flex flex-col border-l border-border/60 bg-secondary/20 p-4">
        <p className="text-xs font-medium">Ticket #1042</p>
        <ul className="mt-3 flex-1 space-y-2.5">
          {TICKET.map((t) => (
            <li key={t.name} className="flex items-center justify-between text-[11px]">
              <span className="text-muted-foreground">
                {t.qty} × {t.name}
              </span>
              <span>{t.total} DA</span>
            </li>
          ))}
        </ul>
        <div className="mt-3 space-y-1 border-t border-border/60 pt-3 text-[11px]">
          <div className="flex justify-between text-muted-foreground">
            <span>Sous-total</span>
            <span>4 940 DA</span>
          </div>
          <div className="flex justify-between font-medium">
            <span>Total</span>
            <span>4 940 DA</span>
          </div>
        </div>
        <button
          type="button"
          className="mt-3 rounded-full bg-primary py-2 text-center text-[11px] font-medium text-primary-foreground"
        >
          Encaisser
        </button>
      </div>
    </div>
  );
}
