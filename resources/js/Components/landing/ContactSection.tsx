import { useState, type FormEvent } from "react";
import { Check, MessageCircle, Phone, Send } from "lucide-react";
import { useReveal } from "./useReveal";
import { CONTACT_PHONE_DISPLAY, WHATSAPP_URL, CONTACT_PHONE } from "./FloatingContact";

const BUSINESS_TYPES = ["Salle de sport", "SPA & bien-être", "Institut de beauté", "Club multi-activités", "Autre"];
const TEAM_SIZES = ["1 – 2", "3 – 10", "11 – 25", "26 – 50", "50 +"];
const SUBJECTS = [
  "Demande de démonstration",
  "Question sur les tarifs",
  "Migration de mes données",
  "Support technique",
  "Partenariat / revendeur",
];
const CHANNELS = ["Téléphone", "WhatsApp", "E-mail"];

/**
 * Presentational only for now, as requested — client-side state and a friendly
 * confirmation state, no network call. When you're ready to wire it up, replace
 * `handleSubmit` with an Inertia `useForm().post(route('contact.store'))`.
 */
export function ContactSection() {
  const reveal = useReveal();
  const [message, setMessage] = useState("");
  const [sent, setSent] = useState(false);

  const handleSubmit = (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setSent(true);
  };

  return (
    <section id="contact" className="border-t border-border/60">
      <div className="mx-auto max-w-6xl px-6 py-24">
        <div ref={reveal.ref} className={`grid gap-12 lg:grid-cols-[0.85fr_1.15fr] ${reveal.className}`}>
          <div>
            <p className="eyebrow">Contact</p>
            <h2 className="mt-4 font-serif text-3xl leading-tight md:text-5xl">
              Parlons de votre club, spa ou institut
            </h2>
            <p className="mt-4 max-w-sm text-sm leading-relaxed text-muted-foreground">
              Démonstration guidée, reprise de vos données existantes, choix des modules et
              accompagnement en dinars : notre équipe en Algérie vous répond sous 24 h ouvrées.
            </p>

            <div className="mt-8 space-y-3">
              <a
                href={`tel:${CONTACT_PHONE}`}
                className="flex items-center gap-3 rounded-2xl border border-border/70 bg-card px-5 py-4 transition hover:border-gold/60"
              >
                <span className="flex size-9 items-center justify-center rounded-full bg-secondary">
                  <Phone className="size-4" />
                </span>
                <span>
                  <span className="block text-sm font-medium">Téléphone</span>
                  <span className="block text-xs text-muted-foreground">{CONTACT_PHONE_DISPLAY}</span>
                </span>
              </a>
              <a
                href={WHATSAPP_URL}
                target="_blank"
                rel="noreferrer"
                className="flex items-center gap-3 rounded-2xl border border-border/70 bg-card px-5 py-4 transition hover:border-gold/60"
              >
                <span className="flex size-9 items-center justify-center rounded-full bg-secondary">
                  <MessageCircle className="size-4" />
                </span>
                <span>
                  <span className="block text-sm font-medium">WhatsApp</span>
                  <span className="block text-xs text-muted-foreground">{CONTACT_PHONE_DISPLAY}</span>
                </span>
              </a>
            </div>
            <p className="mt-5 text-xs leading-relaxed text-muted-foreground">
              Formulaire protégé contre le spam. Vos données restent confidentielles et ne sont jamais
              revendues.
            </p>
          </div>

          <div className="rounded-3xl border border-border/70 bg-card p-8">
            {sent ? (
              <div className="flex flex-col items-center justify-center py-16 text-center">
                <span className="flex size-14 items-center justify-center rounded-full bg-primary/12 text-primary">
                  <Check className="size-7" />
                </span>
                <h3 className="mt-5 font-serif text-2xl">Demande bien reçue</h3>
                <p className="mt-2 max-w-sm text-sm leading-relaxed text-muted-foreground">
                  Merci ! Notre équipe en Algérie étudie votre projet et vous répond sous 24 h ouvrées.
                </p>
                <button
                  type="button"
                  onClick={() => setSent(false)}
                  className="mt-6 rounded-full border border-border px-5 py-2.5 text-sm font-medium transition hover:bg-secondary"
                >
                  Envoyer une autre demande
                </button>
              </div>
            ) : (
              <form onSubmit={handleSubmit} className="grid gap-5 sm:grid-cols-2">
                <p className="text-xs text-muted-foreground sm:col-span-2">
                  Tous les champs marqués d'un astérisque (*) sont obligatoires.
                </p>

                <Field label="Nom complet" required>
                  <input type="text" required placeholder="Amine Belkacem" className="field" />
                </Field>
                <Field label="E-mail" required>
                  <input type="email" required placeholder="contact@monclub.dz" className="field" />
                </Field>
                <Field label="Téléphone" required>
                  <input type="tel" required placeholder="+213 540 16 63 58" className="field" />
                </Field>
                <Field label="Établissement" required>
                  <input type="text" required placeholder="Nom de votre établissement" className="field" />
                </Field>
                <Field label="Type d'activité" required>
                  <select required defaultValue="" className="field">
                    <option value="" disabled>
                      Choisir une activité…
                    </option>
                    {BUSINESS_TYPES.map((t) => (
                      <option key={t}>{t}</option>
                    ))}
                  </select>
                </Field>
                <Field label="Taille de l'équipe" required>
                  <select required defaultValue="" className="field">
                    <option value="" disabled>
                      Choisir…
                    </option>
                    {TEAM_SIZES.map((t) => (
                      <option key={t}>{t} personnes</option>
                    ))}
                  </select>
                </Field>
                <Field label="Sujet" required>
                  <select required defaultValue={SUBJECTS[0]} className="field">
                    {SUBJECTS.map((s) => (
                      <option key={s}>{s}</option>
                    ))}
                  </select>
                </Field>
                <Field label="Canal préféré" required>
                  <select required defaultValue="WhatsApp" className="field">
                    {CHANNELS.map((c) => (
                      <option key={c}>{c}</option>
                    ))}
                  </select>
                </Field>

                <div className="sm:col-span-2">
                  <Field label="Votre message" required>
                    <textarea
                      required
                      rows={5}
                      maxLength={4000}
                      value={message}
                      onChange={(e) => setMessage(e.target.value)}
                      placeholder="Nombre de clients, modules souhaités, outils actuels…"
                      className="field resize-none"
                    />
                  </Field>
                  <p className="mt-1.5 text-right text-[11px] text-muted-foreground">
                    {message.length} / 4000 caractères
                  </p>
                </div>

                <div className="flex flex-wrap items-center gap-4 sm:col-span-2">
                  <button
                    type="submit"
                    className="inline-flex items-center gap-2 rounded-full bg-primary px-7 py-3.5 text-sm font-medium text-primary-foreground transition hover:bg-primary/90"
                  >
                    <Send className="size-4" /> Envoyer la demande
                  </button>
                  <p className="text-xs text-muted-foreground">Réponse sous 24 h ouvrées.</p>
                </div>
              </form>
            )}
          </div>
        </div>
      </div>
    </section>
  );
}

function Field({ label, required, children }: { label: string; required?: boolean; children: React.ReactNode }) {
  return (
    <label className="block text-xs text-muted-foreground">
      {label}
      {required && <span aria-hidden> *</span>}
      <div className="mt-1.5">{children}</div>
    </label>
  );
}
