import { api } from '../lib/axios'

export const authApi = {
  registerFirm: (payload) => api.post('/auth/register', payload).then((r) => r.data),

  login: (payload) => api.post('/auth/login', payload).then((r) => r.data),

  logout: () => api.post('/auth/logout').then((r) => r.data),

  me: () => api.get('/auth/me').then((r) => r.data),

  forgotPassword: (payload) => api.post('/auth/forgot-password', payload).then((r) => r.data),

  resetPassword: (payload) => api.post('/auth/reset-password', payload).then((r) => r.data),

  acceptInvitation: (payload) => api.post('/invitations/accept', payload).then((r) => r.data),
}

export const firmApi = {
  show: () => api.get('/firm').then((r) => r.data),

  update: (payload) => api.patch('/firm', payload).then((r) => r.data),

  team: (params) => api.get('/firm/team', { params }).then((r) => r.data),

  suspendMember: (userId, status) =>
    api.patch(`/firm/team/${userId}/suspend`, { status }).then((r) => r.data),

  invitations: () => api.get('/firm/invitations').then((r) => r.data),

  invite: (payload) => api.post('/firm/invitations', payload).then((r) => r.data),

  revokeInvitation: (invitationId) =>
    api.delete(`/firm/invitations/${invitationId}`).then((r) => r.data),
}

export const caseApi = {
  list: (params) => api.get('/firm/cases', { params }).then((r) => r.data),

  show: (caseId) => api.get(`/firm/cases/${caseId}`).then((r) => r.data),

  create: (payload) => api.post('/firm/cases', payload).then((r) => r.data),

  update: (caseId, payload) => api.patch(`/firm/cases/${caseId}`, payload).then((r) => r.data),

  updateStatus: (caseId, payload) => api.patch(`/firm/cases/${caseId}/status`, payload).then((r) => r.data),

  assignTeam: (caseId, payload) => api.post(`/firm/cases/${caseId}/team`, payload).then((r) => r.data),

  removeTeamMember: (caseId, userId) =>
    api.delete(`/firm/cases/${caseId}/team/${userId}`).then((r) => r.data),

  addNote: (caseId, payload) => api.post(`/firm/cases/${caseId}/notes`, payload).then((r) => r.data),

  archive: (caseId) => api.delete(`/firm/cases/${caseId}`).then((r) => r.data),
}

export const clientApi = {
  list: (params) => api.get('/firm/clients', { params }).then((r) => r.data),

  show: (clientId) => api.get(`/firm/clients/${clientId}`).then((r) => r.data),

  create: (payload) => api.post('/firm/clients', payload).then((r) => r.data),

  update: (clientId, payload) => api.patch(`/firm/clients/${clientId}`, payload).then((r) => r.data),

  archive: (clientId) => api.delete(`/firm/clients/${clientId}`).then((r) => r.data),

  addNote: (clientId, payload) => api.post(`/firm/clients/${clientId}/notes`, payload).then((r) => r.data),
}
