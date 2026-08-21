import { BadgeCheck, Blocks, CalendarDays, Gauge, Users, Wallet } from "lucide-react";
import { useReveal } from "./useReveal";
import { Reveal } from "./Reveal";

const MODULES = [
  { icon: Users, title: "Clients & passagers", text: "Fiches 360°, historique complet, coordonnées centralisées." },
  { icon: BadgeCheck, title: "Abonnements", text: "Catalogue de formules, échéances, gels et alertes." },
  { icon: CalendarDays, title: "Rendez-vous", text: "Créneaux, staff, service et vue calendrier." },
  { icon: Wallet, title: "Caisse & paiements", text: "Encaissements, tickets, journal de caisse." },
  { icon: Blocks, title: "Attendance", text: "Check-in / check-out manuel, historique de présence." },
  { icon: Gauge, title: "Pilotage", text: "Tableaux de bord et rapports, organisation par organisation." },
];

export function ModulesSection() {
  const heading = useReveal();

  return (
    <section id="modules" className="border-b border-border/60">
      <div className="mx-auto max-w-6xl px-6 py-24">
        <div ref={heading.ref} className={`max-w-2xl ${heading.className}`}>
          <p className="eyebrow">Modules</p>
          <h2 className="mt-4 font-serif text-3xl leading-tight md:text-5xl">
            Activez uniquement ce dont vous avez besoin
          </h2>
          <p className="mt-4 text-sm text-muted-foreground md:text-base">
            La navigation s'adapte automatiquement à la configuration de chaque organisation et aux droits
            de chaque utilisateur.
          </p>
        </div>
        <div className="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
          {MODULES.map((m, i) => (
            <Reveal
              key={m.title}
              delay={i * 50}
              className="group rounded-3xl border border-border/70 bg-card p-7 transition hover:border-gold/60"
            >
              <m.icon className="size-5 text-gold" />
              <h3 className="mt-6 font-serif text-2xl">{m.title}</h3>
              <p className="mt-2 text-sm text-muted-foreground">{m.text}</p>
            </Reveal>
          ))}
        </div>
      </div>
    </section>
  );
}
