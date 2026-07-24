import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { billingApi } from '../api/billing'

export function useTimeEntries(filters = {}) {
  return useQuery({ queryKey: ['time-entries', filters], queryFn: () => billingApi.listTimeEntries(filters) })
}

export function useLogTime() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: billingApi.logTime,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['time-entries'] }),
  })
}

export function useLogExpense() {
  return useMutation({ mutationFn: billingApi.logExpense })
}

export function useInvoices(filters = {}) {
  return useQuery({ queryKey: ['invoices', filters], queryFn: () => billingApi.listInvoices(filters) })
}

export function useGenerateInvoice() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: billingApi.generateInvoice,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['invoices'] })
      queryClient.invalidateQueries({ queryKey: ['time-entries'] })
    },
  })
}

export function useUpdateInvoiceStatus() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: ({ id, status }) => billingApi.updateInvoiceStatus(id, status),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['invoices'] }),
  })
}
