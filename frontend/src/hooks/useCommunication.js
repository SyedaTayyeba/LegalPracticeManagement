import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { communicationApi } from '../api/communication'

export function useConversations() {
  return useQuery({ queryKey: ['conversations'], queryFn: communicationApi.list })
}

export function useConversation(conversationId) {
  return useQuery({
    queryKey: ['conversations', conversationId],
    queryFn: () => communicationApi.show(conversationId).then((d) => d.data ?? d),
    enabled: !!conversationId,
    refetchInterval: 10000, // light polling so new replies show up without a manual refresh
  })
}

export function useStartConversation() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: communicationApi.start,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['conversations'] }),
  })
}

export function useSendMessage(conversationId) {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: (payload) => communicationApi.sendMessage(conversationId, payload),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['conversations', conversationId] })
      queryClient.invalidateQueries({ queryKey: ['conversations'] })
    },
  })
}
