import { defineStore } from 'pinia'
import api from '@/shared/services/api'

const USERS_PAGE_SIZE = 20
const MATCHES_PAGE_SIZE = 20

export const useAdminStore = defineStore('admin', {
    state: () => ({
        // --- Dashboard (stats & activity) ---
        stats: null,
        activity: [],
        loading: false,
        error: null,

        // --- Kullanıcı listesi (sayfalı) ---
        users: [],
        usersTotal: 0,
        usersPage: 1,
        usersPageSize: USERS_PAGE_SIZE,

        // --- Seçili kullanıcı (detay sayfası) ---
        selectedUser: null,
        selectedUserLoading: false,
        selectedUserError: null,

        // --- Seçili kullanıcının maç geçmişi ---
        selectedUserGames: [],
        selectedUserGamesLoading: false,
        selectedUserGamesError: null,

        // --- Aksiyon durumları (UI'da spinner/disable göstermek için) ---
        banActionLoading: false,
        xpActionLoading: false,

        // --- Match History (liste sayfası) ---
        matches: [],
        matchesTotal: 0,
        matchesPage: 1,
        matchesPageSize: MATCHES_PAGE_SIZE,
        matchesLoading: false,
        matchesError: null,

        // --- Match Detail (admin/history-match/:id sayfası) ---
        selectedMatch: null,
        selectedMatchLoading: false,
        selectedMatchError: null,

        matchPlayers: [],

        matchLogs: [],
        matchLogsLoading: false,
        matchLogsError: null,
    }),

    getters: {
        usersTotalPages: (state) =>
            Math.max(1, Math.ceil(state.usersTotal / state.usersPageSize)),

        selectedUserWinRate(state) {
            const user = state.selectedUser
            if (!user || !user.total_games) return 0
            return Math.round((user.wins / user.total_games) * 100)
        },

        matchesTotalPages: (state) =>
            Math.max(1, Math.ceil(state.matchesTotal / state.matchesPageSize)),
    },

    actions: {
        async fetchStats() {
            this.loading = true
            this.error = null
            try {
                const { data } = await api.get('/api/admin/stats')
                this.stats = data
            } catch (err) {
                this.error = err?.response?.data?.message || 'Failed to load stats'
            } finally {
                this.loading = false
            }
        },

        async fetchActivity() {
            this.loading = true
            this.error = null
            try {
                const { data } = await api.get('/api/admin/activity', { params: { limit: 10 } })
                this.activity = data
            } catch (err) {
                this.error = err?.response?.data?.message || 'Failed to load activity'
            } finally {
                this.loading = false
            }
        },

        /**
         * GET /api/admin/users?page=&search=&rank=
         * @param {{page?: number, search?: string, rank?: string}} payload
         */
        async fetchUsers({ page = 1, search = '', rank = '' } = {}) {
            this.loading = true
            this.error = null
            try {
                const { data } = await api.get('/api/admin/users', {
                    params: {
                        page,
                        search: search || undefined,
                        rank: rank || undefined,
                    },
                })

                // Farklı API response şekillerine tolerans:
                // { items, total, page } veya { data, meta: { total, current_page } }
                this.users = data.items ?? data.data ?? []
                this.usersTotal = data.total ?? data.meta?.total ?? 0
                this.usersPage = data.page ?? data.meta?.current_page ?? page
            } catch (err) {
                this.error = err?.response?.data?.message || 'Failed to load users'
            } finally {
                this.loading = false
            }
        },

        // --- Detay sayfası ---
        // NOT: Bu action gerçek dosyada yoktu, önceki tahmini sürümden taşındı.
        // Endpoint'i backend'inize göre teyit edin.
        async fetchUser(id) {
            this.selectedUserLoading = true
            this.selectedUserError = null
            try {
                const { data } = await api.get(`/api/admin/users/${id}`)
                this.selectedUser = data
                return data
            } catch (err) {
                this.selectedUserError = err?.response?.data?.message || 'Kullanıcı bulunamadı'
                throw err
            } finally {
                this.selectedUserLoading = false
            }
        },

        // NOT: Tahmini eklendi, endpoint'i teyit edin.
        async fetchUserGames(id, limit = 10) {
            this.selectedUserGamesLoading = true
            this.selectedUserGamesError = null
            try {
                const { data } = await api.get(`/api/admin/users/${id}/games`, {
                    params: { limit },
                })
                this.selectedUserGames = data
                return data
            } catch (err) {
                this.selectedUserGamesError = err?.response?.data?.message || 'Maç geçmişi yüklenemedi'
                throw err
            } finally {
                this.selectedUserGamesLoading = false
            }
        },

        /**
         * Hem liste sayfasının ihtiyacını (kullanıcıyı listeden çıkar)
         * hem de detay sayfasının ihtiyacını (selectedUser'ı güncelle)
         * tek action'da karşılar. Endpoint, gerçek dosyadaki gibi POST.
         */
        async banUser(id) {
            this.banActionLoading = true
            try {
                const { data } = await api.post(`/api/admin/users/${id}/ban`)

                // Liste sayfasındaysak: kullanıcıyı listeden düşür
                this.users = this.users.filter((u) => u.id !== id)
                this.usersTotal = Math.max(0, this.usersTotal - 1)

                // Detay sayfasındaysak: selectedUser'ı güncelle
                if (this.selectedUser && String(this.selectedUser.id) === String(id)) {
                    this.selectedUser = { ...this.selectedUser, ...data }
                }

                return data
            } catch (err) {
                this.error = err?.response?.data?.message || 'Failed to ban user'
                throw err
            } finally {
                this.banActionLoading = false
            }
        },

        // NOT: Tahmini eklendi, gerçek endpoint'i teyit edin.
        async updateUserXp(id, xp) {
            this.xpActionLoading = true
            try {
                const { data } = await api.patch(`/api/admin/users/${id}/xp`, { xp })
                if (this.selectedUser && String(this.selectedUser.id) === String(id)) {
                    this.selectedUser = { ...this.selectedUser, ...data }
                }
                return data
            } finally {
                this.xpActionLoading = false
            }
        },

        // BONUS: is_admin toggle arayüzü için — endpoint adını teyit edin.
        async setUserAdmin(id, isAdmin) {
            const { data } = await api.patch(`/api/admin/users/${id}/role`, {
                is_admin: isAdmin,
            })
            if (this.selectedUser && String(this.selectedUser.id) === String(id)) {
                this.selectedUser = { ...this.selectedUser, ...data }
            }
            return data
        },

        clearSelectedUser() {
            this.selectedUser = null
            this.selectedUserGames = []
            this.selectedUserError = null
            this.selectedUserGamesError = null
        },

        /**
         * Maç geçmişini sayfalı olarak getirir.
         * GET /api/admin/sessions?page=&status=&date_from=&date_to=
         * @param {{page?: number, status?: string, dateFrom?: string, dateTo?: string}} payload
         */
        async fetchMatches({ page = 1, status = 'all', dateFrom = '', dateTo = '' } = {}) {
            this.matchesLoading = true
            this.matchesError = null
            try {
                const { data } = await api.get('/api/admin/sessions', {
                    params: {
                        page,
                        status: status && status !== 'all' ? status : undefined,
                        date_from: dateFrom || undefined,
                        date_to: dateTo || undefined,
                    },
                })

                // Farklı API response şekillerine tolerans:
                // { items, total, page } veya { data, meta: { total, current_page } }
                this.matches = data.items ?? data.data ?? []
                this.matchesTotal = data.total ?? data.meta?.total ?? 0
                this.matchesPage = data.page ?? data.meta?.current_page ?? page
            } catch (err) {
                this.matchesError = err?.response?.data?.message || 'Failed to load matches'
            } finally {
                this.matchesLoading = false
            }
        },

        /**
         * Maç detayını getirir (admin/history-match/:id sayfası).
         * GET /api/admin/sessions/{id}
         * Beklenen response: { id, roomName, winner, totalPlayers, totalRounds,
         *   duration, startedAt, endedAt, players: [...] }
         */
        async fetchMatch(id) {
            this.selectedMatchLoading = true
            this.selectedMatchError = null
            try {
                const { data } = await api.get(`/api/admin/sessions/${id}`)
                this.selectedMatch = data
                this.matchPlayers = data?.players ?? []
                return data
            } catch (err) {
                this.selectedMatchError = err?.response?.data?.message || 'Maç bulunamadı'
                throw err
            } finally {
                this.selectedMatchLoading = false
            }
        },

        /**
         * Maç içindeki round/phase bazlı event log'larını getirir.
         * GET /api/admin/sessions/{id}/logs
         * Beklenen response: [{ id, round, phase, eventType, message, highlight? }]
         */
        async fetchMatchLogs(id) {
            this.matchLogsLoading = true
            this.matchLogsError = null
            try {
                const { data } = await api.get(`/api/admin/sessions/${id}/logs`)
                this.matchLogs = data ?? []
                return data
            } catch (err) {
                this.matchLogsError = err?.response?.data?.message || 'Maç günlüğü yüklenemedi'
                throw err
            } finally {
                this.matchLogsLoading = false
            }
        },

        clearSelectedMatch() {
            this.selectedMatch = null
            this.matchPlayers = []
            this.matchLogs = []
            this.selectedMatchError = null
            this.matchLogsError = null
        },
    },
})

export default useAdminStore