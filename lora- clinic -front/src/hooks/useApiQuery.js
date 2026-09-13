import { useState, useEffect, useCallback } from "react"

/**
 * خطاف عام لأي طلب قراءة - يدير التحميل/الخطأ/إعادة الجلب تلقائيًا.
 *   const { data, loading, error, refetch } = useApiQuery(() => adminApi.dashboard(), [])
 */
export function useApiQuery(queryFn, deps = []) {
  const [data, setData] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [reloadIndex, setReloadIndex] = useState(0)

  const refetch = useCallback(() => setReloadIndex((n) => n + 1), [])

  useEffect(() => {
    let cancelled = false
    setLoading(true)
    setError(null)

    queryFn()
      .then((result) => { if (!cancelled) setData(result) })
      .catch((err) => { if (!cancelled) setError(err) })
      .finally(() => { if (!cancelled) setLoading(false) })

    return () => { cancelled = true }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [...deps, reloadIndex])

  return { data, loading, error, refetch }
}
