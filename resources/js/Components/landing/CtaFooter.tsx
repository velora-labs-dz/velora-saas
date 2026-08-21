import { Link } from "@inertiajs/react";
import { ArrowRight } from "lucide-react";
import { useReveal } from "./useReveal";
import { VeloraLogo, LogoMark } from "./Logo";

const FOOTER_NAV: { title: string; links: { label: string; href?: string; internal?: string }[] }[] = [
  {
    title: "Produit",
    links: [
      { label: "Plateforme", href: "#plateforme" },
      { label: "Aperçu du système", href: "#apercu" },
      { label: "Modules", href: "#modules" },
      { label: "Tarifs", href: "#tarifs" },
      { label: "Questions fréquentes", href: "#faq" },
      { label: "Contact", href: "#contact" },
    ],
  },
  {
    title: "Modules",
    links: [
      { label: "Clients & passagers", href: "#modules" },
      { label: "Abonnements", href: "#modules" },
      { label: "Rendez-vous", href: "#modules" },
      { label: "Caisse & paiements", href: "#modules" },
      { label: "Attendance", href: "#modules" },
    ],
  },
  {
    title: "Métiers",
    links: [
      { label: "Salles de sport", href: "#plateforme" },
      { label: "Spas & hammams", href: "#plateforme" },
      { label: "Instituts de beauté", href: "#plateforme" },
      { label: "Clubs multi-sites", href: "#plateforme" },
    ],
  },
  {
    title: "Compte",
    links: [
      { label: "Créer une organisation", internal: "/register" },
      { label: "Se connecter", internal: "/login" },
      { label: "Essai 15 jours", internal: "/register" },
    ],
  },
];

export function CtaFooter() {
  const cta = useReveal();

  return (
    <>
      <section className="border-t border-border/60 bg-secondary/30">
        <div ref={cta.ref} className={`mx-auto max-w-4xl px-6 py-24 text-center ${cta.className}`}>
          <LogoMark className="mx-auto h-16 w-16" />
          <h2 className="mt-8 font-serif text-3xl leading-tight md:text-5xl">
            Prêt à piloter votre établissement autrement ?
          </h2>
          <p className="mx-auto mt-5 max-w-xl text-sm leading-relaxed text-muted-foreground">
            15 jours gratuits, configuration guidée, paiement en dinars par carte CIB, Edahabia, BaridiMob
            ou CCP.
          </p>
          <Link
            href="/register"
            className="mt-10 inline-flex items-center gap-2 rounded-full bg-primary px-8 py-4 text-sm font-medium text-primary-foreground transition hover:bg-primary/90"
          >
            Créer mon organisation <ArrowRight className="size-4" />
          </Link>
        </div>
      </section>

      <footer className="border-t border-border/60">
        <div className="mx-auto max-w-6xl px-6 py-16">
          <div className="grid gap-12 md:grid-cols-[1.1fr_2.4fr]">
            <div>
              <Link href="/" className="flex items-center gap-3">
                <VeloraLogo markClassName="h-9 w-9" />
              </Link>
              <p className="mt-5 max-w-xs text-sm leading-relaxed text-muted-foreground">
                Logiciel de gestion multi-tenant pour salles de sport, spas et instituts en Algérie. Tarifs
                en dinars, données isolées par organisation.
              </p>
              <div className="mt-6 flex flex-wrap gap-2">
                {["CIB", "Edahabia", "BaridiMob", "CCP", "Virement", "Espèces"].map((m) => (
                  <span key={m} className="rounded-full border border-border/70 px-3 py-1 text-[11px] text-muted-foreground">
                    {m}
                  </span>
                ))}
              </div>
            </div>

            <nav aria-label="Plan du site" className="grid grid-cols-2 gap-8 sm:grid-cols-4">
              {FOOTER_NAV.map((col) => (
                <div key={col.title}>
                  <p className="text-[11px] uppercase tracking-[0.24em] text-muted-foreground">{col.title}</p>
                  <ul className="mt-4 space-y-2.5 text-sm">
                    {col.links.map((l) => (
                      <li key={l.label}>
                        {l.internal ? (
                          <Link href={l.internal} className="text-muted-foreground transition hover:text-foreground">
                            {l.label}
                          </Link>
                        ) : (
                          <a href={l.href} className="text-muted-foreground transition hover:text-foreground">
                            {l.label}
                          </a>
                        )}
                      </li>
                    ))}
                  </ul>
                </div>
              ))}
            </nav>
          </div>

          <div className="mt-14 flex flex-col items-center justify-between gap-3 border-t border-border/60 pt-8 text-sm text-muted-foreground sm:flex-row">
            <span>© {new Date().getFullYear()} Velora — logiciel de gestion pour clubs & instituts, Algérie.</span>
            <div className="flex items-center gap-6">
              <a href="#faq" className="transition hover:text-foreground">
                FAQ
              </a>
              <a href="#tarifs" className="transition hover:text-foreground">
                Tarifs
              </a>
              <Link href="/login" className="transition hover:text-foreground">
                Espace client
              </Link>
            </div>
          </div>
        </div>
      </footer>
    </>
  );
}
