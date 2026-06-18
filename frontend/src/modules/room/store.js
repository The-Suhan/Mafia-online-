import { defineStore } from 'pinia'
import { computed } from 'vue'
import api from '@/shared/services/api'
import { useAuthStore } from '@/modules/auth/store'

export const useRoomStore = defineStore('room', {
  state: () => ({
    room: null,
    session: null,
    players: [],
    messages: [],
    myRole: null,
    myMafiaTeam: [],
    currentPhase: 'waiting',
    phaseEndsAt: null,
    round: 1,
    nightActions: { submitted: false, target_id: null },
    dayVote: { submitted: false, target_id: null },
    winner: null,
    loading: false,
  }),

  getters: {
    alivePlayers: (state) => state.players.filter((p) => p.is_alive),

    isOwner: (state) => {
      const auth = useAuthStore()
      return state.room?.owner_id === auth.user?.id
    },

    myPlayer: (state) => {
      const auth = useAuthStore()
      return state.players.find((p) => p.user_id === auth.user?.id)
    },
  },

  actions: {
    async fetchRoom(roomId) {
      this.loading = true
      try {
        const { data } = await api.get(`/rooms/${roomId}`)
        this.room = data
      } finally {
        this.loading = false
      }
    },

    async fetchSession(roomId) {
      try {
        const { data } = await api.get(`/rooms/${roomId}/session`)
        this.session = data
        if (data?.phase) this.currentPhase = data.phase
        if (data?.round) this.round = data.round
        if (data?.phase_ends_at) this.phaseEndsAt = data.phase_ends_at
      } catch {
        this.session = null
      }
    },

    async fetchPlayers(sessionId) {
      try {
        const { data } = await api.get(`/sessions/${sessionId}/players`)
        this.players = data
      } catch {
        this.players = []
      }
    },

    async fetchMessages(roomId) {
      try {
        const { data } = await api.get(`/rooms/${roomId}/messages`)
        this.messages = data
      } catch {
        this.messages = []
      }
    },

    async sendMessage(roomId, message) {
      const { data } = await api.post(`/rooms/${roomId}/messages`, { message })
      return data
    },

    async startGame(roomId) {
      const { data } = await api.post(`/rooms/${roomId}/start`)
      return data
    },

    async closeRoom(roomId) {
      const { data } = await api.post(`/rooms/${roomId}/close`)
      return data
    },

    async submitNightAction(sessionId, payload) {
      const { data } = await api.post(`/game/${sessionId}/night-action`, payload)
      this.nightActions = { submitted: true, target_id: payload.target_id }
      return data
    },

    async submitVote(sessionId, payload) {
      const { data } = await api.post(`/game/${sessionId}/vote`, payload)
      this.dayVote = { submitted: true, target_id: payload.target_id }
      return data
    },

    resetRoundState() {
      this.nightActions = { submitted: false, target_id: null }
      this.dayVote = { submitted: false, target_id: null }
    },
  },
})
