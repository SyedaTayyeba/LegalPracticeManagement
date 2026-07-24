import { api } from '../lib/axios'

export const communicationApi = {
  list: () => api.get('/firm/conversations').then((r) => r.data),

  show: (conversationId) => api.get(`/firm/conversations/${conversationId}`).then((r) => r.data),

  start: (payload) => api.post('/firm/conversations', payload).then((r) => r.data),

  sendMessage: (conversationId, payload) =>
    api.post(`/firm/conversations/${conversationId}/messages`, payload).then((r) => r.data),
}
