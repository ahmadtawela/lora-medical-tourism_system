import { Clock, FileText, DollarSign } from "lucide-react"
import { patientApi } from "../../api/lora-clinic-api-client"
import { useApiQuery } from "../../hooks/useApiQuery"
import { $C, $D, StatCard, StatusPill, LoadingBlock, ErrorBlock, InterestPill, countryLabel } from "../../components/shared"

export default function PatientDashboardPage() {
  const { data, loading, error, refetch } = useApiQuery(() => patientApi.dashboard(), [])

  if (loading) return <LoadingBlock />
  if (error) return <ErrorBlock message={error.message} onRetry={refetch} />

  const { patient, stats, last_invoice } = data

  return (
    <div className="flex flex-col gap-6">
      <div className="rounded-2xl p-6 text-white flex items-center justify-between flex-wrap gap-4" style={{ background: "#1C4A5E" }}>
        <div>
          <p className="text-sm opacity-75">مرحبًا بعودتك</p>
          <p className="text-xl font-bold mt-0.5" style={{ fontFamily: "Cairo, sans-serif" }}>{patient.name}</p>
          <p className="text-sm opacity-75 mt-1">{countryLabel(patient.country)} · {patient.operation_type}</p>
        </div>
        <InterestPill level={patient.interest_level} label={patient.interest_level_label} />
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <StatCard icon={Clock} label="المبلغ المتبقي" value={$C(stats.remaining_amount)} accent="#C8282B" />
        <StatCard icon={DollarSign} label="فواتيري" value={stats.invoices_count} accent="#B8850A" />
        <StatCard icon={FileText} label="تقاريري" value={stats.reports_count} accent="#1C4A5E" />
      </div>

      <div className="bg-card rounded-2xl border border-border/50 p-5">
        <h2 className="font-bold text-foreground mb-4">آخر فاتورة</h2>
        {!last_invoice ? (
          <p className="text-sm text-muted-foreground py-6 text-center">لا توجد فواتير بعد</p>
        ) : (
          <div className="flex flex-col gap-4">
            <div className="flex items-center justify-between flex-wrap gap-2">
              <div>
                <StatusPill status={last_invoice.status} label={last_invoice.status_label} />
                <p className="font-mono text-sm text-foreground mt-2">{last_invoice.invoice_number}</p>
                <p className="text-xs text-muted-foreground">{last_invoice.operation_type}</p>
              </div>
            </div>
            <div className="grid grid-cols-3 gap-3">
              <div className="bg-muted rounded-xl p-3 text-center">
                <p className="text-xs text-muted-foreground">المتبقي</p>
                <p className="font-bold text-foreground mt-1">{$C(last_invoice.remaining_amount)}</p>
              </div>
              <div className="bg-muted rounded-xl p-3 text-center">
                <p className="text-xs text-muted-foreground">المدفوع</p>
                <p className="font-bold text-foreground mt-1">{$C(last_invoice.paid_amount)}</p>
              </div>
              <div className="bg-muted rounded-xl p-3 text-center">
                <p className="text-xs text-muted-foreground">الإجمالي</p>
                <p className="font-bold text-foreground mt-1">{$C(last_invoice.total_amount)}</p>
              </div>
            </div>
            <div>
              <div className="flex items-center justify-between text-xs text-muted-foreground mb-1">
                <span>نسبة السداد</span><span>{last_invoice.payment_percentage}%</span>
              </div>
              <div className="h-2 rounded-full bg-muted overflow-hidden">
                <div className="h-full rounded-full" style={{ width: `${last_invoice.payment_percentage}%`, background: "#2D6A4F" }} />
              </div>
            </div>
            <p className="text-xs text-muted-foreground">تاريخ الاستحقاق: {$D(last_invoice.due_date)}</p>
          </div>
        )}
      </div>
    </div>
  )
}
