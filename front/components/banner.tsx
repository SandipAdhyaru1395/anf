"use client";

import { cn, resolveBackendAssetUrl } from "@/lib/utils";
import { useSettings } from "./settings-provider";

type BannerProps = {
  className?: string;
};

export function Banner({ className }: BannerProps) {
  const { settings } = useSettings();

  const src = resolveBackendAssetUrl(settings?.banner) ?? settings?.banner;
  if (!src) {
    return null;
  }

  return (
    <div
      className={cn(
        "mx-auto h-[94px] w-full max-w-[380px] shrink-0",
        className,
      )}
      style={{ borderRadius: "4px", overflow: "hidden" }}
    >
      <img
        src={src}
        alt="Banner"
        className="h-full w-full object-cover object-center"
      />
    </div>
  );
}