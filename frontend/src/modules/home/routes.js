export default [
    {
        path: '/',
        name: 'home',
        component: () => import('./views/HomeView.vue'),
        meta: { requiresAuth: true },
    },
]