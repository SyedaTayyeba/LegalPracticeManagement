import { api } from '../lib/axios'

export const portalApi = {
  dashboard: () => api.get('/firm/portal/dashboard').then((r) => r.data),
}
