import { api } from '../lib/axios'
import { demoBillingApi } from '../demo/demoApi'

const DEMO_MODE = import.meta.env.VITE_DEMO_MODE === 'true'

export const billingApi = {
  listTimeEntries: (params) =>
    DEMO_MODE
      ? demoBillingApi.listTimeEntries(params)
      : api.get('/firm/time-entries', { params }).then((r) => r.data),

  logTime: (payload) =>
    DEMO_MODE
      ? demoBillingApi.logTime(payload)
      : api.post('/firm/time-entries', payload).then((r) => r.data),

  logExpense: (payload) =>
    DEMO_MODE
      ? demoBillingApi.logExpense(payload)
      : api.post('/firm/expenses', payload).then((r) => r.data),

  listInvoices: (params) =>
    DEMO_MODE
      ? demoBillingApi.listInvoices(params)
      : api.get('/firm/invoices', { params }).then((r) => r.data),

  showInvoice: (invoiceId) =>
    DEMO_MODE
      ? demoBillingApi.showInvoice(invoiceId)
      : api.get(`/firm/invoices/${invoiceId}`).then((r) => r.data),

  generateInvoice: (payload) =>
    DEMO_MODE
      ? demoBillingApi.generateInvoice(payload)
      : api.post('/firm/invoices', payload).then((r) => r.data),

  updateInvoiceStatus: (invoiceId, status) =>
    DEMO_MODE
      ? demoBillingApi.updateInvoiceStatus(invoiceId, status)
      : api.patch(`/firm/invoices/${invoiceId}/status`, { status }).then((r) => r.data),
}