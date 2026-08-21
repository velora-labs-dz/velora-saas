import { useState } from "react";
import { Link } from "@inertiajs/react";
import { Check, Minus } from "lucide-react";
import { useReveal } from "./useReveal";
import { Reveal } from "./Reveal";

const money = (n: number) => new Intl.NumberFormat("fr-DZ").format(n) + " DZD";

type Plan = {
  name: string;
  year: number;
  month: number;
  hint: string;
  desc: string;
  features: string[];
  highlight?: boolean;
  cta?: string;
  ctaNote?: string;
  free?: boolean;
};

const PLANS: Plan[] = [
  {
    name: "Free",
    year: 0,
    month: 0,
    free: true,
    hint: "Pour démarrer",
    desc: "L'essentiel pour gérer vos clients et leurs abonnements, sans frais.",
    features: [
      "1 organisation",
      "Jusqu'à 2 comptes staff",
      "Clients & passagers",
      "Abonnements & échéances",
      "Rendez-vous et créneaux",
      "Support communautaire",
    ],
    cta: "Créer un compte gratuit",
    ctaNote: "Gratuit à vie · sans carte bancaire",
  },
  {
    name: "Starter",
    year: 15000,
    month: 1500,
    hint: "Petites structures",
    desc: "Tout le Free, avec un nombre de clients illimité pour un établissement.",
    features: [
      "1 organisation",
      "Jusqu'à 5 comptes staff",
      "Clients illimités",
      "Abonnements, échéances & rendez-vous",
      "Attendance",
      "Support par e-mail",
    ],
  },
  {
    name: "Professional",
    year: 45000,
    month: 4500,
    hint: "Le plus choisi",
    desc: "Ajoute la caisse, les paiements et les permissions avancées.",
    features: [
      "1 organisation",
      "Jusqu'à 15 comptes staff",
      "Caisse & journal quotidien",
      "Paiements manuels",
      "Rôles & permissions avancés",
      "Support prioritaire WhatsApp",
    ],
    highlight: true,
  },
  {
    name: "Enterprise",
    year: 120000,
    month: 12000,
    hint: "Groupes multi-sites",
    desc: "Multi-organisations, rapports avancés et accompagnement dédié.",
    features: [
      "Plusieurs organisations",
      "Comptes staff illimités",
      "Rapports avancés",
      "Onboarding accompagné",
      "Accès API & exports",
    ],
  },
];

const MATRIX_ROWS: { label: string; values: (boolean | string)[] }[] = [
  { label: "Clients & passagers", values: [true, true, true, true] },
  { label: "Abonnements & formules", values: [true, true, true, true] },
  { label: "Rendez-vous & créneaux", values: [true, true, true, true] },
  { label: "Clients illimités", values: [false, true, true, true] },
  { label: "Caisse & encaissements", values: [false, false, true, true] },
  { label: "Paiements manuels", values: [false, false, true, true] },
  { label: "Rôles & permissions avancés", values: [false, false, true, true] },
  { label: "Rapports avancés", values: [false, false, false, true] },
  { label: "Multi-organisations", values: [false, false, false, true] },
  { label: "Accès API & exports", values: [false, false, false, true] },
  { label: "Comptes staff inclus", values: ["2", "5", "15", "Illimités"] },
  { label: "Support", values: ["Communauté", "E-mail", "WhatsApp prioritaire", "Dédié"] },
];

const BILLING_DETAILS = [
  { label: "12 mois", note: "Tarif annuel — le plus avantageux" },
  { label: "6 mois", note: "−10 % sur le mensuel" },
  { label: "3 mois", note: "−5 % sur le mensuel" },
  { label: "1 mois", note: "Sans engagement" },
];

export function PricingSection() {
  const [billing, setBilling] = useState<"year" | "month">("year");
  const heading = useReveal();
  const toggle = useReveal(60);
  const matrix = useReveal();

  return (
    <section id="tarifs" className="bg-secondary/30">
      <div className="mx-auto max-w-6xl px-6 py-24">
        <div ref={heading.ref} className={heading.className}>
          <p className="eyebrow">Tarifs</p>
          <h2 className="mt-4 font-serif text-3xl md:text-5xl">Commencez petit, grandissez ensuite</h2>
          <p className="mt-4 max-w-2xl text-sm leading-relaxed text-muted-foreground">
            Tarifs en dinars algériens (DZD), sans engagement. Réglez par carte CIB, carte Edahabia
            (Algérie Poste), BaridiMob, versement CCP, virement bancaire ou en espèces — votre espace est
            activé dès la confirmation du paiement.
          </p>
          <div className="mt-5 flex flex-wrap gap-2">
            {["Carte CIB", "Carte Edahabia", "BaridiMob", "CCP", "Virement", "Espèces"].map((m) => (
              <span
                key={m}
                className="rounded-full border border-border/70 bg-card px-3.5 py-1.5 text-xs text-muted-foreground"
              >
                {m}
              </span>
            ))}
          </div>
        </div>

        <div ref={toggle.ref} className={`mt-8 flex items-center gap-3 ${toggle.className}`}>
          <div className="inline-flex rounded-full border border-border/70 bg-card p-1">
            {(
              [
                { key: "year", label: "Annuel" },
                { key: "month", label: "Mensuel" },
              ] as const
            ).map((opt) => (
              <button
                key={opt.key}
                type="button"
                onClick={() => setBilling(opt.key)}
                className={
                  "rounded-full px-4 py-1.5 text-xs font-medium transition " +
                  (billing === opt.key
                    ? "bg-primary text-primary-foreground"
                    : "text-muted-foreground hover:text-foreground")
                }
              >
                {opt.label}
              </button>
            ))}
          </div>
          <span className="text-xs text-muted-foreground">
            Le paiement annuel revient environ 20 % moins cher que le mensuel.
          </span>
        </div>

        <div className="mt-10 grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
          {PLANS.map((plan, i) => (
            <Reveal
              key={plan.name}
              delay={i * 60}
              className={
                "relative flex flex-col rounded-3xl border bg-card p-8 " +
                (plan.highlight ? "border-gold shadow-soft" : "border-border/70")
              }
            >
              {plan.highlight && (
                <span className="absolute -top-3 left-8 rounded-full bg-primary px-3 py-1 text-[10px] uppercase tracking-[0.2em] text-primary-foreground">
                  Recommandé
                </span>
              )}
              <p className="text-[11px] uppercase tracking-[0.24em] text-muted-foreground">{plan.hint}</p>
              <h3 className="mt-4 font-serif text-3xl">{plan.name}</h3>
              <p className="mt-3 flex items-baseline gap-1.5">
                <span className="font-serif text-3xl">
                  {plan.free ? "0 DZD" : money(billing === "year" ? plan.year : plan.month)}
                </span>
                <span className="text-xs text-muted-foreground">{billing === "year" ? "/ an" : "/ mois"}</span>
              </p>
              <p className="mt-1 text-[11px] text-muted-foreground">
                {plan.free
                  ? "Gratuit à vie, sans carte bancaire"
                  : billing === "year"
                    ? `≈ ${money(Math.round(plan.year / 12))} / mois si payé annuellement`
                    : `soit ${money(plan.month * 12)} / an — économisez ${money(plan.month * 12 - plan.year)} en réglant à l'année`}
              </p>
              <p className="mt-3 text-sm leading-relaxed text-muted-foreground">{plan.desc}</p>
              <ul className="mt-7 space-y-3 text-sm text-muted-foreground">
                {plan.features.map((f) => (
                  <li key={f} className="flex items-start gap-2.5">
                    <Check className="mt-0.5 size-4 shrink-0 text-gold" />
                    {f}
                  </li>
                ))}
              </ul>
              <Link
                href="/register"
                className={
                  "mt-9 inline-flex items-center justify-center rounded-full px-5 py-3 text-sm font-medium transition " +
                  (plan.highlight
                    ? "bg-primary text-primary-foreground hover:bg-primary/90"
                    : "border border-border hover:bg-secondary")
                }
              >
                {plan.cta ?? "Démarrer l'essai"}
              </Link>
              <p className="mt-3 text-center text-[11px] text-muted-foreground">
                {plan.ctaNote ?? "15 jours gratuits · sans carte bancaire"}
              </p>
            </Reveal>
          ))}
        </div>

        <div className="mt-8 grid gap-px overflow-hidden rounded-2xl border border-border/70 bg-border/60 sm:grid-cols-4">
          {BILLING_DETAILS.map((b) => (
            <div key={b.label} className="bg-card px-5 py-4 text-center">
              <p className="text-sm font-medium">{b.label}</p>
              <p className="text-xs text-muted-foreground">{b.note}</p>
            </div>
          ))}
        </div>

        <div ref={matrix.ref} className={`mt-16 overflow-hidden rounded-3xl border border-border/70 bg-card ${matrix.className}`}>
          <div className="overflow-x-auto">
            <table className="w-full min-w-[640px] text-sm">
              <thead>
                <tr className="border-b border-border/70 text-left">
                  <th className="px-6 py-4 font-medium">Comparatif des formules</th>
                  {PLANS.map((p) => (
                    <th key={p.name} className="px-6 py-4 text-center font-medium">
                      {p.name}
                    </th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {MATRIX_ROWS.map((row) => (
                  <tr key={row.label} className="border-b border-border/50 last:border-0">
                    <td className="px-6 py-3.5 text-muted-foreground">{row.label}</td>
                    {row.values.map((v, idx) => (
                      <td key={idx} className="px-6 py-3.5 text-center">
                        {v === true ? (
                          <Check className="mx-auto size-4 text-gold" />
                        ) : v === false ? (
                          <Minus className="mx-auto size-4 text-muted-foreground/50" />
                        ) : (
                          <span className="text-muted-foreground">{v}</span>
                        )}
                      </td>
                    ))}
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>

        <p className="mt-6 text-xs leading-relaxed text-muted-foreground">
          Prix hors taxes en dinars algériens, par organisation. Changement de formule possible à tout
          moment ; le solde de la période en cours est reporté. Facture disponible dans votre espace après
          chaque règlement confirmé.
        </p>
      </div>
    </section>
  );
}
