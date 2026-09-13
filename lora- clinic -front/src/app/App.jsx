import { useState } from "react"
import { AuthProvider, useAuth } from "../api/AuthContext"
import { adminApi } from "../api/lora-clinic-api-client"
import { useApiQuery } from "../hooks/useApiQuery"
import LoginPage from "../pages/LoginPage"
import SetPasswordPage from "../pages/SetPasswordPage"
import AdminShell from "../pages/admin/AdminShell"
import DashboardPage from "../pages/admin/DashboardPage"
import PatientsPage from "../pages/admin/PatientsPage"
import InvoicesPage from "../pages/admin/InvoicesPage"
import ReportsPage from "../pages/admin/ReportsPage"
import NotificationsPage from "../pages/admin/NotificationsPage"
import PatientShell from "../pages/patient/PatientShell"
import PatientDashboardPage from "../pages/patient/PatientDashboardPage"
import PatientInvoicesPage from "../pages/patient/PatientInvoicesPage"
import PatientReportsPage from "../pages/patient/PatientReportsPage"

function AdminApp() {
  const [page, setPage] = useState("dashboard")
  // نجلب عدد التنبيهات غير المقروءة هنا فقط لعرضه كشارة على الشريط الجانبي
  const { data: notifData } = useApiQuery(() => adminApi.notifications.list("unread"), [page])

  const PAGES = {
    dashboard: <DashboardPage onNav={setPage} />,
    patients: <PatientsPage />,
    invoices: <InvoicesPage />,
    reports: <ReportsPage />,
    notifications: <NotificationsPage />,
  }

  return (
    <AdminShell page={page} onNav={setPage} notifCount={notifData?.unread_count || 0}>
      {PAGES[page]}
    </AdminShell>
  )
}

function PatientApp() {
  const [page, setPage] = useState("dashboard")

  const PAGES = {
    dashboard: <PatientDashboardPage />,
    "my-reports": <PatientReportsPage />,
    "my-invoices": <PatientInvoicesPage />,
  }

  return (
    <PatientShell page={page} onNav={setPage}>
      {PAGES[page]}
    </PatientShell>
  )
}

function Root() {
  const { actor, isAuthenticated } = useAuth()

  // رابط الدعوة (بريد/واتساب) يوصل هنا مباشرة - فحص بسيط للمسار بدل مكتبة توجيه كاملة
  if (window.location.pathname === "/set-password" && !isAuthenticated) {
    return <SetPasswordPage />
  }

  if (!isAuthenticated) return <LoginPage />
  if (actor === "patient") return <PatientApp />
  if (actor === "admin") return <AdminApp />
  return <LoginPage /> // احتياطي: توكن موجود لكن الدور غير معروف
}

export default function App() {
  return (
    <AuthProvider>
      <Root />
    </AuthProvider>
  )
}
