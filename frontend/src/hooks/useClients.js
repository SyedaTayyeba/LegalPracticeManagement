import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { clientApi } from '../api/auth'

export function useClients(filters = {}) {
  return useQuery({
    queryKey: ['clients', filters],
    queryFn: () => clientApi.list(filters),
    placeholderData: (prev) => prev, // keep old page while refetching (smooth search-as-you-type)
  })
}

export function useClient(clientId) {
  return useQuery({
    queryKey: ['clients', clientId],
    queryFn: () => clientApi.show(clientId).then((d) => d.data ?? d),
    enabled: !!clientId,
  })
}

export function useCreateClient() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: clientApi.create,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['clients'] }),
  })
}

export function useUpdateClient(clientId) {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: (payload) => clientApi.update(clientId, payload),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['clients'] })
      queryClient.invalidateQueries({ queryKey: ['clients', clientId] })
    },
  })
}

export function useArchiveClient() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: clientApi.archive,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['clients'] }),
  })
}

export function useAddClientNote(clientId) {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: (payload) => clientApi.addNote(clientId, payload),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['clients', clientId] }),
  })
}
