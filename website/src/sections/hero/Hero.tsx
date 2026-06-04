import { useState } from "react";
import { Carousel, CarouselContent, CarouselItem } from "@/components/ui/carousel";
import type { CarouselApi } from "@/components/ui/carousel";
import {
  AMAZON_MUSIC_URL,
  APPLE_MUSIC_URL,
  DEEZER_URL,
  SOUNDCLOUD_URL,
  SPOTIFY_URL,
  YOUTUBE_URL,
} from "@/lib/site-content";
import {
  AmazonMusicIcon,
  AppleMusicIcon,
  DeezerIcon,
  SoundCloudIcon,
  SpotifyIcon,
  YouTubeIcon,
} from "@/components/platform-icons";
import { useHeroCarouselState } from "@/hooks/useHeroCarouselState";
import { PortraitSlide } from "./slides/portrait/PortraitSlide";
import { PromoSlide } from "./slides/promo/PromoSlide";

export function Hero() {
  const [carouselApi, setCarouselApi] = useState<CarouselApi | null>(null);
  const { activeSlide, slideCount } = useHeroCarouselState(carouselApi);

  return (
    <section
      id="top"
      className="em-hero relative isolate flex min-h-[82svh] w-full items-center justify-center overflow-x-hidden px-6 pt-24 pb-14 em-grain md:min-h-svh md:px-12 md:pt-28 md:pb-16"
    >
      <div aria-hidden className="absolute inset-0 -z-10 overflow-hidden">
        <div className="em-aura absolute inset-[-20%]" />
        <div className="absolute inset-0 bg-linear-to-b from-background/30 via-transparent to-background" />
      </div>

      <div className="mx-auto w-full max-w-5xl">
        <div className="-mt-1 mb-6 flex items-center justify-center gap-4 text-primary md:hidden [&_svg]:h-5 [&_svg]:w-5 pb-6">
          <a href={SPOTIFY_URL} target="_blank" rel="noopener noreferrer" aria-label="Spotify" className="transition-colors hover:text-white">
            <SpotifyIcon />
          </a>
          <a href={APPLE_MUSIC_URL} target="_blank" rel="noopener noreferrer" aria-label="Apple Music" className="transition-colors hover:text-white">
            <AppleMusicIcon />
          </a>
          <a href={YOUTUBE_URL} target="_blank" rel="noopener noreferrer" aria-label="YouTube" className="transition-colors hover:text-white">
            <YouTubeIcon />
          </a>
          <a href={DEEZER_URL} target="_blank" rel="noopener noreferrer" aria-label="Deezer" className="transition-colors hover:text-white">
            <DeezerIcon />
          </a>
          <a href={AMAZON_MUSIC_URL} target="_blank" rel="noopener noreferrer" aria-label="Amazon Music" className="transition-colors hover:text-white">
            <AmazonMusicIcon />
          </a>
          <a href={SOUNDCLOUD_URL} target="_blank" rel="noopener noreferrer" aria-label="SoundCloud" className="transition-colors hover:text-white">
            <SoundCloudIcon />
          </a>
        </div>

        <Carousel opts={{ loop: true }} setApi={setCarouselApi} className="relative w-full overflow-hidden rounded-2xl border border-white/30">
          <CarouselContent>
            <CarouselItem className="flex items-stretch">
              <PortraitSlide />
            </CarouselItem>
            <CarouselItem className="flex items-stretch">
              <PromoSlide />
            </CarouselItem>
          </CarouselContent>
        </Carousel>

        {slideCount > 0 ? (
          <div className="mt-2 flex items-center justify-center gap-2 md:mt-3">
            {Array.from({ length: slideCount }).map((_, index) => (
              <button
                key={index}
                type="button"
                aria-label={`Aller au slide ${index + 1}`}
                aria-current={index === activeSlide}
                onClick={() => carouselApi?.scrollTo(index)}
                className={`h-2.5 rounded-full border transition-all duration-300 ${
                  index === activeSlide
                    ? "w-8 border-[oklch(0.68_0.17_182)] bg-[oklch(0.68_0.17_182)]"
                    : "w-2.5 border-white/45 bg-white/75 hover:w-6 hover:bg-white"
                }`}
              />
            ))}
          </div>
        ) : null}
      </div>
    </section>
  );
}
