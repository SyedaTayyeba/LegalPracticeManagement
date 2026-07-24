import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { calendarApi } from '../api/calendar'

export function useCalendarEvents(filters = {}) {
  return useQuery({
    queryKey: ['calendar', filters],
    queryFn: () => calendarApi.list(filters),
    placeholderData: (prev) => prev,
  })
}

export function useCreateEvent() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: calendarApi.create,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['calendar'] }),
  })
}

export function useDeleteEvent() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: calendarApi.destroy,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['calendar'] }),
  })
}
