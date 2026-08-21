import { Head } from "@inertiajs/react";
import { Header } from "@/Components/landing/Header";
import { Hero } from "@/Components/landing/Hero";
import { PlatformSection } from "@/Components/landing/PlatformSection";
import { OverviewSection } from "@/Components/landing/OverviewSection";
import { ModulesSection } from "@/Components/landing/ModulesSection";
import { PricingSection } from "@/Components/landing/PricingSection";
import { FaqSection } from "@/Components/landing/FaqSection";
import { ContactSection } from "@/Components/landing/ContactSection";
import { CtaFooter } from "@/Components/landing/CtaFooter";
import { FloatingContact } from "@/Components/landing/FloatingContact";

const TITLE = "Velora — Logiciel de gestion multi-tenant pour clubs, spas et salles de sport en Algérie";
const DESCRIPTION =
  "Logiciel de gestion conçu pour l'Algérie : clients, abonnements, rendez-vous, caisse et attendance en dinars, avec paiement par carte CIB, Edahabia, BaridiMob ou CCP.";

export default function Home() {
  return (
    <div className="min-h-screen bg-background text-foreground">
      <Head title={TITLE}>
        <meta name="description" content={DESCRIPTION} />
      </Head>

      <Header />

      <main>
        <Hero />
        <PlatformSection />
        <OverviewSection />
        <ModulesSection />
        <PricingSection />
        <FaqSection />
        <ContactSection />
        <CtaFooter />
      </main>

      <FloatingContact />
    </div>
  );
}
