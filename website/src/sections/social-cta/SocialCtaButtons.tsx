import type { ReactNode } from "react";
import type { SocialCtaItem } from "./socialCtaItems";

type SocialCtaButtonsProps = {
  items: SocialCtaItem[];
};

export function SocialCtaButtons({ items }: SocialCtaButtonsProps) {
  return (
    <div className="mt-6 flex flex-wrap justify-start gap-5 text-primary">
      {items.map((item) => (
        <SocialCtaLink key={item.id} id={item.id} href={item.href} title={item.title} label={item.label}>
          {item.icon}
        </SocialCtaLink>
      ))}
    </div>
  );
}

function SocialCtaLink({ href, title, label, id, children }: { href: string; title: string; label: string; id: string; children: ReactNode }) {
  const isSocialIcon = id === "instagram" || id === "tiktok";

  return (
    <a
      href={href}
      target="_blank"
      rel="noopener noreferrer"
      title={title}
      aria-label={label}
      className={`inline-flex items-center justify-center transition-colors [&_svg]:h-6 [&_svg]:w-6 ${
        isSocialIcon ? "text-white hover:text-primary" : "hover:text-white"
      }`}
    >
      {children}
    </a>
  );
}
