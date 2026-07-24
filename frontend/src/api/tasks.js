import { api } from '../lib/axios'

export const taskApi = {
  list: (params) => api.get('/firm/tasks', { params }).then((r) => r.data),

  show: (taskId) => api.get(`/firm/tasks/${taskId}`).then((r) => r.data),

  create: (payload) => api.post('/firm/tasks', payload).then((r) => r.data),

  update: (taskId, payload) => api.patch(`/firm/tasks/${taskId}`, payload).then((r) => r.data),

  destroy: (taskId) => api.delete(`/firm/tasks/${taskId}`).then((r) => r.data),
}
