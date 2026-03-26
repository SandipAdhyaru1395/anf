"use client"

import React, { InputHTMLAttributes, forwardRef, useId, useState } from "react"
import { Eye, EyeOff } from "lucide-react"

export type FloatingInputProps = {
  label: string
  containerClassName?: string
  labelClassName?: string
  inputClassName?: string
  error?: string | null
} & InputHTMLAttributes<HTMLInputElement>

const FloatingInput = forwardRef<HTMLInputElement, FloatingInputProps>(
function FloatingInput(
{ label, containerClassName = "", labelClassName = "", inputClassName = "", type = "text", error, ...rest },
ref
) {

const autoId = useId()
const id = rest.id || autoId
const [showPassword, setShowPassword] = useState(false)

const inputType =
type === "password"
? showPassword
? "text"
: "password"
: type

return (

<div className={`w-full ${containerClassName}`}>

{/* Label */}

<label
htmlFor={id}
className={`form-field-label block mb-1 ${labelClassName}`}
>
{label}
</label>

{/* Input Wrapper */}

<div className="relative">
  
<input
  id={id}
  ref={ref}
  type={inputType}
  {...rest}
  className={`w-full mb-0 rounded-md border px-4 py-3 pr-10 outline-none transition focus:ring-1 focus:ring-blue-500 ${inputClassName}
  ${error ? "!border-red-500 focus:!border-red-600" : ""}`}
/>

{/* Eye Icon */}

{type === "password" && (
<button
type="button"
onClick={() => setShowPassword(!showPassword)}
className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"
>
{showPassword ? <EyeOff size={20} /> : <Eye size={20} />}
</button>
)}

</div>

{/* Error */}

{error && (
<p className="text-red-600 text-xs mt-1">{error}</p>
)}

</div>
)
}
)

export default FloatingInput
