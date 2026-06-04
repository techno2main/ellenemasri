import type { ReactNode } from "react";
import {
  APPLE_MUSIC_URL,
  AMAZON_MUSIC_URL,
  DEEZER_URL,
  INSTAGRAM_URL,
  SOUNDCLOUD_URL,
  SPOTIFY_URL,
  TIKTOK_URL,
  YOUTUBE_URL,
} from "@/lib/site-content";
import {
  AppleMusicIcon,
  AmazonMusicIcon,
  DeezerIcon,
  InstagramIcon,
  SoundCloudIcon,
  SpotifyIcon,
  TikTokIcon,
  YouTubeIcon,
} from "@/components/platform-icons";

export type SocialCtaItem = {
  id: string;
  label: string;
  href: string;
  title: string;
  icon: ReactNode;
};

// Reorder, relabel, or change links here.
export const SOCIAL_CTA_ITEMS: SocialCtaItem[] = [
  { id: "spotify", label: "Spotify", href: SPOTIFY_URL, title: "Spotify", icon: <SpotifyIcon /> },
  { id: "apple-music", label: "Apple Music", href: APPLE_MUSIC_URL, title: "Apple Music", icon: <AppleMusicIcon /> },
  { id: "deezer", label: "Deezer", href: DEEZER_URL, title: "Deezer", icon: <DeezerIcon /> },
  { id: "amazon-music", label: "Amazon Music", href: AMAZON_MUSIC_URL, title: "Amazon Music", icon: <AmazonMusicIcon /> },
    { id: "youtube", label: "YouTube", href: YOUTUBE_URL, title: "YouTube Music", icon: <YouTubeIcon /> },
  { id: "soundcloud", label: "SoundCloud", href: SOUNDCLOUD_URL, title: "SoundCloud", icon: <SoundCloudIcon /> },
  { id: "instagram", label: "Instagram", href: INSTAGRAM_URL, title: "Instagram", icon: <InstagramIcon /> },
  { id: "tiktok", label: "TikTok", href: TIKTOK_URL, title: "TikTok", icon: <TikTokIcon /> },
];
