import { useState } from 'react'
import { Link } from 'react-router-dom'
import { Search, FilePlus, Loader2, Scale } from 'lucide-react'
import WorkspaceHeader from '../components/WorkspaceHeader'
import { useCases, useCreateCase } from '../hooks/useCases'
import { useClients } from '../hooks/useClients'

const STATUS_STYLES = {
  new: 'bg-slate-100 text-slate-600',
  investigation: 'bg-amber-50 text-amber-700',
  active: 'bg-emerald-50 text-emerald-700',
  waiting: 'bg-blue-50 text-blue-700',
  completed: 'bg-slate-100 text-slate-500',
  closed: 'bg-slate-200 text-slate-500',
}

export default function Cases() {
  const [search, setSearch] = useState('')
  const [status, setStatus] = useState('')
  const [showForm, setShowForm] = useState(false)

  const { data, isLoading, isFetching } = useCases({ search, status })
  const { data: clientsData } = useClients({ status: 'active', per_page: 100 })
  const createMutation = useCreateCase()

  const cases = data?.data ?? []
  const clients = clientsData?.data ?? []

  const handleCreate = (e) => {
    e.preventDefault()
    const form = new FormData(e.target)
    const payload = {
      title: form.get('title'),
      case_type: form.get('case_type'),
      client_id: form.get('client_id'),
      opposing_party: form.get('opposing_party') || undefined,
    }
    createMutation.mutate(payload, { onSuccess: () => setShowForm(false) })
  }

  return (
    <div className="min-h-screen bg-slate-50">
      <WorkspaceHeader />

      <main className="max-w-5xl mx-auto px-6 py-10 space-y-6">
        <div className="flex items-center justify-between">
          <div>
            <p className="text-xs uppercase tracking-[0.2em] text-brand-400 font-medium mb-1">
              Case Management
            </p>
            <h1 className="font-serif text-3xl text-slate-900">Cases</h1>
          </div>
          <button
            onClick={() => setShowForm((v) => !v)}
            className="flex items-center gap-2 rounded-md bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium px-4 py-2"
          >
            <FilePlus className="h-4 w-4" />
            Open case
          </button>
        </div>

        {showForm && (
          <form
            onSubmit={handleCreate}
            className="bg-white rounded-lg border border-slate-200 p-6 grid sm:grid-cols-2 gap-4"
          >
            <input name="title" required placeholder="Case title" className="rounded-md border border-slate-300 px-3 py-2 text-sm sm:col-span-2" />
            <input name="case_type" required placeholder="Case type (e.g. Litigation)" className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
            <select name="client_id" required defaultValue="" className="rounded-md border border-slate-300 px-3 py-2 text-sm">
              <option value="" disabled>Select client…</option>
              {clients.map((c) => (
                <option key={c.id} value={c.id}>{c.display_name}</option>
              ))}
            </select>
            <input name="opposing_party" placeholder="Opposing party (optional)" className="rounded-md border border-slate-300 px-3 py-2 text-sm sm:col-span-2" />
            <div className="sm:col-span-2 flex justify-end gap-2">
              <button type="button" onClick={() => setShowForm(false)} className="text-sm text-slate-500 px-4 py-2">
                Cancel
              </button>
              <button
                type="submit"
                disabled={createMutation.isPending}
                className="rounded-md bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium px-4 py-2 disabled:opacity-60"
              >
                {createMutation.isPending ? 'Opening…' : 'Open case'}
              </button>
            </div>
            {createMutation.isError && (
              <p className="text-xs text-red-600 sm:col-span-2">
                {createMutation.error?.response?.data?.message ?? 'Could not open case.'}
              </p>
            )}
          </form>
        )}

        <div className="flex items-center gap-3">
          <div className="relative flex-1">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
            <input
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Search by title or case number…"
              className="w-full rounded-md border border-slate-300 pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
            />
          </div>
          <select
            value={status}
            onChange={(e) => setStatus(e.target.value)}
            className="rounded-md border border-slate-300 px-3 py-2 text-sm"
          >
            <option value="">All statuses</option>
            {Object.keys(STATUS_STYLES).map((s) => (
              <option key={s} value={s}>{s[0].toUpperCase() + s.slice(1)}</option>
            ))}
          </select>
          {isFetching && <Loader2 className="h-4 w-4 animate-spin text-slate-400" />}
        </div>

        <div className="bg-white rounded-lg border border-slate-200 divide-y divide-slate-100">
          {isLoading ? (
            <p className="p-6 text-sm text-slate-400">Loading cases…</p>
          ) : cases.length === 0 ? (
            <p className="p-6 text-sm text-slate-400">No cases match yet — open your first one above.</p>
          ) : (
            cases.map((c) => (
              <Link
                key={c.id}
                to={`/cases/${c.id}`}
                className="flex items-center justify-between px-6 py-4 hover:bg-slate-50 transition-colors"
              >
                <div className="flex items-center gap-3">
                  <div className="h-9 w-9 rounded-full bg-brand-50 flex items-center justify-center text-brand-500">
                    <Scale className="h-4 w-4" />
                  </div>
                  <div>
                    <p className="font-mono text-[11px] text-slate-400">{c.case_number}</p>
                    <p className="text-sm font-medium text-slate-800">{c.title}</p>
                    <p className="text-xs text-slate-400">{c.client?.display_name}</p>
                  </div>
                </div>
                <span className={`text-xs uppercase tracking-wide px-2 py-1 rounded-sm ${STATUS_STYLES[c.status]}`}>
                  {c.status}
                </span>
              </Link>
            ))
          )}
        </div>
      </main>
    </div>
  )
}
