export type MayamiPlatformKey =
  | "spotify"
  | "apple-music"
  | "youtube-music"
  | "deezer"
  | "amazon-music"
  | "soundcloud";

const MAYAMI_BASE_URL = "https://ellenemasri.com/";

export function getMayamiStreamUrl(platform: MayamiPlatformKey): string {
  return `${MAYAMI_BASE_URL}?open-platform=${encodeURIComponent(platform)}#stream`;
}
