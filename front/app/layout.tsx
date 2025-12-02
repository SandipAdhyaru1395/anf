import type { Metadata } from "next";
import { GeistSans } from "geist/font/sans";
import { GeistMono } from "geist/font/mono";
import "./globals.css";
import "./custom.css";

import { Toaster } from "@/components/ui/toaster";
import FaviconProvider from "@/components/favicon-provider";
import { CurrencyProvider } from "@/components/currency-provider";
import { SettingsProvider } from "@/components/settings-provider";
import { ThemeProvider } from "@/components/theme-provider";
import { CustomerProvider } from "@/components/customer-provider";
import LoadingProvider from "@/components/loading-provider";
import StoreMaintenanceGate from "@/components/store-maintenance-gate";
import PwaInstall from "@/components/pwa-install";
import SwRegister from "@/components/sw-register";
import { getBasePath } from "@/lib/utils";
import EarlyRedirect from "@/components/early-redirect";

export const metadata: Metadata = {
  title: "NWB2B",
  description: "",
  generator: "v0.app",
  icons: {},
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  const iconUrl = !process.env.NEXT_PUBLIC_API_URL ? "/icons" : "/nwb2b/front/icons";


  return (
    <html lang="en">
      <head>
        <style>{`
html {
  font-family: ${GeistSans.style.fontFamily};
  --font-sans: ${GeistSans.variable};
  --font-mono: ${GeistMono.variable};
}
        `}</style>
        <link rel="manifest" href={(!process.env.NEXT_PUBLIC_API_URL) ? "/manifest.webmanifest" : "/nwb2b/front/manifest.webmanifest"} />
        <meta name="theme-color" content="#000000" />
        <meta name="mobile-web-app-capable" content="yes" />

        {/* ------------------ Android & Desktop ----------------------- */}
        <link rel="icon" href={`${iconUrl}/32.png`} type="image/png" />
        <link rel="icon" href={`${iconUrl}/96.png`} type="image/png" />
        <link rel="icon" href={`${iconUrl}/192.png`} type="image/png" />
        <link rel="icon" href={`${iconUrl}/512.png`} type="image/png" />


        {/* ------------------ iOS ----------------------- */}
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-status-bar-style" content="default" />
        <meta name="apple-mobile-web-app-title" content="NWB2B" />


        <link rel="apple-touch-icon" sizes="120x120" href={`${iconUrl}/120.png`} />
        <link rel="apple-touch-icon" sizes="152x152" href={`${iconUrl}/152.png`} />
        <link rel="apple-touch-icon" sizes="167x167" href={`${iconUrl}/167.png`} />
        <link rel="apple-touch-icon" sizes="180x180" href={`${iconUrl}/180.png`} />

      </head>
      <body>
        <EarlyRedirect />
        <LoadingProvider>
          <SettingsProvider>
            <ThemeProvider />
            <StoreMaintenanceGate>
              <CurrencyProvider>
                <CustomerProvider>
                  {children}
                  <Toaster />
                  <FaviconProvider />
                  <PwaInstall />
                  <SwRegister />
                </CustomerProvider>
              </CurrencyProvider>
            </StoreMaintenanceGate>
          </SettingsProvider>
        </LoadingProvider>
      </body>
    </html>
  );
}
