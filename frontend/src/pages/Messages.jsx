import { useState } from 'react'
import { Send, Loader2, MessageSquare } from 'lucide-react'
import WorkspaceHeader from '../components/WorkspaceHeader'
import { useConversations, useConversation, useSendMessage } from '../hooks/useCommunication'
import { useAuthStore } from '../store/authStore'

export default function Messages() {
  const { data, isLoading } = useConversations()
  const [activeId, setActiveId] = useState(null)
  const conversations = data?.data ?? []
  const currentUser = useAuthStore((s) => s.user)

  const { data: activeConversation } = useConversation(activeId)
  const sendMutation = useSendMessage(activeId)
  const [body, setBody] = useState('')

  const handleSend = (e) => {
    e.preventDefault()
    if (!body.trim()) return
    sendMutation.mutate({ body }, { onSuccess: () => setBody('') })
  }

  return (
    <div className="min-h-screen bg-slate-50">
      <WorkspaceHeader />

      <main className="max-w-5xl mx-auto px-6 py-10">
        <p className="text-xs uppercase tracking-[0.2em] text-brand-400 font-medium mb-1">
          Communication
        </p>
        <h1 className="font-serif text-3xl text-slate-900 mb-6">Messages</h1>

        <div className="grid sm:grid-cols-3 gap-0 bg-white rounded-lg border border-slate-200 overflow-hidden" style={{ minHeight: 480 }}>
          <div className="border-r border-slate-100 divide-y divide-slate-100 overflow-y-auto">
            {isLoading ? (
              <p className="p-4 text-sm text-slate-400">Loading…</p>
            ) : conversations.length === 0 ? (
              <p className="p-4 text-sm text-slate-400">No conversations yet.</p>
            ) : (
              conversations.map((c) => (
                <button
                  key={c.id}
                  onClick={() => setActiveId(c.id)}
                  className={`w-full text-left px-4 py-3 hover:bg-slate-50 ${activeId === c.id ? 'bg-brand-50' : ''}`}
                >
                  <p className="text-sm font-medium text-slate-800 truncate">{c.subject || c.client?.display_name || 'Conversation'}</p>
                  <p className="text-xs text-slate-400 truncate">{c.case?.title || c.client?.display_name || '—'}</p>
                </button>
              ))
            )}
          </div>

          <div className="sm:col-span-2 flex flex-col">
            {!activeId ? (
              <div className="flex-1 flex items-center justify-center text-sm text-slate-400 gap-2">
                <MessageSquare className="h-4 w-4" /> Select a conversation
              </div>
            ) : (
              <>
                <div className="flex-1 overflow-y-auto p-4 space-y-3">
                  {(activeConversation?.messages ?? []).map((m) => {
                    const isMine = m.sender?.id === currentUser?.id
                    return (
                      <div key={m.id} className={`flex ${isMine ? 'justify-end' : 'justify-start'}`}>
                        <div className={`max-w-[75%] rounded-lg px-3 py-2 text-sm ${isMine ? 'bg-brand-500 text-white' : 'bg-slate-100 text-slate-700'}`}>
                          {!isMine && <p className="text-xs opacity-70 mb-0.5">{m.sender?.name}</p>}
                          <p>{m.body}</p>
                        </div>
                      </div>
                    )
                  })}
                </div>
                <form onSubmit={handleSend} className="border-t border-slate-100 p-3 flex gap-2">
                  <input
                    value={body}
                    onChange={(e) => setBody(e.target.value)}
                    placeholder="Type a message…"
                    className="flex-1 rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                  />
                  <button type="submit" disabled={sendMutation.isPending} className="rounded-md bg-brand-500 hover:bg-brand-600 text-white px-3 py-2 disabled:opacity-60">
                    {sendMutation.isPending ? <Loader2 className="h-4 w-4 animate-spin" /> : <Send className="h-4 w-4" />}
                  </button>
                </form>
              </>
            )}
          </div>
        </div>
      </main>
    </div>
  )
}
