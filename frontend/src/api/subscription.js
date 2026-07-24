import { api } from '../lib/axios'

export const subscriptionApi = {
  listPlans: () => api.get('/plans').then((r) => r.data),

  changePlan: (planKey) => api.patch('/firm/plan', { plan_key: planKey }).then((r) => r.data),
}
