import { zodResolver } from '@hookform/resolvers/zod'
import { useForm } from 'react-hook-form'
import { useSearchParams } from 'react-router-dom'
import { Loader2 } from 'lucide-react'
import AuthLayout from '../components/AuthLayout'
import { acceptInvitationSchema } from '../schemas/authSchemas'
import { useAcceptInvitation } from '../hooks/useAuth'

export default function AcceptInvitation() {
  const [searchParams] = useSearchParams()
  const token = searchParams.get('token') ?? ''

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm({
    resolver: zodResolver(acceptInvitationSchema),
    defaultValues: { token },
  })

  const mutation = useAcceptInvitation()
  const serverError = mutation.error?.response?.data?.message

  if (!token) {
    return (
      <AuthLayout eyebrow="Invitation" title="Invalid invitation link">
        <p className="text-sm text-slate-500">
          This invitation link is missing or malformed. Ask your firm administrator to resend it.
        </p>
      </AuthLayout>
    )
  }

  return (
    <AuthLayout
      eyebrow="Join Your Firm"
      title="Complete your account"
      subtitle="Set a name and password to activate your seat."
    >
      <form onSubmit={handleSubmit((data) => mutation.mutate(data))} className="space-y-5" noValidate>
        <input type="hidden" {...register('token')} />

        {serverError && (
          <div className="rounded-md bg-red-50 border border-red-200 px-3 py-2 text-sm text-red-700">
            {serverError}
          </div>
        )}

        <div>
          <label htmlFor="name" className="block text-sm font-medium text-slate-700 mb-1">
            Full name
          </label>
          <input
            id="name"
            {...register('name')}
            className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
          />
          {errors.name && <p className="mt-1 text-xs text-red-600">{errors.name.message}</p>}
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

        <button
          type="submit"
          disabled={mutation.isPending}
          className="w-full flex items-center justify-center gap-2 rounded-md bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium py-2.5 transition-colors disabled:opacity-60"
        >
          {mutation.isPending && <Loader2 className="h-4 w-4 animate-spin" />}
          Activate account
        </button>
      </form>
    </AuthLayout>
  )
}
