import { useEffect, useState } from 'react';
import {
  View,
  Text,
  Image,
  FlatList,
  TouchableOpacity,
  StyleSheet,
  ActivityIndicator,
  Linking,
} from 'react-native';
import { api } from '../../api/client';

export default function MasterDetailScreen({ route }) {
  const { master } = route.params;
  const [detail, setDetail] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadDetail();
  }, []);

  async function loadDetail() {
    try {
      const data = await api(`/masters/${master.id}`);
      setDetail(data);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  }

  function handleCall() {
    const phone = detail?.user?.phone;
    if (phone) {
      Linking.openURL(`tel:${phone}`);
    }
  }

  if (loading) {
    return (
      <View style={styles.center}>
        <ActivityIndicator size="large" />
      </View>
    );
  }

  if (!detail) {
    return (
      <View style={styles.center}>
        <Text>Ошибка загрузки</Text>
      </View>
    );
  }

  return (
    <FlatList
      data={detail.portfolio_images}
      contentContainerStyle={styles.container}
      ListHeaderComponent={
        <>
          <View style={styles.header}>
            <View style={styles.avatar}>
              <Text style={styles.avatarText}>
                {(detail.user?.phone || 'M')[0].toUpperCase()}
              </Text>
            </View>
            <View style={styles.headerInfo}>
              <Text style={styles.name}>{detail.user?.phone}</Text>
              <View style={styles.statusRow}>
                <View
                  style={[
                    styles.dot,
                    { backgroundColor: detail.is_free ? '#34C759' : '#FF3B30' },
                  ]}
                />
                <Text style={styles.statusText}>
                  {detail.is_free ? 'Свободен' : 'Занят'}
                </Text>
              </View>
              {detail.city && <Text style={styles.meta}>📍 {detail.city.name_ru}</Text>}
              {detail.category && (
                <Text style={styles.meta}>📋 {detail.category.name_ru}</Text>
              )}
            </View>
          </View>

          {detail.bio && (
            <View style={styles.section}>
              <Text style={styles.sectionTitle}>О себе</Text>
              <Text style={styles.bio}>{detail.bio}</Text>
            </View>
          )}

          {detail.portfolio_images?.length > 0 && (
            <View style={styles.section}>
              <Text style={styles.sectionTitle}>Портфолио</Text>
            </View>
          )}
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
        <TouchableOpacity style={styles.callButton} onPress={handleCall}>
          <Text style={styles.callButtonText}>Позвонить</Text>
        </TouchableOpacity>
      }
    />
  );
}

const styles = StyleSheet.create({
  center: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  container: { padding: 16 },
  header: { flexDirection: 'row', marginBottom: 20 },
  avatar: {
    width: 64,
    height: 64,
    borderRadius: 32,
    backgroundColor: '#007AFF',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 16,
  },
  avatarText: { fontSize: 24, color: '#fff', fontWeight: '700' },
  headerInfo: { flex: 1, justifyContent: 'center' },
  name: { fontSize: 20, fontWeight: '700', marginBottom: 4 },
  statusRow: { flexDirection: 'row', alignItems: 'center', marginBottom: 4, gap: 6 },
  dot: { width: 10, height: 10, borderRadius: 5 },
  statusText: { fontSize: 14, color: '#666' },
  meta: { fontSize: 13, color: '#999', marginTop: 2 },
  section: { marginBottom: 20 },
  sectionTitle: { fontSize: 18, fontWeight: '600', marginBottom: 8 },
  bio: { fontSize: 15, color: '#333', lineHeight: 22 },
  portfolioImage: {
    width: '100%',
    height: 200,
    borderRadius: 12,
    marginBottom: 12,
  },
  callButton: {
    backgroundColor: '#34C759',
    padding: 18,
    borderRadius: 12,
    alignItems: 'center',
    marginTop: 12,
    marginBottom: 30,
  },
  callButtonText: { color: '#fff', fontSize: 18, fontWeight: '700' },
});
