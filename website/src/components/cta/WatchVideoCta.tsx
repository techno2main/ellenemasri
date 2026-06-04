import { Video } from "lucide-react";

const WATCH_VIDEO_URL = "https://ellenemasri.com/#video";

type WatchVideoCtaProps = {
  className?: string;
};

export function WatchVideoCta({ className = "" }: WatchVideoCtaProps) {
  return (
    <a
      href={WATCH_VIDEO_URL}
      className={`em-hero-cta em-hero-cta-solid em-hero-cta-watch ${className}`.trim()}
    >
      <Video aria-hidden className="h-4 w-4 shrink-0" />
      Watch
    </a>
  );
}
