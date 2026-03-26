"use client";

import { useSettings } from "@/components/settings-provider";
import { buildPath } from "@/lib/utils";
import { useRouter } from "next/navigation";
import { useEffect } from "react";

type SplashScreenProps = {
  delayMs?: number;
  redirectTo?: string;
};

export default function SplashScreen({
  delayMs = 3000,
  redirectTo,
}: SplashScreenProps) {
  const { settings } = useSettings();
  const router = useRouter();

  useEffect(() => {
    const target = redirectTo || buildPath("/landing");
    const id = window.setTimeout(() => {
      router.replace(target);
    }, Math.max(0, delayMs));
    return () => window.clearTimeout(id);
  }, [delayMs, redirectTo, router]);

  return (
    settings?.company_logo_url ? (
      <div className="min-h-screen bg-[#FAFBFD] flex items-center justify-center app-container">
        <img
          src={settings.company_logo_url}
          alt="Logo"
          className="app-logo"
        />
      </div>
    ) : null
  );
}
