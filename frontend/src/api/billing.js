import { api } from '../lib/axios'

export const billingApi = {
  listTimeEntries: (params) => api.get('/firm/time-entries', { params }).then((r) => r.data),

  logTime: (payload) => api.post('/firm/time-entries', payload).then((r) => r.data),

  logExpense: (payload) => api.post('/firm/expenses', payload).then((r) => r.data),

  listInvoices: (params) => api.get('/firm/invoices', { params }).then((r) => r.data),

  showInvoice: (invoiceId) => api.get(`/firm/invoices/${invoiceId}`).then((r) => r.data),

  generateInvoice: (payload) => api.post('/firm/invoices', payload).then((r) => r.data),

  updateInvoiceStatus: (invoiceId, status) =>
    api.patch(`/firm/invoices/${invoiceId}/status`, { status }).then((r) => r.data),
}
