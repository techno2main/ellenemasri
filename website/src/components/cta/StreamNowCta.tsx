import { Music } from "lucide-react";
import { RELEASES_URL } from "@/lib/site-content";

type StreamNowCtaProps = {
  className?: string;
};

export function StreamNowCta({ className = "" }: StreamNowCtaProps) {
  return (
    <a
      href={RELEASES_URL}
      target="_blank"
      rel="noopener noreferrer"
      className={`em-hero-cta em-hero-cta-solid em-hero-cta-stream ${className}`.trim()}
    >
      <Music aria-hidden className="h-4 w-4 shrink-0" />
      Stream Now
    </a>
  );
}
