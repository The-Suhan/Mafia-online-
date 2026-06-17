<template>
    <AdminLayout>
        <div class="admin-user-detail">
            <!-- Breadcrumb / back -->
            <div class="admin-user-detail__breadcrumb">
                <router-link to="/admin/users" class="admin-user-detail__back-link">
                    <svg viewBox="0 0 24 24" class="admin-user-detail__icon" aria-hidden="true">
                        <path d="M15 18l-6-6 6-6" />
                    </svg>
                    Players
                </router-link>
                <span class="admin-user-detail__breadcrumb-sep">/</span>
                <span class="admin-user-detail__breadcrumb-current">
                    {{ user ? user.nickname : '...' }}
                </span>
            </div>

            <!-- Loading state -->
            <div v-if="pageLoading" class="admin-user-detail__state admin-user-detail__state--loading">
                Kullanıcı bilgileri yükleniyor…
            </div>

            <!-- Error state -->
            <div v-else-if="pageError" class="admin-user-detail__state admin-user-detail__state--error">
                {{ pageError }}
                <button class="admin-user-detail__retry" type="button" @click="loadData">
                    Tekrar dene
                </button>
            </div>

            <template v-else-if="user">
                <!-- Profile card -->
                <section class="admin-user-detail__card admin-user-detail__profile">
                    <div class="admin-user-detail__profile-main">
                        <div class="admin-user-detail__avatar-wrap">
                            <img :src="user.avatar_url || '/placeholder.svg'" :alt="`${user.nickname} avatarı`"
                                class="admin-user-detail__avatar" />
                            <span class="admin-user-detail__level-badge">LVL {{ user.level }}</span>
                        </div>

                        <div class="admin-user-detail__profile-info">
                            <div class="admin-user-detail__title-row">
                                <h1 class="admin-user-detail__nickname">{{ user.nickname }}</h1>

                                <span class="admin-user-detail__rank-badge">
                                    <svg viewBox="0 0 24 24" class="admin-user-detail__icon admin-user-detail__icon--sm"
                                        aria-hidden="true">
                                        <path d="M3 8l4 4 5-7 5 7 4-4-2 11H5L3 8z" />
                                    </svg>
                                    {{ user.rank }}
                                </span>

                                <span class="admin-user-detail__status"
                                    :class="user.is_online ? 'is-online' : 'is-offline'">
                                    <span class="admin-user-detail__status-dot" aria-hidden="true" />
                                    {{ user.is_online ? 'Çevrimiçi' : 'Çevrimdışı' }}
                                </span>

                                <span v-if="user.is_banned" class="admin-user-detail__ban-pill">Banlı</span>
                            </div>

                            <p class="admin-user-detail__email">
                                <svg viewBox="0 0 24 24" class="admin-user-detail__icon admin-user-detail__icon--sm"
                                    aria-hidden="true">
                                    <path d="M3 6h18v12H3V6z" />
                                    <path d="M3 7l9 6 9-6" />
                                </svg>
                                {{ user.email }}
                            </p>

                            <div class="admin-user-detail__xp-block">
                                <div class="admin-user-detail__xp-labels">
                                    <span>XP İlerlemesi</span>
                                    <span class="admin-user-detail__xp-value">
                                        {{ formatNumber(user.xp) }} / {{ formatNumber(user.xp_to_next_level) }} XP
                                    </span>
                                </div>
                                <div class="admin-user-detail__xp-track" role="progressbar" :aria-valuenow="xpPercent"
                                    aria-valuemin="0" aria-valuemax="100">
                                    <div class="admin-user-detail__xp-fill" :style="{ width: xpPercent + '%' }" />
                                </div>
                            </div>

                            <div class="admin-user-detail__meta-grid">
                                <div class="admin-user-detail__meta-item">
                                    <svg viewBox="0 0 24 24" class="admin-user-detail__icon admin-user-detail__icon--sm"
                                        aria-hidden="true">
                                        <rect x="3" y="5" width="18" height="16" rx="2" />
                                        <path d="M3 10h18M8 3v4M16 3v4" />
                                    </svg>
                                    <span class="admin-user-detail__meta-label">Oluşturulma</span>
                                    <span class="admin-user-detail__meta-value">{{ formatDate(user.created_at) }}</span>
                                </div>
                                <div class="admin-user-detail__meta-item">
                                    <svg viewBox="0 0 24 24" class="admin-user-detail__icon admin-user-detail__icon--sm"
                                        aria-hidden="true">
                                        <circle cx="12" cy="12" r="9" />
                                        <path d="M12 7v5l3 3" />
                                    </svg>
                                    <span class="admin-user-detail__meta-label">Son aktif</span>
                                    <span class="admin-user-detail__meta-value">{{ formatRelative(user.last_active_at)
                                        }}</span>
                                </div>
                            </div>

                            <div class="admin-user-detail__admin-toggle-row">
                                <span class="admin-user-detail__meta-label">Admin yetkisi</span>
                                <button type="button" class="admin-user-detail__switch"
                                    :class="{ 'is-on': user.is_admin }" role="switch" :aria-checked="user.is_admin"
                                    :disabled="adminToggleLoading" @click="handleToggleAdmin">
                                    <span class="admin-user-detail__switch-knob" />
                                </button>
                                <span class="admin-user-detail__switch-label">
                                    {{ user.is_admin ? 'Admin' : 'Oyuncu' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Stats grid -->
                <section class="admin-user-detail__stats" aria-label="Oyuncu istatistikleri">
                    <div class="admin-user-detail__stat-card">
                        <div class="admin-user-detail__stat-head">
                            <span>Toplam Oyun</span>
                            <span class="admin-user-detail__stat-icon">
                                <svg viewBox="0 0 24 24" class="admin-user-detail__icon" aria-hidden="true">
                                    <rect x="2" y="8" width="20" height="9" rx="4" />
                                    <path d="M8 12h.01M6 14h4M16 12h.01M18 12h.01" />
                                </svg>
                            </span>
                        </div>
                        <p class="admin-user-detail__stat-value">{{ formatNumber(user.total_games) }}</p>
                    </div>

                    <div class="admin-user-detail__stat-card">
                        <div class="admin-user-detail__stat-head">
                            <span>Galibiyet</span>
                            <span class="admin-user-detail__stat-icon">
                                <svg viewBox="0 0 24 24" class="admin-user-detail__icon" aria-hidden="true">
                                    <path d="M7 4h10v4a5 5 0 01-10 0V4z" />
                                    <path d="M5 6H3v2a4 4 0 004 4M19 6h2v2a4 4 0 01-4 4" />
                                    <path d="M9 18h6M12 14v4" />
                                </svg>
                            </span>
                        </div>
                        <p class="admin-user-detail__stat-value">{{ formatNumber(user.wins) }}</p>
                    </div>

                    <div class="admin-user-detail__stat-card">
                        <div class="admin-user-detail__stat-head">
                            <span>Mağlubiyet</span>
                            <span class="admin-user-detail__stat-icon">
                                <svg viewBox="0 0 24 24" class="admin-user-detail__icon" aria-hidden="true">
                                    <circle cx="12" cy="11" r="7" />
                                    <path d="M9 9l1.5 2L9 13M15 9l-1.5 2L15 13" />
                                    <path d="M9 18l-1 3M15 18l1 3" />
                                </svg>
                            </span>
                        </div>
                        <p class="admin-user-detail__stat-value">{{ formatNumber(user.losses) }}</p>
                    </div>

                    <div class="admin-user-detail__stat-card admin-user-detail__stat-card--accent">
                        <div class="admin-user-detail__stat-head">
                            <span>Kazanma Oranı</span>
                            <span class="admin-user-detail__stat-icon admin-user-detail__stat-icon--accent">
                                <svg viewBox="0 0 24 24" class="admin-user-detail__icon" aria-hidden="true">
                                    <path d="M19 5L5 19M7 5a2 2 0 110 4 2 2 0 010-4zM17 15a2 2 0 110 4 2 2 0 010-4z" />
                                </svg>
                            </span>
                        </div>
                        <p class="admin-user-detail__stat-value admin-user-detail__stat-value--accent">
                            {{ winRate }}%
                        </p>
                    </div>
                </section>

                <!-- Action buttons -->
                <section class="admin-user-detail__card admin-user-detail__actions" aria-label="Admin aksiyonları">
                    <span class="admin-user-detail__actions-label">Admin işlemleri</span>
                    <div class="admin-user-detail__actions-buttons">
                        <BaseButton variant="danger" :loading="banning" :disabled="banning"
                            class="admin-user-detail__ban-btn" @click="handleBanUser">
                            <svg viewBox="0 0 24 24" class="admin-user-detail__icon admin-user-detail__icon--sm"
                                aria-hidden="true">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M6 6l12 12" />
                            </svg>
                            {{ user.is_banned ? 'Banı Kaldır' : 'Kullanıcıyı Banla' }}
                        </BaseButton>

                        <BaseButton variant="secondary" @click="openXpModal">
                            <svg viewBox="0 0 24 24" class="admin-user-detail__icon admin-user-detail__icon--sm"
                                aria-hidden="true">
                                <path d="M12 3l1.8 4.8L19 9l-4.8 1.8L12 16l-1.8-5.2L5 9l5.2-1.2L12 3z" />
                            </svg>
                            XP Düzenle
                        </BaseButton>
                    </div>
                </section>

                <!-- Match history -->
                <section class="admin-user-detail__card admin-user-detail__matches">
                    <div class="admin-user-detail__matches-head">
                        <h2 class="admin-user-detail__matches-title">Son Maçlar</h2>
                        <span class="admin-user-detail__matches-count">{{ games.length }} maç</span>
                    </div>

                    <div class="admin-user-detail__table-wrap">
                        <table class="admin-user-detail__table">
                            <thead>
                                <tr>
                                    <th>Maç ID</th>
                                    <th>Rol</th>
                                    <th>Sonuç</th>
                                    <th class="admin-user-detail__col-right">Tarih</th>
                                    <th class="admin-user-detail__col-right">&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="game in games" :key="game.session_id || game.id">
                                    <td class="admin-user-detail__match-id">{{ game.session_id || game.id }}</td>
                                    <td>
                                        <span class="admin-user-detail__role-pill" :class="roleClass(game.role)">
                                            {{ game.role }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="admin-user-detail__result"
                                            :class="game.result === 'Win' ? 'is-win' : 'is-loss'">
                                            <span class="admin-user-detail__result-dot" aria-hidden="true" />
                                            {{ game.result === 'Win' ? 'Galibiyet' : 'Mağlubiyet' }}
                                        </span>
                                    </td>
                                    <td class="admin-user-detail__col-right admin-user-detail__date">
                                        {{ formatDate(game.date || game.played_at) }}
                                    </td>
                                    <td class="admin-user-detail__col-right">
                                        <router-link :to="`/admin/history-match/${game.session_id || game.id}`"
                                            class="admin-user-detail__view-match">
                                            Maçı Gör
                                        </router-link>
                                    </td>
                                </tr>

                                <tr v-if="!games.length">
                                    <td colspan="5" class="admin-user-detail__empty">
                                        Henüz maç kaydı bulunmuyor.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </template>

            <!-- Edit XP modal -->
            <div v-if="xpModalOpen" class="admin-user-detail__modal-overlay" @click.self="closeXpModal">
                <div class="admin-user-detail__modal" role="dialog" aria-modal="true" aria-label="XP Düzenle">
                    <h3 class="admin-user-detail__modal-title">XP Düzenle</h3>
                    <p class="admin-user-detail__modal-subtitle">
                        {{ user?.nickname }} için yeni XP değerini girin.
                    </p>

                    <BaseInput v-model="xpInput" type="number" min="0" label="XP" placeholder="Örn. 18450"
                        :error="xpFormError" />

                    <div class="admin-user-detail__modal-actions">
                        <BaseButton variant="outline" :disabled="xpSubmitting" @click="closeXpModal">
                            İptal
                        </BaseButton>
                        <BaseButton variant="primary" :loading="xpSubmitting" @click="submitXp">
                            Kaydet
                        </BaseButton>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAdminStore } from '../store'
import AdminLayout from '../components/AdminLayout.vue'
import BaseButton from '@/shared/components/BaseButton.vue'
import BaseInput from '@/shared/components/BaseInput.vue'

const route = useRoute()
const adminStore = useAdminStore()

const userId = computed(() => route.params.id)

const pageLoading = ref(true)
const pageError = ref(null)

const user = computed(() => adminStore.selectedUser)
const games = computed(() => adminStore.selectedUserGames)
const winRate = computed(() => adminStore.selectedUserWinRate)

const xpPercent = computed(() => {
    if (!user.value || !user.value.xp_to_next_level) return 0
    return Math.min(100, Math.round((user.value.xp / user.value.xp_to_next_level) * 100))
})

async function loadData() {
    pageLoading.value = true
    pageError.value = null
    try {
        await Promise.all([
            adminStore.fetchUser(userId.value),
            adminStore.fetchUserGames(userId.value, 10),
        ])
    } catch (err) {
        pageError.value = 'Kullanıcı bilgileri yüklenirken bir hata oluştu.'
    } finally {
        pageLoading.value = false
    }
}

onMounted(loadData)

// --- Ban user ---
const banning = ref(false)
async function handleBanUser() {
    if (!user.value) return
    const confirmMessage = user.value.is_banned
        ? `${user.value.nickname} kullanıcısının banını kaldırmak istediğinize emin misiniz?`
        : `${user.value.nickname} kullanıcısını banlamak istediğinize emin misiniz?`
    // eslint-disable-next-line no-alert
    if (!window.confirm(confirmMessage)) return

    banning.value = true
    try {
        await adminStore.banUser(userId.value)
    } catch (err) {
        // eslint-disable-next-line no-alert
        window.alert('Ban işlemi başarısız oldu.')
    } finally {
        banning.value = false
    }
}

// --- Admin toggle (bonus — bkz. store.js notu) ---
const adminToggleLoading = ref(false)
async function handleToggleAdmin() {
    if (!user.value) return
    adminToggleLoading.value = true
    try {
        await adminStore.setUserAdmin(userId.value, !user.value.is_admin)
    } catch (err) {
        // eslint-disable-next-line no-alert
        window.alert('Admin yetkisi güncellenemedi.')
    } finally {
        adminToggleLoading.value = false
    }
}

// --- Edit XP modal ---
const xpModalOpen = ref(false)
const xpInput = ref(0)
const xpSubmitting = ref(false)
const xpFormError = ref(null)

function openXpModal() {
    xpInput.value = user.value?.xp ?? 0
    xpFormError.value = null
    xpModalOpen.value = true
}

function closeXpModal() {
    if (xpSubmitting.value) return
    xpModalOpen.value = false
}

async function submitXp() {
    const parsed = Number(xpInput.value)
    if (Number.isNaN(parsed) || parsed < 0) {
        xpFormError.value = 'Geçerli bir XP değeri girin.'
        return
    }
    xpSubmitting.value = true
    xpFormError.value = null
    try {
        await adminStore.updateUserXp(userId.value, parsed)
        xpModalOpen.value = false
    } catch (err) {
        xpFormError.value = 'XP güncellenemedi.'
    } finally {
        xpSubmitting.value = false
    }
}

// --- Helpers ---
const roleClassMap = {
    Don: 'is-mafia',
    Mafia: 'is-mafia',
    Sheriff: 'is-sheriff',
    Doctor: 'is-doctor',
    Citizen: 'is-citizen',
}
function roleClass(role) {
    return roleClassMap[role] || 'is-citizen'
}

function formatNumber(value) {
    return Number(value || 0).toLocaleString('tr-TR')
}

function formatDate(value) {
    if (!value) return '—'
    const date = new Date(value)
    if (Number.isNaN(date.getTime())) return value
    return date.toLocaleDateString('tr-TR', { day: '2-digit', month: 'short', year: 'numeric' })
}

function formatRelative(value) {
    if (!value) return '—'
    const date = new Date(value)
    if (Number.isNaN(date.getTime())) return value
    const diffMs = Date.now() - date.getTime()
    const diffMin = Math.round(diffMs / 60000)
    if (diffMin < 1) return 'az önce'
    if (diffMin < 60) return `${diffMin} dk önce`
    const diffHour = Math.round(diffMin / 60)
    if (diffHour < 24) return `${diffHour} sa önce`
    const diffDay = Math.round(diffHour / 24)
    return `${diffDay} gün önce`
}
</script>

<style lang="scss" scoped>
/*
 * Renk/spacing değerleri burada bilinçli olarak literal (hex/px) verildi —
 * projenizdeki _variables.scss / _mixins.scss dosyalarının gerçek değişken
 * adlarını bilmediğim için varsayımsal isimlerle @use etmek derleme hatası
 * riski taşıyordu. Tasarım, mevcut globals.css'teki koyu tema paletinin
 * (oklch) yaklaşık hex karşılıklarından türetildi. Karşılık gelen tokenlarınız
 * varsa bu literalleri onlarla değiştirmeniz önerilir.
 */

$color-bg: #1c1c1e;
$color-card: #232325;
$color-foreground: #f4f4f5;
$color-muted-foreground: #a3a3a6;
$color-muted: #2a2a2c;
$color-border: #34343780;
$color-primary: #d6432d;
$color-primary-foreground: #fff5f0;
$color-emerald: #34d399;
$color-sky: #38bdf8;
$radius-md: 8px;
$radius-lg: 12px;
$space-1: 4px;
$space-2: 8px;
$space-3: 12px;
$space-4: 16px;
$space-5: 20px;
$space-6: 24px;

.admin-user-detail {
    display: flex;
    flex-direction: column;
    gap: $space-6;
    max-width: 1024px;
    margin: 0 auto;
    padding: $space-6;
    color: $color-foreground;

    &__breadcrumb {
        display: flex;
        align-items: center;
        gap: $space-2;
        font-size: 0.875rem;
    }

    &__back-link {
        display: inline-flex;
        align-items: center;
        gap: $space-1;
        color: $color-muted-foreground;
        text-decoration: none;
        transition: color 0.15s ease;

        &:hover {
            color: $color-foreground;
        }
    }

    &__breadcrumb-sep {
        color: rgba($color-muted-foreground, 0.5);
    }

    &__breadcrumb-current {
        font-weight: 500;
    }

    &__icon {
        width: 16px;
        height: 16px;
        fill: none;
        stroke: currentColor;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;

        &--sm {
            width: 14px;
            height: 14px;
        }
    }

    &__state {
        border-radius: $radius-lg;
        border: 1px solid $color-border;
        background: $color-card;
        padding: $space-6;
        text-align: center;
        color: $color-muted-foreground;

        &--error {
            color: $color-primary;
        }
    }

    &__retry {
        display: block;
        margin: $space-3 auto 0;
        border: 1px solid $color-border;
        background: transparent;
        color: $color-foreground;
        border-radius: $radius-md;
        padding: $space-2 $space-4;
        cursor: pointer;
    }

    &__card {
        border-radius: $radius-lg;
        border: 1px solid $color-border;
        background: $color-card;
    }

    // --- Profile ---
    &__profile {
        padding: $space-6;
    }

    &__profile-main {
        display: flex;
        flex-direction: column;
        gap: $space-6;

        @media (min-width: 640px) {
            flex-direction: row;
            align-items: flex-start;
        }
    }

    &__avatar-wrap {
        position: relative;
        flex-shrink: 0;
        align-self: center;

        @media (min-width: 640px) {
            align-self: flex-start;
        }
    }

    &__avatar {
        width: 112px;
        height: 112px;
        object-fit: cover;
        border-radius: $radius-lg;
        border: 2px solid rgba($color-primary, 0.4);
        box-shadow: 0 0 0 4px rgba($color-primary, 0.1);
    }

    &__level-badge {
        position: absolute;
        bottom: -8px;
        left: 50%;
        transform: translateX(-50%);
        border: 1px solid $color-border;
        background: $color-card;
        border-radius: 999px;
        padding: 2px $space-2;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    &__profile-info {
        flex: 1;
        min-width: 0;
    }

    &__title-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: $space-3;
    }

    &__nickname {
        font-size: 1.5rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        margin: 0;
    }

    &__rank-badge {
        display: inline-flex;
        align-items: center;
        gap: $space-1;
        border: 1px solid rgba($color-primary, 0.3);
        background: rgba($color-primary, 0.15);
        color: $color-primary;
        border-radius: 999px;
        padding: 4px $space-3;
        font-size: 0.75rem;
        font-weight: 600;
    }

    &__status {
        display: inline-flex;
        align-items: center;
        gap: $space-1;
        font-size: 0.75rem;
        font-weight: 500;

        &.is-online {
            color: $color-foreground;
        }

        &.is-offline {
            color: $color-muted-foreground;
        }
    }

    &__status-dot {
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: $color-muted-foreground;
    }

    &__status.is-online &__status-dot {
        background: $color-emerald;
    }

    &__ban-pill {
        border-radius: 999px;
        background: rgba($color-primary, 0.2);
        color: $color-primary;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 3px $space-2;
    }

    &__email {
        display: flex;
        align-items: center;
        gap: $space-1;
        margin: $space-1 0 0;
        font-size: 0.875rem;
        color: $color-muted-foreground;
    }

    &__xp-block {
        margin-top: $space-5;
    }

    &__xp-labels {
        display: flex;
        justify-content: space-between;
        font-size: 0.75rem;
        margin-bottom: $space-1;

        span:first-child {
            font-weight: 500;
        }
    }

    &__xp-value {
        color: $color-muted-foreground;
    }

    &__xp-track {
        width: 100%;
        height: 10px;
        border-radius: 999px;
        background: $color-muted;
        overflow: hidden;
    }

    &__xp-fill {
        height: 100%;
        border-radius: 999px;
        background: $color-primary;
        transition: width 0.2s ease;
    }

    &__meta-grid {
        margin-top: $space-5;
        display: grid;
        grid-template-columns: 1fr;
        gap: $space-3;

        @media (min-width: 640px) {
            grid-template-columns: 1fr 1fr;
        }
    }

    &__meta-item {
        display: flex;
        align-items: center;
        gap: $space-2;
        font-size: 0.875rem;
    }

    &__meta-label {
        color: $color-muted-foreground;
    }

    &__meta-value {
        font-weight: 500;
    }

    &__admin-toggle-row {
        margin-top: $space-5;
        display: flex;
        align-items: center;
        gap: $space-3;
        padding-top: $space-4;
        border-top: 1px solid $color-border;
    }

    &__switch {
        position: relative;
        width: 38px;
        height: 22px;
        border-radius: 999px;
        border: none;
        background: $color-muted;
        cursor: pointer;
        transition: background 0.15s ease;
        flex-shrink: 0;

        &.is-on {
            background: $color-primary;
        }

        &:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    }

    &__switch-knob {
        position: absolute;
        top: 2px;
        left: 2px;
        width: 18px;
        height: 18px;
        border-radius: 999px;
        background: $color-foreground;
        transition: transform 0.15s ease;
    }

    &__switch.is-on &__switch-knob {
        transform: translateX(16px);
    }

    &__switch-label {
        font-size: 0.875rem;
        font-weight: 500;
    }

    // --- Stats ---
    &__stats {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: $space-4;

        @media (min-width: 1024px) {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    &__stat-card {
        border-radius: $radius-lg;
        border: 1px solid $color-border;
        background: $color-card;
        padding: $space-5;
    }

    &__stat-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.875rem;
        color: $color-muted-foreground;
    }

    &__stat-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: $radius-md;
        background: $color-muted;
        color: $color-muted-foreground;

        &--accent {
            background: rgba($color-primary, 0.15);
            color: $color-primary;
        }
    }

    &__stat-value {
        margin: $space-3 0 0;
        font-size: 1.875rem;
        font-weight: 700;
        letter-spacing: -0.01em;

        &--accent {
            color: $color-primary;
        }
    }

    // --- Actions ---
    &__actions {
        display: flex;
        flex-direction: column;
        gap: $space-3;
        padding: $space-5;

        @media (min-width: 640px) {
            flex-direction: row;
            align-items: center;
        }
    }

    &__actions-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: $color-muted-foreground;

        @media (min-width: 640px) {
            margin-right: auto;
        }
    }

    &__actions-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: $space-3;
    }

    &__ban-btn {
        box-shadow: 0 0 0 1px rgba($color-primary, 0.15), 0 0 16px rgba($color-primary, 0.25);
    }

    // --- Matches ---
    &__matches-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: $space-4 $space-5;
        border-bottom: 1px solid $color-border;
    }

    &__matches-title {
        font-size: 1rem;
        font-weight: 600;
        margin: 0;
    }

    &__matches-count {
        font-size: 0.75rem;
        color: $color-muted-foreground;
    }

    &__table-wrap {
        overflow-x: auto;
    }

    &__table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;

        th {
            text-align: left;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: $color-muted-foreground;
            font-weight: 500;
            padding: $space-3 $space-5;
            border-bottom: 1px solid $color-border;
        }

        td {
            padding: 14px $space-5;
            border-bottom: 1px solid rgba($color-border, 0.6);
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background: rgba($color-muted, 0.4);
        }
    }

    &__col-right {
        text-align: right;
    }

    &__match-id {
        font-family: var(--font-mono, monospace);
        font-size: 0.75rem;
        color: $color-muted-foreground;
    }

    &__date {
        color: $color-muted-foreground;
    }

    &__role-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        border: 1px solid $color-border;
        padding: 2px $space-3;
        font-size: 0.75rem;
        font-weight: 500;
        background: $color-muted;
        color: $color-muted-foreground;

        &.is-mafia {
            border-color: rgba($color-primary, 0.3);
            background: rgba($color-primary, 0.15);
            color: $color-primary;
        }

        &.is-sheriff {
            border-color: rgba($color-sky, 0.3);
            background: rgba($color-sky, 0.15);
            color: $color-sky;
        }

        &.is-doctor {
            border-color: rgba($color-emerald, 0.3);
            background: rgba($color-emerald, 0.15);
            color: $color-emerald;
        }
    }

    &__result {
        display: inline-flex;
        align-items: center;
        gap: $space-1;
        font-size: 0.75rem;
        font-weight: 600;

        &.is-win {
            color: $color-emerald;
        }

        &.is-loss {
            color: $color-primary;
        }
    }

    &__result-dot {
        width: 6px;
        height: 6px;
        border-radius: 999px;
        background: currentColor;
    }

    &__view-match {
        color: $color-primary;
        font-size: 0.8rem;
        font-weight: 500;
        text-decoration: none;

        &:hover {
            text-decoration: underline;
        }
    }

    &__empty {
        text-align: center;
        color: $color-muted-foreground;
        padding: $space-6 !important;
    }

    // --- Modal ---
    &__modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: $space-4;
        z-index: 50;
    }

    &__modal {
        width: 100%;
        max-width: 380px;
        border-radius: $radius-lg;
        border: 1px solid $color-border;
        background: $color-card;
        padding: $space-6;
    }

    &__modal-title {
        margin: 0;
        font-size: 1.125rem;
        font-weight: 600;
    }

    &__modal-subtitle {
        margin: $space-1 0 $space-5;
        font-size: 0.875rem;
        color: $color-muted-foreground;
    }

    &__modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: $space-3;
        margin-top: $space-5;
    }
}
</style>