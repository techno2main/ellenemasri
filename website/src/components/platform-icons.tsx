import {
  FaAmazon,
  FaApple,
  FaDeezer,
  FaInstagram,
  FaSoundcloud,
  FaSpotify,
  FaTiktok,
  FaYoutube,
} from "react-icons/fa6";

const iconClassName = "h-5 w-5";

export function InstagramIcon() {
  return <FaInstagram className={iconClassName} aria-hidden />;
}

export function TikTokIcon() {
  return <FaTiktok className={iconClassName} aria-hidden />;
}

export function SpotifyIcon() {
  return <FaSpotify className={iconClassName} aria-hidden />;
}

export function AppleMusicIcon() {
  return <FaApple className={iconClassName} aria-hidden />;
}

export function YouTubeIcon() {
  return <FaYoutube className={iconClassName} aria-hidden />;
}

export function DeezerIcon() {
  return <FaDeezer className={iconClassName} aria-hidden />;
}

export function AmazonMusicIcon() {
  return <FaAmazon className={iconClassName} aria-hidden />;
}

export function SoundCloudIcon() {
  return <FaSoundcloud className={iconClassName} aria-hidden />;
}