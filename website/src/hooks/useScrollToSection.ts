import { useCallback } from "react";

export function useScrollToSection() {
  return useCallback((sectionId: string) => {
    if (typeof document === "undefined") {
      return;
    }

    document.getElementById(sectionId)?.scrollIntoView({
      behavior: "smooth",
      block: "start",
    });
  }, []);
}