import axios from 'axios'
import { useAuthStore } from '../store/authStore'

export const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1',
  headers: { Accept: 'application/json' },
})

// Attach the current JWT access token to every request.
api.interceptors.request.use((config) => {
  const token = useAuthStore.getState().accessToken
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Queue of requests waiting on an in-flight token refresh so we don't fire
// N parallel refresh calls if several requests 401 at once.
let isRefreshing = false
let refreshQueue = []

function resolveQueue(error, token = null) {
  refreshQueue.forEach(({ resolve, reject }) => {
    if (error) reject(error)
    else resolve(token)
  })
  refreshQueue = []
}

api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config
    const status = error.response?.status
    const errorCode = error.response?.data?.error_code

    const isAuthEndpoint = ['/auth/login', '/auth/register', '/auth/refresh'].some((path) =>
      originalRequest?.url?.includes(path)
    )

    // Only attempt a silent refresh on an expired access token, exactly once
    // per request, and never for the auth endpoints themselves.
    if (status === 401 && errorCode === 'TOKEN_EXPIRED' && !originalRequest._retry && !isAuthEndpoint) {
      if (isRefreshing) {
        // Wait for the in-flight refresh, then retry with the new token.
        return new Promise((resolve, reject) => {
          refreshQueue.push({ resolve, reject })
        }).then((token) => {
          originalRequest.headers.Authorization = `Bearer ${token}`
          return api(originalRequest)
        })
      }

      originalRequest._retry = true
      isRefreshing = true

      try {
        const { data } = await api.post('/auth/refresh')
        useAuthStore.getState().setAccessToken(data.access_token)
        resolveQueue(null, data.access_token)
        originalRequest.headers.Authorization = `Bearer ${data.access_token}`
        return api(originalRequest)
      } catch (refreshError) {
        resolveQueue(refreshError, null)
        useAuthStore.getState().logout()
        window.location.href = '/login'
        return Promise.reject(refreshError)
      } finally {
        isRefreshing = false
      }
    }

    // Any other 401 (invalid token, logged out elsewhere) → hard logout.
    if (status === 401 && !isAuthEndpoint) {
      useAuthStore.getState().logout()
    }

    return Promise.reject(error)
  }
)
