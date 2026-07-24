import { zodResolver } from '@hookform/resolvers/zod'
import { useForm } from 'react-hook-form'
import { Link } from 'react-router-dom'
import { Loader2 } from 'lucide-react'
import AuthLayout from '../components/AuthLayout'
import { registerFirmSchema } from '../schemas/authSchemas'
import { useRegisterFirm } from '../hooks/useAuth'

const PLANS = [
  { value: 'solo', label: 'Solo Lawyer', blurb: '1 seat · 2 GB storage' },
  { value: 'professional', label: 'Professional Firm', blurb: '15 seats · 20 GB storage' },
  { value: 'enterprise', label: 'Enterprise Firm', blurb: '250 seats · 500 GB storage' },
]

export default function RegisterFirm() {
  const {
    register,
    handleSubmit,
    watch,
    setValue,
    formState: { errors },
  } = useForm({
    resolver: zodResolver(registerFirmSchema),
    defaultValues: { plan: 'professional' },
  })

  const registerMutation = useRegisterFirm()
  const selectedPlan = watch('plan')

  const onSubmit = (data) => {
    const { password_confirmation, ...rest } = data
    registerMutation.mutate({ ...rest, password_confirmation })
  }

  const serverError = registerMutation.error?.response?.data?.message
  const fieldErrors = registerMutation.error?.response?.data?.errors

  return (
    <AuthLayout
      eyebrow="New Workspace"
      title="Set up your firm"
      subtitle="Create your firm's secure workspace in under a minute."
    >
      <form onSubmit={handleSubmit(onSubmit)} className="space-y-5" noValidate>
        {serverError && (
          <div className="rounded-md bg-red-50 border border-red-200 px-3 py-2 text-sm text-red-700">
            {serverError}
          </div>
        )}

        <div>
          <label htmlFor="firm_name" className="block text-sm font-medium text-slate-700 mb-1">
            Firm name
          </label>
          <input
            id="firm_name"
            {...register('firm_name')}
            className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
            placeholder="Harlow & Reyes Legal"
          />
          {errors.firm_name && <p className="mt-1 text-xs text-red-600">{errors.firm_name.message}</p>}
        </div>

        <div>
          <label className="block text-sm font-medium text-slate-700 mb-2">Plan</label>
          <div className="grid grid-cols-1 gap-2">
            {PLANS.map((plan) => (
              <button
                type="button"
                key={plan.value}
                onClick={() => setValue('plan', plan.value, { shouldValidate: true })}
                className={`text-left rounded-md border px-3 py-2 transition-colors ${
                  selectedPlan === plan.value
                    ? 'border-brand-500 bg-brand-50'
                    : 'border-slate-200 hover:border-slate-300'
                }`}
              >
                <p className="text-sm font-medium text-slate-800">{plan.label}</p>
                <p className="text-xs text-slate-500">{plan.blurb}</p>
              </button>
            ))}
          </div>
        </div>

        <div>
          <label htmlFor="name" className="block text-sm font-medium text-slate-700 mb-1">
            Your full name
          </label>
          <input
            id="name"
            {...register('name')}
            className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
            placeholder="Amara Harlow"
          />
          {errors.name && <p className="mt-1 text-xs text-red-600">{errors.name.message}</p>}
        </div>

        <div>
          <label htmlFor="email" className="block text-sm font-medium text-slate-700 mb-1">
            Work email
          </label>
          <input
            id="email"
            type="email"
            {...register('email')}
            className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
            placeholder="you@yourfirm.com"
          />
          {errors.email && <p className="mt-1 text-xs text-red-600">{errors.email.message}</p>}
          {fieldErrors?.email && <p className="mt-1 text-xs text-red-600">{fieldErrors.email[0]}</p>}
        </div>

        <div className="grid grid-cols-2 gap-3">
          <div>
            <label htmlFor="password" className="block text-sm font-medium text-slate-700 mb-1">
              Password
            </label>
            <input
              id="password"
              type="password"
              {...register('password')}
              className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
            />
          </div>
          <div>
            <label htmlFor="password_confirmation" className="block text-sm font-medium text-slate-700 mb-1">
              Confirm
            </label>
            <input
              id="password_confirmation"
              type="password"
              {...register('password_confirmation')}
              className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
            />
          </div>
        </div>
        {errors.password && <p className="text-xs text-red-600">{errors.password.message}</p>}
        {errors.password_confirmation && (
          <p className="text-xs text-red-600">{errors.password_confirmation.message}</p>
        )}
        <p className="text-xs text-slate-400">
          At least 10 characters, with uppercase, lowercase, a number, and a symbol.
        </p>

        <button
          type="submit"
          disabled={registerMutation.isPending}
          className="w-full flex items-center justify-center gap-2 rounded-md bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium py-2.5 transition-colors disabled:opacity-60"
        >
          {registerMutation.isPending && <Loader2 className="h-4 w-4 animate-spin" />}
          Create workspace
        </button>
      </form>

      <p className="text-sm text-slate-500 mt-8 text-center">
        Already have a workspace?{' '}
        <Link to="/login" className="text-brand-500 font-medium hover:underline">
          Sign in
        </Link>
      </p>
    </AuthLayout>
  )
}
