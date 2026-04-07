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
    <div className="flex w-full flex-col gap-1 rounded-[12px] border-[0.59px] border-[#4A90E5] bg-white px-3.5 py-3 shadow-[0_1px_2px_rgba(61,73,94,0.04)]">
      <label className="text-[10px] font-semibold uppercase tracking-[0.06em] text-[#8B95A8]">{label}</label>
      <p className="break-words text-[15px] font-medium leading-[1.45] text-[#2B3447]">{value}</p>
    </div>
  );
}

export function MobileContactUs({ onNavigate }: MobileContactUsProps) {
  const { settings, loading, error } = useSettings();

  if (loading && !settings) {
    return (
      <div
        className="relative mx-auto flex min-h-[100dvh] w-full max-w-[402px] flex-col lg:h-[1024px] lg:min-h-[1024px] lg:max-w-[1000px]"
        style={{ fontFamily: "Roboto, system-ui, sans-serif", background: "#FAFBFD" }}
      >
        <div className="fixed top-0 left-1/2 -translate-x-1/2 z-[60] w-full max-w-[402px] lg:max-w-[1000px]">
          <MobilePageHeader variant="plain" title="Contact Us" onBack={() => onNavigate("account")} />
        </div>
        <main className="flex flex-1 items-center justify-center px-4 pb-6 pt-[112px]" style={{ background: "#FAFBFD" }}>
          <p className="text-[15px] font-medium text-[#6B7280]">Loading…</p>
        </main>
      </div>
    );
  }

  if (error && !settings) {
    return (
      <div
        className="relative mx-auto flex min-h-[100dvh] w-full max-w-[402px] flex-col lg:h-[1024px] lg:min-h-[1024px] lg:max-w-[1000px]"
        style={{ fontFamily: "Roboto, system-ui, sans-serif", background: "#FAFBFD" }}
      >
        <div className="fixed top-0 left-1/2 -translate-x-1/2 z-[60] w-full max-w-[402px] lg:max-w-[1000px]">
          <MobilePageHeader variant="plain" title="Contact Us" onBack={() => onNavigate("account")} />
        </div>
        <main className="flex flex-1 items-center justify-center px-4 pb-6 pt-[112px]" style={{ background: "#FAFBFD" }}>
          <p className="max-w-[320px] text-center text-[15px] font-medium leading-snug text-[#C41E1E]">{error}</p>
        </main>
      </div>
    );
  }

  const s = settings!;

  return (
    <div
      className="relative mx-auto flex min-h-[100dvh] w-full max-w-[402px] flex-col lg:h-[1024px] lg:min-h-[1024px] lg:max-w-[1000px]"
      style={{ fontFamily: "Roboto, system-ui, sans-serif", background: "#FAFBFD" }}
    >
      <div className="fixed top-0 left-1/2 -translate-x-1/2 z-[60] w-full max-w-[402px] lg:max-w-[1000px]">
        <MobilePageHeader variant="plain" title="Contact Us" onBack={() => onNavigate("account")} />
      </div>

      <main className="w-full flex-1 overflow-y-auto px-4 pb-8 pt-[112px] lg:px-4" style={{ background: "#FAFBFD" }}>
        <div className="mx-auto w-full max-w-[370px] rounded-[16px] border border-[#E8EDF5] bg-white p-4 shadow-[0_4px_24px_rgba(24,39,75,0.06)] lg:max-w-[968px]">
          <div className="mb-4 pb-3">
            <h2 className="text-[17px] font-bold leading-snug tracking-[-0.01em] text-[#181725]">Contact Information</h2>
            <p className="mt-1.5 text-[13px] leading-relaxed text-[#6B7280]">Reach us anytime using details below.</p>
            <div className="mx-auto mt-3 h-0 w-[370px] max-w-full border-t border-[#E2E2E2] opacity-100" />
          </div>

          <div className="flex flex-col gap-2.5">
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
