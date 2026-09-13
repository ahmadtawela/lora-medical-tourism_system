import { useState } from "react"
import { Stethoscope, AlertCircle, Eye, EyeOff, CheckCircle2 } from "lucide-react"
import { useAuth } from "../api/AuthContext"
import { inputClass, labelClass } from "../components/shared"

function getQueryParams() {
  const params = new URLSearchParams(window.location.search)
  return { token: params.get("token") || "", email: params.get("email") || "" }
}

export default function SetPasswordPage() {
  const { completeAccountSetup } = useAuth()
  const { token, email } = getQueryParams()

  const [password, setPassword] = useState("")
  const [confirmPassword, setConfirmPassword] = useState("")
  const [showPassword, setShowPassword] = useState(false)
  const [error, setError] = useState("")
  const [loading, setLoading] = useState(false)
  const [done, setDone] = useState(false)

  const linkIsMissingData = !token || !email

  async function handleSubmit(e) {
    e.preventDefault()
    setError("")

    if (password.length < 8) {
      setError("يجب أن تكون كلمة المرور 8 أحرف على الأقل")
      return
    }
    if (password !== confirmPassword) {
      setError("كلمتا المرور غير متطابقتين")
      return
    }

    setLoading(true)
    try {
      await completeAccountSetup(email, token, password, confirmPassword)
      window.history.replaceState(null, "", "/") // نظّف الرابط حتى لا يُعاد استخدامه بعد تحديث الصفحة
      setDone(true)
    } catch (err) {
      setError(err.errors?.token?.[0] || err.errors?.password?.[0] || err.message || "حدث خطأ، حاول مرة أخرى")
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
          <p className="text-muted-foreground mt-1 text-sm">تفعيل حساب بوابة المريض</p>
        </div>

        <div className="bg-white rounded-2xl shadow-sm border border-border/40 p-6">
          {linkIsMissingData ? (
            <div className="flex flex-col items-center text-center gap-2 py-4">
              <AlertCircle size={28} className="text-destructive" />
              <p className="text-sm text-foreground">هذا الرابط غير مكتمل أو غير صالح.</p>
              <p className="text-xs text-muted-foreground">تأكد من فتح نفس الرابط المُرسَل إليك بالكامل عبر البريد أو واتساب.</p>
            </div>
          ) : done ? (
            <div className="flex flex-col items-center text-center gap-3 py-2">
              <CheckCircle2 size={36} color="#2D6A4F" />
              <p className="text-sm text-foreground">تم تفعيل حسابك بنجاح، وتم تسجيل دخولك تلقائيًا.</p>
              <button
                onClick={() => window.location.href = "/"}
                className="w-full py-2.5 rounded-xl text-white text-sm font-semibold mt-1"
                style={{ background: "#1C4A5E" }}
              >
                الذهاب لبوابتي
              </button>
            </div>
          ) : (
            <form onSubmit={handleSubmit} className="flex flex-col gap-4">
              <p className="text-sm text-muted-foreground -mt-1">مرحبًا! أنشئ كلمة مرور لحسابك ({email}) لتفعيل بوابة المريض.</p>

              <div>
                <label className={labelClass}>كلمة المرور الجديدة</label>
                <div className="relative">
                  <input
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    className={`${inputClass} pl-10`}
                    type={showPassword ? "text" : "password"}
                    placeholder="8 أحرف على الأقل"
                    required
                    autoFocus
                  />
                  <button
                    type="button"
                    onClick={() => setShowPassword((v) => !v)}
                    className="absolute left-2.5 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition p-1"
                    tabIndex={-1}
                  >
                    {showPassword ? <EyeOff size={17} /> : <Eye size={17} />}
                  </button>
                </div>
              </div>

              <div>
                <label className={labelClass}>تأكيد كلمة المرور</label>
                <input
                  value={confirmPassword}
                  onChange={(e) => setConfirmPassword(e.target.value)}
                  className={inputClass}
                  type={showPassword ? "text" : "password"}
                  required
                />
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
                {loading ? "جارِ التفعيل..." : "تفعيل الحساب"}
              </button>
            </form>
          )}
        </div>
      </div>
    </div>
  )
}
