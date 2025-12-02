'use client'

import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react'

type LoadingEvent = { type: 'global-loading-start' | 'global-loading-stop' }

export default function LoadingProvider({ children }: { children: React.ReactNode }) {
  const [isVisible, setIsVisible] = useState(false)
  const inFlightCountRef = useRef(0)
  const hideTimerRef = useRef<number | null>(null)

  const show = useCallback(() => {
    inFlightCountRef.current += 1
    if (!isVisible) setIsVisible(true)
  }, [isVisible])

  const hide = useCallback(() => {
    inFlightCountRef.current = Math.max(0, inFlightCountRef.current - 1)
    if (inFlightCountRef.current === 0) {
      if (hideTimerRef.current) window.clearTimeout(hideTimerRef.current)
      hideTimerRef.current = window.setTimeout(() => setIsVisible(false), 120)
    }
  }, [])

  useEffect(() => {
    const onEvent = (e: Event) => {
      const ev = e as CustomEvent<LoadingEvent>
      if (ev.detail?.type === 'global-loading-start') show()
      if (ev.detail?.type === 'global-loading-stop') hide()
    }
    window.addEventListener('global-loading', onEvent as EventListener)
    return () => {
      window.removeEventListener('global-loading', onEvent as EventListener)
      if (hideTimerRef.current) window.clearTimeout(hideTimerRef.current)
    }
  }, [show, hide])

  useEffect(() => {
    if (isVisible) {
      const prev = document.documentElement.style.overflow
      document.documentElement.style.overflow = 'hidden'
      document.body.style.pointerEvents = 'none'
      return () => {
        document.documentElement.style.overflow = prev
        document.body.style.pointerEvents = ''
      }
    }
  }, [isVisible])

  const overlay = useMemo(() => (
    <div
      aria-hidden={!isVisible}
      style={{
        position: 'fixed',
        inset: 0,
        zIndex: 99999,
        display: isVisible ? 'flex' : 'none',
        alignItems: 'center',
        justifyContent: 'center',
        background: 'rgba(249, 250, 251, 0.9)',
        backdropFilter: 'blur(6px) saturate(120%)',
      }}
    >
      <div role="status" aria-live="polite" aria-busy={isVisible} style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 14 }}>
        <div className="sk-fold">
          <div className="sk-fold-cube" />
          <div className="sk-fold-cube" />
          <div className="sk-fold-cube" />
          <div className="sk-fold-cube" />
        </div>
      </div>
      <style>{`
        @keyframes blink { to { visibility: hidden; } }
        /* Folding cube spinner */
        .sk-fold { width: 48px; height: 48px; position: relative; transform: rotateZ(45deg); }
        .sk-fold-cube { float: left; width: 50%; height: 50%; position: relative; transform: scale(1.1); }
        .sk-fold-cube:before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background:rgb(126, 167, 202); animation: sk-foldCubeAngle 2.4s infinite linear both; transform-origin: 100% 100%; }
        .sk-fold-cube:nth-child(2):before { background: #7299ba; animation-delay: 0.3s; }
        .sk-fold-cube:nth-child(3):before { background: #6188a8; animation-delay: 0.6s; }
        .sk-fold-cube:nth-child(4):before { background: #507694; animation-delay: 0.9s; }
        @keyframes sk-foldCubeAngle {
          0%, 10% { transform: perspective(140px) rotateX(-180deg); opacity: 0; }
          25%, 75% { transform: perspective(140px) rotateX(0deg); opacity: 1; }
          90%, 100% { transform: perspective(140px) rotateY(180deg); opacity: 0; }
        }
        @media (prefers-color-scheme: dark) {
          body > div[aria-hidden] { background: rgba(17, 24, 39, 0.78) !important; }
        }
      `}</style>
    </div>
  ), [isVisible])

  return (
    <>
      {children}
      {overlay}
    </>
  )
}


