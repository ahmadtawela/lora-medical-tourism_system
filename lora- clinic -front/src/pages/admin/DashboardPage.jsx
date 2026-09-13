import { AlertTriangle, FileText, DollarSign, Users, Clock, AlertCircle, Bell } from "lucide-react"
import { adminApi } from "../../api/lora-clinic-api-client"
import { useApiQuery } from "../../hooks/useApiQuery"
import { $C, $D, Avatar, initialsOf, colorFor, InterestPill, StatusPill, StatCard, LoadingBlock, ErrorBlock, EmptyState } from "../../components/shared"

const NOTIF_ICON = { upcoming_operation: Clock, payment_overdue: AlertTriangle, new_report: FileText }

export default function DashboardPage({ onNav }) {
  const { data, loading, error, refetch } = useApiQuery(() => adminApi.dashboard(), [])

  if (loading) return <LoadingBlock />
  if (error) return <ErrorBlock message={error.message} onRetry={refetch} />

  const { stats, urgent_notifications, recent_patients, recent_invoices } = data

  return (
    <div className="flex flex-col gap-6">
      <div>
        <h1 className="text-2xl font-bold text-foreground" style={{ fontFamily: "Cairo, sans-serif" }}>لوحة التحكم</h1>
        <p className="text-muted-foreground text-sm mt-0.5">مرحبًا بك في نظام Lora Clinic</p>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard icon={AlertCircle} label="فواتير متأخرة" value={stats.overdue_invoices_count} sub="تجاوزت الموعد" accent="#C8282B" />
        <StatCard icon={FileText} label="فواتير معلقة" value={stats.pending_invoices_count} sub="تحتاج متابعة" accent="#B8850A" />
        <StatCard icon={DollarSign} label="الإيرادات المحصّلة" value={$C(stats.collected_revenue)} sub="إجمالي المدفوعات" accent="#2D6A4F" />
        <StatCard icon={Users} label="إجمالي المرضى" value={stats.total_patients} sub="مريض مسجّل" accent="#1C4A5E" />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="bg-card rounded-2xl border border-border/50 p-5">
          <div className="flex items-center justify-between mb-4">
            <h2 className="font-bold text-foreground">تنبيهات عاجلة</h2>
            <button onClick={() => onNav("notifications")} className="text-xs text-primary font-semibold hover:underline">الكل</button>
          </div>
          {urgent_notifications.length === 0 ? (
            <EmptyState title="لا توجد تنبيهات غير مقروءة" />
          ) : (
            <div className="flex flex-col gap-2">
              {urgent_notifications.map((n) => {
                const Icon = NOTIF_ICON[n.type] || Bell
                return (
                  <div key={n.id} className="flex items-start gap-3 p-3 rounded-xl" style={{ background: n.severity === "danger" ? "#FEF2F2" : "#F8FAFC" }}>
                    <Icon size={16} className="mt-0.5 shrink-0" color={n.severity === "danger" ? "#C8282B" : "#B8850A"} />
                    <div className="min-w-0">
                      <p className="text-sm text-foreground leading-snug">{n.title}</p>
                      <p className="text-xs text-muted-foreground mt-0.5">{n.type_label} · {$D(n.event_date)}</p>
                    </div>
                  </div>
                )
              })}
            </div>
          )}
        </div>

        <div className="bg-card rounded-2xl border border-border/50 p-5">
          <div className="flex items-center justify-between mb-4">
            <h2 className="font-bold text-foreground">أحدث المرضى</h2>
            <button onClick={() => onNav("patients")} className="text-xs text-primary font-semibold hover:underline">عرض الكل</button>
          </div>
          {recent_patients.length === 0 ? (
            <EmptyState title="لا يوجد مرضى بعد" description="أضف أول مريض من قسم إدارة المرضى" />
          ) : (
            <div className="flex flex-col gap-3">
              {recent_patients.map((p) => (
                <div key={p.id} className="flex items-center gap-3">
                  <Avatar initials={p.initials || initialsOf(p.name)} color={colorFor(p.id)} size={9} />
                  <div className="min-w-0 flex-1">
                    <p className="text-sm font-semibold text-foreground truncate">{p.name}</p>
                    <p className="text-xs text-muted-foreground">{p.country} · {p.operation_type}</p>
                  </div>
                  <InterestPill level={p.interest_level} label={p.interest_level_label} />
                </div>
              ))}
            </div>
          )}
        </div>
      </div>

      <div className="bg-card rounded-2xl border border-border/50 p-5">
        <div className="flex items-center justify-between mb-4">
          <h2 className="font-bold text-foreground">آخر الفواتير</h2>
          <button onClick={() => onNav("invoices")} className="text-xs text-primary font-semibold hover:underline">إدارة الفواتير</button>
        </div>
        {recent_invoices.length === 0 ? (
          <EmptyState title="لا توجد فواتير بعد" />
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="text-right text-xs text-muted-foreground border-b border-border/50">
                  <th className="pb-2 font-medium">رقم الفاتورة</th>
                  <th className="pb-2 font-medium">المريض</th>
                  <th className="pb-2 font-medium">العملية</th>
                  <th className="pb-2 font-medium">الإجمالي</th>
                  <th className="pb-2 font-medium">المدفوع</th>
                  <th className="pb-2 font-medium">الحالة</th>
                </tr>
              </thead>
              <tbody>
                {recent_invoices.map((inv) => (
                  <tr key={inv.id} className="border-b border-border/30 last:border-0">
                    <td className="py-2.5 font-mono text-xs text-foreground">{inv.invoice_number}</td>
                    <td className="py-2.5 text-foreground">{inv.patient.name}</td>
                    <td className="py-2.5 text-muted-foreground">{inv.operation_type}</td>
                    <td className="py-2.5 font-semibold text-foreground">{$C(inv.total_amount)}</td>
                    <td className="py-2.5" style={{ color: "#2D6A4F" }}>{$C(inv.paid_amount)}</td>
                    <td className="py-2.5"><StatusPill status={inv.status} label={inv.status_label} /></td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  )
}
