"use client";

import { useSettings } from "./settings-provider";

export function Banner() {
  const { settings } = useSettings();

  if (!settings?.banner) {
    return null;
  }

  return (
    <div className="w-[380px] h-[94px]"  style={{ borderRadius: "10px", overflow: "hidden" }}>
      <img
        src={settings.banner}
        alt="Banner"
        className="w-full h-full"
      />
    </div>
  );
}
