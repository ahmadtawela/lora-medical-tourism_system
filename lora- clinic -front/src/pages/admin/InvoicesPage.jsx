import { useState } from "react"
import { Plus, FileText, AlertCircle, Clock, CheckCircle, Pencil, Trash2 } from "lucide-react"
import { adminApi } from "../../api/lora-clinic-api-client"
import { useApiQuery } from "../../hooks/useApiQuery"
import {
  $C, $D, StatusPill, StatCard, LoadingBlock, ErrorBlock, EmptyState,
  ConfirmDialog, ModalShell, FieldError, Pagination, inputClass, labelClass, selectClass,
  OPERATIONS, INVOICE_STATUSES,
} from "../../components/shared"

const STATUS_TABS = [{ value: "all", label: "الكل" }, ...INVOICE_STATUSES]

export default function InvoicesPage() {
  const [page, setPage] = useState(1)
  const [statusFilter, setStatusFilter] = useState("all")
  const [search, setSearch] = useState("")

  const { data, loading, error, refetch } = useApiQuery(
    () => adminApi.invoices.list({ status: statusFilter, search, page }),
    [statusFilter, search, page]
  )

  const [modalInvoice, setModalInvoice] = useState(null)
  const [deleteTarget, setDeleteTarget] = useState(null)
  const [deleting, setDeleting] = useState(false)

  async function handleDelete() {
    setDeleting(true)
    try {
      await adminApi.invoices.delete(deleteTarget.id)
      setDeleteTarget(null)
      refetch()
    } catch (err) {
      alert(err.message)
    } finally {
      setDeleting(false)
    }
  }

  return (
    <div className="flex flex-col gap-5">
      <div className="flex items-center justify-between flex-wrap gap-3">
        <div>
          <h1 className="text-2xl font-bold text-foreground" style={{ fontFamily: "Cairo, sans-serif" }}>الفواتير</h1>
          <p className="text-muted-foreground text-sm mt-0.5">{data?.stats?.total_invoices ?? "—"} فاتورة</p>
        </div>
        <button
          onClick={() => setModalInvoice({})}
          className="flex items-center gap-2 px-4 py-2.5 rounded-xl text-white text-sm font-semibold transition hover:opacity-90"
          style={{ background: "#B8850A" }}
        >
          <Plus size={16} /> فاتورة جديدة
        </button>
      </div>

      {data?.stats && (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <StatCard icon={FileText} label="إجمالي الفواتير" value={data.stats.total_invoices} accent="#1C4A5E" />
          <StatCard icon={AlertCircle} label="فواتير متأخرة" value={data.stats.overdue_invoices} accent="#C8282B" />
          <StatCard icon={Clock} label="المبلغ المعلّق" value={$C(data.stats.pending_amount)} accent="#B8850A" />
          <StatCard icon={CheckCircle} label="إجمالي المحصَّل" value={$C(data.stats.collected_amount)} accent="#2D6A4F" />
        </div>
      )}

      <div className="flex flex-wrap gap-2">
        {STATUS_TABS.map((t) => (
          <button
            key={t.value}
            onClick={() => { setStatusFilter(t.value); setPage(1) }}
            className="px-4 py-2 rounded-xl text-sm font-semibold transition"
            style={statusFilter === t.value ? { background: "#1C4A5E", color: "white" } : { background: "var(--muted)", color: "var(--muted-foreground)" }}
          >
            {t.label}
          </button>
        ))}
        <input
          value={search}
          onChange={(e) => { setSearch(e.target.value); setPage(1) }}
          placeholder="بحث برقم الفاتورة أو اسم المريض..."
          className={`${inputClass} flex-1 min-w-[200px]`}
        />
      </div>

      <div className="bg-card rounded-2xl border border-border/50 overflow-hidden">
        {loading ? <LoadingBlock /> : error ? <ErrorBlock message={error.message} onRetry={refetch} /> : data.data.length === 0 ? (
          <EmptyState title="لا توجد فواتير مطابقة" description="جرّب تغيير الفلتر، أو أنشئ فاتورة جديدة" />
        ) : (
          <div className="overflow-x-auto p-5">
            <table className="w-full text-sm">
              <thead>
                <tr className="text-right text-xs text-muted-foreground border-b border-border/50">
                  <th className="pb-2 font-medium">رقم الفاتورة</th>
                  <th className="pb-2 font-medium">المريض</th>
                  <th className="pb-2 font-medium">العملية</th>
                  <th className="pb-2 font-medium">الإجمالي</th>
                  <th className="pb-2 font-medium">المدفوع</th>
                  <th className="pb-2 font-medium">المتبقي</th>
                  <th className="pb-2 font-medium">الحالة</th>
                  <th className="pb-2 font-medium">الاستحقاق</th>
                  <th className="pb-2 font-medium">إجراء</th>
                </tr>
              </thead>
              <tbody>
                {data.data.map((inv) => (
                  <tr key={inv.id} className="border-b border-border/30 last:border-0">
                    <td className="py-3 font-mono text-xs text-foreground">{inv.invoice_number}</td>
                    <td className="py-3 text-foreground">{inv.patient.name}</td>
                    <td className="py-3 text-muted-foreground">{inv.operation_type}</td>
                    <td className="py-3 font-semibold text-foreground">{$C(inv.total_amount)}</td>
                    <td className="py-3" style={{ color: "#2D6A4F" }}>{$C(inv.paid_amount)}</td>
                    <td className="py-3" style={{ color: inv.remaining_amount > 0 ? "#C8282B" : "var(--muted-foreground)" }}>{$C(inv.remaining_amount)}</td>
                    <td className="py-3"><StatusPill status={inv.status} label={inv.status_label} /></td>
                    <td className="py-3 text-muted-foreground text-xs">{$D(inv.due_date)}</td>
                    <td className="py-3">
                      <div className="flex items-center gap-1">
                        <button onClick={() => setModalInvoice(inv)} className="p-1.5 rounded-lg hover:bg-muted transition" title="تعديل">
                          <Pencil size={15} className="text-muted-foreground" />
                        </button>
                        <button onClick={() => setDeleteTarget(inv)} className="p-1.5 rounded-lg hover:bg-red-50 transition" title="حذف">
                          <Trash2 size={15} className="text-destructive" />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
            <Pagination meta={data.meta} page={page} onPageChange={setPage} />
          </div>
        )}
      </div>

      {modalInvoice !== null && (
        <InvoiceFormModal
          invoice={modalInvoice}
          onClose={() => setModalInvoice(null)}
          onSaved={() => { setModalInvoice(null); refetch() }}
        />
      )}

      <ConfirmDialog
        open={!!deleteTarget}
        title="حذف الفاتورة"
        description={`سيتم حذف الفاتورة "${deleteTarget?.invoice_number}" (يمكن استرجاعها لاحقًا).`}
        confirmLabel="حذف"
        loading={deleting}
        onConfirm={handleDelete}
        onCancel={() => setDeleteTarget(null)}
      />
    </div>
  )
}

function InvoiceFormModal({ invoice, onClose, onSaved }) {
  const isEdit = Boolean(invoice.id)
  const { data: patientsData, loading: patientsLoading } = useApiQuery(() => adminApi.patients.list({ per_page: 100 }), [])

  const [form, setForm] = useState(isEdit ? {
    patient_id: invoice.patient.id, operation_type: invoice.operation_type,
    total_amount: invoice.total_amount, cost_amount: invoice.cost_amount, paid_amount: invoice.paid_amount,
    due_date: invoice.due_date, status: invoice.status,
  } : {
    patient_id: "", operation_type: OPERATIONS[0], total_amount: "", cost_amount: "", paid_amount: 0,
    due_date: "", status: "pending",
  })
  const [saving, setSaving] = useState(false)
  const [errors, setErrors] = useState(null)

  function set(field, value) { setForm((f) => ({ ...f, [field]: value })) }

  async function handleSubmit(e) {
    e.preventDefault()
    setSaving(true)
    setErrors(null)
    try {
      const payload = { ...form, total_amount: Number(form.total_amount), cost_amount: Number(form.cost_amount), paid_amount: Number(form.paid_amount || 0) }
      if (isEdit) await adminApi.invoices.update(invoice.id, payload)
      else await adminApi.invoices.create(payload)
      onSaved()
    } catch (err) {
      if (err.errors) setErrors(err.errors)
      else alert(err.message)
    } finally {
      setSaving(false)
    }
  }

  return (
    <ModalShell title={isEdit ? "تعديل الفاتورة" : "إنشاء فاتورة جديدة"} onClose={onClose}>
      <form onSubmit={handleSubmit} className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div className="sm:col-span-2">
          <label className={labelClass}>المريض</label>
          <select value={form.patient_id} onChange={(e) => set("patient_id", e.target.value)} className={selectClass} required disabled={isEdit || patientsLoading}>
            <option value="">{patientsLoading ? "جارِ التحميل..." : "اختر المريض"}</option>
            {patientsData?.data.map((p) => <option key={p.id} value={p.id}>{p.name}</option>)}
          </select>
          <FieldError errors={errors} field="patient_id" />
        </div>
        <div>
          <label className={labelClass}>نوع العملية</label>
          <select value={form.operation_type} onChange={(e) => set("operation_type", e.target.value)} className={selectClass}>
            {OPERATIONS.map((o) => <option key={o} value={o}>{o}</option>)}
          </select>
        </div>
        <div>
          <label className={labelClass}>حالة الفاتورة</label>
          <select value={form.status} onChange={(e) => set("status", e.target.value)} className={selectClass}>
            {INVOICE_STATUSES.map((s) => <option key={s.value} value={s.value}>{s.label}</option>)}
          </select>
        </div>
        <div>
          <label className={labelClass}>المبلغ الإجمالي ($)</label>
          <input type="number" min="0" step="0.01" value={form.total_amount} onChange={(e) => set("total_amount", e.target.value)} className={inputClass} required />
          <FieldError errors={errors} field="total_amount" />
        </div>
        <div>
          <label className={labelClass}>سعر التكلفة ($)</label>
          <input type="number" min="0" step="0.01" value={form.cost_amount} onChange={(e) => set("cost_amount", e.target.value)} className={inputClass} required />
          <FieldError errors={errors} field="cost_amount" />
        </div>
        <div>
          <label className={labelClass}>المبلغ المدفوع ($)</label>
          <input type="number" min="0" step="0.01" value={form.paid_amount} onChange={(e) => set("paid_amount", e.target.value)} className={inputClass} />
          <FieldError errors={errors} field="paid_amount" />
        </div>
        <div>
          <label className={labelClass}>تاريخ الاستحقاق</label>
          <input type="date" value={form.due_date} onChange={(e) => set("due_date", e.target.value)} className={inputClass} required />
          <FieldError errors={errors} field="due_date" />
        </div>

        <div className="sm:col-span-2 flex gap-2 justify-end mt-2">
          <button type="button" onClick={onClose} className="px-4 py-2.5 rounded-xl text-sm font-semibold bg-muted text-foreground hover:bg-muted/70 transition">
            إلغاء
          </button>
          <button type="submit" disabled={saving} className="px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition disabled:opacity-60" style={{ background: "#1C4A5E" }}>
            {saving ? "جارِ الحفظ..." : isEdit ? "حفظ التعديلات" : "إنشاء الفاتورة"}
          </button>
        </div>
      </form>
    </ModalShell>
  )
}
