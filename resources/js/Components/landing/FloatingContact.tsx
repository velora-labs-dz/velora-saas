import { useCallback, useEffect, useRef, useState } from "react";
import { Mail, MessageCircle, Phone, X } from "lucide-react";

export const CONTACT_PHONE = "+213540166358";
export const CONTACT_PHONE_DISPLAY = "+213 540 16 63 58";
export const WHATSAPP_URL =
  "https://wa.me/213540166358?text=" + encodeURIComponent("Bonjour Velora, je souhaite des informations.");

const ITEMS = [
  { icon: Phone, label: "Téléphone", value: CONTACT_PHONE_DISPLAY, href: `tel:${CONTACT_PHONE}` },
  { icon: MessageCircle, label: "WhatsApp", value: CONTACT_PHONE_DISPLAY, href: WHATSAPP_URL },
  { icon: Mail, label: "Formulaire de contact", value: "Écrire une demande détaillée", href: "#contact" },
];

export function FloatingContact() {
  const [open, setOpen] = useState(false);
  const panelRef = useRef<HTMLDivElement>(null);
  const buttonRef = useRef<HTMLButtonElement>(null);

  const close = useCallback((returnFocus = true) => {
    setOpen(false);
    if (returnFocus) buttonRef.current?.focus();
  }, []);

  useEffect(() => {
    if (!open) return;
    const onKey = (e: KeyboardEvent) => {
      if (e.key === "Escape") close();
    };
    const onPointer = (e: PointerEvent) => {
      const target = e.target as Node;
      if (panelRef.current?.contains(target) || buttonRef.current?.contains(target)) return;
      close(false);
    };
    document.addEventListener("keydown", onKey);
    document.addEventListener("pointerdown", onPointer);
    return () => {
      document.removeEventListener("keydown", onKey);
      document.removeEventListener("pointerdown", onPointer);
    };
  }, [open, close]);

  return (
    <div className="fixed bottom-6 right-6 z-50 flex flex-col items-end gap-3">
      {open && (
        <div
          ref={panelRef}
          role="dialog"
          aria-labelledby="floating-contact-title"
          className="w-[19rem] origin-bottom-right overflow-hidden rounded-3xl border border-border/70 bg-card shadow-soft"
        >
          <div className="bg-secondary/60 px-5 py-4">
            <p className="text-[11px] uppercase tracking-[0.24em] text-muted-foreground">Velora Algérie</p>
            <p id="floating-contact-title" className="mt-1 font-serif text-lg">
              Parlons de votre établissement
            </p>
          </div>
          <ul className="divide-y divide-border/60">
            {ITEMS.map((item) => {
              const external = item.href.startsWith("http");
              return (
                <li key={item.label}>
                  <a
                    href={item.href}
                    {...(external ? { target: "_blank", rel: "noreferrer" } : {})}
                    onClick={() => close(false)}
                    className="flex items-center gap-3 px-5 py-3.5 transition hover:bg-secondary/50"
                  >
                    <span className="flex size-9 items-center justify-center rounded-full bg-secondary text-foreground/80">
                      <item.icon className="size-4" />
                    </span>
                    <span className="min-w-0">
                      <span className="block text-sm font-medium">{item.label}</span>
                      <span className="block truncate text-xs text-muted-foreground">{item.value}</span>
                    </span>
                  </a>
                </li>
              );
            })}
          </ul>
        </div>
      )}

      <button
        ref={buttonRef}
        type="button"
        onClick={() => (open ? close(false) : setOpen(true))}
        aria-label={open ? "Fermer le menu de contact" : "Nous contacter"}
        aria-expanded={open}
        className="relative flex size-14 items-center justify-center rounded-full bg-primary text-primary-foreground shadow-soft transition hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background"
      >
        {!open && <span className="absolute inset-0 animate-ping rounded-full bg-primary/40" aria-hidden />}
        {open ? <X className="size-6" /> : <MessageCircle className="size-6" />}
      </button>
    </div>
  );
}
