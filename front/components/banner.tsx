"use client";

import { useSettings } from "./settings-provider";

export function Banner() {
  const { settings } = useSettings();

  if (!settings?.banner) {
    return null;
  }

  return (
    <div className="w-full" style={{ height: 266.66 }}>
      <img
        src={settings.banner}
        alt="Banner"
        className="w-full h-full"
      />
    </div>
  );
}
