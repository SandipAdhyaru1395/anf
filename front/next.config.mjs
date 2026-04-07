/** @type {import('next').NextConfig} */
const envBase = (process.env.NEXT_PUBLIC_BASE_PATH || process.env.BASE_PATH || "")
  .trim()
  .replace(/^\/+|\/+$/g, "")
const inferredBase =
  envBase || (process.env.NEXT_PUBLIC_API_URL ? "anf/front" : "")
const nextBasePath = inferredBase ? `/${inferredBase}` : undefined

const nextConfig = {
  eslint: {
    ignoreDuringBuilds: true,
  },
  typescript: {
    ignoreBuildErrors: true,
  },
  images: {
    unoptimized: true,
  },
  // App is served under /anf/front on XAMPP/production; without this, `/_next/static/*` 404s.
  ...(nextBasePath ? { basePath: nextBasePath, assetPrefix: nextBasePath } : {}),
}

export default nextConfig
