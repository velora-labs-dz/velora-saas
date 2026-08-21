import { Link } from "@inertiajs/react";
import { ArrowRight } from "lucide-react";
import { VeloraLogo } from "./Logo";

const NAV = [
  { label: "Plateforme", href: "#plateforme" },
  { label: "Aperçu", href: "#apercu" },
  { label: "Modules", href: "#modules" },
  { label: "Tarifs", href: "#tarifs" },
  { label: "FAQ", href: "#faq" },
  { label: "Contact", href: "#contact" },
];

export function Header() {
  return (
    <header className="sticky top-0 z-40 border-b border-border/50 bg-background/70 backdrop-blur-xl">
      <div className="mx-auto flex h-20 max-w-6xl items-center justify-between px-6">
        <Link href="/" className="flex items-center gap-3">
          <VeloraLogo markClassName="h-10 w-10" />
        </Link>

        <nav className="hidden items-center gap-9 text-sm text-muted-foreground md:flex">
          {NAV.map((item) => (
            <a key={item.label} href={item.href} className="transition hover:text-foreground">
              {item.label}
            </a>
          ))}
        </nav>

        <div className="flex items-center gap-2">
          <Link
            href="/login"
            className="hidden rounded-full border border-border px-5 py-2.5 text-sm font-medium transition hover:bg-secondary sm:inline-flex"
          >
            Se connecter
          </Link>
          <Link
            href="/register"
            className="inline-flex items-center gap-2 rounded-full bg-primary px-5 py-2.5 text-sm font-medium text-primary-foreground transition hover:bg-primary/90"
          >
            Essai gratuit <ArrowRight className="size-3.5" />
          </Link>
        </div>
      </div>
    </header>
  );
}
