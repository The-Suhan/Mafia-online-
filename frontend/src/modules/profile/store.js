import { defineStore } from 'pinia'
import api from '@/shared/services/api'

// ---------------------------------------------------------------------------
// XP / Rank helpers
// ---------------------------------------------------------------------------

const RANK_THRESHOLDS = {
    rookie: { min: 0, max: 199, next: 'novice', nextMin: 200 },
    novice: { min: 200, max: 499, next: 'elite', nextMin: 500 },
    elite: { min: 500, max: 1199, next: 'pro', nextMin: 1200 },
    pro: { min: 1200, max: 2499, next: 'master', nextMin: 2500 },
    master: { min: 2500, max: 4999, next: 'legend', nextMin: 5000 },
    legend: { min: 5000, max: null, next: null, nextMin: null },
}

export function getRankProgress(xp, rank) {
    const key = rank?.toLowerCase()
    const info = RANK_THRESHOLDS[key]
    if (!info) return { progress: 0, xpNeeded: 0, nextRank: null, nextMin: null }
    if (key === 'legend') return { progress: 100, xpNeeded: 0, nextRank: null, nextMin: null }

    const progress = Math.min(
        100,
        Math.round(((xp - info.min) / (info.nextMin - info.min)) * 100),
    )
    return {
        progress,
        xpNeeded: Math.max(0, info.nextMin - xp),
        nextRank: info.next,
        nextMin: info.nextMin,
    }
}

// ---------------------------------------------------------------------------
// Store
// ---------------------------------------------------------------------------

export const useProfileStore = defineStore('profile', {
    state: () => ({
        /** @type {Object|null} */
        profile: null,
        /** @type {Array} */
        recentGames: [],
        loading: false,
        error: null,
    }),

    getters: {
        rankProgress(state) {
            if (!state.profile) return null
            return getRankProgress(state.profile.xp ?? 0, state.profile.rank)
        },

        winRate(state) {
            if (!state.profile) return 0
            const total = state.profile.total_games ?? 0
            if (total === 0) return 0
            return Math.round(((state.profile.wins ?? 0) / total) * 100)
        },

        avatarUrl(state) {
            if (!state.profile) return ''
            return (
                state.profile.avatar_url ||
                `https://api.dicebear.com/7.x/pixel-art/svg?seed=${encodeURIComponent(
                    state.profile.nickname ?? 'player',
                )}`
            )
        },
    },

    actions: {
        async fetchProfile(userId) {
            this.loading = true
            this.error = null
            this.profile = null
            try {
                const { data } = await api.get(`/users/${userId}`)
                this.profile = data
            } catch (err) {
                this.error = err?.response?.status === 404
                    ? 'Player not found.'
                    : 'Failed to load profile. Please try again.'
            } finally {
                this.loading = false
            }
        },

        async fetchRecentGames(userId) {
            try {
                const { data } = await api.get(`/users/${userId}/games`, {
                    params: { limit: 8 },
                })
                this.recentGames = Array.isArray(data) ? data : (data.results ?? [])
            } catch {
                this.recentGames = []
            }
        },
    },
})