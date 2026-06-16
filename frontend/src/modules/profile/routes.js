export default [
    {
        path: '/profile/edit',
        name: 'profile-edit',
        component: () => import('./views/ProfileEditView.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/profile/:id',
        name: 'profile',
        component: () => import('./views/ProfileView.vue'),
        meta: { requiresAuth: true },
    },
]