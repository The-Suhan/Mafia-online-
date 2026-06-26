import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/shared/services/api'

export const useHomeStore = defineStore('home', () => {
    const rooms = ref([])
    const loading = ref(false)
    const error = ref(null)

    async function fetchRooms() {
        loading.value = true
        error.value = null
        try {
            const { data } = await api.get('/rooms', { params: { status: 'waiting' } })
            rooms.value = data.data
        } catch (e) {
            error.value = e?.response?.data?.message ?? 'Could not load rooms.'
        } finally {
            loading.value = false
        }
    }

    async function createRoom(payload) {
        const { data } = await api.post('/rooms', payload)
        return data.data
    }

    async function joinRoom(code) {
        const { data } = await api.post(`/rooms/${code}/join`)
        return data.data
    }

    return { rooms, loading, error, fetchRooms, createRoom, joinRoom }
}, {
    persist: {
        key: 'home',
        paths: ['rooms'],
    }
})