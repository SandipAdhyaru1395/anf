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
  const plain = variant === "plain";

  return (
    <header
      className={
        plain
          ? "z-50 w-full shrink-0 border-b border-[#E2E2E2] bg-[#FAFBFD]"
          : "z-50 w-full shrink-0 bg-[#F8F7FC] shadow-[0_5px_15px_0_rgba(85,94,88,0.09)]"
      }
    >
      <div
        className={`mx-auto box-border w-full max-w-[402px] pb-4 ${plain ? "px-4 pt-0" : `px-[29px]}`}`}
        style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
      >
        <div className="relative flex min-h-[1.25rem] items-center justify-center">
          <button
            type="button"
            onClick={onBack}
            className={
              plain
                ? "absolute left-0 top-1/2 flex -translate-y-1/2 items-center gap-2 text-[#6B7280] transition-colors hover:text-[#181725]"
                : "absolute left-0 top-1/2 flex -translate-y-1/2 items-center gap-2 text-[#9CA3CF] transition-colors hover:text-[#7C83B8]"
            }
          >
            <ChevronLeft className="h-5 w-5 shrink-0" strokeWidth={2} aria-hidden />
            <span className="text-[15px] font-normal leading-none tracking-normal">Back</span>
          </button>
          <h1
            className={
              plain
                ? "pointer-events-none max-w-[min(220px,calc(100%-5rem))] truncate text-center text-[20px] font-bold leading-none tracking-normal text-[#181725]"
                : "pointer-events-none max-w-[min(220px,calc(100%-5.5rem))] truncate text-center text-[20px] font-bold leading-none tracking-[0.04em] text-[#3D495E]"
            }
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
