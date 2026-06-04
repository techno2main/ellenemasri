import { StreamNowCta } from "@/components/cta/StreamNowCta";
import { WatchVideoCta } from "@/components/cta/WatchVideoCta";
import mayamiLogo from "@/assets/mayami-logo.png";

export function PromoSlide() {
  return (
    <div className="relative flex min-h-85 w-full flex-col items-center overflow-hidden rounded-2xl text-center md:min-h-130">
      <div className="relative z-10 w-full px-5 py-8 md:px-6 md:py-12">
        <div className="mb-6 inline-flex items-center gap-2 rounded-full border-2 border-[#1a0d08] bg-[#f5e8d0] px-4 py-2 text-[11px] font-extrabold uppercase tracking-[0.18em] text-[#1a0d08] shadow-[3px_3px_0_#1a0d08] mayami-wiggle">
          <span className="h-2 w-2 rounded-full bg-[#6dccc7]" />
          New single · available!
        </div>

        <div className="mb-8 flex justify-center">
          <img
            src={mayamiLogo}
            alt="Mayami, My Miami"
            className="h-auto w-full max-w-70 select-none sm:max-w-100"
            draggable="false"
          />
        </div>

        <p className="mx-auto mb-6 max-w-2xl text-sm font-semibold leading-relaxed text-foreground sm:text-base">
          <span className="block">A sun-soaked love letter to the city.</span>
          <span className="block">Stream it, watch it, share it.</span>
          <span className="block">Follow the painted walls of Miami.</span>
        </p>

        <div className="flex flex-row flex-nowrap items-center justify-center gap-2 sm:gap-3">
          <StreamNowCta className="promo-cta-shape promo-stream-cta" />
          <WatchVideoCta className="promo-cta-shape" />
        </div>
      </div>
    </div>
  );
}