import { api } from '../lib/axios'
import { demoDocumentApi } from '../demo/demoApi'

const DEMO_MODE = import.meta.env.VITE_DEMO_MODE === 'true'

export const documentApi = {
  list: (params) =>
    DEMO_MODE
      ? demoDocumentApi.list(params)
      : api.get('/firm/documents', { params }).then((r) => r.data),

  show: (documentId) =>
    DEMO_MODE
      ? demoDocumentApi.show(documentId)
      : api.get(`/firm/documents/${documentId}`).then((r) => r.data),

  upload: (formData) =>
    DEMO_MODE
      ? demoDocumentApi.upload(formData)
      : api
          .post('/firm/documents', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
          })
          .then((r) => r.data),

  download: (documentId) =>
    DEMO_MODE
      ? demoDocumentApi.download(documentId)
      : api
          .get(`/firm/documents/${documentId}/download`, {
            responseType: 'blob',
          })
          .then((r) => r.data),

  destroy: (documentId) =>
    DEMO_MODE
      ? demoDocumentApi.destroy(documentId)
      : api.delete(`/firm/documents/${documentId}`).then((r) => r.data),

  uploadVersion: (documentId, formData) =>
    api
      .post(`/firm/documents/${documentId}/versions`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      .then((r) => r.data),

  versions: (documentId) =>
    api.get(`/firm/documents/${documentId}/versions`).then((r) => r.data),

  folders: (params) =>
    api.get('/firm/document-folders', { params }).then((r) => r.data),

  createFolder: (payload) =>
    api.post('/firm/document-folders', payload).then((r) => r.data),
}