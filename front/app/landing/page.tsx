"use client";

import { useSettings } from "@/components/settings-provider";
import { buildPath } from "@/lib/utils";
import Image from "next/image";

export default function LandingPage() {
  const { settings } = useSettings();
  const logoSrc = settings?.company_logo_url;

  return (
    <div className="flex min-h-screen w-screen items-center justify-center">
      <div className="flex h-[874px] w-[402px] max-w-full flex-col items-center bg-[#FAFBFD] pt-[65px] pb-[65px]">
        <div className="relative w-[402px] h-[744px] max-h-[800px] flex flex-col items-center justify-between overflow-hidden">
          <div
            className="absolute left-[20px] right-0 bottom-0 top-[76px] z-0 pointer-events-none"
            style={{
              // backgroundColor: "#6497EA0D",
              backgroundImage: "url('/background.svg')",
              backgroundRepeat: "no-repeat",
              backgroundSize: "contain",
              backgroundPosition: "center",
            }}
          />

        {/* Logo Section */}
        <div className="relative z-10 flex flex-col items-center">
          {logoSrc ? (
            <Image
              src={logoSrc}
              alt="Logo"
              width={214}
              height={151}
              className="app-logo-auth  block mx-auto"
              priority
            />
          ) : null}
        </div>



        {/* Buttons Section */}
        <div className="relative z-10 flex w-[402px] h-[112px] flex-col items-center gap-[16px] px-[32px]">
          <button
            onClick={() => (window.location.href = buildPath("/login"))}
            className="w-[338px] h-[48px] rounded-[25px] px-[26px] py-[15px] text-center text-[20px] leading-[18px] [font-family:Roboto] text-[#FFFFFF] hover:cursor-pointer active:scale-[0.98] transition-all"
            style={{ background: "linear-gradient(0deg, #2868C0 -107.69%, #4C92E9 80.77%)" , fontWeight: "700" }}
          >
            Log In
          </button>

          <button
            onClick={() => window.location.href = buildPath("/register")}
            className="flex w-[338px] h-[48px] items-center justify-center rounded-[25px] border border-[#4A90E5] bg-[#FFFFFF] px-[26px] py-[15px] text-center text-[20px] leading-[18px] font-[700] [font-family:Roboto] text-[#4A90E5] hover:cursor-pointer active:scale-[0.98] transition-all" style={{ fontWeight: "700" }}
          >
            Sign Up
          </button>
        </div>
        
        </div>
      </div>
    </div>
  );
}





