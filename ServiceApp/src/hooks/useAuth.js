import { useState, useEffect, createContext, useContext } from 'react';
import * as storage from '../utils/storage';
import { api } from '../api/client';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    restoreToken();
  }, []);

  async function restoreToken() {
    try {
      const token = await storage.getItemAsync('token');
      if (token) {
        const me = await api('/me');
        setUser(me);
      }
    } catch {
      await storage.deleteItemAsync('token');
    } finally {
      setLoading(false);
    }
  }

  async function login(phone, password) {
    const data = await api('/login', {
      method: 'POST',
      body: JSON.stringify({ phone, password }),
    });
    await storage.setItemAsync('token', data.token);
    setUser(data.user);
    return data;
  }

  async function register(phone, password, role) {
    const data = await api('/register', {
      method: 'POST',
      body: JSON.stringify({ phone, password, role }),
    });
    await storage.setItemAsync('token', data.token);
    setUser(data.user);
    return data;
  }

  async function logout() {
    try {
      await api('/logout', { method: 'POST' });
    } catch {
      // ignore
    }
    await storage.deleteItemAsync('token');
    setUser(null);
  }

  return (
    <AuthContext.Provider value={{ user, loading, login, register, logout }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used within AuthProvider');
  return ctx;
}
