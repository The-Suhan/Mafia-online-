export default [
    {
        path: '/profile/:id',
        name: 'profile',
        component: () => import('./views/ProfileView.vue'),
        meta: { requiresAuth: true },
    },
]