import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/modules/auth/store'
import homeRoutes from '@/modules/home/routes'
import profileRoutes from '@/modules/profile/routes'
import authRoutes from '@/modules/auth/routes'
import staticRoutes from '@/modules/static/routes'
import adminRoutes from '@/modules/admin/routes'  

const routes = [
  ...homeRoutes,
  ...authRoutes,
  ...profileRoutes,
  ...adminRoutes,  

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

  ...staticRoutes,
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior: () => ({ top: 0 }),
})

router.beforeEach((to, from, next) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.token) {
    return next({ name: 'login', query: { redirect: to.fullPath } })
  }

  if (to.meta.guestOnly && auth.token) {
    return next('/')
  }

  if (to.meta.requiresAdmin && !auth.user?.is_admin) {
    return next('/')
  }

  next()
})

export default router