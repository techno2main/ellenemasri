import { Music } from "lucide-react";
import { RELEASES_URL } from "@/lib/site-content";

type NewsSingleCtaProps = {
  className?: string;
};

export function NewsSingleCta({ className = "" }: NewsSingleCtaProps) {
  return (
    <a
      href={RELEASES_URL}
      target="_blank"
      rel="noopener noreferrer"
      className={`em-hero-cta em-hero-cta-solid bg-primary text-primary-foreground hover:bg-primary/90 ${className}`.trim()}
    >
      <Music aria-hidden className="h-4 w-4 shrink-0" />
      New Single!
    </a>
  );
}
