import logoEm from "@/assets/logo-em.png";
import { useScrollToSection } from "@/hooks/useScrollToSection";
import {
  AMAZON_MUSIC_URL,
  APPLE_MUSIC_URL,
  DEEZER_URL,
  INSTAGRAM_URL,
  RELEASES_URL,
  SOUNDCLOUD_URL,
  SPOTIFY_URL,
  TIKTOK_URL,
  YOUTUBE_URL,
} from "@/lib/site-content";
import {
  AmazonMusicIcon,
  AppleMusicIcon,
  DeezerIcon,
  InstagramIcon,
  SoundCloudIcon,
  SpotifyIcon,
  TikTokIcon,
  YouTubeIcon,
} from "@/components/platform-icons";

export function Header() {
  const scrollToSection = useScrollToSection();

  return (
    <header className="em-header fixed top-0 left-0 right-0 z-50 flex items-center justify-between bg-background/30 px-6 py-5 backdrop-blur-sm md:px-12 md:py-7">
      <button type="button" onClick={() => scrollToSection("top")} className="inline-flex items-center cursor-pointer" aria-label="Ellene Masri">
        <img src={logoEm} alt="Ellene Masri" className="h-9 w-auto md:h-10" />
      </button>
      <nav className="flex items-center gap-6 text-xs uppercase tracking-[0.22em] text-muted-foreground md:gap-10 md:text-[0.78rem]">
        <div className="hidden items-center gap-4 text-primary md:flex [&_svg]:h-5 [&_svg]:w-5">
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

        <div className="flex items-center gap-2 text-white [&_svg]:h-4 [&_svg]:w-4 sm:gap-3 sm:[&_svg]:h-5 sm:[&_svg]:w-5">
          <a href={TIKTOK_URL} target="_blank" rel="noopener noreferrer" aria-label="TikTok" className="transition-colors hover:text-primary">
            <TikTokIcon />
          </a>
          <a href={INSTAGRAM_URL} target="_blank" rel="noopener noreferrer" aria-label="Instagram" className="transition-colors hover:text-primary">
            <InstagramIcon />
          </a>
        </div>
        <a href={RELEASES_URL} target="_blank" rel="noopener noreferrer" className="em-link text-foreground">
          RELEASES
        </a>
      </nav>
    </header>
  );
}