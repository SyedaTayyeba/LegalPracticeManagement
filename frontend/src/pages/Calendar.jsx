import { useState } from 'react'
import { CalendarDays, Loader2, MapPin, Plus } from 'lucide-react'
import WorkspaceHeader from '../components/WorkspaceHeader'
import { useCalendarEvents, useCreateEvent } from '../hooks/useCalendar'

const EVENT_TYPE_STYLES = {
  hearing: 'bg-red-50 text-red-700',
  deadline: 'bg-amber-50 text-amber-700',
  meeting: 'bg-blue-50 text-blue-700',
  other: 'bg-slate-100 text-slate-600',
}

export default function Calendar() {
  const { data, isLoading } = useCalendarEvents({ from: new Date().toISOString() })
  const createMutation = useCreateEvent()
  const [showForm, setShowForm] = useState(false)
  const [conflict, setConflict] = useState(null)
  const [pendingPayload, setPendingPayload] = useState(null)

  const events = data?.data ?? []

  const submitEvent = (payload) => {
    createMutation.mutate(payload, {
      onSuccess: () => {
        setShowForm(false)
        setConflict(null)
        setPendingPayload(null)
      },
      onError: (error) => {
        if (error?.response?.status === 409) {
          setConflict(error.response.data.conflicting_events ?? [])
          setPendingPayload(payload)
        }
      },
    })
  }

  const handleCreate = (e) => {
    e.preventDefault()
    const form = new FormData(e.target)
    submitEvent({
      title: form.get('title'),
      event_type: form.get('event_type'),
      starts_at: new Date(form.get('starts_at')).toISOString(),
      location: form.get('location') || undefined,
    })
  }

  const confirmDespiteConflict = () => submitEvent({ ...pendingPayload, force: true })

  return (
    <div className="min-h-screen bg-slate-50">
      <WorkspaceHeader />

      <main className="max-w-4xl mx-auto px-6 py-10 space-y-6">
        <div className="flex items-center justify-between">
          <div>
            <p className="text-xs uppercase tracking-[0.2em] text-brand-400 font-medium mb-1">
              Court Calendar
            </p>
            <h1 className="font-serif text-3xl text-slate-900">Calendar</h1>
          </div>
          <button
            onClick={() => setShowForm((v) => !v)}
            className="flex items-center gap-2 rounded-md bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium px-4 py-2"
          >
            <Plus className="h-4 w-4" />
            Schedule event
          </button>
        </div>

        {showForm && (
          <form onSubmit={handleCreate} className="bg-white rounded-lg border border-slate-200 p-6 grid sm:grid-cols-2 gap-4">
            <input name="title" required placeholder="Event title" className="rounded-md border border-slate-300 px-3 py-2 text-sm sm:col-span-2" />
            <select name="event_type" defaultValue="hearing" className="rounded-md border border-slate-300 px-3 py-2 text-sm">
              <option value="hearing">Hearing</option>
              <option value="deadline">Deadline</option>
              <option value="meeting">Meeting</option>
              <option value="other">Other</option>
            </select>
            <input name="starts_at" type="datetime-local" required className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
            <input name="location" placeholder="Location (optional)" className="rounded-md border border-slate-300 px-3 py-2 text-sm sm:col-span-2" />
            <div className="sm:col-span-2 flex justify-end gap-2">
              <button type="button" onClick={() => setShowForm(false)} className="text-sm text-slate-500 px-4 py-2">Cancel</button>
              <button type="submit" disabled={createMutation.isPending} className="rounded-md bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium px-4 py-2 disabled:opacity-60">
                {createMutation.isPending ? 'Scheduling…' : 'Schedule'}
              </button>
            </div>
          </form>
        )}

        {conflict && (
          <div className="rounded-md bg-amber-50 border border-amber-200 px-4 py-3 space-y-2">
            <p className="text-sm text-amber-800 font-medium">This time conflicts with:</p>
            <ul className="text-sm text-amber-700 list-disc list-inside">
              {conflict.map((title, i) => <li key={i}>{title}</li>)}
            </ul>
            <div className="flex gap-2 pt-1">
              <button onClick={() => { setConflict(null); setPendingPayload(null) }} className="text-sm text-slate-500 px-3 py-1.5">
                Cancel
              </button>
              <button onClick={confirmDespiteConflict} className="rounded-md bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-3 py-1.5">
                Schedule anyway
              </button>
            </div>
          </div>
        )}

        <div className="bg-white rounded-lg border border-slate-200 divide-y divide-slate-100">
          {isLoading ? (
            <p className="p-6 text-sm text-slate-400">Loading calendar…</p>
          ) : events.length === 0 ? (
            <p className="p-6 text-sm text-slate-400">Nothing scheduled yet.</p>
          ) : (
            events.map((event) => (
              <div key={event.id} className="flex items-center justify-between px-6 py-4">
                <div className="flex items-center gap-3">
                  <div className="h-9 w-9 rounded-full bg-brand-50 flex items-center justify-center text-brand-500">
                    <CalendarDays className="h-4 w-4" />
                  </div>
                  <div>
                    <p className="text-sm font-medium text-slate-800">{event.title}</p>
                    <p className="text-xs text-slate-400 flex items-center gap-1">
                      {new Date(event.starts_at).toLocaleString()}
                      {event.location && <><MapPin className="h-3 w-3 ml-1" /> {event.location}</>}
                    </p>
                  </div>
                </div>
                <span className={`text-xs uppercase tracking-wide px-2 py-1 rounded-sm ${EVENT_TYPE_STYLES[event.event_type]}`}>
                  {event.event_type}
                </span>
              </div>
            ))
          )}
        </div>
      </main>
    </div>
  )
}
