import { useState, useRef } from "react"
import { Upload, FileText, FileImage, Eye, Trash2, X } from "lucide-react"
import { adminApi } from "../../api/lora-clinic-api-client"
import { useApiQuery } from "../../hooks/useApiQuery"
import {
  $D, LoadingBlock, ErrorBlock, EmptyState, ConfirmDialog, ModalShell,
  FieldError, Pagination, inputClass, labelClass, selectClass,
} from "../../components/shared"

export default function ReportsPage() {
  const [page, setPage] = useState(1)
  const { data, loading, error, refetch } = useApiQuery(() => adminApi.medicalReports.list({ page }), [page])

  const [showUpload, setShowUpload] = useState(false)
  const [viewReport, setViewReport] = useState(null)
  const [deleteTarget, setDeleteTarget] = useState(null)
  const [deleting, setDeleting] = useState(false)

  async function handleDelete() {
    setDeleting(true)
    try {
      await adminApi.medicalReports.delete(deleteTarget.id)
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
          <h1 className="text-2xl font-bold text-foreground" style={{ fontFamily: "Cairo, sans-serif" }}>التقارير الطبية</h1>
          <p className="text-muted-foreground text-sm mt-0.5">{data?.meta?.total ?? "—"} تقرير مرفوع</p>
        </div>
        <button
          onClick={() => setShowUpload(true)}
          className="flex items-center gap-2 px-4 py-2.5 rounded-xl text-white text-sm font-semibold transition hover:opacity-90"
          style={{ background: "#1C4A5E" }}
        >
          <Upload size={16} /> رفع تقرير جديد
        </button>
      </div>

      {loading ? <LoadingBlock /> : error ? <ErrorBlock message={error.message} onRetry={refetch} /> : data.data.length === 0 ? (
        <div className="bg-card rounded-2xl border border-border/50">
          <EmptyState title="لا توجد تقارير مرفوعة بعد" description="ارفع أول تقرير طبي من الزر أعلاه" />
        </div>
      ) : (
        <>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            {data.data.map((r) => {
              const FileIcon = r.file_type === "image" ? FileImage : FileText
              return (
                <div key={r.id} className="bg-card rounded-2xl border border-border/50 p-5 flex flex-col gap-3">
                  <div className="flex items-start justify-between gap-2">
                    <div className="min-w-0">
                      <p className="font-bold text-foreground text-sm leading-snug">{r.diagnosis_name}</p>
                      <p className="text-xs text-muted-foreground mt-0.5">{r.patient.name}</p>
                    </div>
                    <div className="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" style={{ background: "#FEE2E2" }}>
                      <FileIcon size={16} color="#C8282B" />
                    </div>
                  </div>
                  <p className="text-xs text-muted-foreground truncate">{r.file_name}</p>
                  <p className="text-xs text-muted-foreground flex items-center gap-1">{$D(r.created_at)}</p>
                  {r.description && (
                    <p className="text-xs text-foreground bg-muted rounded-lg px-3 py-2 line-clamp-2">{r.description}</p>
                  )}
                  <div className="flex gap-2 mt-1">
                    <button onClick={() => setViewReport(r)} className="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-xl text-xs font-semibold bg-muted text-foreground hover:bg-muted/70 transition">
                      <Eye size={13} /> عرض التقرير
                    </button>
                    <button onClick={() => setDeleteTarget(r)} className="p-2 rounded-xl hover:bg-red-50 transition" title="حذف">
                      <Trash2 size={15} className="text-destructive" />
                    </button>
                  </div>
                </div>
              )
            })}
          </div>
          <div className="bg-card rounded-2xl border border-border/50 px-5">
            <Pagination meta={data.meta} page={page} onPageChange={setPage} />
          </div>
        </>
      )}

      {showUpload && (
        <UploadReportModal onClose={() => setShowUpload(false)} onSaved={() => { setShowUpload(false); refetch() }} />
      )}

      {viewReport && (
        <ModalShell title={viewReport.diagnosis_name} onClose={() => setViewReport(null)} width="max-w-2xl">
          <div className="flex flex-col gap-3">
            <p className="text-sm text-muted-foreground">المريض: <span className="text-foreground font-semibold">{viewReport.patient.name}</span></p>
            {viewReport.description && <p className="text-sm text-foreground bg-muted rounded-xl p-3">{viewReport.description}</p>}
            {viewReport.file_type === "image" ? (
              <img src={viewReport.file_url} alt={viewReport.diagnosis_name} className="rounded-xl border border-border/50 max-h-[60vh] object-contain" />
            ) : (
              <a href={viewReport.file_url} target="_blank" rel="noreferrer" className="flex items-center justify-center gap-2 py-8 rounded-xl border border-dashed border-border text-sm text-primary font-semibold hover:bg-muted transition">
                <FileText size={18} /> فتح ملف PDF في نافذة جديدة
              </a>
            )}
          </div>
        </ModalShell>
      )}

      <ConfirmDialog
        open={!!deleteTarget}
        title="حذف التقرير"
        description={`سيتم حذف تقرير "${deleteTarget?.diagnosis_name}" (يمكن استرجاعه لاحقًا، والملف يبقى محفوظًا).`}
        confirmLabel="حذف"
        loading={deleting}
        onConfirm={handleDelete}
        onCancel={() => setDeleteTarget(null)}
      />
    </div>
  )
}

function UploadReportModal({ onClose, onSaved }) {
  const { data: patientsData, loading: patientsLoading } = useApiQuery(() => adminApi.patients.list({ per_page: 100 }), [])
  const [patientId, setPatientId] = useState("")
  const [diagnosis, setDiagnosis] = useState("")
  const [description, setDescription] = useState("")
  const [file, setFile] = useState(null)
  const [saving, setSaving] = useState(false)
  const [errors, setErrors] = useState(null)
  const fileRef = useRef(null)

  async function handleSubmit(e) {
    e.preventDefault()
    if (!file) { setErrors({ file: ["يُرجى اختيار ملف"] }); return }
    setSaving(true)
    setErrors(null)
    try {
      const formData = new FormData()
      formData.append("patient_id", patientId)
      formData.append("diagnosis_name", diagnosis)
      if (description) formData.append("description", description)
      formData.append("file", file)
      await adminApi.medicalReports.upload(formData)
      onSaved()
    } catch (err) {
      if (err.errors) setErrors(err.errors)
      else alert(err.message)
    } finally {
      setSaving(false)
    }
  }

  return (
    <ModalShell title="رفع تقرير طبي جديد" onClose={onClose}>
      <form onSubmit={handleSubmit} className="flex flex-col gap-4">
        <div>
          <label className={labelClass}>المريض</label>
          <select value={patientId} onChange={(e) => setPatientId(e.target.value)} className={selectClass} required disabled={patientsLoading}>
            <option value="">{patientsLoading ? "جارِ التحميل..." : "اختر المريض"}</option>
            {patientsData?.data.map((p) => <option key={p.id} value={p.id}>{p.name}</option>)}
          </select>
          <FieldError errors={errors} field="patient_id" />
        </div>
        <div>
          <label className={labelClass}>اسم المرض / التشخيص</label>
          <input value={diagnosis} onChange={(e) => setDiagnosis(e.target.value)} placeholder="مثال: السكري - النوع الثاني" className={inputClass} required />
          <FieldError errors={errors} field="diagnosis_name" />
        </div>
        <div>
          <label className={labelClass}>ملاحظات (اختياري)</label>
          <textarea value={description} onChange={(e) => setDescription(e.target.value)} rows={2} className={inputClass} />
        </div>
        <div>
          <label className={labelClass}>الملف (PDF أو صورة)</label>
          <div
            onClick={() => fileRef.current?.click()}
            className="border-2 border-dashed border-border rounded-xl py-8 flex flex-col items-center justify-center gap-2 cursor-pointer hover:bg-muted/50 transition text-center"
          >
            <Upload size={20} className="text-muted-foreground" />
            <p className="text-sm text-muted-foreground">{file ? file.name : "اضغط لاختيار ملف PDF أو صورة"}</p>
            {file && (
              <button type="button" onClick={(e) => { e.stopPropagation(); setFile(null) }} className="flex items-center gap-1 text-xs text-destructive font-semibold">
                <X size={12} /> إزالة
              </button>
            )}
          </div>
          <input ref={fileRef} type="file" accept=".pdf,.jpg,.jpeg,.png" onChange={(e) => setFile(e.target.files?.[0] || null)} className="hidden" />
          <FieldError errors={errors} field="file" />
        </div>

        <div className="flex gap-2 justify-end mt-1">
          <button type="button" onClick={onClose} className="px-4 py-2.5 rounded-xl text-sm font-semibold bg-muted text-foreground hover:bg-muted/70 transition">
            إلغاء
          </button>
          <button type="submit" disabled={saving} className="px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition disabled:opacity-60" style={{ background: "#1C4A5E" }}>
            {saving ? "جارِ الرفع..." : "رفع التقرير"}
          </button>
        </div>
      </form>
    </ModalShell>
  )
}
