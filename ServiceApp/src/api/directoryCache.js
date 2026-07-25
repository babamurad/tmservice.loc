import AsyncStorage from '@react-native-async-storage/async-storage';
import { API_BASE } from './client';

const dataKey = (cacheKey) => `directory:data:${cacheKey}`;
const etagKey = (cacheKey) => `directory:etag:${cacheKey}`;

export async function getCachedDirectory(cacheKey) {
  const raw = await AsyncStorage.getItem(dataKey(cacheKey));
  return raw ? JSON.parse(raw) : null;
}

export async function fetchDirectory(endpoint, cacheKey) {
  const etag = await AsyncStorage.getItem(etagKey(cacheKey));

  const res = await fetch(`${API_BASE}${endpoint}`, {
    headers: etag ? { 'If-None-Match': etag } : {},
  });

  if (res.status === 304) {
    const cached = await getCachedDirectory(cacheKey);
    if (cached) {
      return cached;
    }
    throw new Error('Server returned 304 but there is no local cache to fall back to.');
  }

  if (!res.ok) {
    throw new Error(`Request to ${endpoint} failed: ${res.status}`);
  }

  const data = await res.json();
  const newEtag = res.headers.get('ETag');

  await AsyncStorage.setItem(dataKey(cacheKey), JSON.stringify(data));
  if (newEtag) {
    await AsyncStorage.setItem(etagKey(cacheKey), newEtag);
  }

  return data;
}
