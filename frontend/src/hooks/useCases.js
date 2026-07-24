import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { caseApi } from '../api/auth'

export function useCases(filters = {}) {
  return useQuery({
    queryKey: ['cases', filters],
    queryFn: () => caseApi.list(filters),
    placeholderData: (prev) => prev,
  })
}

export function useCase(caseId) {
  return useQuery({
    queryKey: ['cases', caseId],
    queryFn: () => caseApi.show(caseId).then((d) => d.data ?? d),
    enabled: !!caseId,
  })
}

export function useCreateCase() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: caseApi.create,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['cases'] }),
  })
}

export function useUpdateCaseStatus(caseId) {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: (payload) => caseApi.updateStatus(caseId, payload),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['cases'] })
      queryClient.invalidateQueries({ queryKey: ['cases', caseId] })
    },
  })
}

export function useAddCaseNote(caseId) {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: (payload) => caseApi.addNote(caseId, payload),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['cases', caseId] }),
  })
}

export function useAssignCaseTeam(caseId) {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: (payload) => caseApi.assignTeam(caseId, payload),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['cases', caseId] }),
  })
}
