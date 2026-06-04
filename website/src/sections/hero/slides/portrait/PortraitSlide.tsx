import { ChevronDown } from "lucide-react";
import ellenePortrait from "@/assets/ellene.png";
import logoEm from "@/assets/logo-em.png";
import { NewsSingleCta } from "@/components/cta/NewsSingleCta";
import { useScrollToSection } from "@/hooks/useScrollToSection";

export function PortraitSlide() {
	const scrollToSection = useScrollToSection();

	return (
		<div className="flex min-h-96 w-full flex-col items-center pb-4 text-center md:min-h-140 md:pb-0">
			<span className="em-eyebrow em-fade-slow pt-6">A guardian of timeless melodies</span>

			<h1 className="em-rise mt-8" aria-label="Ellene Masri">
				<img src={logoEm} alt="Ellene Masri" className="mx-auto h-auto w-[clamp(7.2rem,22vw,11.5rem)] md:w-[clamp(8.5rem,26vw,14rem)]" />
			</h1>

			<p className="em-rise-d1 mt-3 text-xs tracking-wide text-muted-foreground md:text-sm">
				Songwriter, Vocalist & Multi-platinum Artist
			</p>

			<div className="em-rise-d1 mt-6 mb-6">
				<img src={ellenePortrait} alt="Ellene Masri" className="h-24 w-24 rounded-full border-2 border-primary/20 object-cover shadow-2xl md:h-32 md:w-32" />
			</div>

			<p className="em-rise-d2 em-eyebrow">New website — Coming soon</p>

			<p className="em-rise-d2 mt-2 max-w-xl text-base font-light leading-relaxed text-muted-foreground md:text-lg">
				A new digital space is taking shape —
				<br className="hidden md:block" />
				music, visuals, releases and more.
			</p>

			<div className="em-rise-d3 mt-5 flex flex-row flex-nowrap items-center justify-center gap-2.5 sm:mt-6 sm:gap-3">
				<NewsSingleCta className="portrait-mobile-cta" />
				<button type="button" onClick={() => scrollToSection("contact")} className="em-hero-cta em-hero-cta-contact portrait-mobile-cta">
					<span>Contact</span>
					<ChevronDown aria-hidden className="h-4 w-4 text-white" />
				</button>
			</div>
		</div>
	);
}
