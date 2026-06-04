import { RELEASES_URL } from "@/lib/site-content";

export function Footer() {
  return (
    <footer className="em-footer border-t border-border/40 px-6 py-8 md:px-12">
      <div className="mx-auto flex max-w-5xl flex-col items-center justify-between gap-4 text-xs uppercase tracking-[0.22em] text-muted-foreground md:flex-row">
        <span>© {new Date().getFullYear()} Ellene Masri</span>
        <span className="em-serif normal-case tracking-normal italic text-sm">ellenemasri.com</span>
        <a href={RELEASES_URL} target="_blank" rel="noopener noreferrer" className="em-link">
          Explore Releases ↗
        </a>
      </div>
    </footer>
  );
}