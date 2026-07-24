export default function AuthLayout({ eyebrow, title, subtitle, children }) {
  return (
    <div className="min-h-screen grid lg:grid-cols-5 bg-slate-50">
      {/* Left: form column */}
      <div className="lg:col-span-2 flex flex-col justify-center px-6 sm:px-12 py-16">
        <div className="w-full max-w-sm mx-auto">
          <div className="flex items-center gap-2 mb-10">
            <div className="h-8 w-8 rounded-sm bg-brand-500 flex items-center justify-center">
              <span className="text-gold-500 font-serif text-lg leading-none">§</span>
            </div>
            <span className="font-serif text-lg tracking-tight text-brand-900">LegalCaseFlow</span>
          </div>

          {eyebrow && (
            <p className="text-xs uppercase tracking-[0.2em] text-brand-400 font-medium mb-3">
              {eyebrow}
            </p>
          )}
          <h1 className="font-serif text-3xl text-slate-900 mb-2">{title}</h1>
          {subtitle && <p className="text-slate-500 text-sm mb-8">{subtitle}</p>}

          {children}
        </div>
      </div>

      {/* Right: docket-ledger signature panel */}
      <div className="hidden lg:flex lg:col-span-3 relative bg-brand-900 overflow-hidden">
        <div
          className="absolute inset-0 opacity-[0.07]"
          style={{
            backgroundImage:
              'repeating-linear-gradient(0deg, transparent, transparent 47px, #b8860b 47px, #b8860b 48px)',
          }}
        />
        <div className="relative z-10 flex flex-col justify-center px-16 py-16 w-full">
          <p className="text-gold-500 text-xs uppercase tracking-[0.25em] mb-6">Matter Docket — Preview</p>
          <div className="space-y-4 max-w-lg">
            {[
              { no: '2026-0143', title: 'Whitmore v. Alden Constructions', status: 'Active', due: 'Discovery due Aug 3' },
              { no: '2026-0158', title: 'Estate of R. Calloway', status: 'Waiting', due: 'Hearing set Sep 12' },
              { no: '2026-0161', title: 'Reyes Family Trust — Amendment', status: 'New', due: 'Intake review' },
            ].map((row) => (
              <div
                key={row.no}
                className="flex items-center justify-between border-b border-white/10 pb-4 text-white/90"
              >
                <div>
                  <p className="font-mono text-[11px] text-gold-500/80 mb-1">{row.no}</p>
                  <p className="font-serif text-lg">{row.title}</p>
                </div>
                <div className="text-right">
                  <span className="text-[11px] uppercase tracking-wide px-2 py-0.5 rounded-sm border border-white/20 text-white/70">
                    {row.status}
                  </span>
                  <p className="text-xs text-white/50 mt-1">{row.due}</p>
                </div>
              </div>
            ))}
          </div>
          <p className="text-white/40 text-sm mt-10 max-w-md">
            Every case, deadline, and document in one secure workspace — built for firms who
            replace spreadsheets with certainty.
          </p>
        </div>
      </div>
    </div>
  )
}
