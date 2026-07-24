import { api } from '../lib/axios'

export const documentApi = {
  list: (params) => api.get('/firm/documents', { params }).then((r) => r.data),

  show: (documentId) => api.get(`/firm/documents/${documentId}`).then((r) => r.data),

  upload: (formData) =>
    api.post('/firm/documents', formData, { headers: { 'Content-Type': 'multipart/form-data' } }).then((r) => r.data),

  uploadVersion: (documentId, formData) =>
    api.post(`/firm/documents/${documentId}/versions`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    }).then((r) => r.data),

  versions: (documentId) => api.get(`/firm/documents/${documentId}/versions`).then((r) => r.data),

  download: (documentId) =>
    api.get(`/firm/documents/${documentId}/download`, { responseType: 'blob' }).then((r) => r.data),

  destroy: (documentId) => api.delete(`/firm/documents/${documentId}`).then((r) => r.data),

  folders: (params) => api.get('/firm/document-folders', { params }).then((r) => r.data),

  createFolder: (payload) => api.post('/firm/document-folders', payload).then((r) => r.data),
}
