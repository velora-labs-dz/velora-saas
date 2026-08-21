import { Building2, Lock, Search, ShieldCheck } from "lucide-react";
import { useReveal } from "./useReveal";
import { Reveal } from "./Reveal";

const PILLARS = [
  {
    icon: Building2,
    title: "Multi-organisations",
    text: "Chaque établissement dispose de son espace isolé, de sa marque et de ses paramètres — devise, fuseau horaire, coordonnées.",
  },
  {
    icon: ShieldCheck,
    title: "Rôles & permissions",
    text: "Propriétaire, admin, staff, lecteur. Chaque action passe par une autorisation — aucune exception.",
  },
  {
    icon: Search,
    title: "Recherche globale",
    text: "Trouvez un client, un abonnement ou un rendez-vous instantanément — dans votre organisation uniquement.",
  },
  {
    icon: Lock,
    title: "Isolation stricte",
    text: "Les données ne quittent jamais l'organisation : chaque requête est filtrée et vérifiée côté serveur.",
  },
];

export function PlatformSection() {
  const heading = useReveal();

  return (
    <section id="plateforme" className="border-b border-border/60">
      <div className="mx-auto max-w-6xl px-6 py-24">
        <div ref={heading.ref} className={`max-w-2xl ${heading.className}`}>
          <p className="eyebrow">La plateforme</p>
          <h2 className="mt-4 font-serif text-3xl leading-tight md:text-5xl">
            Une plateforme, autant d'organisations que nécessaire
          </h2>
          <p className="mt-4 text-sm text-muted-foreground md:text-base">
            Velora sépare vos établissements, vos équipes et vos droits d'accès sans dupliquer les outils.
          </p>
        </div>
        <div className="mt-14 grid gap-px overflow-hidden rounded-3xl border border-border/70 bg-border/60 sm:grid-cols-2">
          {PILLARS.map((p, i) => (
            <Reveal key={p.title} delay={i * 60} className="bg-card p-8">
              <span className="flex size-11 items-center justify-center rounded-2xl bg-sand/70">
                <p.icon className="size-[18px] text-foreground/70" />
              </span>
              <h3 className="mt-6 font-serif text-2xl">{p.title}</h3>
              <p className="mt-3 text-sm leading-relaxed text-muted-foreground">{p.text}</p>
            </Reveal>
          ))}
        </div>
      </div>
    </section>
  );
}
