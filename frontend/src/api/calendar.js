import { api } from '../lib/axios'

export const calendarApi = {
  list: (params) => api.get('/firm/calendar', { params }).then((r) => r.data),

  show: (eventId) => api.get(`/firm/calendar/${eventId}`).then((r) => r.data),

  create: (payload) => api.post('/firm/calendar', payload).then((r) => r.data),

  update: (eventId, payload) => api.patch(`/firm/calendar/${eventId}`, payload).then((r) => r.data),

  destroy: (eventId) => api.delete(`/firm/calendar/${eventId}`).then((r) => r.data),
}
