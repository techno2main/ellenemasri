import { useEffect } from "react";

const MEDIA_SELECTOR = "img, video, audio, canvas";

export function useContentProtection() {
  useEffect(() => {
    const onContextMenu = (event: MouseEvent) => {
      const target = event.target;
      if (!(target instanceof Element)) return;

      if (target.closest(MEDIA_SELECTOR)) {
        event.preventDefault();
      }
    };

    const onDragStart = (event: DragEvent) => {
      const target = event.target;
      if (!(target instanceof Element)) return;

      if (target.closest(MEDIA_SELECTOR)) {
        event.preventDefault();
      }
    };

    const onKeyDown = (event: KeyboardEvent) => {
      const key = event.key.toLowerCase();
      const hasPrimary = event.ctrlKey || event.metaKey;

      if (hasPrimary && (key === "s" || key === "u")) {
        event.preventDefault();
      }

      if (hasPrimary && event.shiftKey && (key === "i" || key === "j" || key === "c")) {
        event.preventDefault();
      }

      if (event.key === "F12") {
        event.preventDefault();
      }
    };

    const applyMediaAttributes = () => {
      document.querySelectorAll("img").forEach((node) => {
        node.setAttribute("draggable", "false");
      });

      document.querySelectorAll("video, audio").forEach((node) => {
        node.setAttribute("controlsList", "nodownload noplaybackrate");
      });
    };

    applyMediaAttributes();

    const observer = new MutationObserver(() => {
      applyMediaAttributes();
    });

    observer.observe(document.body, { childList: true, subtree: true });

    document.addEventListener("contextmenu", onContextMenu);
    document.addEventListener("dragstart", onDragStart);
    window.addEventListener("keydown", onKeyDown);

    return () => {
      observer.disconnect();
      document.removeEventListener("contextmenu", onContextMenu);
      document.removeEventListener("dragstart", onDragStart);
      window.removeEventListener("keydown", onKeyDown);
    };
  }, []);
}
