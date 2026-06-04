import type { ReactNode } from "react";
import {
  INSTAGRAM_URL,
  TIKTOK_URL,
} from "@/lib/site-content";
import { getMayamiStreamUrl } from "@/lib/mayami-stream";
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
  { id: "spotify", label: "Spotify", href: getMayamiStreamUrl("spotify"), title: "Spotify", icon: <SpotifyIcon /> },
  { id: "apple-music", label: "Apple Music", href: getMayamiStreamUrl("apple-music"), title: "Apple Music", icon: <AppleMusicIcon /> },
  { id: "deezer", label: "Deezer", href: getMayamiStreamUrl("deezer"), title: "Deezer", icon: <DeezerIcon /> },
  { id: "amazon-music", label: "Amazon Music", href: getMayamiStreamUrl("amazon-music"), title: "Amazon Music", icon: <AmazonMusicIcon /> },
  { id: "youtube", label: "YouTube", href: getMayamiStreamUrl("youtube-music"), title: "YouTube Music", icon: <YouTubeIcon /> },
  { id: "soundcloud", label: "SoundCloud", href: getMayamiStreamUrl("soundcloud"), title: "SoundCloud", icon: <SoundCloudIcon /> },
  { id: "instagram", label: "Instagram", href: INSTAGRAM_URL, title: "Instagram", icon: <InstagramIcon /> },
  { id: "tiktok", label: "TikTok", href: TIKTOK_URL, title: "TikTok", icon: <TikTokIcon /> },
];
