"use client"

import React, { InputHTMLAttributes, forwardRef, useId } from "react"

export type FloatingInputProps = {
  label: string
  containerClassName?: string
  error?: string | null
} & InputHTMLAttributes<HTMLInputElement>

const FloatingInput = forwardRef<HTMLInputElement, FloatingInputProps>(function FloatingInput(
  { label, containerClassName = "", type = "text", placeholder, error, ...rest },
  ref
) {
  const autoId = useId()
  const id = rest.id || autoId
  
  return (
    <div className={`relative ${containerClassName}`}>
      <input
        id={id}
        ref={ref}
        type={type}
        className={`peer w-full rounded px-[16px] py-[16px] mt-1 outline-none placeholder-gray-400 focus:placeholder-transparent transition-all duration-150
          focus:!px-[16px] focus:!pt-[22px] focus:!pb-[10px]
          [&:not(:placeholder-shown)]:!px-[16px] [&:not(:placeholder-shown)]:!pt-[22px] [&:not(:placeholder-shown)]:!pb-[10px] [&:not(:placeholder-shown)]:placeholder-transparent
          ${error ? 'border-red-500 focus:border-red-600 border' : 'border focus:border-black/70'} ${rest.className || ''}`}
        placeholder={placeholder}
        {...rest}
      />
      <label
        htmlFor={id}
        className={`absolute left-3 top-2 ${error ? 'text-red-600' : 'text-gray-400'} text-[10px] transition-opacity duration-150 pointer-events-none bg-transparent
          opacity-0 peer-focus:opacity-100 peer-[:not(:placeholder-shown)]:opacity-100
        `}
      >
        {label}
      </label>
      {error && <p className="text-red-600 text-xs mb-4 ml-4">{error}</p>}
    </div>
  )
})

export default FloatingInput


