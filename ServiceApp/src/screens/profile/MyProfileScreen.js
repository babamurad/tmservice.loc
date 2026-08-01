import { useState, useEffect, useCallback } from 'react';
import { View, Text, Switch, TouchableOpacity, FlatList, Image, StyleSheet, ActivityIndicator, Alert, Platform } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import * as ImagePicker from 'expo-image-picker';
import { useAuth } from '../../hooks/useAuth';
import { api, apiUpload } from '../../api/client';
import ScreenContainer from '../../components/ScreenContainer';
import Card from '../../components/Card';
import Avatar from '../../components/Avatar';
import Button from '../../components/Button';
import { colors, spacing, typography } from '../../theme';

export default function MyProfileScreen({ navigation }) {
  const { logout } = useAuth();
  const [profile, setProfile] = useState(null);
  const [loading, setLoading] = useState(true);
  const [toggling, setToggling] = useState(false);
  const [uploading, setUploading] = useState(false);
  const [deletingId, setDeletingId] = useState(null);

  const loadProfile = useCallback(async () => {
    try {
      const data = await api('/profile');
      setProfile(data);
    } catch (err) {
      if (err?.status !== 404) {
        console.error(err);
      }
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
      if (Platform.OS === 'web') {
        const webFile = file.file || await (await fetch(file.uri)).blob();
        formData.append('image', webFile, file.fileName || 'photo.jpg');
      } else {
        formData.append('image', {
          uri: file.uri,
          type: file.mimeType || 'image/jpeg',
          name: file.fileName || 'photo.jpg',
        });
      }

      await apiUpload('/profile/portfolio', formData);
      await loadProfile();
    } catch (err) {
      Alert.alert('Ошибка', 'Не удалось загрузить фото');
    } finally {
      setUploading(false);
    }
  }

  function confirmDeletePhoto(id) {
    Alert.alert('Удалить фото?', 'Это действие нельзя отменить.', [
      { text: 'Отмена', style: 'cancel' },
      { text: 'Удалить', style: 'destructive', onPress: () => deletePhoto(id) },
    ]);
  }

  async function deletePhoto(id) {
    setDeletingId(id);
    try {
      await api(`/profile/portfolio/${id}`, { method: 'DELETE' });
      setProfile((prev) => ({
        ...prev,
        portfolio_images: prev.portfolio_images.filter((image) => image.id !== id),
      }));
    } catch (err) {
      Alert.alert('Ошибка', 'Не удалось удалить фото');
    } finally {
      setDeletingId(null);
    }
  }

  if (loading) {
    return (
      <ScreenContainer style={styles.center}>
        <ActivityIndicator size="large" color={colors.primary} />
      </ScreenContainer>
    );
  }

  return (
    <ScreenContainer style={styles.screenBg}>
      <FlatList
        data={profile?.portfolio_images || []}
        contentContainerStyle={styles.container}
        ListHeaderComponent={
          <>
            <View style={styles.cover}>
              <View style={styles.coverPattern} />
            </View>
            <View style={styles.header}>
              <View style={styles.avatarWrapper}>
                <Avatar phone={profile?.user?.phone} size={80} />
              </View>
              <View style={styles.headerInfo}>
                <Text style={typography.title}>{profile?.user?.phone}</Text>
                <Text style={typography.bodyMuted}>Мастер</Text>
              </View>
            </View>

            <Card style={styles.section}>
              <View style={styles.statusRow}>
                <Text style={typography.heading}>Статус</Text>
                <View style={styles.toggleRow}>
                  <Text style={{ color: profile?.is_free ? '#1E8449' : colors.danger, fontWeight: '600' }}>
                    {profile?.is_free ? 'Свободен' : 'Занят'}
                  </Text>
                  <Switch
                    value={profile?.is_free}
                    onValueChange={toggleStatus}
                    disabled={toggling}
                    trackColor={{ false: colors.border, true: '#1E8449' }}
                  />
                </View>
              </View>
            </Card>

            <Card style={styles.section}>
              <Text style={[typography.heading, styles.sectionTitle]}>О себе</Text>
              <Text style={typography.body}>{profile?.bio || 'Не заполнено'}</Text>
            </Card>

            {profile?.city && (
              <Card style={styles.section}>
                <Text style={[typography.heading, styles.sectionTitle]}>Город</Text>
                <Text style={typography.body}>{profile.city.name_ru}</Text>
              </Card>
            )}

            {profile?.category && (
              <Card style={styles.section}>
                <Text style={[typography.heading, styles.sectionTitle]}>Категория</Text>
                <Text style={typography.body}>{profile.category.name_ru}</Text>
              </Card>
            )}

            <Button
              title="Редактировать профиль"
              icon="create-outline"
              variant="outline"
              onPress={() => navigation.navigate('EditProfile', { profile })}
              style={styles.actionButton}
            />

            <Button
              title="Мой QR-код"
              icon="qr-code-outline"
              variant="dark"
              onPress={() => {
                api('/profile/qr', { method: 'POST' }).then(() => {
                  loadProfile();
                  navigation.navigate('MyQR', { profile });
                }).catch(() => Alert.alert('Ошибка', 'Не удалось сгенерировать QR'));
              }}
              style={styles.actionButton}
            />

            <View style={styles.section}>
              <Text style={[typography.heading, styles.sectionTitle]}>Портфолио</Text>
              <Button
                title="Добавить фото"
                icon="camera-outline"
                variant="outline"
                onPress={pickImage}
                loading={uploading}
                style={{ marginBottom: spacing.md }}
              />
            </View>
          </>
        }
        numColumns={2}
        columnWrapperStyle={styles.portfolioGridRow}
        renderItem={({ item }) => (
          <View style={styles.portfolioItem}>
            <Image
              source={{ uri: `https://tmservice.loc/storage/${item.image_path}` }}
              style={styles.portfolioImage}
              resizeMode="cover"
            />
            <TouchableOpacity
              style={styles.deletePhotoButton}
              onPress={() => confirmDeletePhoto(item.id)}
              disabled={deletingId === item.id}
            >
              {deletingId === item.id ? (
                <ActivityIndicator color={colors.white} size="small" />
              ) : (
                <Ionicons name="close" size={16} color={colors.white} />
              )}
            </TouchableOpacity>
          </View>
        )}
        keyExtractor={(item) => String(item.id)}
        ListFooterComponent={
          <Button title="Выйти" icon="log-out-outline" variant="danger" onPress={logout} style={styles.logoutButton} />
        }
      />
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  center: { justifyContent: 'center', alignItems: 'center' },
  screenBg: { backgroundColor: colors.bg },
  container: { padding: spacing.lg, paddingTop: 80 },
  cover: {
    height: 120,
    backgroundColor: colors.primary,
    borderBottomLeftRadius: 32,
    borderBottomRightRadius: 32,
    overflow: 'hidden',
    position: 'absolute',
    top: -80,
    left: -spacing.lg,
    right: -spacing.lg,
  },
  coverPattern: {
    flex: 1,
    opacity: 0.1,
    backgroundColor: colors.primaryDark,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'flex-end',
    gap: spacing.lg,
    marginBottom: spacing.xl,
  },
  avatarWrapper: {
    padding: 4,
    backgroundColor: colors.bg,
    borderRadius: 50,
  },
  headerInfo: { flex: 1, paddingBottom: spacing.sm },
  section: { marginBottom: spacing.md },
  sectionTitle: { marginBottom: spacing.sm },
  statusRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  toggleRow: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm },
  actionButton: { marginBottom: spacing.md },
  portfolioGridRow: { justifyContent: 'space-between', marginBottom: spacing.md },
  portfolioItem: { flex: 0.48, borderRadius: 16, overflow: 'hidden' },
  portfolioImage: { width: '100%', height: 160, borderRadius: 16 },
  deletePhotoButton: {
    position: 'absolute',
    top: spacing.sm,
    right: spacing.sm,
    width: 28,
    height: 28,
    borderRadius: 14,
    backgroundColor: colors.overlay,
    alignItems: 'center',
    justifyContent: 'center',
  },
  logoutButton: { marginTop: spacing.xl, marginBottom: spacing.xxl },
});
