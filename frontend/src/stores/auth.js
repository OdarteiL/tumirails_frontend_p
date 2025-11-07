import { defineStore } from 'pinia';
import axios from 'axios';

const API_BASE = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null,
    loading: false,
    error: null,
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
  },
  actions: {
    async register(payload) {
      this.loading = true; this.error = null;
      try {
        const { data } = await axios.post(`${API_BASE}/register`, payload);
        this.user = data.user; this.token = data.token;
        localStorage.setItem('token', this.token);
        axios.defaults.headers.common.Authorization = `Bearer ${this.token}`;
      } catch (e) {
        this.error = e.response?.data?.message || 'Registration failed';
        throw e;
      } finally { this.loading = false; }
    },
    async login(payload) {
      this.loading = true; this.error = null;
      try {
        const { data } = await axios.post(`${API_BASE}/login`, payload);
        this.user = data.user; this.token = data.token;
        localStorage.setItem('token', this.token);
        axios.defaults.headers.common.Authorization = `Bearer ${this.token}`;
      } catch (e) {
        this.error = e.response?.data?.message || 'Login failed';
        throw e;
      } finally { this.loading = false; }
    },
    async logout() {
      try {
        await axios.post(`${API_BASE}/logout`, {}, {
          headers: { Authorization: `Bearer ${this.token}` },
        });
      } catch (_) {}
      this.user = null; this.token = null; this.error = null;
      localStorage.removeItem('token');
      delete axios.defaults.headers.common.Authorization;
    },
    async fetchMe() {
      if (!this.token) return;
      const { data } = await axios.get(`${API_BASE}/me`, {
        headers: { Authorization: `Bearer ${this.token}` },
      });
      this.user = data.user;
    },
  },
});
