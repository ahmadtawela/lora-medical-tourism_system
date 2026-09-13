import { useState } from "react"
import { Stethoscope, AlertCircle, Eye, EyeOff } from "lucide-react"
import { useAuth } from "../api/AuthContext"
import { inputClass, labelClass } from "../components/shared"

export default function LoginPage() {
  const { login } = useAuth()
  const [email, setEmail] = useState("")
  const [password, setPassword] = useState("")
  const [showPassword, setShowPassword] = useState(false)
  const [error, setError] = useState("")
  const [loading, setLoading] = useState(false)

  async function handleSubmit(e) {
    e.preventDefault()
    setError("")
    setLoading(true)
    try {
      await login(email, password)
      // لا حاجة لأي تنقّل يدوي - App.jsx يعيد العرض تلقائيًا فور تغيّر actor
    } catch {
      setError("البريد الإلكتروني أو كلمة المرور غير صحيحة")
    } finally {
      setLoading(false)
    }
  }

  return (
    <div
      dir="rtl"
      className="min-h-screen bg-background flex items-center justify-center p-4"
      style={{ fontFamily: "Tajawal, sans-serif", backgroundImage: "radial-gradient(ellipse at 60% 20%, rgba(28,74,94,0.08) 0%, transparent 60%)" }}
    >
      <div className="w-full max-w-md">
        <div className="text-center mb-8">
          <div className="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-4 shadow-lg" style={{ background: "#1C4A5E" }}>
            <Stethoscope size={28} color="#B8850A" />
          </div>
          <h1 className="text-3xl font-bold text-foreground" style={{ fontFamily: "Cairo, sans-serif" }}>Lora Clinic</h1>
          <p className="text-muted-foreground mt-1 text-sm">نظام إدارة مرضى السياحة العلاجية</p>
        </div>

        <form onSubmit={handleSubmit} className="bg-white rounded-2xl shadow-sm border border-border/40 p-6 flex flex-col gap-4">
          <div>
            <label className={labelClass}>البريد الإلكتروني</label>
            <input value={email} onChange={(e) => setEmail(e.target.value)} className={inputClass} type="email" required autoFocus />
          </div>

          <div>
            <label className={labelClass}>كلمة المرور</label>
            <div className="relative">
              <input
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                placeholder="••••••••"
                className={`${inputClass} pl-10`}
                type={showPassword ? "text" : "password"}
                required
              />
              <button
                type="button"
                onClick={() => setShowPassword((v) => !v)}
                className="absolute left-2.5 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition p-1"
                aria-label={showPassword ? "إخفاء كلمة المرور" : "إظهار كلمة المرور"}
                tabIndex={-1}
              >
                {showPassword ? <EyeOff size={17} /> : <Eye size={17} />}
              </button>
            </div>
          </div>

          {error && (
            <div className="flex items-center gap-2 text-destructive text-sm bg-red-50 px-3 py-2 rounded-lg">
              <AlertCircle size={14} /><span>{error}</span>
            </div>
          )}

          <button
            type="submit"
            disabled={loading}
            className="w-full py-3 rounded-xl text-white font-semibold text-sm mt-1 transition-all hover:opacity-90 active:scale-[0.98] disabled:opacity-60"
            style={{ background: "#1C4A5E" }}
          >
            {loading ? "جارِ التحقق..." : "تسجيل الدخول"}
          </button>
        </form>
      </div>
    </div>
  )
}
