import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'
import vueDevTools from 'vite-plugin-vue-devtools'

export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  
  server: {
  port: 3000,
  proxy: {
      '/api': {
        target: 'http://localhost/backend/public',
        changeOrigin: true,
        rewrite: path => path.replace('^/api','')
      }
    }
  },
  

})
