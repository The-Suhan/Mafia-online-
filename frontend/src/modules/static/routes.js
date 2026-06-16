export default [
    {
        path: '/how-to-play',
        name: 'how-to-play',
        component: () => import('./views/HowToPlayView.vue'),
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: () => import('./views/NotFoundView.vue'),
    },
]