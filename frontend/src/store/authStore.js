import { create } from 'zustand'
import { persist } from 'zustand/middleware'

/**
 * Only the access token + user profile are persisted (sessionStorage, not
 * localStorage — cleared when the tab closes, reducing XSS token-theft
 * exposure window). The refresh token itself is never touched by the
 * frontend; refresh happens via the existing access token calling
 * POST /auth/refresh, which is backed server-side by the JWT blacklist.
 */
export const useAuthStore = create(
  persist(
    (set) => ({
      user: null,
      accessToken: null,
      isAuthenticated: false,

      setSession: (user, accessToken) =>
        set({ user, accessToken, isAuthenticated: true }),

      setAccessToken: (accessToken) => set({ accessToken }),

      setUser: (user) => set({ user }),

      logout: () => set({ user: null, accessToken: null, isAuthenticated: false }),
    }),
    {
      name: 'legalcaseflow-auth',
      storage: {
        getItem: (name) => {
          const value = sessionStorage.getItem(name)
          return value ? JSON.parse(value) : null
        },
        setItem: (name, value) => sessionStorage.setItem(name, JSON.stringify(value)),
        removeItem: (name) => sessionStorage.removeItem(name),
      },
    }
  )
)
