import { LogOut, Loader2 } from 'lucide-react'
import { NavLink } from 'react-router-dom'
import { useLogout } from '../hooks/useAuth'
import { useAuthStore } from '../store/authStore'

const NAV_ITEMS = [
  { to: '/dashboard', label: 'Overview' },
  { to: '/clients', label: 'Clients' },
  { to: '/cases', label: 'Cases' },
  { to: '/documents', label: 'Documents' },
  { to: '/tasks', label: 'Tasks' },
  { to: '/calendar', label: 'Calendar' },
  { to: '/messages', label: 'Messages' },
  { to: '/billing', label: 'Billing' },
]

const OWNER_ONLY_ITEMS = [
  { to: '/reports', label: 'Reports' },
  { to: '/subscription', label: 'Plan' },
]

export default function WorkspaceHeader() {
  const logoutMutation = useLogout()
  const user = useAuthStore((s) => s.user)
  const isOwner = user?.role === 'firm_owner'

  const items = isOwner ? [...NAV_ITEMS, ...OWNER_ONLY_ITEMS] : NAV_ITEMS

  return (
    <header className="bg-white border-b border-slate-200">
      <div className="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
        <div className="flex items-center gap-8 min-w-0">
          <div className="flex items-center gap-2 shrink-0">
            <div className="h-8 w-8 rounded-sm bg-brand-500 flex items-center justify-center">
              <span className="text-gold-500 font-serif text-lg leading-none">§</span>
            </div>
            <span className="font-serif text-lg text-brand-900 hidden sm:inline">LegalCaseFlow</span>
          </div>
          <nav className="hidden lg:flex items-center gap-5 overflow-x-auto">
            {items.map((item) => (
              <NavLink
                key={item.to}
                to={item.to}
                className={({ isActive }) =>
                  `text-sm font-medium whitespace-nowrap ${isActive ? 'text-brand-500' : 'text-slate-500 hover:text-slate-800'}`
                }
              >
                {item.label}
              </NavLink>
            ))}
          </nav>
        </div>
        <button
          onClick={() => logoutMutation.mutate()}
          disabled={logoutMutation.isPending}
          className="flex items-center gap-2 text-sm text-slate-500 hover:text-slate-800 shrink-0"
        >
          {logoutMutation.isPending ? <Loader2 className="h-4 w-4 animate-spin" /> : <LogOut className="h-4 w-4" />}
          <span className="hidden sm:inline">Sign out</span>
        </button>
      </div>
    </header>
  )
}
