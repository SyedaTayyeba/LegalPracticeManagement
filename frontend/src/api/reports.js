import { api } from '../lib/axios'

export const reportApi = {
  cases: () => api.get('/firm/reports/cases').then((r) => r.data),

  workload: () => api.get('/firm/reports/workload').then((r) => r.data),

  revenue: (params) => api.get('/firm/reports/revenue', { params }).then((r) => r.data),

  billingStatus: () => api.get('/firm/reports/billing-status').then((r) => r.data),

  casePerformance: () => api.get('/firm/reports/case-performance').then((r) => r.data),
}
