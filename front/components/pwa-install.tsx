"use client";

import React from "react";

type BeforeInstallPromptEvent = Event & {
  prompt: () => Promise<void>;
  userChoice: Promise<{ outcome: "accepted" | "dismissed"; platform: string }>;
};

export default function PwaInstall() {
  const [deferredPrompt, setDeferredPrompt] = React.useState<BeforeInstallPromptEvent | null>(null);
  const [visible, setVisible] = React.useState(false);

  React.useEffect(() => {
    const handler = (e: Event) => {
      e.preventDefault?.();
      setDeferredPrompt(e as BeforeInstallPromptEvent);
      setVisible(true);
    };
    window.addEventListener("beforeinstallprompt", handler as EventListener);
    return () => window.removeEventListener("beforeinstallprompt", handler as EventListener);
  }, []);

  const onInstall = async () => {
    if (!deferredPrompt) return;
    await deferredPrompt.prompt();
    try {
      await deferredPrompt.userChoice;
    } finally {
      setDeferredPrompt(null);
      setVisible(false);
    }
  };

  const onClose = () => setVisible(false);
  if (!visible) return null;

  return (
    <div style={{
      position: "fixed",
      left: 16,
      right: 16,
      bottom: 16,
      zIndex: 9999,
      display: "flex",
      gap: 12,
      alignItems: "center",
      justifyContent: "space-between",
      background: "#111",
      color: "#fff",
      padding: "12px 16px",
      borderRadius: 12,
      boxShadow: "0 8px 24px rgba(0,0,0,.3)",
    }}>
      <div style={{ display: "flex", flexDirection: "column" }}>
        <strong>Install NWB2B</strong>
        <span style={{ opacity: .8, fontSize: 12 }}>Get faster access from your home screen</span>
      </div>
      <div style={{ display: "flex", gap: 8 }}>
        <button onClick={onClose} style={{
          background: "transparent",
          color: "#fff",
          border: "1px solid #444",
          borderRadius: 8,
          padding: "8px 12px",
        }}>Not now</button>
        <button onClick={onInstall} style={{
          background: "#16a34a",
          color: "#fff",
          border: 0,
          borderRadius: 8,
          padding: "8px 12px",
        }}>Install</button>
      </div>
    </div>
  );
}




