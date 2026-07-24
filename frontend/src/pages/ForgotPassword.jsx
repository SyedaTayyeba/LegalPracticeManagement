import { zodResolver } from '@hookform/resolvers/zod'
import { useForm } from 'react-hook-form'
import { Link } from 'react-router-dom'
import { Loader2, CheckCircle2 } from 'lucide-react'
import AuthLayout from '../components/AuthLayout'
import { forgotPasswordSchema } from '../schemas/authSchemas'
import { useForgotPassword } from '../hooks/useAuth'

export default function ForgotPassword() {
  const { register, handleSubmit, formState: { errors } } = useForm({
    resolver: zodResolver(forgotPasswordSchema),
  })
  const mutation = useForgotPassword()

  if (mutation.isSuccess) {
    return (
      <AuthLayout eyebrow="Password Reset" title="Check your inbox">
        <div className="flex items-start gap-3 rounded-md bg-emerald-50 border border-emerald-200 px-4 py-3">
          <CheckCircle2 className="h-5 w-5 text-emerald-600 shrink-0 mt-0.5" />
          <p className="text-sm text-emerald-800">
            If that email exists in our system, a reset link is on its way.
          </p>
        </div>
        <Link to="/login" className="block text-sm text-brand-500 font-medium mt-6 hover:underline">
          Back to sign in
        </Link>
      </AuthLayout>
    )
  }

  return (
    <AuthLayout
      eyebrow="Password Reset"
      title="Forgot your password?"
      subtitle="We'll email you a link to reset it."
    >
      <form onSubmit={handleSubmit((data) => mutation.mutate(data))} className="space-y-5" noValidate>
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
        </div>

        <button
          type="submit"
          disabled={mutation.isPending}
          className="w-full flex items-center justify-center gap-2 rounded-md bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium py-2.5 transition-colors disabled:opacity-60"
        >
          {mutation.isPending && <Loader2 className="h-4 w-4 animate-spin" />}
          Send reset link
        </button>
      </form>

      <Link to="/login" className="block text-sm text-slate-500 mt-8 text-center hover:underline">
        Back to sign in
      </Link>
    </AuthLayout>
  )
}
