import { CalendarDays, FileText, MessageSquare, Scale, Loader2 } from 'lucide-react'
import { useLogout } from '../hooks/useAuth'
import { usePortalDashboard } from '../hooks/usePortal'
import { useAuthStore } from '../store/authStore'

export default function ClientPortal() {
  const user = useAuthStore((s) => s.user)
  const { data, isLoading } = usePortalDashboard()
  const logoutMutation = useLogout()

  return (
    <div className="min-h-screen bg-white">
      <header className="border-b border-slate-100">
        <div className="max-w-3xl mx-auto px-6 py-5 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <div className="h-8 w-8 rounded-sm bg-brand-500 flex items-center justify-center">
              <span className="text-gold-500 font-serif text-lg leading-none">§</span>
            </div>
            <span className="font-serif text-lg text-brand-900">LegalCaseFlow</span>
            <span className="text-xs text-slate-400 border-l border-slate-200 pl-2 ml-1">Client Portal</span>
          </div>
          <button onClick={() => logoutMutation.mutate()} className="text-sm text-slate-500 hover:text-slate-800">
            Sign out
          </button>
        </div>
      </header>

      <main className="max-w-3xl mx-auto px-6 py-12">
        <h1 className="font-serif text-3xl text-slate-900 mb-1">
          Welcome{user?.name ? `, ${user.name.split(' ')[0]}` : ''}
        </h1>
        <p className="text-slate-500 mb-10">Here's where things stand with your matter.</p>

        {isLoading ? (
          <p className="text-sm text-slate-400">Loading…</p>
        ) : !data ? (
          <p className="text-sm text-slate-400">No client record is linked to your account yet.</p>
        ) : (
          <div className="grid sm:grid-cols-2 gap-6">
            <div className="border border-slate-100 rounded-lg p-6">
              <div className="flex items-center gap-2 mb-3 text-brand-500">
                <Scale className="h-4 w-4" /><h2 className="font-medium text-slate-800">Your cases</h2>
              </div>
              <p className="text-3xl font-serif text-slate-900">{data.open_case_count}</p>
              <p className="text-sm text-slate-400">open of {data.total_case_count} total</p>
            </div>

            <div className="border border-slate-100 rounded-lg p-6">
              <div className="flex items-center gap-2 mb-3 text-brand-500">
                <CalendarDays className="h-4 w-4" /><h2 className="font-medium text-slate-800">Upcoming events</h2>
              </div>
              <ul className="text-sm space-y-1.5">
                {data.upcoming_events.length === 0 && <li className="text-slate-400">Nothing scheduled.</li>}
                {data.upcoming_events.map((e) => (
                  <li key={e.id} className="flex justify-between">
                    <span className="text-slate-700">{e.title}</span>
                    <span className="text-slate-400 text-xs">{new Date(e.starts_at).toLocaleDateString()}</span>
                  </li>
                ))}
              </ul>
            </div>

            <div className="border border-slate-100 rounded-lg p-6">
              <div className="flex items-center gap-2 mb-3 text-brand-500">
                <FileText className="h-4 w-4" /><h2 className="font-medium text-slate-800">Recent documents</h2>
              </div>
              <ul className="text-sm space-y-1.5">
                {data.recent_documents.length === 0 && <li className="text-slate-400">No documents shared yet.</li>}
                {data.recent_documents.map((d) => (
                  <li key={d.id} className="text-slate-700">{d.name}</li>
                ))}
              </ul>
            </div>

            <div className="border border-slate-100 rounded-lg p-6">
              <div className="flex items-center gap-2 mb-3 text-brand-500">
                <MessageSquare className="h-4 w-4" /><h2 className="font-medium text-slate-800">Messages</h2>
              </div>
              <p className="text-3xl font-serif text-slate-900">{data.unread_conversations}</p>
              <p className="text-sm text-slate-400">unread conversation{data.unread_conversations === 1 ? '' : 's'}</p>
            </div>

            {data.outstanding_invoice_total > 0 && (
              <div className="border border-amber-200 bg-amber-50 rounded-lg p-6 sm:col-span-2">
                <p className="text-sm text-amber-800">
                  You have an outstanding balance of <span className="font-medium">${data.outstanding_invoice_total.toFixed(2)}</span>.
                </p>
              </div>
            )}
          </div>
        )}
      </main>
    </div>
  )
}
