import { patientApi } from "../../api/lora-clinic-api-client"
import { useApiQuery } from "../../hooks/useApiQuery"
import { $C, $D, StatusPill, LoadingBlock, ErrorBlock, EmptyState } from "../../components/shared"

export default function PatientInvoicesPage() {
  const { data, loading, error, refetch } = useApiQuery(() => patientApi.invoices.list(), [])

  return (
    <div className="flex flex-col gap-5">
      <div>
        <h1 className="text-2xl font-bold text-foreground" style={{ fontFamily: "Cairo, sans-serif" }}>فواتيري</h1>
        <p className="text-muted-foreground text-sm mt-0.5">{data?.meta?.total ?? "—"} فاتورة</p>
      </div>

      {loading ? <LoadingBlock /> : error ? <ErrorBlock message={error.message} onRetry={refetch} /> : data.data.length === 0 ? (
        <div className="bg-card rounded-2xl border border-border/50">
          <EmptyState title="لا توجد فواتير بعد" />
        </div>
      ) : (
        <div className="flex flex-col gap-4">
          {data.data.map((inv) => (
            <div key={inv.id} className="bg-card rounded-2xl border border-border/50 p-5">
              <div className="flex items-start justify-between flex-wrap gap-2 mb-4">
                <div>
                  <StatusPill status={inv.status} label={inv.status_label} />
                  <p className="font-mono text-sm text-foreground mt-2">{inv.invoice_number}</p>
                  <p className="text-xs text-muted-foreground">{inv.operation_type}</p>
                </div>
                <p className="text-xs text-muted-foreground">استحقاق: {$D(inv.due_date)}</p>
              </div>
              <div className="grid grid-cols-3 gap-3 mb-3">
                <div className="bg-muted rounded-xl p-3 text-center">
                  <p className="text-xs text-muted-foreground">المتبقي</p>
                  <p className="font-bold text-foreground mt-1">{$C(inv.remaining_amount)}</p>
                </div>
                <div className="bg-muted rounded-xl p-3 text-center">
                  <p className="text-xs text-muted-foreground">المدفوع</p>
                  <p className="font-bold text-foreground mt-1">{$C(inv.paid_amount)}</p>
                </div>
                <div className="bg-muted rounded-xl p-3 text-center">
                  <p className="text-xs text-muted-foreground">الإجمالي</p>
                  <p className="font-bold text-foreground mt-1">{$C(inv.total_amount)}</p>
                </div>
              </div>
              <div className="flex items-center justify-between text-xs text-muted-foreground mb-1">
                <span>نسبة السداد</span><span>{inv.payment_percentage}%</span>
              </div>
              <div className="h-2 rounded-full bg-muted overflow-hidden">
                <div className="h-full rounded-full" style={{ width: `${inv.payment_percentage}%`, background: "#2D6A4F" }} />
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  )
}
