import { Users, Building2, Loader2, UserPlus } from 'lucide-react'
import { useState } from 'react'
import WorkspaceHeader from '../components/WorkspaceHeader'
import { useAuthStore } from '../store/authStore'
import { useCurrentUser, useFirm, useTeam, useInviteUser } from '../hooks/useAuth'

const ROLE_LABELS = {
  platform_admin: 'Platform Admin',
  firm_owner: 'Firm Owner / Partner',
  lawyer: 'Lawyer',
  paralegal: 'Paralegal / Assistant',
  client: 'Client',
}

export default function Dashboard() {
  const user = useAuthStore((s) => s.user)
  const { isLoading: meLoading } = useCurrentUser()
  const { data: firm, isLoading: firmLoading } = useFirm()
  const { data: team, isLoading: teamLoading } = useTeam()
  const inviteMutation = useInviteUser()

  const [inviteEmail, setInviteEmail] = useState('')
  const [inviteRole, setInviteRole] = useState('lawyer')

  const isOwner = user?.role === 'firm_owner'

  const handleInvite = (e) => {
    e.preventDefault()
    inviteMutation.mutate(
      { email: inviteEmail, role: inviteRole },
      { onSuccess: () => setInviteEmail('') }
    )
  }

  return (
    <div className="min-h-screen bg-slate-50">
      <WorkspaceHeader />

      <main className="max-w-5xl mx-auto px-6 py-10 space-y-8">
        <div>
          <p className="text-xs uppercase tracking-[0.2em] text-brand-400 font-medium mb-1">
            {meLoading ? 'Loading…' : ROLE_LABELS[user?.role] ?? 'Team Member'}
          </p>
          <h1 className="font-serif text-3xl text-slate-900">
            Welcome back{user?.name ? `, ${user.name.split(' ')[0]}` : ''}
          </h1>
        </div>

        <div className="grid md:grid-cols-2 gap-6">
          <section className="bg-white rounded-lg border border-slate-200 p-6">
            <div className="flex items-center gap-2 mb-4">
              <Building2 className="h-4 w-4 text-brand-500" />
              <h2 className="font-medium text-slate-800">Firm Workspace</h2>
            </div>
            {firmLoading ? (
              <p className="text-sm text-slate-400">Loading firm details…</p>
            ) : firm ? (
              <dl className="text-sm space-y-2">
                <div className="flex justify-between">
                  <dt className="text-slate-500">Name</dt>
                  <dd className="text-slate-800 font-medium">{firm.name}</dd>
                </div>
                <div className="flex justify-between">
                  <dt className="text-slate-500">Plan</dt>
                  <dd className="text-slate-800 font-medium capitalize">{firm.plan}</dd>
                </div>
                <div className="flex justify-between">
                  <dt className="text-slate-500">Seats</dt>
                  <dd className="text-slate-800 font-medium">
                    {firm.staff_count ?? '—'} / {firm.seat_limit}
                  </dd>
                </div>
              </dl>
            ) : (
              <p className="text-sm text-slate-400">No firm data available.</p>
            )}
          </section>

          <section className="bg-white rounded-lg border border-slate-200 p-6">
            <div className="flex items-center gap-2 mb-4">
              <Users className="h-4 w-4 text-brand-500" />
              <h2 className="font-medium text-slate-800">Team</h2>
            </div>
            {teamLoading ? (
              <p className="text-sm text-slate-400">Loading team…</p>
            ) : (
              <ul className="text-sm divide-y divide-slate-100">
                {(team?.data ?? []).slice(0, 5).map((member) => (
                  <li key={member.id} className="flex items-center justify-between py-2">
                    <span className="text-slate-700">{member.name}</span>
                    <span className="text-xs text-slate-400">{ROLE_LABELS[member.role] ?? member.role}</span>
                  </li>
                ))}
                {(team?.data ?? []).length === 0 && (
                  <li className="text-slate-400 py-2">No team members yet.</li>
                )}
              </ul>
            )}
          </section>
        </div>

        {isOwner && (
          <section className="bg-white rounded-lg border border-slate-200 p-6">
            <div className="flex items-center gap-2 mb-4">
              <UserPlus className="h-4 w-4 text-brand-500" />
              <h2 className="font-medium text-slate-800">Invite a team member</h2>
            </div>
            <form onSubmit={handleInvite} className="flex flex-wrap items-end gap-3">
              <div className="flex-1 min-w-[220px]">
                <label className="block text-xs text-slate-500 mb-1">Email</label>
                <input
                  type="email"
                  required
                  value={inviteEmail}
                  onChange={(e) => setInviteEmail(e.target.value)}
                  className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                  placeholder="colleague@yourfirm.com"
                />
              </div>
              <div>
                <label className="block text-xs text-slate-500 mb-1">Role</label>
                <select
                  value={inviteRole}
                  onChange={(e) => setInviteRole(e.target.value)}
                  className="rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                >
                  <option value="lawyer">Lawyer</option>
                  <option value="paralegal">Paralegal / Assistant</option>
                  <option value="client">Client</option>
                  <option value="firm_owner">Firm Owner / Partner</option>
                </select>
              </div>
              <button
                type="submit"
                disabled={inviteMutation.isPending}
                className="rounded-md bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium px-4 py-2 disabled:opacity-60"
              >
                {inviteMutation.isPending ? 'Sending…' : 'Send invite'}
              </button>
            </form>
            {inviteMutation.isError && (
              <p className="text-xs text-red-600 mt-2">
                {inviteMutation.error?.response?.data?.message ?? 'Could not send invitation.'}
              </p>
            )}
            {inviteMutation.isSuccess && (
              <p className="text-xs text-emerald-600 mt-2">Invitation sent.</p>
            )}
          </section>
        )}
      </main>
    </div>
  )
}
