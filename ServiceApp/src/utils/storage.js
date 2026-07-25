import { Platform } from 'react-native';
import * as SecureStore from 'expo-secure-store';

export async function getItemAsync(key) {
  try {
    if (Platform.OS === 'web') {
      return typeof localStorage !== 'undefined' ? localStorage.getItem(key) : null;
    }
    return await SecureStore.getItemAsync(key);
  } catch {
    return null;
  }
}

export async function setItemAsync(key, value) {
  try {
    if (Platform.OS === 'web') {
      if (typeof localStorage !== 'undefined') {
        localStorage.setItem(key, value);
      }
      return;
    }
    await SecureStore.setItemAsync(key, value);
  } catch {
    // ignore storage errors
  }
}

export async function deleteItemAsync(key) {
  try {
    if (Platform.OS === 'web') {
      if (typeof localStorage !== 'undefined') {
        localStorage.removeItem(key);
      }
      return;
    }
    await SecureStore.deleteItemAsync(key);
  } catch {
    // ignore storage errors
  }
}
