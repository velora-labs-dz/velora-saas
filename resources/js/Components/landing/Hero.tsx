import { Link } from "@inertiajs/react";
import { ArrowRight } from "lucide-react";
import { useReveal } from "./useReveal";
import { BrowserFrame } from "./mockups";

const DESC =
  "Logiciel de gestion conçu pour l'Algérie : clients, abonnements, rendez-vous, caisse et attendance en dinars, avec paiement par carte CIB, Edahabia, BaridiMob ou CCP.";

const MARQUEE = [
  "Salles de sport",
  "Spas & hammams",
  "Instituts de beauté",
  "Clubs multi-sites",
  "Studios de coaching",
  "Centres wellness",
];

export function Hero() {
  const eyebrow = useReveal();
  const heading = useReveal(80);
  const sub = useReveal(160);
  const shot = useReveal(220);

  return (
    <section className="relative overflow-hidden">
      <img
        src="/images/landing/hero-bg.jpg"
        alt=""
        aria-hidden="true"
        className="pointer-events-none absolute inset-x-0 top-0 h-[46rem] w-full select-none object-cover opacity-70 [mask-image:linear-gradient(to_bottom,black,transparent_88%)]"
      />
      <div
        className="pointer-events-none absolute -left-40 -top-56 h-[36rem] w-[36rem] rounded-full opacity-70 blur-3xl"
        style={{ background: "radial-gradient(circle, var(--gold-soft), transparent 65%)" }}
      />
      <div
        className="pointer-events-none absolute -right-40 top-24 h-[30rem] w-[30rem] rounded-full opacity-60 blur-3xl"
        style={{ background: "radial-gradient(circle, var(--sand), transparent 68%)" }}
      />

      <div className="relative mx-auto max-w-6xl px-6 pb-10 pt-24 md:pt-32">
        <p
          ref={eyebrow.ref}
          className={`mb-8 inline-flex items-center gap-2 rounded-full border border-border/70 bg-card/60 px-4 py-1.5 text-[11px] uppercase tracking-[0.28em] text-muted-foreground ${eyebrow.className}`}
        >
          Logiciel de gestion multi-tenant · Algérie
        </p>
        <h1
          ref={heading.ref}
          className={`max-w-4xl font-serif text-[clamp(2.9rem,7vw,6rem)] leading-[0.95] tracking-tight ${heading.className}`}
        >
          Gérez tout votre établissement
          <span className="block text-gradient-gold">depuis une seule plateforme.</span>
        </h1>
        <div
          ref={sub.ref}
          className={`mt-10 grid gap-10 border-t border-border/60 pt-8 md:grid-cols-[1.1fr_0.9fr] ${sub.className}`}
        >
          <p className="max-w-xl text-base leading-relaxed text-muted-foreground md:text-lg">{DESC}</p>
          <div className="flex flex-wrap items-start gap-3 md:justify-end">
            <Link
              href="/register"
              className="inline-flex items-center gap-2 rounded-full bg-primary px-7 py-3.5 text-sm font-medium text-primary-foreground transition hover:bg-primary/90"
            >
              Démarrer 15 jours gratuits <ArrowRight className="size-4" />
            </Link>
            <Link
              href="/login"
              className="inline-flex items-center rounded-full border border-border px-7 py-3.5 text-sm font-medium transition hover:bg-secondary"
            >
              Accéder à mon espace
            </Link>
            <a
              href="#apercu"
              className="inline-flex items-center rounded-full border border-border px-7 py-3.5 text-sm font-medium transition hover:bg-secondary"
            >
              Voir le produit
            </a>
          </div>
        </div>
      </div>

      <div className="relative mx-auto max-w-6xl px-6 pb-20">
        <div ref={shot.ref} className={shot.className}>
          <BrowserFrame url="velora.app / tableau-de-bord">
            <img
              src="/images/landing/shot-dashboard.jpg"
              alt="Tableau de bord Velora"
              className="w-full"
            />
          </BrowserFrame>
        </div>
      </div>

      <div className="overflow-hidden border-y border-border/60 bg-secondary/40 py-4">
        <div className="flex gap-10 whitespace-nowrap text-[11px] uppercase tracking-[0.34em] text-muted-foreground [animation:marquee_32s_linear_infinite]">
          {[...MARQUEE, ...MARQUEE].map((item, i) => (
            <span key={item + i} className="flex items-center gap-10">
              {item}
              <span className="size-1 rounded-full bg-gold" />
            </span>
          ))}
        </div>
      </div>
    </section>
  );
}
