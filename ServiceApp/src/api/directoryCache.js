import { getItemAsync, setItemAsync } from '../utils/storage';
import { API_BASE } from './client';

const sanitizeKey = (key) => key.replace(/[^a-zA-Z0-9._-]/g, '_');
const dataKey = (cacheKey) => sanitizeKey(`directory_data_${cacheKey}`);
const etagKey = (cacheKey) => sanitizeKey(`directory_etag_${cacheKey}`);

export async function getCachedDirectory(cacheKey) {
  const raw = await getItemAsync(dataKey(cacheKey));
  if (!raw) return null;

  try {
    const parsed = JSON.parse(raw);
    return Array.isArray(parsed) ? parsed : null;
  } catch {
    return null;
  }
}

export async function fetchDirectory(endpoint, cacheKey) {
  const etag = await getItemAsync(etagKey(cacheKey));

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

  const rawData = await res.json();
  let data = rawData?.data !== undefined ? rawData.data : rawData;
  if (!Array.isArray(data) && data && typeof data === 'object') {
    data = Object.values(data);
  }
  if (!Array.isArray(data)) {
    throw new Error(`Unexpected response shape from ${endpoint}: expected an array`);
  }

  const newEtag = res.headers.get('ETag');

  await setItemAsync(dataKey(cacheKey), JSON.stringify(data));
  if (newEtag) {
    await setItemAsync(etagKey(cacheKey), newEtag);
  }

  return data;
}

