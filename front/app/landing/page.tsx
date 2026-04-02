"use client";

import { useEffect, useRef, useState } from "react";
import { useSettings } from "@/components/settings-provider";
import { buildPath, publicAssetUrl } from "@/lib/utils";
import Image from "next/image";

export default function LandingPage() {
  const { settings } = useSettings();
  const logoSrc = settings?.company_logo_url;
  // const logoSrc = "https://aidemo.in/anf/admin/public/storage/settings/EIr5VV7lgIUiUYJFe7cRJrqAr5Mja5DVlp70TcL0.svg";
  const contentRef = useRef<HTMLDivElement | null>(null);
  const [showScrollTop, setShowScrollTop] = useState(false);
  const [isCompactActions, setIsCompactActions] = useState(false);

  useEffect(() => {
    const container = contentRef.current;
    if (!container) return;

    const onScroll = () => {
      const hasScrolled = container.scrollTop > 120;
      setShowScrollTop(hasScrolled);
      setIsCompactActions(container.scrollTop > 40);
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
    // <div className="flex min-h-screen w-full items-center justify-center bg-[#f0f2f5]">
    <div className="flex min-h-screen w-full items-center justify-center">
      <div className="fullscreen-sec relative flex min-h-screen w-full flex-col items-center overflow-hidden bg-[#FAFBFD] shadow-xl sm:h-screen sm:max-h-[874px] sm:max-w-[402px]">
        <div className="relative w-full h-full flex flex-col items-center">
          <div
            className="absolute inset-0 z-0 pointer-events-none"
            style={{
              backgroundImage: `url('${publicAssetUrl("background.svg")}')`,
              backgroundRepeat: "no-repeat",
              backgroundSize: "auto 90%",
              backgroundPosition: "top 60px right",
            }}
          />

          <div
            ref={contentRef}
            className="relative z-10 flex h-full w-full flex-col items-center justify-between px-[18px] pt-[20px] pb-[22px] sm:px-[32px] sm:pt-[20px] sm:pb-[60px] overflow-y-auto no-scrollbar"
          >
            {/* Logo Section */}
            <div className="sticky top-0 z-10 flex w-full flex-col items-center shrink-0 bg-transparent py-2">
              {logoSrc ? (
                <Image
                  src={logoSrc}
                  alt="Logo"
                  width={214}
                  height={151}
                  className="block mx-auto h-auto w-[155px] sm:w-[214px]"
                  priority
                />
              ) : null}
            </div>

            <div className="flex w-full flex-col items-center gap-[12px] mt-[34px] sm:mt-0 shrink-0">
              <button
                onClick={() => (window.location.href = buildPath("/login"))}
                className={`w-full rounded-[25px] text-center [font-family:Roboto] text-[#FFFFFF] hover:cursor-pointer active:scale-[0.98] transition-all font-[700] ${
                  isCompactActions
                    ? "max-w-[268px] h-[38px] px-[20px] py-[9px] text-[18px] leading-[16px]"
                    : "max-w-[306px] h-[44px] px-[22px] py-[12px] text-[18px] leading-[16px] sm:max-w-[338px] sm:h-[48px] sm:px-[26px] sm:py-[15px] sm:text-[20px] sm:leading-[18px]"
                }`}
                style={{ background: "linear-gradient(0deg, #2868C0 -107.69%, #4C92E9 80.77%)" }}
              >
                Log In
              </button>

              <button
                onClick={() => (window.location.href = buildPath("/register"))}
                className={`flex w-full items-center justify-center rounded-[25px] border border-[#4A90E5] bg-[#FFFFFF] text-center font-[700] [font-family:Roboto] text-[#4A90E5] hover:cursor-pointer active:scale-[0.98] transition-all ${
                  isCompactActions
                    ? "max-w-[268px] h-[38px] px-[20px] py-[9px] text-[18px] leading-[16px]"
                    : "max-w-[306px] h-[44px] px-[22px] py-[12px] text-[18px] leading-[16px] sm:max-w-[338px] sm:h-[48px] sm:px-[26px] sm:py-[15px] sm:text-[20px] sm:leading-[18px]"
                }`}
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