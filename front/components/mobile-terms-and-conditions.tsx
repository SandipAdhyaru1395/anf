"use client";

import { useSettings } from "@/components/settings-provider";
import { MobilePageHeader } from "@/components/mobile-page-header";

interface MobileTermsAndConditionsProps {
  onNavigate: (page: any, favorites?: boolean) => void;
}

export function MobileTermsAndConditions({ onNavigate }: MobileTermsAndConditionsProps) {
  const { settings, loading } = useSettings();
  const body = (settings?.terms_and_conditions ?? "").trim();

  return (
    <div
      className="relative mx-auto flex min-h-[100dvh] w-full max-w-[402px] flex-col bg-[#FAFBFD]"
      style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
    >
      <MobilePageHeader variant="plain" title="Terms & Conditions" onBack={() => onNavigate("account")} />

      <main className="min-h-0 w-full flex-1 bg-[#FAFBFD] px-4 pb-6 pt-3">
        <div className="mx-auto w-full max-w-[370px] rounded-[14px] border border-[#DFE7F4] bg-[linear-gradient(180deg,#FFFFFF_0%,#F8FBFF_100%)] p-4 shadow-[0_8px_20px_rgba(61,73,94,0.08)]">
          {loading ? (
            <p className="text-[14px] text-[#7D8798]">Loading…</p>
          ) : body ? (
            <div
              className="prose prose-sm max-w-none text-[#3D495E]"
              dangerouslySetInnerHTML={{ __html: body }}
            />
          ) : (
            <p className="text-[14px] leading-6 text-[#7D8798]">
              No terms and conditions have been set yet. An administrator can add them in the admin panel under Settings → Terms &amp; Conditions.
            </p>
          )}
        </div>
      </main>
    </div>
  );
}
