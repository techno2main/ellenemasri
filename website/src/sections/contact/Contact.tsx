import { CONTACT_EMAIL } from "@/lib/site-content";
import { SocialCtaButtons } from "@/sections/social-cta/SocialCtaButtons";
import { SOCIAL_CTA_ITEMS } from "@/sections/social-cta/socialCtaItems";

export function Contact() {
  return (
    <section id="contact" className="em-contact relative px-6 pt-0 pb-20 md:px-12 md:pb-28">
      <div className="mx-auto max-w-5xl">
        <div className="grid gap-16 md:grid-cols-12 md:gap-12">
          <div className="md:col-span-5">
            <span className="em-eyebrow">— Get in touch</span>
            <h2 className="em-serif mt-6 text-[clamp(2.5rem,6vw,4.5rem)] leading-none tracking-[-0.02em]">
              For bookings,
              <br />
              <span className="italic text-primary">collaborations</span>
              <br />& press.
            </h2>
          </div>

          <div className="md:col-span-7 md:pt-4">
            <p className="max-w-md text-base font-light leading-relaxed text-muted-foreground md:text-lg">
              The full website is being shaped. In the meantime, reach out directly or follow the journey across platforms.
            </p>

            <div className="mt-12 flex items-baseline gap-4">
              <span className="em-eyebrow shrink-0">Email</span>
              <a
                href={`mailto:${CONTACT_EMAIL}?subject=contact%20from%20the%20website`}
                className="em-serif em-link text-xl tracking-tight md:text-3xl"
              >
                {CONTACT_EMAIL}
              </a>
            </div>

            <div className="mt-12">
              <span className="em-eyebrow">
                <span className="text-primary">Listen</span>
                <span className="text-white"> & Follow</span>
              </span>
              <SocialCtaButtons items={SOCIAL_CTA_ITEMS} />
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
