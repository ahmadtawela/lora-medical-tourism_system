import { Loader2, Inbox, AlertTriangle } from "lucide-react"

export const $C = (n) => `$${Number(n || 0).toLocaleString("en-US")}`
export const $D = (s) => s ? new Date(s).toLocaleDateString("ar-SA", { year: "numeric", month: "short", day: "numeric" }) : "—"

export function Avatar({ initials, color = "#1C4A5E", size = 10 }) {
  return (
    <div
      className="rounded-full flex items-center justify-center text-white font-bold shrink-0"
      style={{ width: `${size * 4}px`, height: `${size * 4}px`, background: color, fontSize: `${size * 1.3}px` }}
    >
      {initials}
    </div>
  )
}

export function initialsOf(name = "") {
  const parts = name.trim().split(/\s+/)
  return ((parts[0]?.[0] || "") + (parts[1]?.[0] || "")).toUpperCase()
}

export function colorFor(id) {
  const palette = ["#1C4A5E", "#2D6A4F", "#6B4F8C", "#B8850A", "#C8282B", "#0F2D3D"]
  return palette[Number(id) % palette.length]
}

export function Pill({ label, bg, text }) {
  return (
    <span className="px-2.5 py-1 rounded-full text-xs font-semibold whitespace-nowrap" style={{ background: bg, color: text }}>
      {label}
    </span>
  )
}

const INTEREST_STYLE = {
  very_interested: { bg: "#DCFCE7", text: "#166534" },
  interested: { bg: "#DBEAFE", text: "#1E40AF" },
  follow_up: { bg: "#FEF9C3", text: "#854D0E" },
  not_interested: { bg: "#FEE2E2", text: "#991B1B" },
}
export function InterestPill({ level, label }) {
  const s = INTEREST_STYLE[level] || INTEREST_STYLE.interested
  return <Pill label={label} bg={s.bg} text={s.text} />
}

const STATUS_STYLE = {
  paid: { bg: "#DCFCE7", text: "#166534" },
  partial: { bg: "#FEF9C3", text: "#854D0E" },
  overdue: { bg: "#FEE2E2", text: "#991B1B" },
  pending: { bg: "#E4DDD3", text: "#57534E" },
}
export function StatusPill({ status, label }) {
  const s = STATUS_STYLE[status] || STATUS_STYLE.pending
  return <Pill label={label} bg={s.bg} text={s.text} />
}

export function StatCard({ icon: Icon, label, value, sub, accent = "#1C4A5E" }) {
  return (
    <div className="bg-card rounded-2xl border border-border/50 p-5 flex items-start gap-4">
      <div className="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style={{ background: `${accent}1A` }}>
        <Icon size={20} color={accent} />
      </div>
      <div className="min-w-0">
        <p className="text-sm text-muted-foreground">{label}</p>
        <p className="text-2xl font-bold text-foreground mt-0.5">{value}</p>
        {sub && <p className="text-xs text-muted-foreground mt-0.5">{sub}</p>}
      </div>
    </div>
  )
}

export function LoadingBlock({ label = "جارِ التحميل..." }) {
  return (
    <div className="flex flex-col items-center justify-center py-16 text-muted-foreground gap-2">
      <Loader2 size={22} className="animate-spin" />
      <span className="text-sm">{label}</span>
    </div>
  )
}

export function ErrorBlock({ message, onRetry }) {
  return (
    <div className="flex flex-col items-center justify-center py-16 text-center gap-2">
      <AlertTriangle size={22} className="text-destructive" />
      <p className="text-sm text-foreground">{message || "حدث خطأ أثناء تحميل البيانات"}</p>
      {onRetry && (
        <button onClick={onRetry} className="text-sm text-primary font-semibold hover:underline mt-1">
          إعادة المحاولة
        </button>
      )}
    </div>
  )
}

export function EmptyState({ title = "لا توجد بيانات بعد", description }) {
  return (
    <div className="flex flex-col items-center justify-center py-16 text-center gap-2">
      <Inbox size={26} className="text-muted-foreground" />
      <p className="text-sm font-semibold text-foreground">{title}</p>
      {description && <p className="text-xs text-muted-foreground">{description}</p>}
    </div>
  )
}

/** نافذة تأكيد بسيطة (للحذف مثلًا) */
export function ConfirmDialog({ open, title, description, confirmLabel = "تأكيد", danger = true, loading, onConfirm, onCancel }) {
  if (!open) return null
  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" onClick={onCancel}>
      <div className="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6" onClick={(e) => e.stopPropagation()}>
        <h3 className="font-bold text-foreground mb-1.5">{title}</h3>
        {description && <p className="text-sm text-muted-foreground mb-5">{description}</p>}
        <div className="flex gap-2 justify-end">
          <button onClick={onCancel} className="px-4 py-2 rounded-xl text-sm font-semibold text-foreground bg-muted hover:bg-muted/70 transition">
            إلغاء
          </button>
          <button
            onClick={onConfirm}
            disabled={loading}
            className="px-4 py-2 rounded-xl text-sm font-semibold text-white transition disabled:opacity-60"
            style={{ background: danger ? "#C8282B" : "#1C4A5E" }}
          >
            {loading ? "جارِ التنفيذ..." : confirmLabel}
          </button>
        </div>
      </div>
    </div>
  )
}

/** غلاف موحّد لكل نوافذ الإضافة/التعديل المنبثقة */
export function ModalShell({ title, onClose, children, width = "max-w-lg" }) {
  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" onClick={onClose}>
      <div
        className={`bg-white rounded-2xl shadow-xl w-full ${width} max-h-[90vh] overflow-y-auto`}
        onClick={(e) => e.stopPropagation()}
      >
        <div className="flex items-center justify-between px-6 py-4 border-b border-border/50 sticky top-0 bg-white rounded-t-2xl">
          <h3 className="font-bold text-foreground">{title}</h3>
          <button onClick={onClose} className="text-muted-foreground hover:text-foreground transition">✕</button>
        </div>
        <div className="p-6">{children}</div>
      </div>
    </div>
  )
}

export function FieldError({ errors, field }) {
  const msg = errors?.[field]?.[0]
  if (!msg) return null
  return <p className="text-xs text-destructive mt-1">{msg}</p>
}

export const inputClass =
  "w-full px-3.5 py-2.5 rounded-xl border border-border bg-input-background text-sm text-foreground outline-none focus:ring-2 focus:ring-primary/30 transition"
export const labelClass = "block text-sm font-semibold text-foreground mb-1.5"

export const COUNTRIES = [
  { code: "SA", label: "السعودية" }, { code: "AE", label: "الإمارات" }, { code: "KW", label: "الكويت" },
  { code: "QA", label: "قطر" }, { code: "BH", label: "البحرين" }, { code: "OM", label: "عُمان" },
  { code: "IQ", label: "العراق" }, { code: "EG", label: "مصر" }, { code: "JO", label: "الأردن" },
  { code: "LY", label: "ليبيا" }, { code: "TN", label: "تونس" }, { code: "MA", label: "المغرب" },
  { code: "TR", label: "تركيا" }, { code: "SY", label: "سوريا" },
]

export const OPERATIONS = [
  "زراعة الشعر", "تكميم المعدة", "زراعة الأسنان", "تجميل الأنف (رينوبلاستي)",
  "جراحة القلب", "شفط الدهون", "علاج الأورام", "زراعة الكبد",
  "جراحة العيون (ليزك)", "تقويم العظام", "تكبير الثدي",
]

export const INTEREST_LEVELS = [
  { value: "very_interested", label: "مهتم جدًا" },
  { value: "interested", label: "مهتم" },
  { value: "follow_up", label: "متابعة" },
  { value: "not_interested", label: "غير مهتم" },
]

export const INVOICE_STATUSES = [
  { value: "pending", label: "معلقة" },
  { value: "partial", label: "جزئي" },
  { value: "paid", label: "مدفوعة" },
  { value: "overdue", label: "متأخرة" },
]

export const countryLabel = (code) => COUNTRIES.find((c) => c.code === code)?.label || code

/** رابط "افتح محادثة واتساب" الرسمي (wa.me) - يتطلب الرقم بصيغة دولية بدون + أو مسافات */
export const waLink = (phone) => `https://wa.me/${String(phone || "").replace(/[^\d]/g, "")}`

export const selectClass =
  "w-full px-3.5 py-2.5 rounded-xl border border-border bg-input-background text-sm text-foreground outline-none focus:ring-2 focus:ring-primary/30 transition"

export function Pagination({ meta, page, onPageChange }) {
  if (!meta || meta.last_page <= 1) return null
  return (
    <div className="flex items-center justify-between pt-4 mt-2 border-t border-border/50">
      <p className="text-xs text-muted-foreground">
        صفحة {meta.current_page} من {meta.last_page} · {meta.total} سجل
      </p>
      <div className="flex gap-2">
        <button
          disabled={page <= 1}
          onClick={() => onPageChange(page - 1)}
          className="px-3 py-1.5 rounded-lg text-xs font-semibold bg-muted text-foreground disabled:opacity-40 hover:bg-muted/70 transition"
        >
          السابق
        </button>
        <button
          disabled={page >= meta.last_page}
          onClick={() => onPageChange(page + 1)}
          className="px-3 py-1.5 rounded-lg text-xs font-semibold bg-muted text-foreground disabled:opacity-40 hover:bg-muted/70 transition"
        >
          التالي
        </button>
      </div>
    </div>
  )
}
