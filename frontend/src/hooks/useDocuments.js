import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { documentApi } from '../api/documents'

export function useDocuments(filters = {}) {
  return useQuery({
    queryKey: ['documents', filters],
    queryFn: () => documentApi.list(filters),
    placeholderData: (prev) => prev,
  })
}

export function useUploadDocument() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: documentApi.upload,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['documents'] }),
  })
}

export function useDeleteDocument() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: documentApi.destroy,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['documents'] }),
  })
}

export function useDownloadDocument() {
  return useMutation({
    mutationFn: async ({ id, filename }) => {
      const blob = await documentApi.download(id)
      const url = window.URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = filename
      a.click()
      window.URL.revokeObjectURL(url)
    },
  })
}
