"use client";

import type { ReactNode } from "react";
import { ChevronLeft } from "lucide-react";

type MobilePageHeaderProps = {
  title: string;
  onBack: () => void;
  /**
   * `tinted` — Wallet Figma: #F8F7FC + shadow, title #3D495E + 4% tracking, Back lavender.
   * `plain` — Account Figma: #FAFBFD frame + #E2E2E2 bottom rule, title #181725, Back #6B7280.
   */
  variant?: "tinted" | "plain";
  /** Remove large top offset used by tinted header. */
  noTopPadding?: boolean;
  /** e.g. Order Summary status pill on the right */
  trailing?: ReactNode;
};

export function MobilePageHeader({ title, onBack, variant = "tinted", noTopPadding = false, trailing }: MobilePageHeaderProps) {
  void variant;
  void noTopPadding;

  return (
    <header className="z-50 h-[96px] w-full shrink-0 bg-[#F8F7FC] shadow-[0px_5px_15px_0px_#555E5817]">
      <div
        className="mx-auto box-border h-full w-full max-w-[402px] px-[29px] pb-4 pt-[65px] lg:max-w-[1000px]"
        style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
      >
        <div className="relative mx-auto flex h-[15px] w-full max-w-[344px] items-center justify-center lg:max-w-full">
          <button
            type="button"
            onClick={onBack}
            className="absolute left-0 top-1/2 flex h-[15px] -translate-y-1/2 items-center gap-2 text-[#3D495E] transition-opacity hover:opacity-80"
          >
            <ChevronLeft className="h-[15px] w-[15px] shrink-0" strokeWidth={2} aria-hidden />
            <span className="inline-flex h-[15px] w-[30px] items-center justify-center text-[13px] font-light leading-none tracking-[0.03em]">Back</span>
          </button>
          <h1
            className="pointer-events-none max-w-[min(220px,calc(100%-6rem))] truncate text-center text-[20px] font-bold leading-none tracking-[0.04em] text-[#3D495E]"
            title={title}
          >
            {title}
          </h1>
          {trailing ? (
            <div className="absolute right-0 top-1/2 flex max-w-[120px] -translate-y-1/2 justify-end">{trailing}</div>
          ) : null}
        </div>
      </div>
    </header>
  );
}
