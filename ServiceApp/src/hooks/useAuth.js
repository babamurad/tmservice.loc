import { useState, useEffect, createContext, useContext } from 'react';
import * as SecureStore from 'expo-secure-store';
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
      const token = await SecureStore.getItemAsync('token');
      if (token) {
        // TODO: fetch user profile to validate token
        setUser({ token });
      }
    } catch {
      await SecureStore.deleteItemAsync('token');
    } finally {
      setLoading(false);
    }
  }

  async function login(phone, password) {
    const data = await api('/login', {
      method: 'POST',
      body: JSON.stringify({ phone, password }),
    });
    await SecureStore.setItemAsync('token', data.token);
    setUser(data.user);
    return data;
  }

  async function register(phone, password, role) {
    const data = await api('/register', {
      method: 'POST',
      body: JSON.stringify({ phone, password, role }),
    });
    await SecureStore.setItemAsync('token', data.token);
    setUser(data.user);
    return data;
  }

  async function logout() {
    try {
      await api('/logout', { method: 'POST' });
    } catch {
      // ignore
    }
    await SecureStore.deleteItemAsync('token');
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
