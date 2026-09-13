import { createContext, useContext, useState, useCallback, useEffect } from "react"
import { auth, getToken, getActor, onUnauthorized } from "../api/lora-clinic-api-client"

const USER_KEY = "lora_clinic_user"
const AuthContext = createContext(null)

function loadStoredUser() {
  try {
    const raw = localStorage.getItem(USER_KEY)
    return raw ? JSON.parse(raw) : null
  } catch {
    return null
  }
}

export function AuthProvider({ children }) {
  const [user, setUser] = useState(loadStoredUser)
  const [actor, setActor] = useState(getActor()) // 'admin' | 'patient' | null

  useEffect(() => {
    onUnauthorized(() => {
      localStorage.removeItem(USER_KEY)
      setUser(null)
      setActor(null)
    })
  }, [])

  const login = useCallback(async (email, password) => {
    const { actor: loggedInActor, user: loggedInUser } = await auth.login(email, password)
    localStorage.setItem(USER_KEY, JSON.stringify(loggedInUser))
    setUser(loggedInUser)
    setActor(loggedInActor)
    return loggedInActor
  }, [])

  /** يكمل تعيين كلمة المرور من رابط الدعوة، ويسجّل الدخول تلقائيًا بنفس خطوة login() */
  const completeAccountSetup = useCallback(async (email, token, password, passwordConfirmation) => {
    const patientUser = await auth.setPassword(email, token, password, passwordConfirmation)
    localStorage.setItem(USER_KEY, JSON.stringify(patientUser))
    setUser(patientUser)
    setActor("patient")
  }, [])

  const logout = useCallback(async () => {
    await auth.logout()
    localStorage.removeItem(USER_KEY)
    setUser(null)
    setActor(null)
  }, [])

  const value = {
    user,
    actor,
    isAdmin: actor === "admin",
    isPatient: actor === "patient",
    isAuthenticated: Boolean(getToken()),
    login,
    completeAccountSetup,
    logout,
  }

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export function useAuth() {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error("useAuth() يجب أن يُستخدم داخل <AuthProvider>")
  return ctx
}
