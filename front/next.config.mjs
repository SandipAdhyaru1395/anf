import withPWA from 'next-pwa'

/** @type {import('next').NextConfig} */
const baseConfig = {
  eslint: { 
    ignoreDuringBuilds: true,
  },
  typescript: {
    ignoreBuildErrors: true,
  },
  images: {
    unoptimized: true,
  },
  // async rewrites() {
  //   return [
  //     {
  //       source: "/api/:path*",
  //       destination: "http://localhost:8000/api/:path*", // Laravel API
  //     },
  //   ];
  // },
}

const nextConfig = withPWA({
  dest: (!process.env.NEXT_PUBLIC_API_URL) ? 'public' : 'anf/front/public',
  disable: (!process.env.NEXT_PUBLIC_API_URL) ? true : false,
  register: true,
  skipWaiting: true,
})(baseConfig)

export default nextConfig
