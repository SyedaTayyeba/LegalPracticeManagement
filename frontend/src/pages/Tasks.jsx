import { useState } from 'react'
import { CheckCircle2, Circle, Loader2, Plus } from 'lucide-react'
import WorkspaceHeader from '../components/WorkspaceHeader'
import { useTasks, useCreateTask, useUpdateTask } from '../hooks/useTasks'

const PRIORITY_STYLES = {
  low: 'bg-slate-100 text-slate-600',
  medium: 'bg-blue-50 text-blue-700',
  high: 'bg-amber-50 text-amber-700',
  urgent: 'bg-red-50 text-red-700',
}

export default function Tasks() {
  const [status, setStatus] = useState('')
  const [showOverdue, setShowOverdue] = useState(false)
  const [showForm, setShowForm] = useState(false)

  const { data, isLoading, isFetching } = useTasks({ status, overdue: showOverdue ? 1 : undefined })
  const createMutation = useCreateTask()
  const updateMutation = useUpdateTask()

  const tasks = data?.data ?? []

  const handleCreate = (e) => {
    e.preventDefault()
    const form = new FormData(e.target)
    createMutation.mutate(
      {
        title: form.get('title'),
        priority: form.get('priority'),
        due_date: form.get('due_date') || undefined,
      },
      { onSuccess: () => setShowForm(false) }
    )
  }

  const toggleComplete = (task) => {
    updateMutation.mutate({
      id: task.id,
      payload: { status: task.status === 'completed' ? 'pending' : 'completed' },
    })
  }

  return (
    <div className="min-h-screen bg-slate-50">
      <WorkspaceHeader />

      <main className="max-w-4xl mx-auto px-6 py-10 space-y-6">
        <div className="flex items-center justify-between">
          <div>
            <p className="text-xs uppercase tracking-[0.2em] text-brand-400 font-medium mb-1">
              Task & Deadline Management
            </p>
            <h1 className="font-serif text-3xl text-slate-900">Tasks</h1>
          </div>
          <button
            onClick={() => setShowForm((v) => !v)}
            className="flex items-center gap-2 rounded-md bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium px-4 py-2"
          >
            <Plus className="h-4 w-4" />
            New task
          </button>
        </div>

        {showForm && (
          <form onSubmit={handleCreate} className="bg-white rounded-lg border border-slate-200 p-6 grid sm:grid-cols-3 gap-4">
            <input name="title" required placeholder="Task title" className="rounded-md border border-slate-300 px-3 py-2 text-sm sm:col-span-3" />
            <select name="priority" defaultValue="medium" className="rounded-md border border-slate-300 px-3 py-2 text-sm">
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
              <option value="urgent">Urgent</option>
            </select>
            <input name="due_date" type="date" className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
            <div className="flex justify-end gap-2">
              <button type="button" onClick={() => setShowForm(false)} className="text-sm text-slate-500 px-4 py-2">Cancel</button>
              <button type="submit" disabled={createMutation.isPending} className="rounded-md bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium px-4 py-2 disabled:opacity-60">
                {createMutation.isPending ? 'Saving…' : 'Save'}
              </button>
            </div>
          </form>
        )}

        <div className="flex items-center gap-3">
          <select value={status} onChange={(e) => setStatus(e.target.value)} className="rounded-md border border-slate-300 px-3 py-2 text-sm">
            <option value="">All statuses</option>
            <option value="pending">Pending</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
          </select>
          <label className="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" checked={showOverdue} onChange={(e) => setShowOverdue(e.target.checked)} />
            Overdue only
          </label>
          {isFetching && <Loader2 className="h-4 w-4 animate-spin text-slate-400" />}
        </div>

        <div className="bg-white rounded-lg border border-slate-200 divide-y divide-slate-100">
          {isLoading ? (
            <p className="p-6 text-sm text-slate-400">Loading tasks…</p>
          ) : tasks.length === 0 ? (
            <p className="p-6 text-sm text-slate-400">No tasks match yet.</p>
          ) : (
            tasks.map((task) => (
              <div key={task.id} className="flex items-center justify-between px-6 py-4">
                <div className="flex items-center gap-3">
                  <button onClick={() => toggleComplete(task)} className="text-brand-500">
                    {task.status === 'completed' ? <CheckCircle2 className="h-5 w-5" /> : <Circle className="h-5 w-5 text-slate-300" />}
                  </button>
                  <div>
                    <p className={`text-sm font-medium ${task.status === 'completed' ? 'text-slate-400 line-through' : 'text-slate-800'}`}>
                      {task.title}
                    </p>
                    <p className="text-xs text-slate-400">
                      {task.due_date ? `Due ${task.due_date}` : 'No due date'}
                      {task.is_overdue && <span className="text-red-500 ml-1">· overdue</span>}
                      {task.assignee && ` · ${task.assignee.name}`}
                    </p>
                  </div>
                </div>
                <span className={`text-xs uppercase tracking-wide px-2 py-1 rounded-sm ${PRIORITY_STYLES[task.priority]}`}>
                  {task.priority}
                </span>
              </div>
            ))
          )}
        </div>
      </main>
    </div>
  )
}
