"use client";

import { useEffect, useRef, useState } from "react";
import { useSettings } from "@/components/settings-provider";
import { buildPath, publicAssetUrl } from "@/lib/utils"; // publicAssetUrl અહી ઈમ્પોર્ટ કરવું પડશે
import Image from "next/image";

export default function LandingPage() {
  const { settings } = useSettings();
  const logoSrc = settings?.company_logo_url;
  const contentRef = useRef<HTMLDivElement | null>(null);
  const [showScrollTop, setShowScrollTop] = useState(false);

  useEffect(() => {
    const container = contentRef.current;
    if (!container) return;

    const onScroll = () => {
      const hasScrolled = container.scrollTop > 120;
      setShowScrollTop(hasScrolled);
    };

    container.addEventListener("scroll", onScroll);
    onScroll();

    return () => {
      container.removeEventListener("scroll", onScroll);
    };
  }, []);

  const scrollToTop = () => {
    contentRef.current?.scrollTo({ top: 0, behavior: "smooth" });
  };

  return (
    <div className="flex h-[100dvh] max-h-[100dvh] w-full items-center justify-center overflow-hidden bg-[#F2F5F9]">
      <div className="fullscreen-sec relative flex w-full max-w-[1004px] flex-col overflow-hidden bg-[#FAFBFD] px-6 py-10 shadow-sm min-h-screen max-h-[812px] sm:h-[812px] sm:min-h-[812px] sm:max-h-[812px] sm:w-[1004px] sm:px-10 sm:py-12">
        {/* Same watermark layer as login (`components/login.tsx`) — basePath-safe URL */}
        <div
          className="pointer-events-none absolute left-[calc(50%+170px)] top-[95px] z-0 hidden h-[812px] w-[1004px] -translate-x-1/2 lg:block"
          style={{
            backgroundImage: `url('${publicAssetUrl("background.svg")}')`,
            backgroundRepeat: "no-repeat",
            backgroundSize: "1004px 812px",
            backgroundPosition: "center top",
          }}
        />

        <div className="relative z-10 flex h-full min-h-0 w-full flex-1 flex-col">
          <div
            ref={contentRef}
            className="relative z-10 flex h-full min-h-0 w-full flex-1 flex-col items-center justify-between overflow-y-auto no-scrollbar"
          >
            {/* Logo Section */}
            <div className="sticky top-0 z-10 flex w-full shrink-0 flex-col items-center bg-transparent py-2">
              {logoSrc ? (
                <Image
                  src={logoSrc}
                  alt="Logo"
                  width={276}
                  height={195}
                  className="mx-auto block aspect-[276/195] w-[276px] max-w-full h-auto object-contain object-center"
                  priority
                />
              ) : null}
            </div>

            <div className="mx-auto flex w-full max-w-[477px] shrink-0 flex-col items-stretch gap-[11px] mb-[30px]"> {/* નીચે થોડી જગ્યા છોડી */}
              <button
                onClick={() => (window.location.href = buildPath("/login"))}
                className="box-border flex h-[48px] w-full items-center justify-center rounded-[25px] px-[26px] py-[15px] text-center font-[700] leading-none [font-family:Roboto] text-white transition-all hover:cursor-pointer active:scale-[0.98] text-[18px] sm:text-[20px]"
                style={{
                  background:
                    "linear-gradient(0deg, #2868C0 -107.69%, #4C92E9 80.77%)",
                }}
              >
                Log In
              </button>

              <button
                onClick={() => (window.location.href = buildPath("/register"))}
                className="box-border flex h-[48px] w-full items-center justify-center rounded-[25px] border border-[#6497EA] bg-white px-[26px] py-[15px] text-center font-[700] leading-none [font-family:Roboto] text-[#6497EA] transition-all hover:cursor-pointer active:scale-[0.98] text-[18px] sm:text-[20px]"
              >
                Sign Up
              </button>
            </div>
          </div>
        </div>

        {showScrollTop ? (
          <button
            onClick={scrollToTop}
            className="absolute bottom-4 right-4 z-20 flex h-8 w-8 items-center justify-center rounded-full bg-[#4C92E9] text-sm text-white shadow-lg transition-transform active:scale-95 sm:bottom-5 sm:right-5 sm:h-10 sm:w-10 sm:text-base"
            aria-label="Scroll to top"
          >
            ↑
          </button>
        ) : null}
      </div>
    </div>
  );
}