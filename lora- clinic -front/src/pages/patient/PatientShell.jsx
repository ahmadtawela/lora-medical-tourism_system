import { LayoutDashboard, Activity, DollarSign, LogOut, Stethoscope } from "lucide-react"
import { useAuth } from "../../api/AuthContext"

const NAV = [
  { id: "dashboard", label: "بوابتي", icon: LayoutDashboard },
  { id: "my-reports", label: "تقاريري", icon: Activity },
  { id: "my-invoices", label: "فواتيري", icon: DollarSign },
]

export default function PatientShell({ page, onNav, children }) {
  const { user, logout } = useAuth()

  return (
    <div dir="rtl" className="min-h-screen bg-background" style={{ fontFamily: "Tajawal, sans-serif" }}>
      <header className="bg-white border-b border-border/50 sticky top-0 z-10">
        <div className="max-w-5xl mx-auto px-5 py-3.5 flex items-center justify-between">
          <div className="flex items-center gap-2.5">
            <div className="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" style={{ background: "#1C4A5E" }}>
              <Stethoscope size={18} color="#B8850A" />
            </div>
            <p className="font-bold text-foreground" style={{ fontFamily: "Cairo, sans-serif" }}>Lora Clinic</p>
          </div>

          <nav className="hidden sm:flex items-center gap-1">
            {NAV.map(({ id, label, icon: Icon }) => (
              <button
                key={id}
                onClick={() => onNav(id)}
                className="flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-semibold transition"
                style={page === id ? { background: "#1C4A5E1A", color: "#1C4A5E" } : { color: "var(--muted-foreground)" }}
              >
                <Icon size={16} /> {label}
              </button>
            ))}
          </nav>

          <div className="flex items-center gap-3">
            <div className="text-left hidden sm:block">
              <p className="text-sm font-semibold text-foreground">{user?.name}</p>
            </div>
            <button onClick={logout} className="p-2 rounded-xl hover:bg-muted transition text-muted-foreground" title="تسجيل الخروج">
              <LogOut size={17} />
            </button>
          </div>
        </div>

        <nav className="sm:hidden flex items-center gap-1 px-4 pb-2 overflow-x-auto">
          {NAV.map(({ id, label, icon: Icon }) => (
            <button
              key={id}
              onClick={() => onNav(id)}
              className="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition"
              style={page === id ? { background: "#1C4A5E1A", color: "#1C4A5E" } : { color: "var(--muted-foreground)" }}
            >
              <Icon size={13} /> {label}
            </button>
          ))}
        </nav>
      </header>

      <main className="max-w-5xl mx-auto p-5 md:p-8">{children}</main>
    </div>
  )
}
