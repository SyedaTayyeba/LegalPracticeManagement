import { z } from 'zod'

// Mirrors the backend's Password::min(10)->mixedCase()->numbers()->symbols()
const passwordSchema = z
  .string()
  .min(10, 'Password must be at least 10 characters')
  .regex(/[a-z]/, 'Password must include a lowercase letter')
  .regex(/[A-Z]/, 'Password must include an uppercase letter')
  .regex(/[0-9]/, 'Password must include a number')
  .regex(/[^a-zA-Z0-9]/, 'Password must include a symbol')

export const registerFirmSchema = z
  .object({
    firm_name: z.string().min(2, 'Firm name is required').max(255),
    plan: z.enum(['solo', 'professional', 'enterprise']),
    name: z.string().min(2, 'Your name is required').max(255),
    email: z.string().email('Enter a valid email address'),
    phone: z.string().optional().or(z.literal('')),
    password: passwordSchema,
    password_confirmation: z.string(),
  })
  .refine((data) => data.password === data.password_confirmation, {
    message: 'Passwords do not match',
    path: ['password_confirmation'],
  })

export const loginSchema = z.object({
  email: z.string().email('Enter a valid email address'),
  password: z.string().min(1, 'Password is required'),
})

export const acceptInvitationSchema = z
  .object({
    token: z.string().min(1),
    name: z.string().min(2, 'Your name is required').max(255),
    password: passwordSchema,
    password_confirmation: z.string(),
  })
  .refine((data) => data.password === data.password_confirmation, {
    message: 'Passwords do not match',
    path: ['password_confirmation'],
  })

export const inviteUserSchema = z.object({
  email: z.string().email('Enter a valid email address'),
  role: z.enum(['firm_owner', 'lawyer', 'paralegal', 'client']),
})

export const forgotPasswordSchema = z.object({
  email: z.string().email('Enter a valid email address'),
})

export const clientSchema = z
  .object({
    type: z.enum(['individual', 'organization']),
    first_name: z.string().max(120).optional().or(z.literal('')),
    last_name: z.string().max(120).optional().or(z.literal('')),
    organization_name: z.string().max(255).optional().or(z.literal('')),
    email: z.string().email('Enter a valid email address').optional().or(z.literal('')),
    phone: z.string().max(30).optional().or(z.literal('')),
  })
  .refine(
    (data) =>
      data.type === 'organization'
        ? !!data.organization_name
        : !!data.first_name && !!data.last_name,
    {
      message: 'Provide a first and last name (or an organization name)',
      path: ['first_name'],
    }
  )
