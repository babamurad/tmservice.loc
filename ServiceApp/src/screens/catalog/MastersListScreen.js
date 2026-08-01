import { useState, useEffect } from 'react';
import { View, Text, FlatList, StyleSheet, ActivityIndicator } from 'react-native';
import { api } from '../../api/client';
import ScreenContainer from '../../components/ScreenContainer';
import Card from '../../components/Card';
import CategoryIcon from '../../components/CategoryIcon';
import StatusBadge from '../../components/StatusBadge';
import RatingStars from '../../components/RatingStars';
import EmptyState from '../../components/EmptyState';
import { colors, spacing, typography } from '../../theme';

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
      <ScreenContainer style={styles.center}>
        <ActivityIndicator size="large" color={colors.primary} />
      </ScreenContainer>
    );
  }

  return (
    <ScreenContainer>
      <FlatList
        data={masters}
        contentContainerStyle={styles.list}
        renderItem={({ item }) => (
          <Card style={styles.card} onPress={() => navigation.navigate('MasterDetail', { master: item })}>
            <View style={styles.cardHeader}>
              <CategoryIcon category={item.category} size={20} />
              <View style={styles.cardHeaderInfo}>
                <Text style={typography.heading}>{item.user?.phone || 'Мастер'}</Text>
                {item.reviews_count > 0 && (
                  <RatingStars rating={item.avg_rating} reviewsCount={item.reviews_count} size={13} />
                )}
              </View>
              <StatusBadge isFree={item.is_free} compact />
            </View>
            <Text style={[typography.bodyMuted, styles.bio]} numberOfLines={2}>
              {item.bio || 'Нет описания'}
            </Text>
            {item.city && <Text style={typography.caption}>📍 {item.city.name_ru}</Text>}
          </Card>
        )}
        keyExtractor={(item) => String(item.id)}
        onEndReached={() => hasMore && loadMasters()}
        onEndReachedThreshold={0.5}
        ListEmptyComponent={
          <EmptyState icon="people-outline" title="Мастера не найдены" subtitle="Попробуйте выбрать другой город" />
        }
      />
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  center: { justifyContent: 'center', alignItems: 'center', backgroundColor: colors.bg },
  list: { padding: spacing.lg, gap: spacing.md },
  card: { padding: spacing.xl, borderRadius: 24, ...colors.shadows.medium },
  cardHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
    marginBottom: spacing.md,
  },
  cardHeaderInfo: { flex: 1, gap: 4 },
  bio: { marginBottom: spacing.md, lineHeight: 22 },
});
