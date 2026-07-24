import { useState } from 'react'
import { Link } from 'react-router-dom'
import { Search, UserPlus, Loader2, Building2, User as UserIcon } from 'lucide-react'
import WorkspaceHeader from '../components/WorkspaceHeader'
import { useClients, useCreateClient } from '../hooks/useClients'

export default function Clients() {
  const [search, setSearch] = useState('')
  const [status, setStatus] = useState('')
  const [showForm, setShowForm] = useState(false)

  const { data, isLoading, isFetching } = useClients({ search, status })
  const createMutation = useCreateClient()

  const clients = data?.data ?? []

  const handleCreate = (e) => {
    e.preventDefault()
    const form = new FormData(e.target)
    const type = form.get('type')
    const payload = {
      type,
      first_name: form.get('first_name') || undefined,
      last_name: form.get('last_name') || undefined,
      organization_name: form.get('organization_name') || undefined,
      email: form.get('email') || undefined,
      phone: form.get('phone') || undefined,
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
              Client Management
            </p>
            <h1 className="font-serif text-3xl text-slate-900">Clients</h1>
          </div>
          <button
            onClick={() => setShowForm((v) => !v)}
            className="flex items-center gap-2 rounded-md bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium px-4 py-2"
          >
            <UserPlus className="h-4 w-4" />
            New client
          </button>
        </div>

        {showForm && (
          <form
            onSubmit={handleCreate}
            className="bg-white rounded-lg border border-slate-200 p-6 grid sm:grid-cols-2 gap-4"
          >
            <select name="type" defaultValue="individual" className="rounded-md border border-slate-300 px-3 py-2 text-sm sm:col-span-2">
              <option value="individual">Individual</option>
              <option value="organization">Organization</option>
            </select>
            <input name="first_name" placeholder="First name" className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
            <input name="last_name" placeholder="Last name" className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
            <input name="organization_name" placeholder="Organization name (if applicable)" className="rounded-md border border-slate-300 px-3 py-2 text-sm sm:col-span-2" />
            <input name="email" type="email" placeholder="Email" className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
            <input name="phone" placeholder="Phone" className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
            <div className="sm:col-span-2 flex justify-end gap-2">
              <button type="button" onClick={() => setShowForm(false)} className="text-sm text-slate-500 px-4 py-2">
                Cancel
              </button>
              <button
                type="submit"
                disabled={createMutation.isPending}
                className="rounded-md bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium px-4 py-2 disabled:opacity-60"
              >
                {createMutation.isPending ? 'Saving…' : 'Save client'}
              </button>
            </div>
          </form>
        )}

        <div className="flex items-center gap-3">
          <div className="relative flex-1">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
            <input
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Search by name or email…"
              className="w-full rounded-md border border-slate-300 pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
            />
          </div>
          <select
            value={status}
            onChange={(e) => setStatus(e.target.value)}
            className="rounded-md border border-slate-300 px-3 py-2 text-sm"
          >
            <option value="">All statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="archived">Archived</option>
          </select>
          {isFetching && <Loader2 className="h-4 w-4 animate-spin text-slate-400" />}
        </div>

        <div className="bg-white rounded-lg border border-slate-200 divide-y divide-slate-100">
          {isLoading ? (
            <p className="p-6 text-sm text-slate-400">Loading clients…</p>
          ) : clients.length === 0 ? (
            <p className="p-6 text-sm text-slate-400">No clients match your search yet.</p>
          ) : (
            clients.map((client) => (
              <Link
                key={client.id}
                to={`/clients/${client.id}`}
                className="flex items-center justify-between px-6 py-4 hover:bg-slate-50 transition-colors"
              >
                <div className="flex items-center gap-3">
                  <div className="h-9 w-9 rounded-full bg-brand-50 flex items-center justify-center text-brand-500">
                    {client.type === 'organization' ? <Building2 className="h-4 w-4" /> : <UserIcon className="h-4 w-4" />}
                  </div>
                  <div>
                    <p className="text-sm font-medium text-slate-800">{client.display_name}</p>
                    <p className="text-xs text-slate-400">{client.email || client.phone || '—'}</p>
                  </div>
                </div>
                <span className="text-xs uppercase tracking-wide text-slate-400">{client.status}</span>
              </Link>
            ))
          )}
        </div>
      </main>
    </div>
  )
}
