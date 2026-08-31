import { getDemoData, setDemoData } from './storage'
import {
  initialTasks,
  demoUser,
  demoFirm,
  demoTeam,
  initialCases,
  initialClients,
  initialDocuments,
  initialEvents,
  initialTimeEntries,
  initialExpenses,
  initialInvoices,
} from './mockData'

const TASKS_KEY = 'tasks'

function getTasks() {
  return getDemoData(TASKS_KEY, initialTasks)
}

function saveTasks(tasks) {
  return setDemoData(TASKS_KEY, tasks)
}

export const demoTaskApi = {
  list: async (params = {}) => {
    let tasks = getTasks()

    // Search
    if (params.search) {
      const search = params.search.toLowerCase()

      tasks = tasks.filter(
        (task) =>
          task.title?.toLowerCase().includes(search) ||
          task.description?.toLowerCase().includes(search)
      )
    }

    // Status filter
    if (params.status) {
      tasks = tasks.filter((task) => task.status === params.status)
    }

    // Priority filter
    if (params.priority) {
      tasks = tasks.filter((task) => task.priority === params.priority)
    }
// Overdue filter
if (params.overdue) {
  const today = new Date().toISOString().split('T')[0]

  tasks = tasks.filter(
    (task) =>
      task.due_date &&
      task.due_date < today &&
      task.status !== 'completed'
  )
}
    return {
      data: tasks,
      total: tasks.length,
    }
  },

  show: async (taskId) => {
    const tasks = getTasks()
    const task = tasks.find((task) => task.id === Number(taskId))

    if (!task) {
      throw new Error('Task not found')
    }

    return {
      data: task,
    }
  },

  create: async (payload) => {
    const tasks = getTasks()

    const newTask = {
      id: Date.now(),
      ...payload,
    }

    const updatedTasks = [newTask, ...tasks]

    saveTasks(updatedTasks)

    return {
      data: newTask,
      message: 'Task created successfully',
    }
  },

  update: async (taskId, payload) => {
    const tasks = getTasks()
    const id = Number(taskId)

    const index = tasks.findIndex((task) => task.id === id)

    if (index === -1) {
      throw new Error('Task not found')
    }

    const updatedTask = {
      ...tasks[index],
      ...payload,
    }

    tasks[index] = updatedTask

    saveTasks(tasks)

    return {
      data: updatedTask,
      message: 'Task updated successfully',
    }
  },

  destroy: async (taskId) => {
    const tasks = getTasks()
    const id = Number(taskId)

    const taskExists = tasks.some((task) => task.id === id)

    if (!taskExists) {
      throw new Error('Task not found')
    }

    const updatedTasks = tasks.filter((task) => task.id !== id)

    saveTasks(updatedTasks)

    return {
      message: 'Task deleted successfully',
    }
  },
}
//demo user
export const demoAuthApi = {
  login: async ({ email, password }) => {
    if (email !== 'demo@legalflow.com' || password !== 'password') {
      throw new Error('Invalid demo credentials')
    }

    return {
      user: demoUser,
      access_token: 'demo-access-token',
      token_type: 'Bearer',
    }
  },

me: async () => demoUser,

  logout: async () => ({
    message: 'Logged out successfully',
  }),

  registerFirm: async () => ({
    user: demoUser,
    access_token: 'demo-access-token',
    token_type: 'Bearer',
  }),
}
export const demoFirmApi = {
  show: async () => ({
    data: demoFirm,
  }),

  team: async () => ({
    data: demoTeam,
    total: demoTeam.length,
  }),

  update: async (payload) => ({
    data: {
      ...demoFirm,
      ...payload,
    },
  }),

  invite: async ({ email, role }) => ({
    data: {
      id: Date.now(),
      name: email.split('@')[0],
      email,
      role,
    },
    message: 'Invitation sent successfully.',
  }),
}
const CASES_KEY = 'cases'

function getCases() {
  return getDemoData(CASES_KEY, initialCases)
}

function saveCases(cases) {
  return setDemoData(CASES_KEY, cases)
}

export const demoCaseApi = {
  list: async (params = {}) => {
    let cases = [...getCases()]

    // Search
    if (params.search) {
      const search = params.search.toLowerCase()

      cases = cases.filter(
        (item) =>
          item.title?.toLowerCase().includes(search) ||
          item.case_number?.toLowerCase().includes(search) ||
          item.client_name?.toLowerCase().includes(search)
      )
    }

    // Status filter
    if (params.status) {
      cases = cases.filter((item) => item.status === params.status)
    }

    return {
      data: cases,
      total: cases.length,
    }
  },

  show: async (caseId) => {
    const cases = getCases()

    const item = cases.find(
      (item) => item.id === Number(caseId)
    )

    if (!item) {
      throw new Error('Case not found')
    }

    return {
      data: item,
    }
  },

  create: async (payload) => {
    const cases = getCases()

    const newCase = {
      id: Date.now(),
      case_number: `CASE-${Date.now().toString().slice(-5)}`,
      status: 'active',
      ...payload,
    }

    const updatedCases = [newCase, ...cases]

    saveCases(updatedCases)

    return {
      data: newCase,
      message: 'Case created successfully',
    }
  },

  updateStatus: async (caseId, payload) => {
    const cases = getCases()

    const index = cases.findIndex(
      (item) => item.id === Number(caseId)
    )

    if (index === -1) {
      throw new Error('Case not found')
    }

    cases[index] = {
      ...cases[index],
      ...payload,
    }

    saveCases(cases)

    return {
      data: cases[index],
      message: 'Case status updated successfully',
    }
  },

  addNote: async (caseId, payload) => {
    const cases = getCases()

    const index = cases.findIndex(
      (item) => item.id === Number(caseId)
    )

    if (index === -1) {
      throw new Error('Case not found')
    }

    const existingNotes = cases[index].notes || []

    const newNote = {
      id: Date.now(),
      ...payload,
      created_at: new Date().toISOString(),
    }

    cases[index] = {
      ...cases[index],
      notes: [newNote, ...existingNotes],
    }

    saveCases(cases)

    return {
      data: newNote,
      message: 'Note added successfully',
    }
  },

  assignTeam: async (caseId, payload) => {
    const cases = getCases()

    const index = cases.findIndex(
      (item) => item.id === Number(caseId)
    )

    if (index === -1) {
      throw new Error('Case not found')
    }

    cases[index] = {
      ...cases[index],
      ...payload,
    }

    saveCases(cases)

    return {
      data: cases[index],
      message: 'Team assigned successfully',
    }
  },
}
// DEMO CLIENT API 

const CLIENTS_KEY = 'clients'

function getClients() {
  return getDemoData(CLIENTS_KEY, initialClients)
}

function saveClients(clients) {
  return setDemoData(CLIENTS_KEY, clients)
}

export const demoClientApi = {
  // Get all clients + search/filter
  list: async (params = {}) => {
    let clients = getClients()

    // Search by name, email, phone
    if (params.search) {
      const search = params.search.toLowerCase()

      clients = clients.filter(
        (client) =>
          client.display_name?.toLowerCase().includes(search) ||
          client.email?.toLowerCase().includes(search) ||
          client.phone?.toLowerCase().includes(search)
      )
    }

    // Filter by status
    if (params.status) {
      clients = clients.filter(
        (client) => client.status === params.status
      )
    }

    return {
      data: clients,
      total: clients.length,
    }
  },

  // Get single client
  show: async (clientId) => {
    const clients = getClients()

    const client = clients.find(
      (client) => client.id === Number(clientId)
    )

    if (!client) {
      throw new Error('Client not found')
    }

    return {
      data: client,
    }
  },

  // Create client
  create: async (payload) => {
    const clients = getClients()

    const displayName =
      payload.type === 'organization'
        ? payload.organization_name
        : `${payload.first_name || ''} ${payload.last_name || ''}`.trim()

    const newClient = {
      id: Date.now(),
      ...payload,
      display_name: displayName,
      status: 'active',
    }

    const updatedClients = [newClient, ...clients]

    saveClients(updatedClients)

    return {
      data: newClient,
      message: 'Client created successfully',
    }
  },

  // Update client
  update: async (clientId, payload) => {
    const clients = getClients()

    const id = Number(clientId)

    const index = clients.findIndex(
      (client) => client.id === id
    )

    if (index === -1) {
      throw new Error('Client not found')
    }

    const updatedClient = {
      ...clients[index],
      ...payload,
    }

    // Update display name
    if (
      payload.first_name !== undefined ||
      payload.last_name !== undefined ||
      payload.organization_name !== undefined
    ) {
      updatedClient.display_name =
        updatedClient.type === 'organization'
          ? updatedClient.organization_name
          : `${updatedClient.first_name || ''} ${
              updatedClient.last_name || ''
            }`.trim()
    }

    clients[index] = updatedClient

    saveClients(clients)

    return {
      data: updatedClient,
      message: 'Client updated successfully',
    }
  },

  // Archive client
  archive: async (clientId) => {
    const clients = getClients()

    const id = Number(clientId)

    const index = clients.findIndex(
      (client) => client.id === id
    )

    if (index === -1) {
      throw new Error('Client not found')
    }

    clients[index] = {
      ...clients[index],
      status: 'archived',
    }

    saveClients(clients)

    return {
      message: 'Client archived successfully',
    }
  },

  // Add client note
  addNote: async (clientId, payload) => {
    const clients = getClients()
    const id = Number(clientId)

    const index = clients.findIndex(
      (client) => client.id === id
    )

    if (index === -1) {
      throw new Error('Client not found')
    }

    const currentNotes = clients[index].notes || []

    const newNote = {
      id: Date.now(),
      body: payload.body,
      author: 'Demo Admin',
      created_at: new Date().toISOString(),
    }

    clients[index] = {
      ...clients[index],
      notes: [newNote, ...currentNotes],
    }

    saveClients(clients)

    return {
      data: newNote,
      message: 'Note added successfully',
    }
  },
}
//  DEMO DOCUMENT API

const DOCUMENTS_KEY = 'documents'

function getDocuments() {
  return getDemoData(DOCUMENTS_KEY, initialDocuments)
}

function saveDocuments(documents) {
  return setDemoData(DOCUMENTS_KEY, documents)
}

export const demoDocumentApi = {
  list: async (params = {}) => {
    let documents = getDocuments()

    if (params.search) {
      const search = params.search.toLowerCase()

      documents = documents.filter(
        (doc) =>
          doc.name?.toLowerCase().includes(search) ||
          doc.original_filename?.toLowerCase().includes(search)
      )
    }

    if (params.category) {
      documents = documents.filter(
        (doc) => doc.category === params.category
      )
    }

    return {
      data: documents,
      total: documents.length,
    }
  },

  show: async (documentId) => {
    const documents = getDocuments()

    const document = documents.find(
      (doc) => doc.id === Number(documentId)
    )

    if (!document) {
      throw new Error('Document not found')
    }

    return {
      data: document,
    }
  },

  upload: async (formData) => {
    const documents = getDocuments()

    const file = formData.get('file')
    const category = formData.get('category') || 'other'
    const name = formData.get('name') || file?.name || 'Untitled document'

    const newDocument = {
      id: Date.now(),
      name,
      original_filename: file?.name || name,
      category,
      version: 1,
      size_bytes: file?.size || 0,
      case_id: null,
      case: null,
      created_at: new Date().toISOString(),
    }

    saveDocuments([newDocument, ...documents])

    return {
      data: newDocument,
      message: 'Document uploaded successfully',
    }
  },

  download: async (documentId) => {
    const documents = getDocuments()

    const document = documents.find(
      (doc) => doc.id === Number(documentId)
    )

    if (!document) {
      throw new Error('Document not found')
    }

    const content = `Demo document: ${document.name}`

    return new Blob([content], {
      type: 'text/plain',
    })
  },

  destroy: async (documentId) => {
    const documents = getDocuments()

    const id = Number(documentId)

    const exists = documents.some((doc) => doc.id === id)

    if (!exists) {
      throw new Error('Document not found')
    }

    saveDocuments(documents.filter((doc) => doc.id !== id))

    return {
      message: 'Document deleted successfully',
    }
  },
}
//  DEMO CALENDAR API 

const EVENTS_KEY = 'calendar_events'

function getEvents() {
  return getDemoData(EVENTS_KEY, initialEvents)
}

function saveEvents(events) {
  return setDemoData(EVENTS_KEY, events)
}

export const demoCalendarApi = {
  list: async (params = {}) => {
    let events = [...getEvents()]

    if (params.from) {
      const fromDate = new Date(params.from)

      events = events.filter(
        (event) => new Date(event.starts_at) >= fromDate
      )
    }

    return {
      data: events.sort(
        (a, b) =>
          new Date(a.starts_at) - new Date(b.starts_at)
      ),
      total: events.length,
    }
  },

  show: async (eventId) => {
    const events = getEvents()

    const event = events.find(
      (item) => item.id === Number(eventId)
    )

    if (!event) {
      throw new Error('Event not found')
    }

    return {
      data: event,
    }
  },

  create: async (payload) => {
    const events = getEvents()

    const newEvent = {
      id: Date.now(),
      ...payload,
    }

    // Simple conflict detection
    const newStart = new Date(newEvent.starts_at).getTime()

    const conflicts = events.filter((event) => {
      const eventStart = new Date(event.starts_at).getTime()

      return Math.abs(eventStart - newStart) < 60 * 60 * 1000
    })

    if (conflicts.length > 0 && !payload.force) {
      const error = new Error('Calendar conflict')
      error.response = {
        status: 409,
        data: {
          conflicting_events: conflicts.map(
            (event) => event.title
          ),
        },
      }

      throw error
    }

    const updatedEvents = [
      newEvent,
      ...events,
    ]

    saveEvents(updatedEvents)

    return {
      data: newEvent,
      message: 'Event scheduled successfully',
    }
  },

  update: async (eventId, payload) => {
    const events = getEvents()

    const id = Number(eventId)

    const index = events.findIndex(
      (event) => event.id === id
    )

    if (index === -1) {
      throw new Error('Event not found')
    }

    const updatedEvent = {
      ...events[index],
      ...payload,
    }

    events[index] = updatedEvent

    saveEvents(events)

    return {
      data: updatedEvent,
      message: 'Event updated successfully',
    }
  },

  destroy: async (eventId) => {
    const events = getEvents()

    const id = Number(eventId)

    const exists = events.some(
      (event) => event.id === id
    )

    if (!exists) {
      throw new Error('Event not found')
    }

    const updatedEvents = events.filter(
      (event) => event.id !== id
    )

    saveEvents(updatedEvents)

    return {
      message: 'Event deleted successfully',
    }
  },
}
// ==================== DEMO BILLING API ====================

const TIME_ENTRIES_KEY = 'time_entries'
const EXPENSES_KEY = 'expenses'
const INVOICES_KEY = 'invoices'

function getTimeEntries() {
  return getDemoData(TIME_ENTRIES_KEY, initialTimeEntries)
}

function saveTimeEntries(entries) {
  return setDemoData(TIME_ENTRIES_KEY, entries)
}

function getExpenses() {
  return getDemoData(EXPENSES_KEY, initialExpenses)
}

function saveExpenses(expenses) {
  return setDemoData(EXPENSES_KEY, expenses)
}

function getInvoices() {
  return getDemoData(INVOICES_KEY, initialInvoices)
}

function saveInvoices(invoices) {
  return setDemoData(INVOICES_KEY, invoices)
}

export const demoBillingApi = {
  listTimeEntries: async (params = {}) => {
    let entries = [...getTimeEntries()]

    if (params.case_id) {
      entries = entries.filter(
        (entry) => entry.case_id === Number(params.case_id)
      )
    }

    return {
      data: entries,
      total: entries.length,
    }
  },

  logTime: async (payload) => {
    const entries = getTimeEntries()

    const hours = Number(payload.hours || 0)
    const rate = Number(payload.rate || 0)

    const newEntry = {
      id: Date.now(),
      ...payload,
      hours,
      rate,
      amount: hours * rate,
      date: payload.date || new Date().toISOString().slice(0, 10),
    }

    saveTimeEntries([newEntry, ...entries])

    return {
      data: newEntry,
      message: 'Time logged successfully',
    }
  },

  logExpense: async (payload) => {
    const expenses = getExpenses()

    const newExpense = {
      id: Date.now(),
      ...payload,
      amount: Number(payload.amount || 0),
      date: payload.date || new Date().toISOString().slice(0, 10),
    }

    saveExpenses([newExpense, ...expenses])

    return {
      data: newExpense,
      message: 'Expense logged successfully',
    }
  },

  listInvoices: async (params = {}) => {
    let invoices = [...getInvoices()]

    if (params.status) {
      invoices = invoices.filter(
        (invoice) => invoice.status === params.status
      )
    }

    return {
      data: invoices,
      total: invoices.length,
    }
  },

  showInvoice: async (invoiceId) => {
    const invoices = getInvoices()

    const invoice = invoices.find(
      (item) => item.id === Number(invoiceId)
    )

    if (!invoice) {
      throw new Error('Invoice not found')
    }

    return {
      data: invoice,
    }
  },

  generateInvoice: async (payload) => {
    const invoices = getInvoices()

    const newInvoice = {
      id: Date.now(),
      invoice_number: `INV-${Date.now().toString().slice(-5)}`,
      issue_date: new Date().toISOString().slice(0, 10),
      status: 'draft',
      subtotal: Number(payload.subtotal || 0),
      tax: Number(payload.tax || 0),
      total:
        Number(payload.subtotal || 0) +
        Number(payload.tax || 0),
      ...payload,
    }

    saveInvoices([newInvoice, ...invoices])

    return {
      data: newInvoice,
      message: 'Invoice generated successfully',
    }
  },

  updateInvoiceStatus: async (invoiceId, status) => {
    const invoices = getInvoices()

    const index = invoices.findIndex(
      (invoice) => invoice.id === Number(invoiceId)
    )

    if (index === -1) {
      throw new Error('Invoice not found')
    }

    invoices[index] = {
      ...invoices[index],
      status,
    }

    saveInvoices(invoices)

    return {
      data: invoices[index],
      message: 'Invoice status updated successfully',
    }
  },
}