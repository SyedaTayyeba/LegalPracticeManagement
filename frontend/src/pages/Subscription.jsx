import { Check, Loader2 } from 'lucide-react'
import WorkspaceHeader from '../components/WorkspaceHeader'
import { usePlans, useChangePlan } from '../hooks/useSubscription'
import { useFirm } from '../hooks/useAuth'

export default function Subscription() {
  const { data: plans, isLoading } = usePlans()
  const { data: firm } = useFirm()
  const changePlanMutation = useChangePlan()

  return (
    <div className="min-h-screen bg-slate-50">
      <WorkspaceHeader />

      <main className="max-w-4xl mx-auto px-6 py-10 space-y-6">
        <div>
          <p className="text-xs uppercase tracking-[0.2em] text-brand-400 font-medium mb-1">
            Subscription
          </p>
          <h1 className="font-serif text-3xl text-slate-900">Plan & Billing</h1>
          {firm && <p className="text-sm text-slate-500 mt-1">Current plan: <span className="font-medium capitalize">{firm.plan}</span></p>}
        </div>

        {changePlanMutation.isError && (
          <div className="rounded-md bg-red-50 border border-red-200 px-3 py-2 text-sm text-red-700">
            {changePlanMutation.error?.response?.data?.message ?? 'Could not change plan.'}
          </div>
        )}

        {isLoading ? (
          <p className="text-sm text-slate-400">Loading plans…</p>
        ) : (
          <div className="grid sm:grid-cols-3 gap-6">
            {(plans ?? []).map((plan) => {
              const isCurrent = firm?.plan === plan.key
              return (
                <div key={plan.key} className={`bg-white rounded-lg border p-6 flex flex-col ${isCurrent ? 'border-brand-500 ring-1 ring-brand-500' : 'border-slate-200'}`}>
                  <h2 className="font-serif text-xl text-slate-900">{plan.name}</h2>
                  <p className="text-2xl font-medium text-slate-800 mt-2">${plan.price_monthly}<span className="text-sm text-slate-400 font-normal">/mo</span></p>
                  <ul className="text-sm text-slate-600 space-y-2 my-4 flex-1">
                    <li>{plan.seat_limit} seat{plan.seat_limit > 1 ? 's' : ''}</li>
                    <li>{(plan.storage_limit_mb / 1024).toFixed(0)} GB storage</li>
                    {(plan.features ?? []).map((f) => (
                      <li key={f} className="flex items-center gap-1.5">
                        <Check className="h-3.5 w-3.5 text-emerald-500" /> {f.replace('_', ' ')}
                      </li>
                    ))}
                  </ul>
                  <button
                    disabled={isCurrent || changePlanMutation.isPending}
                    onClick={() => changePlanMutation.mutate(plan.key)}
                    className={`rounded-md text-sm font-medium px-4 py-2 ${
                      isCurrent ? 'bg-slate-100 text-slate-400' : 'bg-brand-500 hover:bg-brand-600 text-white'
                    } disabled:opacity-60`}
                  >
                    {changePlanMutation.isPending ? <Loader2 className="h-4 w-4 animate-spin mx-auto" /> : isCurrent ? 'Current plan' : 'Switch to this plan'}
                  </button>
                </div>
              )
            })}
          </div>
        )}
      </main>
    </div>
  )
}
