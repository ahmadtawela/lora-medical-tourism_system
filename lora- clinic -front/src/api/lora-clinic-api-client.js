/**
 * Lora Clinic - API Client
 * ------------------------
 * وحدة JS عادية (بدون أي مكتبة خارجية) تتعامل مع كل نقاط نهاية الباك إند:
 * تسجيل الدخول الموحّد، تخزين التوكن وإرفاقه تلقائيًا، ورفع الملفات.
 */

const API_BASE_URL = "http://localhost:8000/api" // غيّرها لرابط الإنتاج عند النشر

// ---------------------------------------------------------------------------
// تخزين الجلسة
// ---------------------------------------------------------------------------
const TOKEN_KEY = "lora_clinic_token"
const ACTOR_KEY = "lora_clinic_actor" // 'admin' | 'patient'

export function getToken() {
  return localStorage.getItem(TOKEN_KEY)
}
export function getActor() {
  return localStorage.getItem(ACTOR_KEY)
}
function setSession(token, actor) {
  localStorage.setItem(TOKEN_KEY, token)
  localStorage.setItem(ACTOR_KEY, actor)
}
function clearSession() {
  localStorage.removeItem(TOKEN_KEY)
  localStorage.removeItem(ACTOR_KEY)
}

let unauthorizedHandler = () => { window.location.href = "/" }
export function onUnauthorized(handler) { unauthorizedHandler = handler }

export class ApiError extends Error {
  constructor(status, message, errors = null) {
    super(message)
    this.status = status
    this.errors = errors // شكل Laravel: { field: ["رسالة"] }
  }
}

// ---------------------------------------------------------------------------
// المُغلِّف الأساسي
// ---------------------------------------------------------------------------
async function request(path, { method = "GET", body, query } = {}) {
  const url = new URL(API_BASE_URL + path)
  if (query) {
    Object.entries(query).forEach(([k, v]) => {
      if (v !== undefined && v !== null && v !== "") url.searchParams.set(k, v)
    })
  }

  const headers = { Accept: "application/json" }
  const token = getToken()
  if (token) headers.Authorization = `Bearer ${token}`

  const isFormData = body instanceof FormData
  if (body && !isFormData) headers["Content-Type"] = "application/json"

  const response = await fetch(url, {
    method,
    headers,
    body: body ? (isFormData ? body : JSON.stringify(body)) : undefined,
  })

  if (response.status === 401) {
    clearSession()
    unauthorizedHandler()
    throw new ApiError(401, "انتهت الجلسة، يُرجى تسجيل الدخول مجددًا")
  }

  const data = await response.json().catch(() => null)

  if (!response.ok) {
    throw new ApiError(response.status, data?.message || "حدث خطأ غير متوقع", data?.errors || null)
  }
  return data
}

const get = (path, query) => request(path, { method: "GET", query })
const post = (path, body) => request(path, { method: "POST", body })
const patch = (path, body) => request(path, { method: "PATCH", body })
const del = (path) => request(path, { method: "DELETE" })

// ---------------------------------------------------------------------------
// مصادقة موحّدة - endpoint واحد يحدد الدور تلقائيًا
// ---------------------------------------------------------------------------
export const auth = {
  async login(email, password) {
    const data = await post("/login", { email, password }) // { actor, user, token }
    setSession(data.token, data.actor)
    return { actor: data.actor, user: data.user }
  },
  async logout() {
    await post("/logout").catch(() => {})
    clearSession()
  },
  /** يكمل دعوة المريض (رابط البريد/واتساب) ويسجّل دخوله تلقائيًا عند النجاح */
  async setPassword(email, token, password, passwordConfirmation) {
    const data = await post("/patient/set-password", {
      email, token, password, password_confirmation: passwordConfirmation,
    })
    setSession(data.token, "patient")
    return data.patient
  },
}

// ---------------------------------------------------------------------------
// لوحة الإدارة
// ---------------------------------------------------------------------------
export const adminApi = {
  dashboard: () => get("/admin/dashboard"),

  patients: {
    list: (filters = {}) => get("/admin/patients", filters),
    create: (data) => post("/admin/patients", data),
    get: (id) => get(`/admin/patients/${id}`),
    update: (id, data) => patch(`/admin/patients/${id}`, data),
    delete: (id) => del(`/admin/patients/${id}`),
    restore: (id) => patch(`/admin/patients/${id}/restore`),
    resendInvite: (id) => post(`/admin/patients/${id}/resend-invite`),
    // formData يجب أن يحتوي: file (ملف Excel/CSV)
    import: (formData) => post("/admin/patients/import", formData),
    // يفتح رابط تحميل النموذج مباشرة في المتصفح (يحمل التوكن عبر fetch لأنه محمي بمصادقة)
    async downloadTemplate() {
      const res = await fetch(`${API_BASE_URL}/admin/patients/import-template`, {
        headers: { Authorization: `Bearer ${getToken()}` },
      })
      if (!res.ok) throw new ApiError(res.status, "تعذّر تحميل النموذج")
      const blob = await res.blob()
      const url = URL.createObjectURL(blob)
      const a = document.createElement("a")
      a.href = url
      a.download = "نموذج_استيراد_المرضى.xlsx"
      document.body.appendChild(a)
      a.click()
      a.remove()
      URL.revokeObjectURL(url)
    },
  },

  invoices: {
    list: (filters = {}) => get("/admin/invoices", filters),
    create: (data) => post("/admin/invoices", data),
    get: (id) => get(`/admin/invoices/${id}`),
    update: (id, data) => patch(`/admin/invoices/${id}`, data),
    delete: (id) => del(`/admin/invoices/${id}`),
    restore: (id) => patch(`/admin/invoices/${id}/restore`),
  },

  medicalReports: {
    list: (filters = {}) => get("/admin/medical-reports", filters),
    upload: (formData) => post("/admin/medical-reports", formData),
    get: (id) => get(`/admin/medical-reports/${id}`),
    delete: (id) => del(`/admin/medical-reports/${id}`),
    restore: (id) => patch(`/admin/medical-reports/${id}/restore`),
  },

  notifications: {
    list: (filter = "all") => get("/admin/notifications", { filter }),
    markAsRead: (id) => patch(`/admin/notifications/${id}/read`),
    markAllAsRead: () => patch("/admin/notifications/mark-all-read"),
  },
}

// ---------------------------------------------------------------------------
// بوابة المريض
// ---------------------------------------------------------------------------
export const patientApi = {
  dashboard: () => get("/patient/dashboard"),
  invoices: {
    list: () => get("/patient/invoices"),
    get: (id) => get(`/patient/invoices/${id}`),
  },
  medicalReports: {
    list: () => get("/patient/medical-reports"),
    upload: (formData) => post("/patient/medical-reports", formData),
  },
}
