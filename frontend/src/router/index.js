import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/modules/auth/store'
import homeRoutes from '@/modules/home/routes'
import profileRoutes from '@/modules/profile/routes'
import authRoutes from '@/modules/auth/routes'
// ── Import other module routes here as the project grows ──────
// import roomRoutes from '@/modules/room/routes'
// import adminRoutes from '@/modules/admin/routes'

const routes = [
  ...homeRoutes,
  ...authRoutes,
  ...profileRoutes,
  // ── Add other module routes here ──────────────────────────
  // ...roomRoutes,
  // ...adminRoutes,

  {
    path: '/profile',
    name: 'my-profile',
    meta: { requiresAuth: true },
    beforeEnter: (to, from, next) => {
      const auth = useAuthStore()
      if (auth.user?.id) {
        next(`/profile/${auth.user.id}`)
      } else {
        next('/login')
      }
    },
    component: { template: '<div></div>' }, 
  },
  // Fallback
  {
    path: '/:pathMatch(.*)*',
    redirect: '/',
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior: () => ({ top: 0 }),
})

// ── Navigation guard ──────────────────────────────────────────
router.beforeEach((to, from, next) => {
  // Pinia store must be accessed inside the guard (after createPinia is called)
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.token) {
    return next({ name: 'login', query: { redirect: to.fullPath } })
  }

  if (to.meta.guestOnly && auth.token) {
    return next('/')
  }

  next()
})

export default router