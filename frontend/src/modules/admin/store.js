import { defineStore } from 'pinia'
import api from '@/shared/services/api'

const USERS_PAGE_SIZE = 20

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
    }),

    getters: {
        usersTotalPages: (state) =>
            Math.max(1, Math.ceil(state.usersTotal / state.usersPageSize)),

        selectedUserWinRate(state) {
            const user = state.selectedUser
            if (!user || !user.total_games) return 0
            return Math.round((user.wins / user.total_games) * 100)
        },
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
    },
})

export default useAdminStore