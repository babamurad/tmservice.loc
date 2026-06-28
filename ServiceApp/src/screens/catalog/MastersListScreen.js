import { useState, useEffect } from 'react';
import {
  View,
  Text,
  FlatList,
  TouchableOpacity,
  StyleSheet,
  ActivityIndicator,
} from 'react-native';
import { api } from '../../api/client';

export default function MastersListScreen({ route, navigation }) {
  const { category, cityId } = route.params;
  const [masters, setMasters] = useState([]);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);

  useEffect(() => {
    navigation.setOptions({ title: category.name_ru });
    loadMasters();
  }, []);

  async function loadMasters() {
    try {
      const params = new URLSearchParams({ category_id: category.id, page });
      if (cityId) params.append('city_id', cityId);

      const data = await api(`/masters?${params}`);
      setMasters((prev) => [...prev, ...data.data]);
      setHasMore(data.current_page < data.last_page);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
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
      data={masters}
      contentContainerStyle={styles.list}
      renderItem={({ item }) => (
        <TouchableOpacity
          style={styles.card}
          onPress={() => navigation.navigate('MasterDetail', { master: item })}
        >
          <View style={styles.cardHeader}>
            <Text style={styles.name}>{item.user?.phone || 'Мастер'}</Text>
            <View
              style={[
                styles.dot,
                { backgroundColor: item.is_free ? '#34C759' : '#FF3B30' },
              ]}
            />
          </View>
          <Text style={styles.bio} numberOfLines={2}>
            {item.bio || 'Нет описания'}
          </Text>
          {item.city && <Text style={styles.meta}>📍 {item.city.name_ru}</Text>}
        </TouchableOpacity>
      )}
      keyExtractor={(item) => String(item.id)}
      onEndReached={() => hasMore && loadMasters()}
      onEndReachedThreshold={0.5}
      ListEmptyComponent={
        <View style={styles.center}>
          <Text style={styles.empty}>Мастера не найдены</Text>
        </View>
      }
    />
  );
}

const styles = StyleSheet.create({
  center: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  list: { padding: 12 },
  card: {
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: 16,
    marginBottom: 12,
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 3,
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  name: { fontSize: 16, fontWeight: '600' },
  dot: { width: 12, height: 12, borderRadius: 6 },
  bio: { fontSize: 14, color: '#666', marginBottom: 6 },
  meta: { fontSize: 12, color: '#999' },
  empty: { fontSize: 16, color: '#999' },
});
