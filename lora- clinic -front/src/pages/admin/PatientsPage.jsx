import { useState } from "react"
import { Plus, Search, Pencil, Trash2, MessageCircle, Mail, FileSpreadsheet, Download, Upload, X, AlertTriangle, CheckCircle2 } from "lucide-react"
import { adminApi } from "../../api/lora-clinic-api-client"
import { useApiQuery } from "../../hooks/useApiQuery"
import {
  $D, Avatar, initialsOf, colorFor, InterestPill, LoadingBlock, ErrorBlock, EmptyState,
  ConfirmDialog, ModalShell, FieldError, Pagination, inputClass, labelClass, selectClass,
  COUNTRIES, OPERATIONS, INTEREST_LEVELS, countryLabel, waLink,
} from "../../components/shared"

const emptyForm = {
  name: "", email: "", phone_whatsapp: "", birth_date: "", country: "SA",
  operation_type: OPERATIONS[0], interest_level: "interested", operation_date: "", note: "",
}

export default function PatientsPage() {
  const [page, setPage] = useState(1)
  const [interestFilter, setInterestFilter] = useState("all")
  const [countryFilter, setCountryFilter] = useState("all")
  const [search, setSearch] = useState("")

  const { data, loading, error, refetch } = useApiQuery(
    () => adminApi.patients.list({ interest_level: interestFilter, country: countryFilter, search, page }),
    [interestFilter, countryFilter, search, page]
  )

  const [modalPatient, setModalPatient] = useState(null) // null = closed, {} = add, {...} = edit
  const [showImport, setShowImport] = useState(false)
  const [deleteTarget, setDeleteTarget] = useState(null)
  const [deleting, setDeleting] = useState(false)
  const [resendingId, setResendingId] = useState(null)

  async function handleResendInvite(patient) {
    setResendingId(patient.id)
    try {
      await adminApi.patients.resendInvite(patient.id)
      alert(`تم إعادة إرسال رابط التفعيل إلى ${patient.name} عبر البريد وواتساب`)
    } catch (err) {
      alert(err.message)
    } finally {
      setResendingId(null)
    }
  }

  async function handleDelete() {
    setDeleting(true)
    try {
      await adminApi.patients.delete(deleteTarget.id)
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
          <h1 className="text-2xl font-bold text-foreground" style={{ fontFamily: "Cairo, sans-serif" }}>إدارة المرضى</h1>
          <p className="text-muted-foreground text-sm mt-0.5">{data?.meta?.total ?? "—"} مريض</p>
        </div>
        <div className="flex items-center gap-2">
          <button
            onClick={() => setShowImport(true)}
            className="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold transition bg-muted text-foreground hover:bg-muted/70"
          >
            <FileSpreadsheet size={16} /> استيراد من إكسل
          </button>
          <button
            onClick={() => setModalPatient({})}
            className="flex items-center gap-2 px-4 py-2.5 rounded-xl text-white text-sm font-semibold transition hover:opacity-90"
            style={{ background: "#1C4A5E" }}
          >
            <Plus size={16} /> إضافة مريض
          </button>
        </div>
      </div>

      <div className="flex flex-wrap gap-2">
        <select value={interestFilter} onChange={(e) => { setInterestFilter(e.target.value); setPage(1) }} className={`${selectClass} w-auto`}>
          <option value="all">كل المستويات</option>
          {INTEREST_LEVELS.map((l) => <option key={l.value} value={l.value}>{l.label}</option>)}
        </select>
        <select value={countryFilter} onChange={(e) => { setCountryFilter(e.target.value); setPage(1) }} className={`${selectClass} w-auto`}>
          <option value="all">كل الدول</option>
          {COUNTRIES.map((c) => <option key={c.code} value={c.code}>{c.label}</option>)}
        </select>
        <div className="relative flex-1 min-w-[200px]">
          <Search size={15} className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
          <input
            value={search}
            onChange={(e) => { setSearch(e.target.value); setPage(1) }}
            placeholder="بحث بالاسم أو الواتساب أو البريد..."
            className={`${inputClass} pr-9`}
          />
        </div>
      </div>

      <div className="bg-card rounded-2xl border border-border/50 overflow-hidden">
        {loading ? <LoadingBlock /> : error ? <ErrorBlock message={error.message} onRetry={refetch} /> : data.data.length === 0 ? (
          <EmptyState title="لا يوجد مرضى مطابقون" description="جرّب تغيير الفلاتر، أو أضف مريضًا جديدًا" />
        ) : (
          <div className="overflow-x-auto p-5">
            <table className="w-full text-sm">
              <thead>
                <tr className="text-right text-xs text-muted-foreground border-b border-border/50">
                  <th className="pb-2 font-medium">المريض</th>
                  <th className="pb-2 font-medium">الواتساب</th>
                  <th className="pb-2 font-medium">الدولة</th>
                  <th className="pb-2 font-medium">العملية</th>
                  <th className="pb-2 font-medium">الاهتمام</th>
                  <th className="pb-2 font-medium">التسجيل</th>
                  <th className="pb-2 font-medium">إجراء</th>
                </tr>
              </thead>
              <tbody>
                {data.data.map((p) => (
                  <tr key={p.id} className="border-b border-border/30 last:border-0">
                    <td className="py-3">
                      <div className="flex items-center gap-2.5">
                        <Avatar initials={p.initials || initialsOf(p.name)} color={colorFor(p.id)} size={8} />
                        <div className="min-w-0">
                          <p className="font-semibold text-foreground truncate">{p.name}</p>
                          <p className="text-xs text-muted-foreground truncate">{p.email}</p>
                          {p.awaiting_password_setup && (
                            <span className="inline-block mt-1 px-1.5 py-0.5 rounded text-[10px] font-semibold" style={{ background: "#FEF9C3", color: "#854D0E" }}>
                              بانتظار التفعيل
                            </span>
                          )}
                        </div>
                      </div>
                    </td>
                    <td className="py-3">
                      <a
                        href={waLink(p.phone_whatsapp)}
                        target="_blank"
                        rel="noreferrer"
                        onClick={(e) => e.stopPropagation()}
                        className="inline-flex items-center gap-1.5 text-foreground hover:text-[#25D366] transition"
                        dir="ltr"
                        title="فتح محادثة واتساب"
                      >
                        <MessageCircle size={14} />
                        {p.phone_whatsapp}
                      </a>
                    </td>
                    <td className="py-3 text-muted-foreground">{countryLabel(p.country)}</td>
                    <td className="py-3 text-muted-foreground">{p.operation_type}</td>
                    <td className="py-3"><InterestPill level={p.interest_level} label={p.interest_level_label} /></td>
                    <td className="py-3 text-muted-foreground text-xs">{$D(p.registered_at)}</td>
                    <td className="py-3">
                      <div className="flex items-center gap-1">
                        {p.awaiting_password_setup && (
                          <button
                            onClick={() => handleResendInvite(p)}
                            disabled={resendingId === p.id}
                            className="p-1.5 rounded-lg hover:bg-muted transition disabled:opacity-50"
                            title="إعادة إرسال رابط التفعيل"
                          >
                            <Mail size={15} className="text-muted-foreground" />
                          </button>
                        )}
                        <button onClick={() => setModalPatient(p)} className="p-1.5 rounded-lg hover:bg-muted transition" title="تعديل">
                          <Pencil size={15} className="text-muted-foreground" />
                        </button>
                        <button onClick={() => setDeleteTarget(p)} className="p-1.5 rounded-lg hover:bg-red-50 transition" title="حذف">
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

      {modalPatient !== null && (
        <PatientFormModal
          patient={modalPatient}
          onClose={() => setModalPatient(null)}
          onSaved={() => { setModalPatient(null); refetch() }}
        />
      )}

      {showImport && (
        <ImportModal onClose={() => setShowImport(false)} onImported={() => { setShowImport(false); refetch() }} />
      )}

      <ConfirmDialog
        open={!!deleteTarget}
        title="حذف المريض"
        description={`سيتم حذف "${deleteTarget?.name}" (يمكن استرجاعه لاحقًا من قاعدة البيانات - فواتيره وتقاريره تبقى محفوظة).`}
        confirmLabel="حذف"
        loading={deleting}
        onConfirm={handleDelete}
        onCancel={() => setDeleteTarget(null)}
      />
    </div>
  )
}

function PatientFormModal({ patient, onClose, onSaved }) {
  const isEdit = Boolean(patient.id)
  const [form, setForm] = useState(isEdit ? {
    name: patient.name, email: patient.email, phone_whatsapp: patient.phone_whatsapp,
    birth_date: patient.birth_date || "", country: patient.country, operation_type: patient.operation_type,
    interest_level: patient.interest_level, operation_date: patient.operation_date || "", note: patient.note || "",
  } : emptyForm)
  const [saving, setSaving] = useState(false)
  const [errors, setErrors] = useState(null)

  function set(field, value) { setForm((f) => ({ ...f, [field]: value })) }

  async function handleSubmit(e) {
    e.preventDefault()
    setSaving(true)
    setErrors(null)
    try {
      const payload = { ...form, birth_date: form.birth_date || null, operation_date: form.operation_date || null, note: form.note || null }
      if (isEdit) {
        await adminApi.patients.update(patient.id, payload)
      } else {
        await adminApi.patients.create(payload) // الباك إند يرسل رابط تفعيل تلقائيًا للمريض (بريد + واتساب)
      }
      onSaved()
    } catch (err) {
      if (err.errors) setErrors(err.errors)
      else alert(err.message)
    } finally {
      setSaving(false)
    }
  }

  return (
    <ModalShell title={isEdit ? "تعديل بيانات المريض" : "إضافة مريض جديد"} onClose={onClose}>
      <form onSubmit={handleSubmit} className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div className="sm:col-span-2">
          <label className={labelClass}>الاسم الكامل</label>
          <input value={form.name} onChange={(e) => set("name", e.target.value)} className={inputClass} required />
          <FieldError errors={errors} field="name" />
        </div>
        <div>
          <label className={labelClass}>البريد الإلكتروني</label>
          <input type="email" value={form.email} onChange={(e) => set("email", e.target.value)} className={inputClass} required />
          <FieldError errors={errors} field="email" />
        </div>
        <div>
          <label className={labelClass}>رقم الواتساب</label>
          <input value={form.phone_whatsapp} onChange={(e) => set("phone_whatsapp", e.target.value)} placeholder="+9665xxxxxxxx" className={inputClass} required />
          <FieldError errors={errors} field="phone_whatsapp" />
        </div>
        <div>
          <label className={labelClass}>تاريخ الميلاد</label>
          <input type="date" value={form.birth_date} onChange={(e) => set("birth_date", e.target.value)} className={inputClass} />
          <FieldError errors={errors} field="birth_date" />
        </div>
        <div>
          <label className={labelClass}>الدولة</label>
          <select value={form.country} onChange={(e) => set("country", e.target.value)} className={selectClass}>
            {COUNTRIES.map((c) => <option key={c.code} value={c.code}>{c.label}</option>)}
          </select>
        </div>
        <div>
          <label className={labelClass}>نوع العملية</label>
          <select value={form.operation_type} onChange={(e) => set("operation_type", e.target.value)} className={selectClass}>
            {OPERATIONS.map((o) => <option key={o} value={o}>{o}</option>)}
          </select>
        </div>
        <div>
          <label className={labelClass}>مستوى الاهتمام</label>
          <select value={form.interest_level} onChange={(e) => set("interest_level", e.target.value)} className={selectClass}>
            {INTEREST_LEVELS.map((l) => <option key={l.value} value={l.value}>{l.label}</option>)}
          </select>
        </div>
        <div>
          <label className={labelClass}>تاريخ العملية (إن وُجد)</label>
          <input type="date" value={form.operation_date} onChange={(e) => set("operation_date", e.target.value)} className={inputClass} />
        </div>
        <div className="sm:col-span-2">
          <label className={labelClass}>ملاحظات</label>
          <textarea value={form.note} onChange={(e) => set("note", e.target.value)} rows={3} className={inputClass} />
        </div>

        <div className="sm:col-span-2 flex gap-2 justify-end mt-2">
          <button type="button" onClick={onClose} className="px-4 py-2.5 rounded-xl text-sm font-semibold bg-muted text-foreground hover:bg-muted/70 transition">
            إلغاء
          </button>
          <button type="submit" disabled={saving} className="px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition disabled:opacity-60" style={{ background: "#1C4A5E" }}>
            {saving ? "جارِ الحفظ..." : isEdit ? "حفظ التعديلات" : "إضافة المريض"}
          </button>
        </div>
      </form>
    </ModalShell>
  )
}

function ImportModal({ onClose, onImported }) {
  const [file, setFile] = useState(null)
  const [importing, setImporting] = useState(false)
  const [downloadingTemplate, setDownloadingTemplate] = useState(false)
  const [result, setResult] = useState(null) // { imported, failed, errors }
  const [error, setError] = useState("")

  async function handleDownloadTemplate() {
    setDownloadingTemplate(true)
    try {
      await adminApi.patients.downloadTemplate()
    } catch (err) {
      alert(err.message)
    } finally {
      setDownloadingTemplate(false)
    }
  }

  async function handleImport() {
    if (!file) return
    setImporting(true)
    setError("")
    try {
      const formData = new FormData()
      formData.append("file", file)
      const res = await adminApi.patients.import(formData)
      setResult(res)
      if (res.imported > 0) onImported()
    } catch (err) {
      setError(err.message)
    } finally {
      setImporting(false)
    }
  }

  return (
    <ModalShell title="استيراد مرضى من إكسل" onClose={onClose}>
      <div className="flex flex-col gap-4">
        {!result ? (
          <>
            <button
              onClick={handleDownloadTemplate}
              disabled={downloadingTemplate}
              className="flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-semibold bg-muted text-foreground hover:bg-muted/70 transition disabled:opacity-60"
            >
              <Download size={15} /> {downloadingTemplate ? "جارِ التحميل..." : "تحميل نموذج فارغ (xlsx)"}
            </button>
            <p className="text-xs text-muted-foreground -mt-2">
              حمّل النموذج، عبّئ بيانات مرضاك (اسم، بريد، واتساب، دولة، نوع العملية إلزامية - الباقي اختياري)، ثم ارفعه هنا. سيُرسَل لكل مريض رابط تفعيل حساب تلقائيًا.
            </p>

            <div>
              <label className={labelClass}>ملف Excel أو CSV</label>
              <div
                onClick={() => document.getElementById("import-file-input")?.click()}
                className="border-2 border-dashed border-border rounded-xl py-8 flex flex-col items-center justify-center gap-2 cursor-pointer hover:bg-muted/50 transition text-center"
              >
                <Upload size={20} className="text-muted-foreground" />
                <p className="text-sm text-muted-foreground">{file ? file.name : "اضغط لاختيار ملف xlsx أو csv"}</p>
                {file && (
                  <button type="button" onClick={(e) => { e.stopPropagation(); setFile(null) }} className="flex items-center gap-1 text-xs text-destructive font-semibold">
                    <X size={12} /> إزالة
                  </button>
                )}
              </div>
              <input id="import-file-input" type="file" accept=".xlsx,.xls,.csv" onChange={(e) => setFile(e.target.files?.[0] || null)} className="hidden" />
            </div>

            {error && (
              <div className="flex items-center gap-2 text-destructive text-sm bg-red-50 px-3 py-2 rounded-lg">
                <AlertTriangle size={14} /><span>{error}</span>
              </div>
            )}

            <div className="flex gap-2 justify-end mt-1">
              <button type="button" onClick={onClose} className="px-4 py-2.5 rounded-xl text-sm font-semibold bg-muted text-foreground hover:bg-muted/70 transition">
                إلغاء
              </button>
              <button
                onClick={handleImport}
                disabled={!file || importing}
                className="px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition disabled:opacity-60"
                style={{ background: "#1C4A5E" }}
              >
                {importing ? "جارِ الاستيراد..." : "استيراد"}
              </button>
            </div>
          </>
        ) : (
          <div className="flex flex-col gap-3">
            <div className="flex items-center gap-2">
              <CheckCircle2 size={20} color="#2D6A4F" />
              <p className="text-sm text-foreground">تم استيراد <strong>{result.imported}</strong> مريض بنجاح.</p>
            </div>
            {result.failed > 0 && (
              <div className="flex flex-col gap-2">
                <div className="flex items-center gap-2">
                  <AlertTriangle size={18} color="#C8282B" />
                  <p className="text-sm text-foreground">فشل استيراد <strong>{result.failed}</strong> صف:</p>
                </div>
                <div className="bg-red-50 rounded-xl p-3 max-h-48 overflow-y-auto flex flex-col gap-1.5">
                  {result.errors.map((e, i) => (
                    <p key={i} className="text-xs text-destructive">صف {e.row}: {e.message}</p>
                  ))}
                </div>
              </div>
            )}
            <button onClick={onClose} className="w-full py-2.5 rounded-xl text-white text-sm font-semibold mt-1" style={{ background: "#1C4A5E" }}>
              تم
            </button>
          </div>
        )}
      </div>
    </ModalShell>
  )
}
