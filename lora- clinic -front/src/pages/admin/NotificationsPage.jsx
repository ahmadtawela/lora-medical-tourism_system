import { useState } from "react"
import { Bell, Clock, AlertTriangle, FileText, CheckCheck } from "lucide-react"
import { adminApi } from "../../api/lora-clinic-api-client"
import { useApiQuery } from "../../hooks/useApiQuery"
import { $D, LoadingBlock, ErrorBlock, EmptyState } from "../../components/shared"

const TABS = [
  { value: "all", label: "الكل" },
  { value: "new_report", label: "تقارير جديدة" },
  { value: "payment_overdue", label: "دفعات متأخرة" },
  { value: "upcoming_operation", label: "عمليات قادمة" },
  { value: "unread", label: "غير مقروء" },
]

const TYPE_ICON = { upcoming_operation: Clock, payment_overdue: AlertTriangle, new_report: FileText }

export default function NotificationsPage() {
  const [filter, setFilter] = useState("all")
  const { data, loading, error, refetch } = useApiQuery(() => adminApi.notifications.list(filter), [filter])

  async function markAllRead() {
    await adminApi.notifications.markAllAsRead()
    refetch()
  }

  async function markOneRead(id) {
    await adminApi.notifications.markAsRead(id)
    refetch()
  }

  return (
    <div className="flex flex-col gap-5">
      <div className="flex items-center justify-between flex-wrap gap-3">
        <div>
          <h1 className="text-2xl font-bold text-foreground" style={{ fontFamily: "Cairo, sans-serif" }}>التنبيهات</h1>
          <p className="text-muted-foreground text-sm mt-0.5">{data?.unread_count ?? 0} تنبيه غير مقروء</p>
        </div>
        {data?.unread_count > 0 && (
          <button onClick={markAllRead} className="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-muted text-foreground hover:bg-muted/70 transition">
            <CheckCheck size={16} /> تحديد الكل كمقروء
          </button>
        )}
      </div>

      <div className="flex flex-wrap gap-2">
        {TABS.map((t) => (
          <button
            key={t.value}
            onClick={() => setFilter(t.value)}
            className="px-4 py-2 rounded-xl text-sm font-semibold transition"
            style={filter === t.value ? { background: "#1C4A5E", color: "white" } : { background: "var(--muted)", color: "var(--muted-foreground)" }}
          >
            {t.label}
          </button>
        ))}
      </div>

      <div className="bg-card rounded-2xl border border-border/50 overflow-hidden">
        {loading ? <LoadingBlock /> : error ? <ErrorBlock message={error.message} onRetry={refetch} /> : data.data.length === 0 ? (
          <EmptyState title="لا توجد تنبيهات" />
        ) : (
          <div className="divide-y divide-border/30">
            {data.data.map((n) => {
              const Icon = TYPE_ICON[n.type] || Bell
              return (
                <button
                  key={n.id}
                  onClick={() => !n.is_read && markOneRead(n.id)}
                  className="w-full flex items-start gap-3 p-4 text-right transition hover:bg-muted/40"
                  style={{ background: n.is_read ? "transparent" : n.severity === "danger" ? "#FEF2F2" : "#FFFBEB" }}
                >
                  {!n.is_read && <span className="w-2 h-2 rounded-full mt-1.5 shrink-0" style={{ background: "#C8282B" }} />}
                  <Icon size={17} className="mt-0.5 shrink-0" color={n.severity === "danger" ? "#C8282B" : "#B8850A"} />
                  <div className="min-w-0 flex-1">
                    <p className="text-sm text-foreground leading-snug">{n.title}</p>
                    <p className="text-xs text-muted-foreground mt-1">
                      {n.patient?.name && <span>{n.patient.name} · </span>}
                      {n.type_label} · {$D(n.event_date)}
                    </p>
                  </div>
                </button>
              )
            })}
          </div>
        )}
      </div>
    </div>
  )
}
