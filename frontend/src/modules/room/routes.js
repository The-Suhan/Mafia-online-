export default [
  {
    path: '/room/:id',
    name: 'room',
    component: () => import('./views/RoomView.vue'),
    meta: { requiresAuth: true },
  },
]
