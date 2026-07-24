import { useRef, useState } from 'react'
import { Upload, Download, Trash2, Loader2, FileText, Search } from 'lucide-react'
import WorkspaceHeader from '../components/WorkspaceHeader'
import { useDocuments, useUploadDocument, useDeleteDocument, useDownloadDocument } from '../hooks/useDocuments'

const CATEGORIES = ['contract', 'agreement', 'evidence', 'court_file', 'client_document', 'other']

export default function Documents() {
  const [category, setCategory] = useState('')
  const [search, setSearch] = useState('')
  const fileInputRef = useRef(null)

  const { data, isLoading, isFetching } = useDocuments({ category, search })
  const uploadMutation = useUploadDocument()
  const deleteMutation = useDeleteDocument()
  const downloadMutation = useDownloadDocument()

  const documents = data?.data ?? []

  const handleFileSelected = (e) => {
    const file = e.target.files?.[0]
    if (!file) return

    const formData = new FormData()
    formData.append('file', file)
    formData.append('category', 'other')
    formData.append('name', file.name)

    uploadMutation.mutate(formData)
    e.target.value = ''
  }

  return (
    <div className="min-h-screen bg-slate-50">
      <WorkspaceHeader />

      <main className="max-w-5xl mx-auto px-6 py-10 space-y-6">
        <div className="flex items-center justify-between">
          <div>
            <p className="text-xs uppercase tracking-[0.2em] text-brand-400 font-medium mb-1">
              Document Management
            </p>
            <h1 className="font-serif text-3xl text-slate-900">Documents</h1>
          </div>
          <div>
            <input ref={fileInputRef} type="file" onChange={handleFileSelected} className="hidden" />
            <button
              onClick={() => fileInputRef.current?.click()}
              disabled={uploadMutation.isPending}
              className="flex items-center gap-2 rounded-md bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium px-4 py-2 disabled:opacity-60"
            >
              {uploadMutation.isPending ? <Loader2 className="h-4 w-4 animate-spin" /> : <Upload className="h-4 w-4" />}
              Upload
            </button>
          </div>
        </div>

        {uploadMutation.isError && (
          <div className="rounded-md bg-red-50 border border-red-200 px-3 py-2 text-sm text-red-700">
            {uploadMutation.error?.response?.data?.message ?? 'Upload failed.'}
          </div>
        )}

        <div className="flex items-center gap-3">
          <div className="relative flex-1">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
            <input
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Search documents…"
              className="w-full rounded-md border border-slate-300 pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
            />
          </div>
          <select value={category} onChange={(e) => setCategory(e.target.value)} className="rounded-md border border-slate-300 px-3 py-2 text-sm">
            <option value="">All categories</option>
            {CATEGORIES.map((c) => (
              <option key={c} value={c}>{c.replace('_', ' ')}</option>
            ))}
          </select>
          {isFetching && <Loader2 className="h-4 w-4 animate-spin text-slate-400" />}
        </div>

        <div className="bg-white rounded-lg border border-slate-200 divide-y divide-slate-100">
          {isLoading ? (
            <p className="p-6 text-sm text-slate-400">Loading documents…</p>
          ) : documents.length === 0 ? (
            <p className="p-6 text-sm text-slate-400">No documents uploaded yet.</p>
          ) : (
            documents.map((doc) => (
              <div key={doc.id} className="flex items-center justify-between px-6 py-4">
                <div className="flex items-center gap-3">
                  <div className="h-9 w-9 rounded-full bg-brand-50 flex items-center justify-center text-brand-500">
                    <FileText className="h-4 w-4" />
                  </div>
                  <div>
                    <p className="text-sm font-medium text-slate-800">{doc.name}</p>
                    <p className="text-xs text-slate-400">
                      {doc.category.replace('_', ' ')} · v{doc.version} · {(doc.size_bytes / 1024).toFixed(0)} KB
                      {doc.case && ` · ${doc.case.title}`}
                    </p>
                  </div>
                </div>
                <div className="flex items-center gap-3">
                  <button
                    onClick={() => downloadMutation.mutate({ id: doc.id, filename: doc.original_filename })}
                    className="text-slate-400 hover:text-brand-500"
                    title="Download"
                  >
                    <Download className="h-4 w-4" />
                  </button>
                  <button
                    onClick={() => deleteMutation.mutate(doc.id)}
                    className="text-slate-400 hover:text-red-500"
                    title="Delete"
                  >
                    <Trash2 className="h-4 w-4" />
                  </button>
                </div>
              </div>
            ))
          )}
        </div>
      </main>
    </div>
  )
}
