"use client";

import { MobilePageHeader } from "@/components/mobile-page-header";
import { useSettings } from "@/components/settings-provider";

interface MobileContactUsProps {
  onNavigate: (page: any, favorites?: boolean) => void;
}

interface ContactFieldProps {
  label: string;
  value: string;
}

function ContactField({ label, value }: ContactFieldProps) {
  return (
    <div className="flex w-full flex-col gap-1.5 rounded-[10px] border border-[#E7ECF4] bg-white px-3 py-2.5">
      <label className="text-[11px] font-semibold uppercase tracking-[0.04em] text-[#7D8798]">{label}</label>
      <p className="text-[14px] font-medium leading-5 text-[#3D495E]">{value}</p>
    </div>
  );
}

export function MobileContactUs({ onNavigate }: MobileContactUsProps) {
  const { settings, loading, error } = useSettings();

  if (loading && !settings) {
    return (
      <div
        className="relative mx-auto flex min-h-[100dvh] w-full max-w-[402px] flex-col bg-[#FAFBFD]"
        style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
      >
        <MobilePageHeader variant="plain" title="Contact Us" onBack={() => onNavigate("account")} />
        <main className="flex flex-1 items-center justify-center px-4 pb-6 pt-3">
          <p className="text-[14px] text-[#7D8798]">Loading…</p>
        </main>
      </div>
    );
  }

  if (error && !settings) {
    return (
      <div
        className="relative mx-auto flex min-h-[100dvh] w-full max-w-[402px] flex-col bg-[#FAFBFD]"
        style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
      >
        <MobilePageHeader variant="plain" title="Contact Us" onBack={() => onNavigate("account")} />
        <main className="flex flex-1 items-center justify-center px-4 pb-6 pt-3">
          <p className="text-center text-[14px] text-[#B42318]">{error}</p>
        </main>
      </div>
    );
  }

  const s = settings!;

  return (
    <div
      className="relative mx-auto flex min-h-[100dvh] w-full max-w-[402px] flex-col bg-[#FAFBFD]"
      style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
    >
      <MobilePageHeader variant="plain" title="Contact Us" onBack={() => onNavigate("account")} />

      <main className="w-full bg-[#FAFBFD] px-4 pb-6 pt-3">
        <div className="mx-auto w-full max-w-[370px] rounded-[14px] border border-[#DFE7F4] bg-[linear-gradient(180deg,#FFFFFF_0%,#F8FBFF_100%)] p-3 shadow-[0_8px_20px_rgba(61,73,94,0.08)]">
          <div className="mb-3 border-b border-[#E6ECF5] pb-2">
            <h2 className="text-[16px] font-bold leading-tight text-[#3D495E]">Contact Information</h2>
            <p className="mt-1 text-[12px] text-[#7D8798]">Reach us anytime using details below.</p>
          </div>

          <div className="flex flex-col gap-3">
            <ContactField label="Name" value={s.company_title ?? ""} />
            <ContactField label="Company Name" value={s.company_name ?? ""} />
            <ContactField label="Address" value={s.company_address ?? ""} />
            <ContactField label="Email" value={s.company_email ?? ""} />
            <ContactField label="Phone" value={s.company_phone ?? ""} />
          </div>
        </div>
      </main>
    </div>
  );
}
