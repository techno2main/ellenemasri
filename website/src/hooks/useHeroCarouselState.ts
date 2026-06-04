import { useEffect, useState } from "react";
import type { CarouselApi } from "@/components/ui/carousel";

export function useHeroCarouselState(api: CarouselApi | null) {
  const [activeSlide, setActiveSlide] = useState(0);

  useEffect(() => {
    if (!api) {
      return;
    }

    const updateActiveSlide = () => {
      setActiveSlide(api.selectedScrollSnap());
    };

    updateActiveSlide();
    api.on("select", updateActiveSlide);
    api.on("reInit", updateActiveSlide);

    return () => {
      api.off("select", updateActiveSlide);
      api.off("reInit", updateActiveSlide);
    };
  }, [api]);

  return {
    activeSlide,
    slideCount: api?.scrollSnapList().length ?? 0,
  };
}