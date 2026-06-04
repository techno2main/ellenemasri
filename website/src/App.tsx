import { Contact } from "@/sections/contact/Contact";
import { Footer } from "@/sections/footer/Footer";
import { Header } from "@/sections/header/Header";
import { Hero } from "@/sections/hero/";
import { useContentProtection } from "@/hooks/useContentProtection";

export default function App() {
  useContentProtection();

  return (
    <main className="relative min-h-screen overflow-x-hidden bg-background text-foreground">
      <Header />
      <Hero />
      <Contact />
      <Footer />
    </main>
  );
}
