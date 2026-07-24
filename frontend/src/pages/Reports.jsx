import { BarChart3, DollarSign, Scale, Users } from 'lucide-react'
import WorkspaceHeader from '../components/WorkspaceHeader'
import { useCaseReport, useWorkloadReport, useRevenueReport, useBillingStatusReport } from '../hooks/useReports'

export default function Reports() {
  const { data: caseReport, isLoading: caseLoading } = useCaseReport()
  const { data: workload, isLoading: workloadLoading } = useWorkloadReport()
  const { data: revenue, isLoading: revenueLoading } = useRevenueReport()
  const { data: billingStatus, isLoading: billingLoading } = useBillingStatusReport()

  return (
    <div className="min-h-screen bg-slate-50">
      <WorkspaceHeader />

      <main className="max-w-5xl mx-auto px-6 py-10 space-y-6">
        <div>
          <p className="text-xs uppercase tracking-[0.2em] text-brand-400 font-medium mb-1">
            Reporting
          </p>
          <h1 className="font-serif text-3xl text-slate-900">Firm Reports</h1>
        </div>

        <div className="grid sm:grid-cols-2 gap-6">
          <section className="bg-white rounded-lg border border-slate-200 p-6">
            <div className="flex items-center gap-2 mb-4">
              <Scale className="h-4 w-4 text-brand-500" />
              <h2 className="font-medium text-slate-800">Case pipeline</h2>
            </div>
            {caseLoading ? (
              <p className="text-sm text-slate-400">Loading…</p>
            ) : (
              <dl className="text-sm space-y-2">
                <div className="flex justify-between"><dt className="text-slate-500">Active</dt><dd className="font-medium">{caseReport?.active_total ?? 0}</dd></div>
                <div className="flex justify-between"><dt className="text-slate-500">Completed/Closed</dt><dd className="font-medium">{caseReport?.completed_total ?? 0}</dd></div>
                {Object.entries(caseReport?.by_status ?? {}).map(([status, count]) => (
                  <div key={status} className="flex justify-between text-xs text-slate-400">
                    <dt className="capitalize">{status}</dt><dd>{count}</dd>
                  </div>
                ))}
              </dl>
            )}
          </section>

          <section className="bg-white rounded-lg border border-slate-200 p-6">
            <div className="flex items-center gap-2 mb-4">
              <Users className="h-4 w-4 text-brand-500" />
              <h2 className="font-medium text-slate-800">Lawyer workload</h2>
            </div>
            {workloadLoading ? (
              <p className="text-sm text-slate-400">Loading…</p>
            ) : (
              <ul className="text-sm space-y-2">
                {(workload ?? []).map((w) => (
                  <li key={w.id} className="flex justify-between">
                    <span>{w.name}</span>
                    <span className="text-slate-500">{w.open_case_count} open</span>
                  </li>
                ))}
                {(workload ?? []).length === 0 && <p className="text-slate-400">No staff data yet.</p>}
              </ul>
            )}
          </section>

          <section className="bg-white rounded-lg border border-slate-200 p-6">
            <div className="flex items-center gap-2 mb-4">
              <DollarSign className="h-4 w-4 text-brand-500" />
              <h2 className="font-medium text-slate-800">Revenue</h2>
            </div>
            {revenueLoading ? (
              <p className="text-sm text-slate-400">Loading…</p>
            ) : (
              <dl className="text-sm space-y-2">
                <div className="flex justify-between"><dt className="text-slate-500">Total collected</dt><dd className="font-medium">${(revenue?.total_revenue ?? 0).toFixed(2)}</dd></div>
                <div className="flex justify-between"><dt className="text-slate-500">Unbilled hours value</dt><dd className="font-medium">${(revenue?.unbilled_hours_value ?? 0).toFixed(2)}</dd></div>
              </dl>
            )}
          </section>

          <section className="bg-white rounded-lg border border-slate-200 p-6">
            <div className="flex items-center gap-2 mb-4">
              <BarChart3 className="h-4 w-4 text-brand-500" />
              <h2 className="font-medium text-slate-800">Billing status</h2>
            </div>
            {billingLoading ? (
              <p className="text-sm text-slate-400">Loading…</p>
            ) : (
              <dl className="text-sm space-y-2">
                {Object.entries(billingStatus ?? {}).map(([status, info]) => (
                  <div key={status} className="flex justify-between">
                    <dt className="capitalize text-slate-500">{status}</dt>
                    <dd className="font-medium">{info.count} · ${info.amount.toFixed(2)}</dd>
                  </div>
                ))}
              </dl>
            )}
          </section>
        </div>
      </main>
    </div>
  )
}
