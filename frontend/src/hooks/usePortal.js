import { useQuery } from '@tanstack/react-query'
import { portalApi } from '../api/portal'

export function usePortalDashboard() {
  return useQuery({ queryKey: ['portal', 'dashboard'], queryFn: () => portalApi.dashboard().then((d) => d.data) })
}
