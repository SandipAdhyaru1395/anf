"use client";

import { useState } from "react";
import { MobilePageHeader } from "@/components/mobile-page-header";

interface MobileContactUsProps {
  onNavigate: (page: any, favorites?: boolean) => void;
}

const initialContactUsData = {
  name: "ANF",
  companyName: "Your Company Name ANF",
  address: "1109, SATYAMEV EMINENCE, Science City Rd, near shukan mall, Sola, Ahmedabad, Gujarat 380060",
  email: "sandip@silverwebbuzz.com",
  phone: "7405896475",
  sessionTimeout: "60",
  defaultCurrency: "GBP -British Pound Sterling (£)",
  accountName: "Account Name",
  bank: "Bank Name",
  sortCode: "00-00-00",
  accountNumber: "Account Number",
};

interface EditableFieldProps {
  label: string;
  value: string;
  onChange: (value: string) => void;
  multiline?: boolean;
}

function EditableField({ label, value, onChange, multiline = false }: EditableFieldProps) {
  return (
    <div className="flex w-full flex-col gap-1.5">
      <label className="text-[13px] font-medium text-[#3D495E]">{label}</label>
      {multiline ? (
        <textarea
          value={value}
          onChange={(e) => onChange(e.target.value)}
          rows={3}
          className="w-full resize-none rounded-[6px] border border-[#4A90E5] bg-white px-3 py-2 text-[14px] leading-5 text-[#A3A3A3] outline-none focus:border-[#4A90E5]"
        />
      ) : (
        <input
          type="text"
          value={value}
          onChange={(e) => onChange(e.target.value)}
          className="h-10 w-full rounded-[6px] border border-[#4A90E5] bg-white px-3 text-[14px] text-[#A3A3A3] outline-none focus:border-[#4A90E5]"
        />
      )}
    </div>
  );
}

export function MobileContactUs({ onNavigate }: MobileContactUsProps) {
  const [contactUsData, setContactUsData] = useState(initialContactUsData);

  const updateField = (key: keyof typeof initialContactUsData, value: string) => {
    setContactUsData((prev) => ({ ...prev, [key]: value }));
  };

  return (
    <div
      className="relative mx-auto flex min-h-[100dvh] w-full max-w-[402px] flex-col bg-[#FAFBFD]"
      style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
    >
      <MobilePageHeader variant="plain" title="Contact Us" onBack={() => onNavigate("account")} />

      <main className="w-full bg-[#FAFBFD] px-4 pb-6">
        <form
          className="mx-auto flex w-full max-w-[370px] flex-col gap-4 py-2"
          onSubmit={(e) => e.preventDefault()}
        >
          <EditableField label="Name *" value={contactUsData.name} onChange={(value) => updateField("name", value)} />
          <EditableField
            label="Company Name *"
            value={contactUsData.companyName}
            onChange={(value) => updateField("companyName", value)}
          />
          <EditableField
            label="Address"
            value={contactUsData.address}
            onChange={(value) => updateField("address", value)}
            multiline
          />

          <div className="grid grid-cols-2 gap-4">
            <EditableField label="Email" value={contactUsData.email} onChange={(value) => updateField("email", value)} />
            <EditableField label="Phone" value={contactUsData.phone} onChange={(value) => updateField("phone", value)} />
          </div>

          <div className="grid grid-cols-2 gap-4">
            <EditableField
              label="Session Timeout (minutes)"
              value={contactUsData.sessionTimeout}
              onChange={(value) => updateField("sessionTimeout", value)}
            />
            <EditableField
              label="Default Currency"
              value={contactUsData.defaultCurrency}
              onChange={(value) => updateField("defaultCurrency", value)}
            />
          </div>

          <h2 className="pt-2 text-[24px] font-normal leading-[30px] text-[#3D495E]">Bank Account Details</h2>

          <div className="grid grid-cols-2 gap-4">
            <EditableField
              label="Account Name"
              value={contactUsData.accountName}
              onChange={(value) => updateField("accountName", value)}
            />
            <EditableField label="Bank" value={contactUsData.bank} onChange={(value) => updateField("bank", value)} />
          </div>

          <div className="grid grid-cols-2 gap-4 pb-2">
            <EditableField
              label="Sort Code"
              value={contactUsData.sortCode}
              onChange={(value) => updateField("sortCode", value)}
            />
            <EditableField
              label="Account No"
              value={contactUsData.accountNumber}
              onChange={(value) => updateField("accountNumber", value)}
            />
          </div>

          <button
            type="submit"
            className="mt-3 h-11 w-full rounded-[8px] bg-[#4A90E5] text-[15px] font-semibold text-white transition-colors hover:bg-[#3f82d3] active:bg-[#3373c2]"
          >
            Submit
          </button>

          <div className="mt-2 border-b border-[#D9D9D9]" />
        </form>
      </main>
    </div>
  );
}
