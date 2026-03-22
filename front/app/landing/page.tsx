"use client";

import { useSettings } from "@/components/settings-provider";
import { buildPath } from "@/lib/utils";
import Image from "next/image";
import { useRouter } from "next/navigation";

export default function LandingPage() {
  const { settings } = useSettings();
  const router = useRouter();
  const logoSrc = settings?.company_logo_url;

  return (
    <div className="min-h-screen bg-gray-100">
      <div className="h-screen app-container flex flex-col items-center justify-between px-6 py-10 bg-white">
      
      {/* Logo Section */}
      <div className="flex flex-col items-center">
        {logoSrc ? (
          <Image
            src={logoSrc}
            alt="Logo"
            width={214}
            height={151}
            className="app-logo-auth animate-pulse"
            priority
          />
        ) : null}
      </div>

      {/* Buttons Section */}
      <div className="landing-auth-actions items-center">
        <button
          onClick={() => (window.location.href = buildPath("/login"))}
          className="landing-login-button bg-[#4e91e4] hover:cursor-pointer text-white shadow-md active:scale-[0.98]"
        >
          Log In
        </button>

        <button
          onClick={() => window.location.href = buildPath("/register")}
          className="landing-login-button border-2 hover:cursor-pointer border-[#4e91e4] bg-white text-[#4e91e4]"
        >
          Sign Up
        </button>
      </div>
      
      </div>
    </div>
  );
}