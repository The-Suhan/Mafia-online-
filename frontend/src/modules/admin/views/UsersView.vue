<template>
    <AdminLayout>
        <div class="admin-users">
            <!-- Header -->
            <div class="admin-users__header">
                <div>
                    <h1 class="admin-users__title">USERS</h1>
                    <p class="admin-users__subtitle">Tüm aileyi buradan yönetin.</p>
                </div>
                <span class="admin-users__total">
                    <strong>{{ usersTotal }}</strong> kullanıcı
                </span>
            </div>

            <!-- Filters -->
            <div class="admin-users__filters">
                <div class="admin-users__search">
                    <svg class="admin-users__search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <circle cx="11" cy="11" r="7" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                    <BaseInput v-model="search" type="text" placeholder="Nickname veya email ile ara..."
                        class="admin-users__search-input" aria-label="Kullanıcı ara" />
                </div>

                <select v-model="rankFilter" class="admin-users__select" aria-label="Rank filtresi">
                    <option value="">Tüm Ranklar</option>
                    <option v-for="r in RANKS" :key="r" :value="r">{{ r }}</option>
                </select>
            </div>

            <!-- Table -->
            <div class="admin-users__panel">
                <div class="admin-users__table-wrap">
                    <table class="admin-users__table">
                        <thead>
                            <tr>
                                <th>Kullanıcı</th>
                                <th>Email</th>
                                <th>Rank</th>
                                <th class="is-right">XP</th>
                                <th class="is-right">Games</th>
                                <th class="is-right">Win Rate</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th class="is-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="usersLoading">
                                <td colspan="9" class="admin-users__placeholder">Yükleniyor...</td>
                            </tr>
                            <tr v-else-if="!users.length">
                                <td colspan="9" class="admin-users__placeholder">
                                    Eşleşen kullanıcı bulunamadı.
                                </td>
                            </tr>
                            <tr v-else v-for="u in users" :key="u.id">
                                <td>
                                    <div class="admin-users__user-cell">
                                        <img :src="avatarSrc(u)" @error="onAvatarError($event, u)"
                                            class="admin-users__avatar" alt="" />
                                        <span class="admin-users__nickname">{{ u.nickname }}</span>
                                    </div>
                                </td>
                                <td class="admin-users__muted">{{ u.email }}</td>
                                <td>
                                    <span class="admin-users__badge" :class="rankClass(u.rank)">
                                        {{ u.rank }}
                                    </span>
                                </td>
                                <td class="is-right admin-users__mono">{{ formatXp(u.xp) }}</td>
                                <td class="is-right admin-users__mono admin-users__muted">
                                    {{ u.total_games }}
                                </td>
                                <td class="is-right admin-users__mono" :class="winRateClass(u)">
                                    {{ winRate(u) }}%
                                </td>
                                <td>
                                    <span class="admin-users__status">
                                        <span class="admin-users__dot"
                                            :class="u.status === 'Online' ? 'is-online' : 'is-offline'" />
                                        {{ u.status === 'Online' ? 'Online' : 'Offline' }}
                                    </span>
                                </td>
                                <td class="admin-users__muted">{{ formatJoined(u.joined_at) }}</td>
                                <td>
                                    <div class="admin-users__actions">
                                        <router-link :to="`/admin/users/${u.id}`" class="admin-users__icon-btn"
                                            :aria-label="`${u.nickname} görüntüle`">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>
                                        </router-link>
                                        <button type="button"
                                            class="admin-users__icon-btn admin-users__icon-btn--danger"
                                            :disabled="deletingId === u.id" :aria-label="`${u.nickname} sil`"
                                            @click="deleteUser(u)">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6" />
                                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                                                <path d="M10 11v6M14 11v6" />
                                                <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="admin-users__pagination">
                    <p class="admin-users__pagination-info">
                        Sayfa <strong>{{ usersPage }}</strong> / {{ totalPages }}
                        <span class="admin-users__muted"> &middot; {{ usersTotal }} sonuç</span>
                    </p>
                    <div class="admin-users__pagination-actions">
                        <BaseButton variant="outline" :disabled="usersPage <= 1 || usersLoading"
                            @click="goToPage(usersPage - 1)">
                            Önceki
                        </BaseButton>
                        <BaseButton variant="outline" :disabled="usersPage >= totalPages || usersLoading"
                            @click="goToPage(usersPage + 1)">
                            Sonraki
                        </BaseButton>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { useAdminStore } from '../store'
import AdminLayout from '../components/AdminLayout.vue'
import BaseInput from '@/shared/components/BaseInput.vue'
import BaseButton from '@/shared/components/BaseButton.vue'

const PAGE_SIZE = 20
const RANKS = ['Rookie', 'Novice', 'Elite', 'Pro', 'Master', 'Legend']

const adminStore = useAdminStore()

const search = ref('')
const rankFilter = ref('')
const deletingId = ref(null)

const users = computed(() => adminStore.users)
const usersTotal = computed(() => adminStore.usersTotal)
const usersPage = computed(() => adminStore.usersPage)
const usersLoading = computed(() => adminStore.loading)

const totalPages = computed(() =>
    Math.max(1, Math.ceil(usersTotal.value / PAGE_SIZE)),
)

function loadUsers(page = 1) {
    adminStore.fetchUsers({
        page,
        search: search.value.trim(),
        rank: rankFilter.value,
    })
}

function goToPage(page) {
    if (page < 1 || page > totalPages.value) return
    loadUsers(page)
}

let debounceTimer = null
watch([search, rankFilter], () => {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(() => loadUsers(1), 300)
})

onMounted(() => loadUsers(1))
onBeforeUnmount(() => clearTimeout(debounceTimer))

function rankClass(rank) {
    return `admin-users__badge--${String(rank || '').toLowerCase()}`
}

function winRate(u) {
    if (!u.total_games) return '0.0'
    return ((u.wins / u.total_games) * 100).toFixed(1)
}

function winRateClass(u) {
    const rate = Number(winRate(u))
    if (rate >= 60) return 'is-high'
    if (rate >= 45) return 'is-mid'
    return ''
}

function formatXp(xp) {
    return new Intl.NumberFormat('en-US').format(xp ?? 0)
}

function formatJoined(dateStr) {
    if (!dateStr) return '—'
    return new Date(dateStr).toLocaleDateString('tr-TR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    })
}

function dicebearUrl(nickname) {
    return `https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(nickname || '?')}`
}

function avatarSrc(u) {
    return u.avatar_url || dicebearUrl(u.nickname)
}

function onAvatarError(event, u) {
    event.target.src = dicebearUrl(u.nickname)
}

async function deleteUser(u) {
    if (!confirm(`${u.nickname} kullanıcısını kalıcı olarak silmek istediğinize emin misiniz? Bu işlem geri alınamaz.`)) {
        return
    }
    deletingId.value = u.id
    try {
        await adminStore.deleteUser(u.id)
    } catch (err) {
        // eslint-disable-next-line no-alert
        window.alert(err?.response?.data?.message || 'Kullanıcı silinemedi.')
    } finally {
        deletingId.value = null
    }
}
</script>

<style lang="scss" scoped>
@import '@/shared/styles/variables';
@import '@/shared/styles/mixins';

.admin-users {
    display: flex;
    flex-direction: column;
    gap: 24px;

    &__header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }

    &__title {
        margin: 0;
        font-size: 24px;
        font-weight: 600;
        letter-spacing: 0.04em;
        color: $color-text;
    }

    &__subtitle {
        margin: 4px 0 0;
        font-size: 13px;
        color: $color-muted;
    }

    &__total {
        font-size: 13px;
        color: $color-muted;

        strong {
            color: $color-text;
        }
    }

    &__filters {
        display: flex;
        flex-direction: column;
        gap: 12px;

        @include respond-to(sm) {
            flex-direction: row;
            align-items: center;
        }
    }

    &__search {
        position: relative;
        flex: 1;
    }

    &__search-icon {
        position: absolute;
        left: 10px;
        top: 50%;
        width: 16px;
        height: 16px;
        transform: translateY(-50%);
        color: $color-muted;
        pointer-events: none;
    }

    &__search-input {
        width: 100%;
        padding-left: 32px;
    }

    &__select {
        height: 34px;
        width: 100%;
        border: 1px solid $color-border;
        border-radius: 8px;
        background: transparent;
        color: $color-text;
        padding: 0 10px;
        font-size: 13px;

        @include respond-to(sm) {
            width: 192px;
        }

        &:focus {
            outline: none;
            border-color: $color-primary;
        }
    }

    &__panel {
        background: $color-card;
        border: 1px solid $color-border;
        border-radius: 10px;
        overflow: hidden;
    }

    &__table-wrap {
        overflow-x: auto;
    }

    &__table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;

        th {
            text-align: left;
            padding: 10px 12px;
            font-weight: 500;
            color: $color-text;
            border-bottom: 1px solid $color-border;
            white-space: nowrap;
        }

        td {
            padding: 8px 12px;
            vertical-align: middle;
            white-space: nowrap;
            border-bottom: 1px solid $color-border;
        }

        tbody tr {
            transition: background-color 0.15s ease;

            &:hover {
                background-color: rgba(255, 255, 255, 0.03);
            }

            &:last-child td {
                border-bottom: none;
            }
        }

        .is-right {
            text-align: right;
        }
    }

    &__placeholder {
        text-align: center;
        padding: 48px 0;
        color: $color-muted;
    }

    &__user-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    &__avatar {
        width: 32px;
        height: 32px;
        border-radius: 999px;
        object-fit: cover;
        border: 1px solid $color-border;
    }

    &__nickname {
        font-weight: 500;
        color: $color-text;
    }

    &__muted {
        color: $color-muted;
    }

    &__mono {
        font-family: $font-mono, monospace;

        &.is-high {
            color: $color-success;
        }

        &.is-mid {
            color: $color-warning;
        }
    }

    &__badge {
        display: inline-flex;
        align-items: center;
        border-radius: 6px;
        border: 1px solid currentColor;
        padding: 2px 8px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.04em;

        &--rookie {
            color: #9ca3af;
            background: rgba(156, 163, 175, 0.12);
        }

        &--novice {
            color: #60a5fa;
            background: rgba(96, 165, 250, 0.12);
        }

        &--elite {
            color: #f87171;
            background: rgba(248, 113, 113, 0.12);
        }

        &--pro {
            color: #fb923c;
            background: rgba(251, 146, 60, 0.12);
        }

        &--master {
            color: #c084fc;
            background: rgba(192, 132, 252, 0.12);
        }

        &--legend {
            color: #facc15;
            background: rgba(250, 204, 21, 0.12);
        }
    }

    &__status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    &__dot {
        width: 8px;
        height: 8px;
        border-radius: 999px;

        &.is-online {
            background: $color-success;
            box-shadow: 0 0 8px rgba(34, 197, 94, 0.6);
        }

        &.is-offline {
            background: $color-muted;
        }
    }

    &__actions {
        display: flex;
        justify-content: flex-end;
        gap: 4px;
    }

    &__icon-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        border: none;
        background: transparent;
        color: $color-muted;
        cursor: pointer;
        transition: background-color 0.15s ease, color 0.15s ease;

        svg {
            width: 16px;
            height: 16px;
        }

        &:hover {
            color: $color-text;
            background: rgba(255, 255, 255, 0.06);
        }

        &:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        &--danger:hover {
            color: $color-danger;
            background: rgba(248, 113, 113, 0.12);
        }
    }

    &__pagination {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 12px 16px;
        border-top: 1px solid $color-border;

        @include respond-to(sm) {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }
    }

    &__pagination-info {
        margin: 0;
        font-size: 13px;
        color: $color-muted;

        strong {
            color: $color-text;
        }
    }

    &__pagination-actions {
        display: flex;
        gap: 8px;
    }
}
</style>