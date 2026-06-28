import * as SecureStore from 'expo-secure-store';

const API_BASE = 'https://tmservice.loc/api';

export async function api(endpoint, options = {}) {
  const token = await SecureStore.getItemAsync('token');

  const headers = {
    'Content-Type': 'application/json',
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
    ...options.headers,
  };

  const res = await fetch(`${API_BASE}${endpoint}`, {
    ...options,
    headers,
  });

  if (!res.ok) {
    const error = await res.json().catch(() => ({}));
    throw { status: res.status, ...error };
  }

  return res.json();
}
