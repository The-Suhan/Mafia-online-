import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/shared/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('auth_token') || null)

  // ── Register ──────────────────────────────────────────────────────────────
  async function register(payload) {
    // payload: { nickname, email, password, password_confirmation }
    const { data } = await api.post('/register', payload)

    token.value = data.token
    user.value = data.user
    localStorage.setItem('auth_token', data.token)

    return data
  }

  // ── Login ─────────────────────────────────────────────────────────────────
  async function login(payload) {
    // payload: { email, password }
    const { data } = await api.post('/login', payload)

    token.value = data.token
    user.value = data.user
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
      user.value = null
      localStorage.removeItem('auth_token')
    }
  }

  // ── Forgot Password ───────────────────────────────────────────────────────
  async function forgotPassword(payload) {
    // payload: { email }
    const { data } = await api.post('/forgot-password', payload)
    return data
  }

  // ── Reset Password ────────────────────────────────────────────────────────
  async function resetPassword(payload) {
    // payload: { token, email, password, password_confirmation }
    const { data } = await api.post('/reset-password', payload)
    return data
  }

  return { user, token, register, login, logout, forgotPassword, resetPassword }
})