import { useState } from 'react'
import { Clock, FileStack, Loader2, Plus } from 'lucide-react'
import WorkspaceHeader from '../components/WorkspaceHeader'
import { useTimeEntries, useLogTime, useInvoices, useUpdateInvoiceStatus } from '../hooks/useBilling'
import { useAuthStore } from '../store/authStore'

const INVOICE_STATUS_STYLES = {
  draft: 'bg-slate-100 text-slate-600',
  sent: 'bg-blue-50 text-blue-700',
  paid: 'bg-emerald-50 text-emerald-700',
  overdue: 'bg-red-50 text-red-700',
}

export default function Billing() {
  const user = useAuthStore((s) => s.user)
  const isOwner = user?.role === 'firm_owner'

  const { data: timeData, isLoading: timeLoading } = useTimeEntries()
  const logTimeMutation = useLogTime()
  const [showTimeForm, setShowTimeForm] = useState(false)

  const { data: invoiceData, isLoading: invoicesLoading } = useInvoices()
  const updateStatusMutation = useUpdateInvoiceStatus()

  const entries = timeData?.data ?? []
  const invoices = invoiceData?.data ?? []

  const handleLogTime = (e) => {
    e.preventDefault()
    const form = new FormData(e.target)
    logTimeMutation.mutate(
      {
        case_id: form.get('case_id'),
        description: form.get('description'),
        minutes: Number(form.get('minutes')),
        hourly_rate: Number(form.get('hourly_rate')),
        entry_date: form.get('entry_date'),
      },
      { onSuccess: () => setShowTimeForm(false) }
    )
  }

  return (
    <div className="min-h-screen bg-slate-50">
      <WorkspaceHeader />

      <main className="max-w-5xl mx-auto px-6 py-10 space-y-8">
        <div>
          <p className="text-xs uppercase tracking-[0.2em] text-brand-400 font-medium mb-1">
            Billing & Time Tracking
          </p>
          <h1 className="font-serif text-3xl text-slate-900">Billing</h1>
        </div>

        <section className="bg-white rounded-lg border border-slate-200 p-6">
          <div className="flex items-center justify-between mb-4">
            <div className="flex items-center gap-2">
              <Clock className="h-4 w-4 text-brand-500" />
              <h2 className="font-medium text-slate-800">Time entries</h2>
            </div>
            <button onClick={() => setShowTimeForm((v) => !v)} className="flex items-center gap-1 text-sm text-brand-500 hover:underline">
              <Plus className="h-4 w-4" /> Log time
            </button>
          </div>

          {showTimeForm && (
            <form onSubmit={handleLogTime} className="grid sm:grid-cols-5 gap-3 mb-4 bg-slate-50 rounded-md p-4">
              <input name="case_id" required placeholder="Case UUID" className="rounded-md border border-slate-300 px-3 py-2 text-sm sm:col-span-2" />
              <input name="description" required placeholder="Description" className="rounded-md border border-slate-300 px-3 py-2 text-sm sm:col-span-3" />
              <input name="minutes" type="number" required placeholder="Minutes" className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
              <input name="hourly_rate" type="number" step="0.01" required placeholder="Rate/hr" className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
              <input name="entry_date" type="date" required defaultValue={new Date().toISOString().slice(0, 10)} className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
              <button type="submit" disabled={logTimeMutation.isPending} className="rounded-md bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium px-4 py-2 disabled:opacity-60 sm:col-span-2">
                {logTimeMutation.isPending ? 'Saving…' : 'Log time'}
              </button>
            </form>
          )}

          <div className="divide-y divide-slate-100">
            {timeLoading ? (
              <p className="text-sm text-slate-400 py-3">Loading…</p>
            ) : entries.length === 0 ? (
              <p className="text-sm text-slate-400 py-3">No time logged yet.</p>
            ) : (
              entries.map((entry) => (
                <div key={entry.id} className="flex items-center justify-between py-3 text-sm">
                  <div>
                    <p className="text-slate-800">{entry.description}</p>
                    <p className="text-xs text-slate-400">{entry.case?.title} · {entry.user?.name} · {entry.entry_date}</p>
                  </div>
                  <div className="text-right">
                    <p className="font-medium text-slate-800">${entry.amount.toFixed(2)}</p>
                    <p className="text-xs text-slate-400">{(entry.minutes / 60).toFixed(1)}h {entry.is_invoiced && '· invoiced'}</p>
                  </div>
                </div>
              ))
            )}
          </div>
        </section>

        <section className="bg-white rounded-lg border border-slate-200 p-6">
          <div className="flex items-center gap-2 mb-4">
            <FileStack className="h-4 w-4 text-brand-500" />
            <h2 className="font-medium text-slate-800">Invoices</h2>
          </div>

          <div className="divide-y divide-slate-100">
            {invoicesLoading ? (
              <p className="text-sm text-slate-400 py-3">Loading…</p>
            ) : invoices.length === 0 ? (
              <p className="text-sm text-slate-400 py-3">
                No invoices yet.{!isOwner && ' Only the Firm Owner can generate invoices.'}
              </p>
            ) : (
              invoices.map((invoice) => (
                <div key={invoice.id} className="flex items-center justify-between py-3 text-sm">
                  <div>
                    <p className="font-mono text-xs text-slate-400">{invoice.invoice_number}</p>
                    <p className="text-slate-800">{invoice.client?.display_name}</p>
                  </div>
                  <div className="flex items-center gap-3">
                    <span className="font-medium text-slate-800">${invoice.total.toFixed(2)}</span>
                    {isOwner ? (
                      <select
                        value={invoice.status}
                        onChange={(e) => updateStatusMutation.mutate({ id: invoice.id, status: e.target.value })}
                        className={`text-xs uppercase tracking-wide px-2 py-1 rounded-sm border-0 ${INVOICE_STATUS_STYLES[invoice.status]}`}
                      >
                        <option value="draft">Draft</option>
                        <option value="sent">Sent</option>
                        <option value="paid">Paid</option>
                        <option value="overdue">Overdue</option>
                      </select>
                    ) : (
                      <span className={`text-xs uppercase tracking-wide px-2 py-1 rounded-sm ${INVOICE_STATUS_STYLES[invoice.status]}`}>
                        {invoice.status}
                      </span>
                    )}
                  </div>
                </div>
              ))
            )}
          </div>
        </section>
      </main>
    </div>
  )
}
