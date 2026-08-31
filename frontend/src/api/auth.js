import { api } from '../lib/axios'
import { demoAuthApi, demoFirmApi ,demoCaseApi, demoClientApi  } from '../demo/demoApi'

const DEMO_MODE = import.meta.env.VITE_DEMO_MODE === 'true'

export const authApi = {
  registerFirm: (payload) =>
    DEMO_MODE
      ? demoAuthApi.registerFirm(payload)
      : api.post('/auth/register', payload).then((r) => r.data),

  login: (payload) =>
    DEMO_MODE
      ? demoAuthApi.login(payload)
      : api.post('/auth/login', payload).then((r) => r.data),

  logout: () =>
    DEMO_MODE
      ? demoAuthApi.logout()
      : api.post('/auth/logout').then((r) => r.data),

  me: () =>
    DEMO_MODE
      ? demoAuthApi.me()
      : api.get('/auth/me').then((r) => r.data),

  forgotPassword: (payload) =>
    DEMO_MODE
      ? Promise.resolve({ message: 'Demo password reset simulated.' })
      : api.post('/auth/forgot-password', payload).then((r) => r.data),

  resetPassword: (payload) =>
    DEMO_MODE
      ? Promise.resolve({ message: 'Demo password reset simulated.' })
      : api.post('/auth/reset-password', payload).then((r) => r.data),

  acceptInvitation: (payload) =>
    DEMO_MODE
      ? Promise.resolve({ message: 'Demo invitation accepted.' })
      : api.post('/invitations/accept', payload).then((r) => r.data),
}


export const firmApi = {
  show: () =>
    DEMO_MODE
      ? demoFirmApi.show()
      : api.get('/firm').then((r) => r.data),

  update: (payload) =>
    DEMO_MODE
      ? demoFirmApi.update(payload)
      : api.patch('/firm', payload).then((r) => r.data),

  team: (params) =>
    DEMO_MODE
      ? demoFirmApi.team(params)
      : api.get('/firm/team', { params }).then((r) => r.data),

  suspendMember: (userId, status) =>
    api.patch(`/firm/team/${userId}/suspend`, { status }).then((r) => r.data),

  invitations: () =>
    api.get('/firm/invitations').then((r) => r.data),

  invite: (payload) =>
    DEMO_MODE
      ? demoFirmApi.invite(payload)
      : api.post('/firm/invitations', payload).then((r) => r.data),

  revokeInvitation: (invitationId) =>
    api.delete(`/firm/invitations/${invitationId}`).then((r) => r.data),
}


export const caseApi = {
  list: (params) =>
    DEMO_MODE
      ? demoCaseApi.list(params)
      : api.get('/firm/cases', { params }).then((r) => r.data),

  show: (caseId) =>
    DEMO_MODE
      ? demoCaseApi.show(caseId)
      : api.get(`/firm/cases/${caseId}`).then((r) => r.data),

  create: (payload) =>
    DEMO_MODE
      ? demoCaseApi.create(payload)
      : api.post('/firm/cases', payload).then((r) => r.data),

  update: (caseId, payload) =>
    DEMO_MODE
      ? demoCaseApi.updateStatus(caseId, payload)
      : api.patch(`/firm/cases/${caseId}`, payload).then((r) => r.data),

  updateStatus: (caseId, payload) =>
    DEMO_MODE
      ? demoCaseApi.updateStatus(caseId, payload)
      : api.patch(`/firm/cases/${caseId}/status`, payload).then((r) => r.data),

  assignTeam: (caseId, payload) =>
    DEMO_MODE
      ? demoCaseApi.assignTeam(caseId, payload)
      : api.post(`/firm/cases/${caseId}/team`, payload).then((r) => r.data),

  removeTeamMember: (caseId, userId) =>
    api.delete(`/firm/cases/${caseId}/team/${userId}`).then((r) => r.data),

  addNote: (caseId, payload) =>
    DEMO_MODE
      ? demoCaseApi.addNote(caseId, payload)
      : api.post(`/firm/cases/${caseId}/notes`, payload).then((r) => r.data),

  archive: (caseId) =>
    api.delete(`/firm/cases/${caseId}`).then((r) => r.data),
}


export const clientApi = {
  list: (params) =>
    DEMO_MODE
      ? demoClientApi.list(params)
      : api.get('/firm/clients', { params }).then((r) => r.data),

  show: (clientId) =>
    DEMO_MODE
      ? demoClientApi.show(clientId)
      : api.get(`/firm/clients/${clientId}`).then((r) => r.data),

  create: (payload) =>
    DEMO_MODE
      ? demoClientApi.create(payload)
      : api.post('/firm/clients', payload).then((r) => r.data),

  update: (clientId, payload) =>
    DEMO_MODE
      ? demoClientApi.update(clientId, payload)
      : api.patch(`/firm/clients/${clientId}`, payload).then((r) => r.data),

  archive: (clientId) =>
    DEMO_MODE
      ? demoClientApi.archive(clientId)
      : api.delete(`/firm/clients/${clientId}`).then((r) => r.data),

  addNote: (clientId, payload) =>
    DEMO_MODE
      ? demoClientApi.addNote(clientId, payload)
      : api.post(`/firm/clients/${clientId}/notes`, payload).then((r) => r.data),
}