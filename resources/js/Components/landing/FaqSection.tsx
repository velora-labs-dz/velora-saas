import { Link } from "@inertiajs/react";
import { ArrowRight, ChevronDown } from "lucide-react";
import { useReveal } from "./useReveal";

const FAQ = [
  {
    q: "Comment démarre l'essai gratuit ?",
    a: "Créez votre compte, choisissez votre type d'activité : vous disposez de 15 jours complets, sans carte bancaire ni engagement. Vos données sont conservées si vous décidez de vous abonner ensuite.",
  },
  {
    q: "Quels moyens de paiement sont acceptés en Algérie ?",
    a: "Carte CIB, carte Edahabia (Algérie Poste), BaridiMob, versement CCP, virement bancaire ou espèces. Vous déclarez le paiement depuis votre espace et l'activation est faite dès la confirmation.",
  },
  {
    q: "Que se passe-t-il à la fin de l'essai ?",
    a: "Rien n'est supprimé : vos données restent intactes. Sur la formule Free, clients, abonnements et rendez-vous restent utilisables sans limite de durée.",
  },
  {
    q: "Mes données sont-elles isolées des autres clients ?",
    a: "Oui. Chaque organisation possède son espace : chaque requête est filtrée et vérifiée côté serveur — un utilisateur ne peut voir que les données de son organisation.",
  },
  {
    q: "Puis-je gérer plusieurs établissements ?",
    a: "Oui, avec la formule Enterprise. Vous créez autant d'organisations que nécessaire et basculez entre elles, avec des droits distincts par site.",
  },
  {
    q: "Peut-on limiter ce que voit chaque employé ?",
    a: "Oui. Les rôles Owner, Admin, Staff et Viewer définissent les actions autorisées, appliqués par organisation.",
  },
  {
    q: "Velora fonctionne-t-il pour un spa ou un institut de beauté ?",
    a: "Oui. La plateforme est pensée pour les salles de sport, spas et instituts de beauté : la navigation et les modules s'adaptent à votre activité.",
  },
  {
    q: "Est-ce utilisable sur téléphone à l'accueil ?",
    a: "Oui, l'interface est responsive : check-in, encaissement, recherche et prise de rendez-vous fonctionnent sur mobile et tablette.",
  },
];

export function FaqSection() {
  const left = useReveal();
  const right = useReveal(100);

  return (
    <section id="faq" className="border-t border-border/60">
      <div className="mx-auto max-w-6xl px-6 py-24">
        <div className="grid gap-12 md:grid-cols-[0.8fr_1.2fr]">
          <div ref={left.ref} className={left.className}>
            <p className="eyebrow">FAQ</p>
            <h2 className="mt-4 font-serif text-3xl leading-tight md:text-5xl">Questions fréquentes</h2>
            <p className="mt-4 text-sm leading-relaxed text-muted-foreground">
              Une autre question ? Écrivez-nous depuis votre espace ou contactez l'équipe Velora avant de
              créer votre organisation.
            </p>
            <Link
              href="/register"
              className="mt-7 inline-flex items-center gap-2 rounded-full border border-border px-6 py-3 text-sm font-medium transition hover:bg-secondary"
            >
              Essayer maintenant <ArrowRight className="size-4" />
            </Link>
          </div>

          <div ref={right.ref} className={`divide-y divide-border/60 border-y border-border/60 ${right.className}`}>
            {FAQ.map((item) => (
              <details key={item.q} className="group py-5">
                <summary className="flex cursor-pointer list-none items-center justify-between gap-6 text-left text-base font-medium [&::-webkit-details-marker]:hidden">
                  {item.q}
                  <ChevronDown className="size-4 shrink-0 text-muted-foreground transition group-open:rotate-180" />
                </summary>
                <p className="mt-3 max-w-2xl text-sm leading-relaxed text-muted-foreground">{item.a}</p>
              </details>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}
