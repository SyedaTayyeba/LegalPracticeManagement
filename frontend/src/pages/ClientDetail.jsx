import { useState } from 'react'
import { useParams, Link } from 'react-router-dom'
import { ArrowLeft, Loader2, Pin } from 'lucide-react'
import WorkspaceHeader from '../components/WorkspaceHeader'
import { useClient, useAddClientNote } from '../hooks/useClients'

export default function ClientDetail() {
  const { clientId } = useParams()
  const { data: client, isLoading } = useClient(clientId)
  const addNoteMutation = useAddClientNote(clientId)
  const [noteBody, setNoteBody] = useState('')

  const handleAddNote = (e) => {
    e.preventDefault()
    if (!noteBody.trim()) return
    addNoteMutation.mutate({ body: noteBody }, { onSuccess: () => setNoteBody('') })
  }

  return (
    <div className="min-h-screen bg-slate-50">
      <WorkspaceHeader />
      <main className="max-w-4xl mx-auto px-6 py-10 space-y-6">
        <Link to="/clients" className="flex items-center gap-1 text-sm text-slate-500 hover:text-slate-800 w-fit">
          <ArrowLeft className="h-4 w-4" /> Back to clients
        </Link>

        {isLoading ? (
          <p className="text-sm text-slate-400">Loading client…</p>
        ) : !client ? (
          <p className="text-sm text-slate-400">Client not found.</p>
        ) : (
          <>
            <div>
              <p className="text-xs uppercase tracking-[0.2em] text-brand-400 font-medium mb-1">
                {client.type === 'organization' ? 'Organization' : 'Individual'} · {client.status}
              </p>
              <h1 className="font-serif text-3xl text-slate-900">{client.display_name}</h1>
            </div>

            <div className="grid sm:grid-cols-2 gap-6">
              <section className="bg-white rounded-lg border border-slate-200 p-6">
                <h2 className="font-medium text-slate-800 mb-3">Contact details</h2>
                <dl className="text-sm space-y-2">
                  <div className="flex justify-between"><dt className="text-slate-500">Email</dt><dd>{client.email || '—'}</dd></div>
                  <div className="flex justify-between"><dt className="text-slate-500">Phone</dt><dd>{client.phone || '—'}</dd></div>
                  <div className="flex justify-between"><dt className="text-slate-500">City</dt><dd>{client.address?.city || '—'}</dd></div>
                </dl>
              </section>

              <section className="bg-white rounded-lg border border-slate-200 p-6">
                <h2 className="font-medium text-slate-800 mb-3">Related cases</h2>
                <p className="text-sm text-slate-400">
                  No cases linked yet — Case Management module attaches here.
                </p>
              </section>
            </div>

            <section className="bg-white rounded-lg border border-slate-200 p-6">
              <h2 className="font-medium text-slate-800 mb-4">Activity timeline</h2>

              <form onSubmit={handleAddNote} className="flex gap-2 mb-6">
                <input
                  value={noteBody}
                  onChange={(e) => setNoteBody(e.target.value)}
                  placeholder="Add a note…"
                  className="flex-1 rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                />
                <button
                  type="submit"
                  disabled={addNoteMutation.isPending}
                  className="rounded-md bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium px-4 py-2 disabled:opacity-60"
                >
                  {addNoteMutation.isPending ? <Loader2 className="h-4 w-4 animate-spin" /> : 'Add'}
                </button>
              </form>

              <ul className="space-y-4">
                {(client.notes ?? []).map((note) => (
                  <li key={note.id} className="border-l-2 border-brand-100 pl-4">
                    <div className="flex items-center gap-2 text-xs text-slate-400 mb-1">
                      {note.pinned && <Pin className="h-3 w-3 text-gold-500" />}
                      <span>{note.author}</span>
                      <span>·</span>
                      <span>{new Date(note.created_at).toLocaleString()}</span>
                    </div>
                    <p className="text-sm text-slate-700">{note.body}</p>
                  </li>
                ))}
                {(client.notes ?? []).length === 0 && (
                  <p className="text-sm text-slate-400">No activity recorded yet.</p>
                )}
              </ul>
            </section>
          </>
        )}
      </main>
    </div>
  )
}
