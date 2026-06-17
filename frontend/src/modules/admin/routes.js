export default [
    {
        path: '/admin',
        name: 'admin-dashboard',
        component: () => import('./views/DashboardView.vue'),
        meta: { requiresAuth: true, requiresAdmin: true },
    },
    {
        path: '/admin/users',
        name: 'admin-users',
        component: () => import('./views/UsersView.vue'),
        meta: { requiresAuth: true, requiresAdmin: true },
    },
    {
        path: '/admin/users/:id',   
        name: 'admin-user-detail',
        component: () => import('./views/UserDetailView.vue'),
        meta: { requiresAuth: true, requiresAdmin: true },
    },
    {
        path: '/admin/history-match',
        name: 'admin-match-history',
        component: () => import('./views/MatchHistoryView.vue'),
        meta: { requiresAuth: true, requiresAdmin: true },
    },
    {
        path: '/admin/history-match/:id',
        name: 'admin-match-detail',
        component: () => import('./views/MatchDetailView.vue'),
        meta: { requiresAuth: true, requiresAdmin: true },
    },
]

// Add to router/index.js beforeEach:
//
// import { useAuthStore } from '@/modules/auth/store'
//
// router.beforeEach((to, from, next) => {
//   const auth = useAuthStore()
//   if (to.meta.requiresAuth && !auth.user) {
//     return next('/login')
//   }
//   if (to.meta.requiresAdmin && !auth.user?.is_admin) {
//     return next('/')
//   }
//   next()
// })