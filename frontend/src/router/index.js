import { createRouter, createWebHistory } from 'vue-router'
import authRoutes from '../modules/auth/routes'

const routes = [
  ...authRoutes,
  { path: '/', redirect: '/login' }, 
]

export default createRouter({
  history: createWebHistory(),
  routes,
})