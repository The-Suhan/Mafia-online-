import { defineStore } from 'pinia'
import api from '@/shared/services/api'

const USERS_PAGE_SIZE = 20

export const useAdminStore = defineStore('admin', {
    state: () => ({
        stats: null,
        activity: [],
        loading: false,
        error: null,

        // Users yönetimi
        users: [],
        usersTotal: 0,
        usersPage: 1,
        usersPageSize: USERS_PAGE_SIZE,
    }),

    getters: {
        usersTotalPages: (state) =>
            Math.max(1, Math.ceil(state.usersTotal / state.usersPageSize)),
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

        // BONUS — spekte açıkça istenmedi, "Ban" ikonunun bir karşılığı olması
        // için eklendi. Backend endpoint'iniz farklıysa güncelleyin/silin.
        async banUser(userId) {
            try {
                await api.post(`/api/admin/users/${userId}/ban`)
                this.users = this.users.filter((u) => u.id !== userId)
                this.usersTotal = Math.max(0, this.usersTotal - 1)
            } catch (err) {
                this.error = err?.response?.data?.message || 'Failed to ban user'
                throw err
            }
        },
    },
})