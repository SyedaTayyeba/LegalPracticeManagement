export const initialTasks = [
  {
    id: 1,
    title: 'Review Client Agreement',
    description: 'Review the agreement and prepare it for client approval.',
    status: 'pending',
    priority: 'high',
    due_date: '2026-09-02',
    assigned_to: 2,
    case_id: 1,
  },
  {
    id: 2,
    title: 'Prepare Court Documents',
    description: 'Prepare required documents for the upcoming hearing.',
    status: 'in_progress',
    priority: 'high',
    due_date: '2026-09-04',
    assigned_to: 1,
    case_id: 2,
  },
  {
    id: 3,
    title: 'Client Follow-up',
    description: 'Contact the client regarding the pending documents.',
    status: 'pending',
    priority: 'medium',
    due_date: '2026-09-06',
    assigned_to: 3,
    case_id: 1,
  },
  {
    id: 4,
    title: 'Archive Case Documents',
    description: 'Organize and archive completed case documents.',
    status: 'completed',
    priority: 'low',
    due_date: '2026-08-28',
    assigned_to: 2,
    case_id: 3,
  },
]
//demo user
export const demoUser = {
  id: 1,
  name: 'Demo Admin',
  email: 'demo@legalflow.com',
  role: 'firm_owner',
  firm_id: 1,
  firm: {
    id: 1,
    name: 'Demo Legal Firm',
  },
}
export const demoFirm = {
  id: 1,
  name: 'Demo Legal Associates',
  plan: 'professional',
  staff_count: 4,
  seat_limit: 10,
}

export const demoTeam = [
  {
    id: 1,
    name: 'Demo Admin',
    email: 'demo@legalflow.com',
    role: 'firm_owner',
  },
  {
    id: 2,
    name: 'Sarah Khan',
    email: 'sarah@demo.com',
    role: 'lawyer',
  },
  {
    id: 3,
    name: 'Ali Ahmed',
    email: 'ali@demo.com',
    role: 'paralegal',
  },
  {
    id: 4,
    name: 'Client Demo',
    email: 'client@demo.com',
    role: 'client',
  },
]
export const initialCases = [
  {
    id: 1,
    case_number: 'CASE-001',
    title: 'ABC Property Dispute',
    description: 'Property ownership dispute between two parties.',
    status: 'active',
    case_type: 'Civil',
    priority: 'high',

    client_id: 1,
    client_name: 'Ahmed Khan',
    client: {
      id: 1,
      display_name: 'Ahmed Khan',
    },

    assigned_to: 2,
    lawyer_name: 'Sarah Khan',

    filing_date: '2026-08-10',
    next_hearing_date: '2026-09-15',

    court: {
      name: 'Lahore Civil Court',
      case_number: 'LC-2026-001',
    },

    opposing_party: 'ABC Properties Ltd.',

    team: [
      {
        id: 2,
        name: 'Sarah Khan',
        role_on_case: 'Lead Lawyer',
      },
      {
        id: 3,
        name: 'Ali Ahmed',
        role_on_case: 'Assistant',
      },
    ],

    status_history: [
      {
        from_status: null,
        to_status: 'new',
        changed_by: 'Demo Admin',
        created_at: '2026-08-10T10:00:00',
      },
      {
        from_status: 'new',
        to_status: 'active',
        changed_by: 'Sarah Khan',
        created_at: '2026-08-12T14:30:00',
      },
    ],

    notes: [
      {
        id: 1,
        author: 'Sarah Khan',
        body: 'Initial documents have been reviewed.',
        pinned: true,
        created_at: '2026-08-12T15:00:00',
      },
    ],
  },

  {
    id: 2,
    case_number: 'CASE-002',
    title: 'XYZ Criminal Defense',
    description: 'Criminal defense case for the client.',
    status: 'waiting',
    case_type: 'Criminal',
    priority: 'high',

    client_id: 2,
    client_name: 'Usman Ali',
    client: {
      id: 2,
      display_name: 'Usman Ali',
    },

    assigned_to: 2,
    lawyer_name: 'Sarah Khan',

    filing_date: '2026-08-18',
    next_hearing_date: '2026-09-20',

    court: {
      name: 'District Court',
      case_number: 'DC-2026-045',
    },

    opposing_party: 'State',

    team: [
      {
        id: 2,
        name: 'Sarah Khan',
        role_on_case: 'Lead Lawyer',
      },
    ],

    status_history: [
      {
        from_status: null,
        to_status: 'new',
        changed_by: 'Demo Admin',
        created_at: '2026-08-18T09:00:00',
      },
      {
        from_status: 'new',
        to_status: 'waiting',
        changed_by: 'Sarah Khan',
        created_at: '2026-08-20T11:30:00',
      },
    ],

    notes: [],
  },

  {
    id: 3,
    case_number: 'CASE-003',
    title: 'Smith Family Settlement',
    description: 'Family settlement and legal documentation.',
    status: 'closed',
    case_type: 'Family',
    priority: 'medium',

    client_id: 3,
    client_name: 'Maria Smith',
    client: {
      id: 3,
      display_name: 'Maria Smith',
    },

    assigned_to: 1,
    lawyer_name: 'Demo Admin',

    filing_date: '2026-07-05',
    next_hearing_date: null,

    court: {
      name: 'Family Court',
      case_number: 'FC-2026-012',
    },

    opposing_party: 'Private Party',

    team: [
      {
        id: 1,
        name: 'Demo Admin',
        role_on_case: 'Lead Lawyer',
      },
    ],

    status_history: [
      {
        from_status: null,
        to_status: 'new',
        changed_by: 'Demo Admin',
        created_at: '2026-07-05T10:00:00',
      },
      {
        from_status: 'active',
        to_status: 'closed',
        changed_by: 'Demo Admin',
        created_at: '2026-08-15T16:00:00',
      },
    ],

    notes: [
      {
        id: 1,
        author: 'Demo Admin',
        body: 'Settlement successfully completed and case closed.',
        pinned: false,
        created_at: '2026-08-15T16:30:00',
      },
    ],
  },
]
export const initialClients = [
  {
    id: 1,
    type: 'individual',
    first_name: 'Ahmed',
    last_name: 'Khan',
    display_name: 'Ahmed Khan',
    email: 'ahmed@example.com',
    phone: '+92 300 1234567',
    status: 'active',
  },
  {
    id: 2,
    type: 'individual',
    first_name: 'Usman',
    last_name: 'Ali',
    display_name: 'Usman Ali',
    email: 'usman@example.com',
    phone: '+92 321 9876543',
    status: 'active',
  },
  {
    id: 3,
    type: 'individual',
    first_name: 'Maria',
    last_name: 'Smith',
    display_name: 'Maria Smith',
    email: 'maria@example.com',
    phone: '+92 333 4567890',
    status: 'active',
  },
  {
    id: 4,
    type: 'organization',
    organization_name: 'ABC Properties',
    display_name: 'ABC Properties',
    email: 'contact@abcproperties.com',
    phone: '+92 42 1234567',
    status: 'active',
  },
]
export const initialDocuments = [
  {
    id: 1,
    name: 'Property Agreement.pdf',
    original_filename: 'Property Agreement.pdf',
    category: 'agreement',
    version: 1,
    size_bytes: 245760,
    case_id: 1,
    case: {
      id: 1,
      title: 'ABC Property Dispute',
    },
    created_at: '2026-08-20T10:00:00.000Z',
  },
  {
    id: 2,
    name: 'Court Evidence.pdf',
    original_filename: 'Court Evidence.pdf',
    category: 'evidence',
    version: 1,
    size_bytes: 524288,
    case_id: 2,
    case: {
      id: 2,
      title: 'XYZ Criminal Defense',
    },
    created_at: '2026-08-22T10:00:00.000Z',
  },
  {
    id: 3,
    name: 'Client Identification.pdf',
    original_filename: 'Client Identification.pdf',
    category: 'client_document',
    version: 1,
    size_bytes: 153600,
    case_id: 1,
    case: {
      id: 1,
      title: 'ABC Property Dispute',
    },
    created_at: '2026-08-25T10:00:00.000Z',
  },
]
export const initialEvents = [
  {
    id: 1,
    title: 'ABC Property Dispute Hearing',
    event_type: 'hearing',
    starts_at: '2026-09-15T10:00:00',
    location: 'Lahore High Court',
    case_id: 1,
  },
  {
    id: 2,
    title: 'Client Meeting - Usman Ali',
    event_type: 'meeting',
    starts_at: '2026-09-08T14:00:00',
    location: 'Demo Legal Firm',
    case_id: 2,
  },
  {
    id: 3,
    title: 'Court Documents Deadline',
    event_type: 'deadline',
    starts_at: '2026-09-10T12:00:00',
    location: null,
    case_id: 2,
  },
  {
    id: 4,
    title: 'Case Review',
    event_type: 'other',
    starts_at: '2026-09-18T11:00:00',
    location: 'Demo Legal Firm',
    case_id: 1,
  },
]
export const initialTimeEntries = [
  {
    id: 1,
    description: 'Review property case documents',
    hours: 3,
    rate: 5000,
    amount: 15000,
    date: '2026-08-25',
    user_id: 2,
    user_name: 'Sarah Khan',
    case_id: 1,
    case: {
      id: 1,
      title: 'ABC Property Dispute',
    },
  },
  {
    id: 2,
    description: 'Prepare criminal defense documents',
    hours: 2,
    rate: 5000,
    amount: 10000,
    date: '2026-08-27',
    user_id: 2,
    user_name: 'Sarah Khan',
    case_id: 2,
    case: {
      id: 2,
      title: 'XYZ Criminal Defense',
    },
  },
]

export const initialExpenses = [
  {
    id: 1,
    description: 'Court filing fee',
    amount: 3000,
    date: '2026-08-26',
    case_id: 1,
    case: {
      id: 1,
      title: 'ABC Property Dispute',
    },
  },
]

export const initialInvoices = [
  {
    id: 1,
    invoice_number: 'INV-001',
    client_id: 1,
    client_name: 'Ahmed Khan',
    case_id: 1,
    case_title: 'ABC Property Dispute',
    issue_date: '2026-08-28',
    due_date: '2026-09-15',
    subtotal: 15000,
    tax: 0,
    total: 15000,
    status: 'sent',
  },
  {
    id: 2,
    invoice_number: 'INV-002',
    client_id: 2,
    client_name: 'Usman Ali',
    case_id: 2,
    case_title: 'XYZ Criminal Defense',
    issue_date: '2026-08-29',
    due_date: '2026-09-20',
    subtotal: 10000,
    tax: 0,
    total: 10000,
    status: 'draft',
  },
]
