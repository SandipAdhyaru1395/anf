"use client";

import { useSettings } from "./settings-provider";

export function Thumbnail() {
  const { settings } = useSettings();

  if (!settings?.thumbnail) {
    return null;
  }

  return (
    <div className="w-full" style={{ height: 266.66 }}>
      <img
        src={settings.thumbnail}
        alt="thumbnail"
        className="w-full h-full"
      />
    </div>
  );
}
