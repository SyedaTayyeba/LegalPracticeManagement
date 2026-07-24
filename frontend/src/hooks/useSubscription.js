import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { subscriptionApi } from '../api/subscription'

export function usePlans() {
  return useQuery({ queryKey: ['plans'], queryFn: () => subscriptionApi.listPlans().then((d) => d.data ?? d) })
}

export function useChangePlan() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: subscriptionApi.changePlan,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['firm'] }),
  })
}
