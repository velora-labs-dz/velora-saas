import { useReveal } from "./useReveal";
import { Reveal } from "./Reveal";

const SHOTS = [
  {
    label: "Tableau de bord",
    text: "Chiffre d'affaires, adhésions, occupation — la santé de l'établissement en un écran.",
    src: "/images/landing/shot-dashboard.jpg",
    alt: "Tableau de bord Velora",
  },
  {
    label: "Rendez-vous",
    text: "Vue semaine, créneaux de 30 minutes, filtres praticien et prestation.",
    src: "/images/landing/shot-calendar.jpg",
    alt: "Planning des rendez-vous Velora",
  },
  {
    label: "Caisse",
    text: "Encaissement rapide, remises, tickets et journal de caisse quotidien.",
    src: "/images/landing/shot-caisse.jpg",
    alt: "Caisse Velora",
  },
];

export function OverviewSection() {
  const heading = useReveal();

  return (
    <section id="apercu" className="border-b border-border/60 bg-secondary/30">
      <div className="mx-auto max-w-6xl px-6 py-24">
        <div ref={heading.ref} className={`max-w-2xl ${heading.className}`}>
          <p className="eyebrow">Aperçu du système</p>
          <h2 className="mt-4 font-serif text-3xl leading-tight md:text-5xl">
            Des écrans pensés pour l'accueil, pas pour les tableurs
          </h2>
        </div>

        <div className="mt-16 space-y-24">
          {SHOTS.map((shot, i) => (
            <Reveal
              key={shot.label}
              className={
                "grid items-center gap-10 md:grid-cols-[0.85fr_1.15fr] " +
                (i % 2 === 1 ? "md:[&>figure]:order-first" : "")
              }
            >
              <div>
                <p className="text-[11px] uppercase tracking-[0.28em] text-muted-foreground">
                  0{i + 1} — Module
                </p>
                <h3 className="mt-3 font-serif text-3xl md:text-4xl">{shot.label}</h3>
                <p className="mt-4 max-w-sm text-sm leading-relaxed text-muted-foreground">{shot.text}</p>
                <div className="mt-6 hairline max-w-[8rem]" />
              </div>
              <figure className="overflow-hidden rounded-3xl border border-border/70 bg-card shadow-soft transition duration-700 hover:scale-[1.01]">
                <img src={shot.src} alt={shot.alt} className="w-full" loading="lazy" />
              </figure>
            </Reveal>
          ))}
        </div>
      </div>
    </section>
  );
}
