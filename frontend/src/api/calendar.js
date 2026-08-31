import { api } from '../lib/axios'
import { demoCalendarApi } from '../demo/demoApi'

const DEMO_MODE = import.meta.env.VITE_DEMO_MODE === 'true'

export const calendarApi = {
  list: (params) =>
    DEMO_MODE
      ? demoCalendarApi.list(params)
      : api.get('/firm/calendar', { params }).then((r) => r.data),

  show: (eventId) =>
    DEMO_MODE
      ? demoCalendarApi.show(eventId)
      : api.get(`/firm/calendar/${eventId}`).then((r) => r.data),

  create: (payload) =>
    DEMO_MODE
      ? demoCalendarApi.create(payload)
      : api.post('/firm/calendar', payload).then((r) => r.data),

  update: (eventId, payload) =>
    DEMO_MODE
      ? demoCalendarApi.update(eventId, payload)
      : api.patch(`/firm/calendar/${eventId}`, payload).then((r) => r.data),

  destroy: (eventId) =>
    DEMO_MODE
      ? demoCalendarApi.destroy(eventId)
      : api.delete(`/firm/calendar/${eventId}`).then((r) => r.data),
}