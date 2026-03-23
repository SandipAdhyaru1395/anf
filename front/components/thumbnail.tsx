"use client";

import { useSettings } from "./settings-provider";
import { resolveBackendAssetUrl } from "@/lib/utils";

type ThumbnailProps = {
  height?: number;
  containerClassName?: string;
  imgClassName?: string;
};

export function Thumbnail({
  height = 107.84,
  containerClassName = "",
  imgClassName = "",
}: ThumbnailProps = {}) {
  const { settings } = useSettings();

  const src =
    resolveBackendAssetUrl(settings?.thumbnail) ??
    (settings?.thumbnail ? String(settings.thumbnail).trim() : null);

  if (!src) {
    return null;
  }

  return (
    <div className={`w-full bg-white overflow-hidden ${containerClassName}`.trim()} style={{ height }}>
      <img
        src={src}
        alt="thumbnail"
        className={`w-full h-full object-contain ${imgClassName}`.trim()}
        onError={(e) => {
          e.currentTarget.src = "https://placehold.co/600x400?text=Image+Not+Found";
        }}
      />
    </div>
  );
}