import { useState, useEffect, useCallback } from 'react';
import {
  View,
  Text,
  Switch,
  TouchableOpacity,
  FlatList,
  Image,
  StyleSheet,
  ActivityIndicator,
  Alert,
} from 'react-native';
import * as ImagePicker from 'expo-image-picker';
import { useAuth } from '../../hooks/useAuth';
import { api, apiUpload } from '../../api/client';

export default function MyProfileScreen({ navigation }) {
  const { user, logout } = useAuth();
  const [profile, setProfile] = useState(null);
  const [loading, setLoading] = useState(true);
  const [toggling, setToggling] = useState(false);
  const [uploading, setUploading] = useState(false);

  const loadProfile = useCallback(async () => {
    try {
      const data = await api('/profile');
      setProfile(data);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    loadProfile();
  }, [loadProfile]);

  async function toggleStatus() {
    setToggling(true);
    try {
      const data = await api('/profile/status', { method: 'POST' });
      setProfile((prev) => ({ ...prev, is_free: data.is_free }));
    } catch (err) {
      Alert.alert('Ошибка', 'Не удалось изменить статус');
    } finally {
      setToggling(false);
    }
  }

  async function pickImage() {
    const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
    if (status !== 'granted') {
      Alert.alert('Доступ запрещён', 'Разрешите доступ к галерее в настройках');
      return;
    }

    const result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ['images'],
      quality: 0.8,
      allowsEditing: true,
    });

    if (result.canceled) return;

    setUploading(true);
    try {
      const formData = new FormData();
      const file = result.assets[0];
      formData.append('image', {
        uri: file.uri,
        type: file.mimeType || 'image/jpeg',
        name: file.fileName || 'photo.jpg',
      });

      await apiUpload('/profile/portfolio', formData);
      await loadProfile();
    } catch (err) {
      Alert.alert('Ошибка', 'Не удалось загрузить фото');
    } finally {
      setUploading(false);
    }
  }

  if (loading) {
    return (
      <View style={styles.center}>
        <ActivityIndicator size="large" />
      </View>
    );
  }

  return (
    <FlatList
      data={profile?.portfolio_images || []}
      contentContainerStyle={styles.container}
      ListHeaderComponent={
        <>
          <View style={styles.header}>
            <View style={styles.avatar}>
              <Text style={styles.avatarText}>
                {(profile?.user?.phone || 'M')[0].toUpperCase()}
              </Text>
            </View>
            <View style={styles.headerInfo}>
              <Text style={styles.phone}>{profile?.user?.phone}</Text>
              <Text style={styles.role}>Мастер</Text>
            </View>
          </View>

          <View style={styles.section}>
            <View style={styles.statusRow}>
              <Text style={styles.sectionTitle}>Статус</Text>
              <View style={styles.toggleRow}>
                <Text style={{ color: profile?.is_free ? '#34C759' : '#FF3B30' }}>
                  {profile?.is_free ? 'Свободен' : 'Занят'}
                </Text>
                <Switch
                  value={profile?.is_free}
                  onValueChange={toggleStatus}
                  disabled={toggling}
                  trackColor={{ false: '#ddd', true: '#34C759' }}
                />
              </View>
            </View>
          </View>

          <View style={styles.section}>
            <Text style={styles.sectionTitle}>О себе</Text>
            <Text style={styles.bio}>{profile?.bio || 'Не заполнено'}</Text>
          </View>

          {profile?.city && (
            <View style={styles.section}>
              <Text style={styles.sectionTitle}>Город</Text>
              <Text style={styles.bio}>{profile.city.name_ru}</Text>
            </View>
          )}

          {profile?.category && (
            <View style={styles.section}>
              <Text style={styles.sectionTitle}>Категория</Text>
              <Text style={styles.bio}>{profile.category.name_ru}</Text>
            </View>
          )}

          <TouchableOpacity
            style={styles.editButton}
            onPress={() => navigation.navigate('EditProfile', { profile })}
          >
            <Text style={styles.editButtonText}>Редактировать профиль</Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={styles.qrButton}
            onPress={() => {
              api('/profile/qr', { method: 'POST' }).then(() => {
                loadProfile();
                navigation.navigate('MyQR', { profile });
              }).catch(() => Alert.alert('Ошибка', 'Не удалось сгенерировать QR'));
            }}
          >
            <Text style={styles.qrButtonText}>Мой QR-код</Text>
          </TouchableOpacity>

          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Портфолио</Text>
            <TouchableOpacity
              style={styles.uploadButton}
              onPress={pickImage}
              disabled={uploading}
            >
              {uploading ? (
                <ActivityIndicator color="#fff" />
              ) : (
                <Text style={styles.uploadButtonText}>+ Добавить фото</Text>
              )}
            </TouchableOpacity>
          </View>
        </>
      }
      renderItem={({ item }) => (
        <Image
          source={{ uri: `https://tmservice.loc/storage/${item.image_path}` }}
          style={styles.portfolioImage}
          resizeMode="cover"
        />
      )}
      keyExtractor={(item) => String(item.id)}
      ListFooterComponent={
        <TouchableOpacity style={styles.logoutButton} onPress={logout}>
          <Text style={styles.logoutText}>Выйти</Text>
        </TouchableOpacity>
      }
    />
  );
}

const styles = StyleSheet.create({
  center: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  container: { padding: 16 },
  header: { flexDirection: 'row', marginBottom: 24, alignItems: 'center' },
  avatar: {
    width: 60,
    height: 60,
    borderRadius: 30,
    backgroundColor: '#007AFF',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 16,
  },
  avatarText: { fontSize: 22, color: '#fff', fontWeight: '700' },
  headerInfo: { flex: 1 },
  phone: { fontSize: 18, fontWeight: '700' },
  role: { fontSize: 14, color: '#666', marginTop: 2 },
  section: { marginBottom: 20 },
  sectionTitle: { fontSize: 16, fontWeight: '600', marginBottom: 6 },
  statusRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  toggleRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  bio: { fontSize: 15, color: '#333', lineHeight: 22 },
  editButton: {
    backgroundColor: '#007AFF',
    padding: 14,
    borderRadius: 10,
    alignItems: 'center',
    marginBottom: 12,
  },
  editButtonText: { color: '#fff', fontSize: 15, fontWeight: '600' },
  qrButton: {
    backgroundColor: '#000',
    padding: 14,
    borderRadius: 10,
    alignItems: 'center',
    marginBottom: 20,
  },
  qrButtonText: { color: '#fff', fontSize: 15, fontWeight: '600' },
  uploadButton: {
    backgroundColor: '#007AFF',
    padding: 12,
    borderRadius: 8,
    alignItems: 'center',
    marginBottom: 12,
  },
  uploadButtonText: { color: '#fff', fontSize: 14, fontWeight: '600' },
  portfolioImage: {
    width: '100%',
    height: 200,
    borderRadius: 12,
    marginBottom: 12,
  },
  logoutButton: {
    backgroundColor: '#FF3B30',
    padding: 16,
    borderRadius: 10,
    alignItems: 'center',
    marginTop: 12,
    marginBottom: 40,
  },
  logoutText: { color: '#fff', fontSize: 16, fontWeight: '600' },
});
