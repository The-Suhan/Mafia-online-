import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/shared/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user  = ref(null)
  const token = ref(localStorage.getItem('auth_token') || null)

  // ── Register ──────────────────────────────────────────────────────────────
  async function register(payload) {
    // payload: { nickname, email, password, password_confirmation }
    const { data } = await api.post('/register', payload)

    token.value = data.token
    user.value  = data.user
    localStorage.setItem('auth_token', data.token)

    return data
  }

  // ── Login ─────────────────────────────────────────────────────────────────
  async function login(payload) {
    // payload: { email, password }
    const { data } = await api.post('/login', payload)

    token.value = data.token
    user.value  = data.user
    localStorage.setItem('auth_token', data.token)

    return data
  }

  // ── Logout ────────────────────────────────────────────────────────────────
  async function logout() {
    try {
      await api.post('/logout')
    } catch {
      // swallow — clear local state regardless
    } finally {
      token.value = null
      user.value  = null
      localStorage.removeItem('auth_token')
    }
  }

  return { user, token, register, login, logout }
})
