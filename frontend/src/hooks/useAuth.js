import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useNavigate } from 'react-router-dom'
import { authApi, firmApi } from '../api/auth'
import { useAuthStore } from '../store/authStore'

export function useRegisterFirm() {
  const setSession = useAuthStore((s) => s.setSession)
  const navigate = useNavigate()

  return useMutation({
    mutationFn: authApi.registerFirm,
    onSuccess: (data) => {
      setSession(data.user, data.access_token)
      navigate('/dashboard')
    },
  })
}

export function useLogin() {
  const setSession = useAuthStore((s) => s.setSession)
  const navigate = useNavigate()

  return useMutation({
    mutationFn: authApi.login,
    onSuccess: (data) => {
      setSession(data.user, data.access_token)
      navigate(data.user.role === 'client' ? '/portal' : '/dashboard')
    },
  })
}

export function useLogout() {
  const logout = useAuthStore((s) => s.logout)
  const navigate = useNavigate()
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: authApi.logout,
    onSettled: () => {
      // Clear local session regardless of whether the network call succeeded —
      // the user should always be able to log out client-side.
      logout()
      queryClient.clear()
      navigate('/login')
    },
  })
}

export function useAcceptInvitation() {
  const setSession = useAuthStore((s) => s.setSession)
  const navigate = useNavigate()

  return useMutation({
    mutationFn: authApi.acceptInvitation,
    onSuccess: (data) => {
      setSession(data.user, data.access_token)
      navigate(data.user.role === 'client' ? '/portal' : '/dashboard')
    },
  })
}

export function useForgotPassword() {
  return useMutation({ mutationFn: authApi.forgotPassword })
}

/** Refetches /auth/me whenever we have a token — keeps the profile fresh
 *  (e.g. role changes made by a Firm Owner elsewhere) without a full reload. */
export function useCurrentUser() {
  const accessToken = useAuthStore((s) => s.accessToken)
  const setUser = useAuthStore((s) => s.setUser)

  return useQuery({
    queryKey: ['me'],
    queryFn: () => authApi.me().then((data) => {
      setUser(data.data ?? data)
      return data.data ?? data
    }),
    enabled: !!accessToken,
    staleTime: 60_000,
    retry: false,
  })
}

export function useFirm() {
  const accessToken = useAuthStore((s) => s.accessToken)
  return useQuery({
    queryKey: ['firm'],
    queryFn: () => firmApi.show().then((d) => d.data ?? d),
    enabled: !!accessToken,
  })
}

export function useTeam(params = {}) {
  const accessToken = useAuthStore((s) => s.accessToken)
  return useQuery({
    queryKey: ['team', params],
    queryFn: () => firmApi.team(params),
    enabled: !!accessToken,
  })
}

export function useInviteUser() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: firmApi.invite,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['team'] }),
  })
}
