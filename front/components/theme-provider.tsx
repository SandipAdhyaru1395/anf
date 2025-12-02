"use client"

import { useEffect } from "react"
import { useSettings } from "./settings-provider"

/**
 * ThemeProvider component that applies theme colors from settings to all green color usages
 * This replaces all green color classes with the theme color:
 * - All bg-green-* backgrounds
 * - All text-green-* text colors
 * - All border-green-* borders
 * - Hover color effects have been removed (no hover color changes applied)
 * - Login buttons (bg-black)
 */
export function ThemeProvider() {
  const { settings, loading } = useSettings()

  useEffect(() => {
    if (loading || !settings?.theme) return

    const theme = settings.theme

    // If default colors are enabled, don't apply any theme colors
    if (theme.use_default === true) {
      // Remove any existing theme styles
      removeThemeStyles()
      return
    }

    // Apply theme colors to all green color usages
    applyThemeColors(
      theme.primary_bg_color,
      theme.primary_font_color,
      theme.secondary_bg_color,
      theme.secondary_font_color,
      theme.button_login
    )
  }, [settings?.theme, loading])

  return null
}

/**
 * Remove theme styles if default colors are selected
 */
function removeThemeStyles() {
  const styleId = 'dynamic-theme-styles'
  const styleElement = document.getElementById(styleId)
  if (styleElement) {
    styleElement.remove()
  }
}

/**
 * Apply theme colors dynamically to all green color classes
 */
function applyThemeColors(
  themeColor?: string | null,
  themeHover?: string | null,
  secondaryBgColor?: string | null,
  secondaryFontColor?: string | null,
  login?: string | null
) {
  const styleId = 'dynamic-theme-styles'
  let styleElement = document.getElementById(styleId) as HTMLStyleElement

  if (!styleElement) {
    styleElement = document.createElement('style')
    styleElement.id = styleId
    document.head.appendChild(styleElement)
  }

  let css = ''

  // Apply theme color to all background green classes
  if (themeColor) {
    css += `
      /* Background colors */
      .bg-green-50,
      .bg-green-100,
      .bg-green-200,
      .bg-green-300,
      .bg-green-400,
      .bg-green-500,
      .bg-green-600,
      .bg-green-700,
      .bg-green-800,
      .bg-green-900 {
        background-color: ${themeColor} !important;
      }
    `

    // Apply theme color to all text green classes
    css += `
      /* Text colors */
      .text-green-50,
      .text-green-100,
      .text-green-200,
      .text-green-300,
      .text-green-400,
      .text-green-500,
      .text-green-600,
      .text-green-700,
      .text-green-800,
      .text-green-900 {
        color: ${themeColor} !important;
      }
    `

    // Apply primary_font_color only to specific text elements
    if (themeHover) {
      css += `
        /* Font color for Referral Rewards card - only specific labels */
        .bg-green-500 .referralbox h3,
        .bg-green-500 .referralbox p {
          color: ${themeHover} !important;
        }
        /* Font color for Shop and Favourites button text and icons */
        button.bg-green-500 span.font-semibold,
        button.bg-green-500 svg {
          color: ${themeHover} !important;
        }
        /* Font color for top-level category buttons (depth 0) */
        .space-y-2 button.bg-green-600 span,
        .space-y-2 button.bg-green-600 svg,
        .space-y-2 button.bg-green-600 > svg {
          color: ${themeHover} !important;
        }
        /* Font color for Login and Register buttons */
        .loginRegisterWrapper button.bg-green-500 {
          color: ${themeHover} !important;
        }
      `
    }

    // Apply theme color to all border green classes
    css += `
      /* Border colors */
      .border-green-50,
      .border-green-100,
      .border-green-200,
      .border-green-300,
      .border-green-400,
      .border-green-500,
      .border-green-600,
      .border-green-700,
      .border-green-800,
      .border-green-900 {
        border-color: ${themeColor} !important;
      }
    `

    // Apply secondary colors to second-level category buttons (depth 1)
    if (secondaryBgColor) {
      css += `
        /* Apply secondary background color to second-level category buttons (depth 1) */
        .space-y-2 button.bg-green-50 {
          background-color: ${secondaryBgColor} !important;
        }
      `
    }

    if (secondaryFontColor) {
      css += `
        /* Apply secondary font color to second-level category buttons (depth 1) */
        .space-y-2 button.bg-green-50 span,
        .space-y-2 button.bg-green-50 svg,
        .space-y-2 button.bg-green-50 > svg {
          color: ${secondaryFontColor} !important;
        }
      `
    }

    // Exclude theme colors from depth 2+ category buttons
    css += `
      /* Exclude theme colors from depth 2+ category buttons - restore original Tailwind colors */
      .space-y-2 button.bg-green-100 {
        background-color: unset !important;
      }
      .space-y-2 button.bg-green-100 {
        background-color: #dcfce7 !important;
      }
      /* Exclude theme colors from depth 2+ category button contents (lines 526-532) */
      .space-y-2 button.bg-green-100 span,
      .space-y-2 button.bg-green-100 div {
        color: unset !important;
      }
      /* Exclude theme colors from tags (lines 546-554) - more specific selectors */
      .space-y-2 button.bg-green-600 span.bg-green-600,
      .space-y-2 button.bg-green-50 span.bg-green-600,
      .space-y-2 button.bg-green-100 span.bg-green-600,
      .space-y-2 button.bg-green-600 .flex.items-center.gap-1 span.bg-green-600,
      .space-y-2 button.bg-green-50 .flex.items-center.gap-1 span.bg-green-600,
      .space-y-2 button.bg-green-100 .flex.items-center.gap-1 span.bg-green-600 {
        background-color: #16a34a !important;
      }
    `

    // Exclude theme colors from elements inside CategoryNode children and products (below line 562-563)
    css += `
      /* Exclude theme colors from nested category nodes and products - restore original Tailwind colors */
      .space-y-3 .product-grid-responsive .text-green-400,
      .space-y-3 .product-grid-responsive .text-green-500,
      .space-y-3 .product-grid-responsive .text-green-600,
      .space-y-3 > div > .space-y-2 .text-green-400,
      .space-y-3 > div > .space-y-2 .text-green-500,
      .space-y-3 > div > .space-y-2 .text-green-600 {
        color: unset !important;
      }
      .space-y-3 .product-grid-responsive .text-green-400 {
        color: #4ade80 !important;
      }
      .space-y-3 .product-grid-responsive .text-green-500,
      .space-y-3 > div > .space-y-2 .text-green-500 {
        color: #22c55e !important;
      }
      .space-y-3 .product-grid-responsive .text-green-600,
      .space-y-3 > div > .space-y-2 .text-green-600 {
        color: #16a34a !important;
      }
      .space-y-3 > div > .space-y-2 .bg-green-100 {
        background-color: #dcfce7 !important;
      }
      .space-y-3 > div > .space-y-2 .bg-green-600 {
        background-color: #16a34a !important;
      }
    `

    // Apply secondary colors to nested second-level category buttons (inside expanded sections)
    if (secondaryBgColor) {
      css += `
        .space-y-3 > div > .space-y-2 button.bg-green-50 {
          background-color: ${secondaryBgColor} !important;
        }
      `
    } else {
      css += `
        .space-y-3 > div > .space-y-2 button.bg-green-50 {
          background-color: #f0fdf4 !important;
        }
      `
    }

    if (secondaryFontColor) {
      css += `
        .space-y-3 > div > .space-y-2 button.bg-green-50 span,
        .space-y-3 > div > .space-y-2 button.bg-green-50 svg,
        .space-y-3 > div > .space-y-2 button.bg-green-50 > svg {
          color: ${secondaryFontColor} !important;
        }
      `
    }

    css += `
      /* Exclude theme colors from nested category buttons (depth 0 and depth 2+ inside expanded sections) */
      .space-y-3 > div > .space-y-2 button.bg-green-600,
      .space-y-3 > div > .space-y-2 button.bg-green-100 {
        background-color: unset !important;
      }
      .space-y-3 > div > .space-y-2 button.bg-green-600 {
        background-color: #16a34a !important;
      }
      .space-y-3 > div > .space-y-2 button.bg-green-100 {
        background-color: #dcfce7 !important;
      }
      .space-y-3 > div > .space-y-2 button.bg-green-600 span,
      .space-y-3 > div > .space-y-2 button.bg-green-100 span,
      .space-y-3 > div > .space-y-2 button.bg-green-600 div,
      .space-y-3 > div > .space-y-2 button.bg-green-100 div {
        color: unset !important;
      }
      /* Exclude theme colors from nested tags (inside expanded sections) */
      .space-y-3 > div > .space-y-2 button.bg-green-600 span.bg-green-600,
      .space-y-3 > div > .space-y-2 button.bg-green-50 span.bg-green-600,
      .space-y-3 > div > .space-y-2 button.bg-green-100 span.bg-green-600,
      .space-y-3 > div > .space-y-2 button.bg-green-600 .flex.items-center.gap-1 span.bg-green-600,
      .space-y-3 > div > .space-y-2 button.bg-green-50 .flex.items-center.gap-1 span.bg-green-600,
      .space-y-3 > div > .space-y-2 button.bg-green-100 .flex.items-center.gap-1 span.bg-green-600 {
        background-color: #16a34a !important;
      }
    `
  }

  // Hover color effects removed - no hover color changes applied

  // Login buttons: bg-black
  if (login) {
    css += `
      .loginregisterform button,
      button.bg-black {
        background-color: ${login} !important;
      }
    `
  }

  // Final catch-all: Exclude theme colors from tag spans (must come last to override)
  if (themeColor) {
    css += `
      /* Final override for tag spans - ensure original Tailwind green-600 color */
      button span.bg-green-600.text-white,
      button span.bg-green-600.rounded-full,
      .space-y-2 button span.bg-green-600,
      .space-y-3 > div > .space-y-2 button span.bg-green-600 {
        background-color: #16a34a !important;
      }
    `
  }

  styleElement.textContent = css
}
