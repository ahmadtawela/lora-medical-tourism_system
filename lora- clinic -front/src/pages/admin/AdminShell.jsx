import { LayoutDashboard, Users, FileText, Bell, LogOut, Stethoscope } from "lucide-react"
import { useAuth } from "../../api/AuthContext"

const NAV = [
  { id: "dashboard", label: "لوحة التحكم", icon: LayoutDashboard },
  { id: "patients", label: "إدارة المرضى", icon: Users },
  { id: "invoices", label: "الفواتير", icon: FileText },
  { id: "reports", label: "التقارير الطبية", icon: FileText },
  { id: "notifications", label: "التنبيهات", icon: Bell },
]

export default function AdminShell({ page, onNav, notifCount = 0, children }) {
  const { user, logout } = useAuth()

  return (
    <div dir="rtl" className="h-screen flex overflow-hidden bg-background" style={{ fontFamily: "Tajawal, sans-serif" }}>
      <aside className="w-64 shrink-0 flex flex-col text-white" style={{ background: "#0F2D3D" }}>
        <div className="flex items-center gap-2.5 px-5 py-5">
          <div className="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" style={{ background: "#1C4A5E" }}>
            <Stethoscope size={18} color="#B8850A" />
          </div>
          <div>
            <p className="font-bold text-sm" style={{ fontFamily: "Cairo, sans-serif" }}>Lora Clinic</p>
            <p className="text-[11px] opacity-60">السياحة العلاجية</p>
          </div>
        </div>

        <nav className="flex-1 px-3 py-2 flex flex-col gap-1">
          {NAV.map(({ id, label, icon: Icon }) => (
            <button
              key={id}
              onClick={() => onNav(id)}
              className="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all relative"
              style={{
                background: page === id ? "rgba(255,255,255,0.08)" : "transparent",
                color: page === id ? "#F2EDE4" : "rgba(242,237,228,0.65)",
              }}
            >
              <Icon size={17} />
              <span>{label}</span>
              {id === "notifications" && notifCount > 0 && (
                <span
                  className="ms-auto min-w-[18px] h-[18px] rounded-full flex items-center justify-center text-[10px] font-bold text-white px-1"
                  style={{ background: "#C8282B" }}
                >
                  {notifCount}
                </span>
              )}
            </button>
          ))}
        </nav>

        <div className="px-3 py-4 border-t" style={{ borderColor: "rgba(255,255,255,0.08)" }}>
          <div className="px-3 py-2 mb-1">
            <p className="text-sm font-semibold truncate">{user?.name || "مدير النظام"}</p>
            <p className="text-[11px] opacity-60 truncate">{user?.email}</p>
          </div>
          <button
            onClick={logout}
            className="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all hover:bg-white/5"
            style={{ color: "rgba(242,237,228,0.65)" }}
          >
            <LogOut size={17} />
            <span>تسجيل الخروج</span>
          </button>
        </div>
      </aside>

      <main className="flex-1 overflow-y-auto">
        <div className="max-w-6xl mx-auto p-6 md:p-8">{children}</div>
      </main>
    </div>
  )
}
