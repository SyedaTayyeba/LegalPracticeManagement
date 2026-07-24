import { useState } from 'react'
import { useParams, Link } from 'react-router-dom'
import { ArrowLeft, Loader2, Pin, Scale, Users } from 'lucide-react'
import WorkspaceHeader from '../components/WorkspaceHeader'
import { useCase, useUpdateCaseStatus, useAddCaseNote } from '../hooks/useCases'

const STATUSES = ['new', 'investigation', 'active', 'waiting', 'completed', 'closed']

export default function CaseDetail() {
  const { caseId } = useParams()
  const { data: caseFile, isLoading } = useCase(caseId)
  const statusMutation = useUpdateCaseStatus(caseId)
  const addNoteMutation = useAddCaseNote(caseId)
  const [noteBody, setNoteBody] = useState('')

  const handleStatusChange = (e) => {
    statusMutation.mutate({ status: e.target.value })
  }

  const handleAddNote = (e) => {
    e.preventDefault()
    if (!noteBody.trim()) return
    addNoteMutation.mutate({ body: noteBody }, { onSuccess: () => setNoteBody('') })
  }

  return (
    <div className="min-h-screen bg-slate-50">
      <WorkspaceHeader />
      <main className="max-w-4xl mx-auto px-6 py-10 space-y-6">
        <Link to="/cases" className="flex items-center gap-1 text-sm text-slate-500 hover:text-slate-800 w-fit">
          <ArrowLeft className="h-4 w-4" /> Back to cases
        </Link>

        {isLoading ? (
          <p className="text-sm text-slate-400">Loading case…</p>
        ) : !caseFile ? (
          <p className="text-sm text-slate-400">Case not found.</p>
        ) : (
          <>
            <div className="flex items-start justify-between">
              <div>
                <p className="font-mono text-xs text-slate-400 mb-1">{caseFile.case_number}</p>
                <h1 className="font-serif text-3xl text-slate-900">{caseFile.title}</h1>
                <p className="text-sm text-slate-500 mt-1">{caseFile.client?.display_name} · {caseFile.case_type}</p>
              </div>
              <select
                value={caseFile.status}
                onChange={handleStatusChange}
                disabled={statusMutation.isPending}
                className="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium capitalize"
              >
                {STATUSES.map((s) => (
                  <option key={s} value={s}>{s}</option>
                ))}
              </select>
            </div>

            <div className="grid sm:grid-cols-3 gap-6">
              <section className="bg-white rounded-lg border border-slate-200 p-6">
                <div className="flex items-center gap-2 mb-3">
                  <Scale className="h-4 w-4 text-brand-500" />
                  <h2 className="font-medium text-slate-800">Court information</h2>
                </div>
                <dl className="text-sm space-y-2">
                  <div className="flex justify-between"><dt className="text-slate-500">Court</dt><dd>{caseFile.court?.name || '—'}</dd></div>
                  <div className="flex justify-between"><dt className="text-slate-500">Docket #</dt><dd>{caseFile.court?.case_number || '—'}</dd></div>
                  <div className="flex justify-between"><dt className="text-slate-500">Opposing</dt><dd>{caseFile.opposing_party || '—'}</dd></div>
                </dl>
              </section>

              <section className="bg-white rounded-lg border border-slate-200 p-6">
                <div className="flex items-center gap-2 mb-3">
                  <Users className="h-4 w-4 text-brand-500" />
                  <h2 className="font-medium text-slate-800">Team</h2>
                </div>
                <ul className="text-sm space-y-1">
                  {(caseFile.team ?? []).map((member) => (
                    <li key={member.id} className="flex justify-between">
                      <span>{member.name}</span>
                      <span className="text-xs text-slate-400 capitalize">{member.role_on_case}</span>
                    </li>
                  ))}
                  {(caseFile.team ?? []).length === 0 && <li className="text-slate-400">Unassigned</li>}
                </ul>
              </section>

              <section className="bg-white rounded-lg border border-slate-200 p-6">
                <h2 className="font-medium text-slate-800 mb-3">Related documents</h2>
                <p className="text-sm text-slate-400">
                  No documents linked yet — Document Management module attaches here.
                </p>
              </section>
            </div>

            <section className="bg-white rounded-lg border border-slate-200 p-6">
              <h2 className="font-medium text-slate-800 mb-4">Case timeline</h2>
              <ul className="space-y-3 mb-6">
                {(caseFile.status_history ?? []).map((entry, i) => (
                  <li key={i} className="flex items-center gap-3 text-sm">
                    <span className="h-2 w-2 rounded-full bg-brand-500 shrink-0" />
                    <span className="text-slate-700">
                      {entry.from_status ? `${entry.from_status} → ${entry.to_status}` : `Opened as ${entry.to_status}`}
                    </span>
                    <span className="text-xs text-slate-400">{entry.changed_by}</span>
                    <span className="text-xs text-slate-400 ml-auto">
                      {new Date(entry.created_at).toLocaleDateString()}
                    </span>
                  </li>
                ))}
              </ul>

              <h3 className="font-medium text-slate-800 mb-3 text-sm">Internal notes</h3>
              <form onSubmit={handleAddNote} className="flex gap-2 mb-6">
                <input
                  value={noteBody}
                  onChange={(e) => setNoteBody(e.target.value)}
                  placeholder="Add a case note…"
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
                {(caseFile.notes ?? []).map((note) => (
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
                {(caseFile.notes ?? []).length === 0 && (
                  <p className="text-sm text-slate-400">No notes recorded yet.</p>
                )}
              </ul>
            </section>
          </>
        )}
      </main>
    </div>
  )
}
